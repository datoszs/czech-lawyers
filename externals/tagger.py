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
states = dict(ok="processed", no="failed")
Variants = namedtuple('Variants', ["full", "normal", "reverse"])
threshold = 5


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


def print_statistic(tagger):
    print("----\n"
          "Causes: {}, unique advocates: {}\n"
          "Matched: {}, No matched: {}, Without changes: {}\n"
          "Bad: {}, Empty: {}\n"
          "Processed: {}, Failed: {}, Hited: {}\n"
          "Size of match_cache: {}, Size of no_match_cache: {}\n"
          "Compare full: {}, Contains normal or reverse: {}".format(
            len(tagger.causes), len(tagger.advocates),
            tagger.matched, tagger.no_matched, tagger.without_changes,
            tagger.bad, tagger.empty,
            tagger.processed, tagger.failed, tagger.hit,
            len(tagger.match_cache), len(tagger.no_match_cache),
            tagger.cmp_full, tagger.cont_normal)
    )


class QueryDB(object):
    def __init__(self, cursor):
        self.cur = cursor

    def find_unique_advocates(self):
        self.cur.execute(
            "SELECT concat(degree_before,'/',name,' ',surname) AS fullname, string_agg(DISTINCT advocate_id::text, ' ') AS advocate_id "
            "FROM advocate_info "
            "GROUP BY degree_before, name, surname "
            "HAVING array_length(array_agg(DISTINCT advocate_id), 1) = 1"
            "ORDER BY degree_before DESC"
            ";")
        return self.cur.fetchall()

    def find_for_advocate_tagging(self, court):
        self.cur.execute(
                "SELECT *"
                "FROM \"case\""
                "WHERE court_id = %s AND id_case NOT IN (SELECT case_id FROM tagging_advocate WHERE is_final);" % court)
        return self.cur.fetchall()

    def insert_to_db(self, record):
        self.cur.execute(
            "INSERT INTO tagging_advocate ({}) VALUES ({},{},'{}',{},'{}',{},{},{},{})".format(
                    ",".join(record.keys()),
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
        self.cur.execute(
                "SELECT * "
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
        return string.strip(), "EXTRACT"

    @staticmethod
    def prepare_advocate(raw_string):
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
        self.court = int(court_id)
        self.job = job_id
        self.conn = connection(path_to_neon)
        self.cursor = self.conn.cursor(cursor_factory=extras.RealDictCursor)
        super().__init__(self.cursor)
        self.causes = self.find_for_advocate_tagging(court_id)
        self.advocates = self.find_unique_advocates()
        t1 = datetime.now()
        print(t1 - t0)
        self.cleaner = NameCleaner()
        self.bad, self.empty = 0, 0
        self.matched, self.no_matched, self.without_changes = (0,) * 3
        self.cmp_full, self.cont_normal = (0, 0)
        self.processed, self.failed, self.hit = (0, ) * 3
        self.no_match_cache, self.match_cache = {}, {}

    @staticmethod
    def get_name(off_data, court_id):
        if court_id == 1:
            return (off_data["names"]).strip()
        elif court_id == 3:
            return " ".join([off_data["name"].strip(), off_data["surname"].strip()])
        else:
            return None

    @staticmethod
    def print_info(case, text, name, advocate_name, advocate_id, type_of_comparison):
        print("{} - '{}' -> {}".format(case["registry_sign"], text, name).encode())
        print("\t {}, {}, >> \"{}\". <<".format(advocate_name, advocate_id, type_of_comparison).encode())

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

    def choice_best_match(self, matches, text, name):
        for i in range(0, threshold):
            if matches.get(i):
                records = matches.get(i)
                distance = i
                break
        # advocate_id, advocate_name, debug, status
        # variants, advocate_id
        if len(records) == 1:
            best = records[0]
            self.cont_normal += 1
            return best[1], best[0].full, "was tagged from: %s (%s)" % (text, name), states["ok"], best[-1]
        elif len(records) < 6:
            result = []
            names = []
            for variants, advocate_id, note in records:
                if advocate_id not in result:
                    result.append(advocate_id)
                    names.append(variants.full)
            debug = "\"%s\" with levenshtein distance = %d" % ("; ".join(names), distance)
            return "NULL", debug, debug, states["no"], "mixed"
        else:
            return (None,) * 5

    def process_case(self, cause, text):
        # check_cache record with the same name in match_cache
        exist = self.match_cache.get(text)
        name = self.cleaner.extract_name(text)[0].title()
        if exist is not None:
            new, advocate_name, status = exist
            new["case_id"] = cause["id_case"]
            type_of_comparison = "now exist"
            self.hit += 1
        else:
            type_of_comparison, status, variants, advocate_id = None, None, None, None
            matches = {}
            for advocate in self.advocates:
                variants = self.cleaner.prepare_advocate(advocate["fullname"])
                # compare full variant name (with degrees) with 'text'
                if variants.full == text:
                    self.cmp_full += 1
                    status = states["ok"]
                    type_of_comparison = "full compare"
                    advocate_id = advocate["advocate_id"]
                    break
                # normal variant of name
                if variants.normal.title() in text:
                    distance = lev.distance(variants.normal.title(), name)
                    if distance < threshold:
                        matches.setdefault(distance, []).append((variants, advocate["advocate_id"], "contains normal - levenshtein"))
                # reverse variant of name
                if variants.reverse.title() in text:
                    distance = lev.distance(variants.reverse.title(), name)
                    if distance < threshold:
                        matches.setdefault(distance, []).append((variants, advocate["advocate_id"], "contains reverse - levenshtein"))
                # special compare only with surname
                if self.court == 3:
                    if name in variants.normal:
                        distance = lev.distance(variants.normal.split()[-1], name)
                        if distance < threshold:
                            matches.setdefault(distance, []).append((variants, advocate["advocate_id"], "contains only surname - levenshtein"))

            if advocate_id:
                advocate_name = variants.full
                debug = "was tagged from: %s (%s)" % (text, name)
            elif matches:
                advocate_id, advocate_name, debug, status, type_of_comparison = self.choice_best_match(matches, text, name)

            if advocate_id is not None:
                new = self.prepare_tagging(cause, status, debug, advocate=advocate_id)
            else:
                return False
        self.match_cache.setdefault(text, (new, advocate_name, status))
        if self.insert_if_differs(new, self.get_last_tagging(cause["id_case"])):
            if new["status"] == states["ok"]:
                self.processed += 1
            else:
                self.failed += 1
            self.print_info(cause, text, name, advocate_name,
                            new["advocate_id"], type_of_comparison)
            return True
        else:
            return None

    def run(self):
        bad = []
        for cause in self.causes:
            official_data = cause["official_data"]
            if official_data and len(official_data) == 1:
                string = self.get_name(official_data[0], self.court)
                string, status = self.cleaner.clear(string)
                name = self.cleaner.extract_name(string)
                if status in ["WRONG", "MORE_NAMES", "SKIP"]:
                    self.no_match_cache[string] = 1
                    self.bad += 1
                    bad.append((string, status))
                    continue
                try:
                    if self.no_match_cache[name]:
                        self.no_matched += 1
                        continue
                    elif self.no_match_cache[string]:
                        self.no_matched += 1
                        continue
                except KeyError:
                    pass

                ret = self.process_case(cause, string)
                if ret is True:  # insert
                    self.matched += 1
                elif ret is None:  # not insert
                    self.without_changes += 1
                else:  # not match
                    self.no_matched += 1
                    self.no_match_cache[name] = 1
            else:
                self.empty += 1
        self.conn.commit()
        # print(bad, len(bad))

if __name__ == "__main__":
    if len(sys.argv) == 4:
        # print(sys.argv)
        job_id = sys.argv[1]
        court = sys.argv[2]
        path = sys.argv[3] + '/../Config/config.local.neon'
    else:
        print("Usage:\n"
              "- first arg is jobID\n"
              "- second arg is courtID\n"
              "- third arg is path to Command folder")
        sys.exit(-1)
    t0 = datetime.now()
    print(t0.replace(microsecond=0))
    tagger = AdvocateTagger(path, court, job_id)
    tagger.run()
    print_statistic(tagger)
    t2 = datetime.now()
    # print(t2.replace(microsecond=0))
    print("Duration: {}".format((t2 - t0)))
    sys.exit(0)
