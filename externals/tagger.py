#!/usr/bin/env python
# -*- encoding: utf-8 -*-
# coding=utf-8

import json
import os
import re
import sys
import time
from collections import namedtuple, OrderedDict
from datetime import datetime

import Levenshtein as lev
import neon
import psycopg2
from psycopg2 import extras

bad_word = [")", "agentura", "asociace", "advokátní", "koncipient"]
skip_word = ["sama", "§", "s.r.o.", "a.s.", "o.s.", "v.o.s.", "kancelář"]
states = dict(ok="processed", fuzzy="fuzzy", no="failed")
Variants = namedtuple('Variants', ["full", "normal", "reverse"])


def load_config(path_to_neon):
    with open(path_to_neon, 'r') as fd:
        config = neon.decode(fd.read())
    return config


def connection(path_to_neon):
    config = load_config(path_to_neon)
    dbal = config["dbal"]
    return psycopg2.connect(dbname=dbal["database"],
                            user=dbal["username"],
                            password=dbal["password"],
                            host=dbal["host"])


def get_status(type_of_comparison):
    if "contains" in type_of_comparison:
        return states["fuzzy"]
    elif "compare" in type_of_comparison:
        return states["ok"]
    else:
        return states["no"]


class QueryDB(object):
    def __init__(self, cursor):
        self.cur = cursor

    def find_unique_advocates(self):
        self.cur.execute(
            "SELECT concat(degree_before,'/',name,' ',surname) AS fullname, string_agg(DISTINCT advocate_id::text, ' ') AS advocate_id "
            "FROM advocate_info "
            "GROUP BY degree_before, name, surname "
            "HAVING array_length(array_agg(DISTINCT advocate_id), 1) = 1;")
        return self.cur.fetchall()

    def find_for_advocate_tagging(self, court):
        self.cur.execute("SELECT *"
                         "FROM \"case\""
                         "WHERE court_id = %s AND id_case NOT IN (SELECT case_id FROM tagging_advocate WHERE is_final);" % court)
        return self.cur.fetchall()

    def insert_to_db(self, record):
        self.cur.execute(
            "INSERT INTO tagging_advocate (%s) VALUES (%s,%s,'%s',%s,'%s',%s,%s,%s,%i)" % (",".join(record.keys()),
                                                                                           record["document_id"],
                                                                                           record["advocate_id"],
                                                                                           record["status"],
                                                                                           record["is_final"],
                                                                                           record["debug"],
                                                                                           record["inserted"],
                                                                                           record["inserted_by"],
                                                                                           record["job_run_id"],
                                                                                           record["case_id"]
                                                                                           ))

    def get_last_tagging(self, cause_id):
        self.cur.execute("SELECT * "
                         "FROM tagging_advocate "
                         "WHERE case_id=%i "
                         "ORDER BY inserted DESC "
                         "LIMIT 1" % int(cause_id))
        return self.cur.fetchone()

    def insert_if_differs(self, new, old):
        if new and old:
            diff = []
            for key in ["advocate_id", "status", "case_id"]:
                old_val = str(old[key]) if old[key] is not None else "NULL"
                if str(new[key]) != old_val:
                    diff.append(True)
            #print(diff)
            #print(old)
            #print(new)
            if diff:
                self.insert_to_db(new)
                return True
            return False
        else:
            self.insert_to_db(new)
            return True


class NameCleaner:
    @staticmethod
    def extract_name(string):
        string = string.replace(".", ". ")
        string = string.replace(',', " ,")

        tails = string.split()
        only_name = [t for t in tails if '.' not in t]
        #print(string, only_name)
        name = []
        for n in only_name:
            m = n[0].isupper()

            if m and len(n) > 2 and n is not "MBA" and n.lower() not in bad_word:
                name.append(n.strip().replace(',', ''))

        status = "OK"
        extracted_name = " ".join(list(name))
        length = len(name)

        if extracted_name.isupper():
            print(extracted_name)
        if length > 3:
            status = "WARNING"
        elif length < 2:
            status = "WRONG"
        return extracted_name, length

    def advance(self, raw_string):
        strings = raw_string.split(',')
        names = []
        for string in strings:
            string = string.replace(')', '').replace('(', '')
            name, length = self.extract_name(string)
            #print(name)
            if len(name) > 0 and name.lower() not in bad_word and length > 1:
                names.append(name)
        #print(names)
        status = "OK"
        if len(names) > 1:
            status = "MORE_NAMES"
            string = " | ".join(list(names))
        elif len(names) == 1:
            string = names[0]
            status = "OK"
        else:
            status = "EMPTY"
            string = ""
        return string, status

    def clear(self, string):
        #print(string.lower())
        if string is None:
            return None, None
        if any(s in string.lower() for s in skip_word):
            return ["", "SKIP"]
        elif any(s in string.lower() for s in bad_word):
            return self.advance(string)

        m = re.compile(r'^(.*)[-].*$').search(string)  # remove all after last ','
        if m:
            result = m.group(1)
            if len(result) >= 12:
                string = result
        return string, "EXTRACT"

    def prepare_advocate(self, raw_string):
        parts = raw_string.split('/')
        #print(parts)
        normalize = list(filter(str.strip, parts))
        variants = Variants(
            " ".join(normalize),
            normalize[-1],
            " ".join(reversed(normalize[-1].split(" ")))
        )
        return variants


class AdvocateTagger(QueryDB):
    def __init__(self, path_to_neon, court_id, job_id):
        self.court = court_id
        self.job = job_id
        self.conn = connection(path_to_neon)
        self.cursor = self.conn.cursor(cursor_factory=extras.RealDictCursor)
        super().__init__(self.cursor)
        self.causes = self.find_for_advocate_tagging(court_id)
        self.advocates = self.find_unique_advocates()
        t1 = datetime.utcnow()
        print(t1 - t0)
        self.cleaner = NameCleaner()
        self.matched, self.no_matched = 0, 0
        self.cmp_full, self.cont_full, self.cmp_normal, self.cont_normal = (0, 0, 0, 0)
        self.no_match_cache, self.match_cache = {}, {}

    @staticmethod
    def get_name(off_data, court_id):
        if court_id == 1:
            return (off_data["names"]).strip()
        elif court_id == 3:
            return " ".join([off_data["name"].strip(), off_data["surname"].strip()])
        else:
            return None

    def print_info(self, case_name, text, name, name_full, advocate_id, type_of_comparison):
        print(U"%s - '%s' -> %s" % (case_name, text, name))
        print(U"\t %s, %s, >> \"%s\" <<" % (name_full, advocate_id, type_of_comparison))

    def prepare_tagging(self, cause, status, debug, advocate="NULL", role=2):
        record = OrderedDict([
            ("document_id", "NULL"),
            ("advocate_id", advocate),
            ("status", '%s' % status),
            ("is_final", "false"),
            ("debug", '%s' % debug),
            ("inserted", "now()"),
            ("inserted_by", int(role)),
            ("job_run_id", int(self.job)),
            ("case_id", int(cause["id_case"]))
        ])
        return record

    def full_match(self, for_match, text):
        if for_match == text:
            self.cmp_full += 1
            return "full compare - dummy"
        elif for_match in text:
            self.cont_full += 1
            return "full contains - dummy"
        else:
            return None

    def prepare_debug(self, matches, name):
        result = []
        #print(matches)
        for names, advocate, distance, type_of_comparison in matches.values():
            #print("\t", names.full, distance, ",", advocate, "-", type_of_comparison)
            result.append(names.full)
        return "; ".join(result), "NULL"

    def process_case(self, cause, text):
        try:
            exist = self.match_cache[text]
            if self.insert_if_differs(exist[0], self.get_last_tagging(cause["id_case"])):
                self.print_info(
                    cause["registry_sign"],
                    text,
                    self.cleaner.extract_name(text)[0],
                    exist[1],
                    exist[0]["advocate_id"],
                    "now exist"
                )
                return True
        except KeyError:
            pass
        type_of_comparison, variants, advocate_id = None, None, None
        name = self.cleaner.extract_name(text)[0].title()
        full_matches, matches = {}, {}
        for advocate in self.advocates:
            variants = self.cleaner.prepare_advocate(advocate["fullname"])
            # compare full variant name (with degrees) with 'text'
            type_of_comparison = self.full_match(variants.full.lower(), text.lower())
            if type_of_comparison is not None:
                advocate_id = advocate["advocate_id"]
                full_matches[advocate_id] = (variants, advocate_id, 0, type_of_comparison)
            # normal variant of name
            distance = lev.distance(variants.normal.title(), name)
            if variants.normal.title() in text:
                if distance < 4:
                    advocate_id = advocate["advocate_id"]
                    matches[advocate_id] = (variants, advocate_id, distance, "contains normal - levenshtein")
            # reverse variant of name
            distance = lev.distance(variants.reverse.title(), name)
            if variants.reverse.title() in text:
                if distance < 4:
                    advocate_id = advocate["advocate_id"]
                    matches[advocate_id] = (variants, advocate_id, distance, "contains reverse - levenshtein")
        debug = text
        if full_matches and len(full_matches) == 1:
            variants, advocate_id, distance, type_of_comparison = list(full_matches.values())[0]
        elif full_matches and len(full_matches) < 4:
            debug, advocate_id = self.prepare_debug(full_matches, name)
            type_of_comparison = "more matches"
        elif matches and len(matches) == 1:
            variants, advocate_id, distance, type_of_comparison = list(matches.values())[0]
            self.cmp_normal += 1
        elif matches and len(matches) < 4:
            debug, advocate_id = self.prepare_debug(matches, name)
            type_of_comparison = "more matches"
        else:
            return False
        if type_of_comparison is None:
            return False
        status = get_status(type_of_comparison)
        if status == "failed":
            new = self.prepare_tagging(cause, status, debug)
            if self.insert_if_differs(new, self.get_last_tagging(cause["id_case"])):
                self.print_info(cause["registry_sign"], text, name, debug,
                                '', type_of_comparison)
                return False
        else:
            new = self.prepare_tagging(cause, status, debug, advocate_id)
            if self.insert_if_differs(new, self.get_last_tagging(cause["id_case"])):
                self.print_info(cause["registry_sign"], text, name, variants.full,
                                advocate_id, type_of_comparison)
                self.match_cache[text] = (new, variants.full)
                return True

    def run(self):
        for cause in self.causes:
            official_data = cause["official_data"]
            if official_data and len(official_data) == 1:
                string = self.get_name(official_data[0], int(self.court))
                name, status = self.cleaner.clear(string)
                if status in ["WRONG", "MORE_NAMES", "SKIP"]:
                    self.no_match_cache[string] = 1
                    continue
                try:
                    if self.no_match_cache[name]:
                        continue
                    elif self.no_match_cache[string]:
                        continue
                except KeyError:
                    pass
                # TODO osetrit rozliseni nevlozeni od nenalezeni
                if self.process_case(cause, string):
                    self.matched += 1
                else:
                    self.no_matched += 1
                    self.no_match_cache[name] = 1
        self.conn.commit()
        print("No full_match (%d):\n" % len(self.no_match_cache))
        #print(self.no_match_cache.keys())


if __name__ == "__main__":
    if len(sys.argv) == 4:
        print(sys.argv)
        job_id = sys.argv[1]
        court = sys.argv[2]
        path = sys.argv[3] + '/../Config/config.local.neon'
    else:
        print("Usage:\n"
            "- first arg is jobID\n"
            "- second arg is courtID\n"
            "- third arg is path to Command folder")
        sys.exit(-1)
    t0 = datetime.utcnow()
    print(t0)
    tagger = AdvocateTagger(path, court, job_id)
    tagger.run()
    print("Causes: %d, unique advocates: %d, Matched: %d, No matched: %d" % (
        len(tagger.causes), len(tagger.advocates), tagger.matched, tagger.no_matched))
    print("Compare full: %d, Contains full: %d\nCompare normal: %d" % (
    tagger.cmp_full, tagger.cont_full, tagger.cmp_normal))
    t2 = datetime.utcnow()
    print(t2)
    print("Duration: {}".format(t2 - t0))
