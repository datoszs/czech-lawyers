#!/usr/bin/env python
# -*- encoding: utf-8 -*-
# coding=utf-8

"""
This program assigns cases to lawyers

according to the name of the decision and the Czech Bar Association
"""

__author__ = "Radim Jílek"
__copyright__ = "Copyright 2017, DATOS - data o spravedlnosti, z.s."
__license__ = "GNU GPL"

import re
import sys
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
    """
    load configuration from local 'neon' file

    :param path_to_neon: path to neon file with configuration
    :return: dict of values
    """
    with open(path_to_neon, 'r') as fd:
        config = neon.decode(fd.read())
    # print("Load config file...")
    return config


def connection(path_to_neon):
    """
    make coonection to the database

    :param path_to_neon: path to neon file with configuration
    :return: connection psycopg2 object
    """
    config = load_config(path_to_neon)
    dbal = config["dbal"]
    print("Conecting...")
    return psycopg2.connect(dbname=dbal["database"],
                            user=dbal["username"],
                            password=dbal["password"],
                            host=dbal["host"])


def print_statistic(tagger):
    """
    print computed statistics on standard output

    :param tagger: instance of 'tagger' object
    """
    print("----\n"
          "Causes: {}, unique advocates: {}\n"
          "Matched: {}, No matched: {}, Without changes: {}\n"
          "Bad: {}, Empty: {}\n"
          "Processed: {}, Failed: {}, Hited: {}\n"
          "Size of match_cache: {}, Size of no_match_cache: {}\n"
          "Compare full: {}, Compare normal: {}, Compare reverse: {}\n"
          "Compare only surname: {}\n".format(
            len(tagger.causes), len(tagger.advocates),
            tagger.matched, tagger.no_matched, tagger.without_changes,
            tagger.bad, tagger.empty,
            tagger.processed, tagger.failed, tagger.hit,
            len(tagger.match_cache), len(tagger.no_match_cache),
            tagger.cmp_full, tagger.cmp_normal, tagger.cmp_reverse, tagger.cmp_surname)
    )


class QueryDB(object):
    """
    The class that caters to the database and defines database queries

    """

    def __init__(self, cursor):
        self.cur = cursor

    def find_unique_advocates(self):
        """
        Find unique three 'degree_before, name, surname' in the database

        :return:
        """
        self.cur.execute(
                "SELECT concat(degree_before,'/',name,' ',surname) AS fullname, string_agg(DISTINCT advocate_id::text, ' ') AS advocate_id "
                "FROM advocate_info "
                "GROUP BY degree_before, name, surname "
                "HAVING array_length(array_agg(DISTINCT advocate_id), 1) = 1"
                "ORDER BY degree_before DESC"
                ";")
        return self.cur.fetchall()

    def find_for_advocate_tagging(self, court):
        """
        Find all cases to be tagged by an advocate
        """
        self.cur.execute(
                "SELECT *"
                "FROM \"case\""
                "WHERE court_id = %s AND id_case NOT IN (SELECT case_id FROM tagging_advocate WHERE is_final);" % court)
        return self.cur.fetchall()

    def insert_to_db(self, record):
        """
        insert new result to the database
        """
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
        """
        get last tagging of 'case' with cause_id

        :param cause_id: id of case
        :return: dict with information about last tagging for this case
        """
        self.cur.execute(
                "SELECT * "
                "FROM tagging_advocate "
                "WHERE case_id=%i "
                "ORDER BY inserted DESC "
                "LIMIT 1" % int(cause_id))
        return self.cur.fetchone()

    def insert_if_differs(self, new, old):
        """
        compare old and new result of tagging advocate

        :param new: result of new tagging
        :param old: result of last tagging
        :return: bool
        """
        if new and old:
            diff = []
            for key in ["advocate_id", "status", "case_id"]:
                old_val = str(old[key]) if old[key] is not None else "NULL"
                if str(new[key]) != old_val:
                    diff.append(True)
            # print(diff)
            # print(old)
            # print(new)
            if diff:
                self.insert_to_db(new)
                return True
            return False
        else:
            self.insert_to_db(new)
            return True


class NameCleaner:
    """
    This class takes care of string cleaning
    """

    @staticmethod
    def extract_name(string):
        """
        extract only name and surname from text

        :param string: text for extraction
        :return: extracted name and his length - tuple
        """
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
        """
        extract name(s) from complicated text

        :param raw_string: text for extraction
        :return: one or more names and type of extracted data
        """
        strings = raw_string.split(',')
        names = []
        for string in strings:
            string = string.replace(')', '').replace('(', '')
            name, length = self.extract_name(string)
            # print(name)
            if len(name) > 0 and name.lower() not in bad_word and length > 1:
                names.append(name)
        # print(names)
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
        # print(string.lower())
        """
        clear text with many spaces and other irrelevant information's

        :param string: text for cleaning
        :return: clear name
        """
        if string is None:
            return None, None
        if any(s in string.lower() for s in skip_word):
            return ["", "SKIP"]
        elif any(s in string.lower() for s in bad_word):
            return self.advance(string)

        # remove all after last '-'
        m = re.compile(r'^(.*)[-].*$').search(string)
        if m:
            result = m.group(1)
            if len(result) >= 12:
                string = result
        return string.strip(), "EXTRACT"

    @staticmethod
    def prepare_advocate(raw_string):
        """
        prepare variants of advocate name for later using
        - full name with degrees
        - only name and surname (in this order)
        - only surname and name (in this order)

        :param raw_string: advocate full name with degrees before separated by '/'
        :return:
        """
        parts = raw_string.split('/')
        # print(parts)
        normalize = list(filter(str.strip, parts))
        variants = Variants(
                " ".join(normalize),
                normalize[-1],
                " ".join(reversed(normalize[-1].split(" ")))
        )
        return variants


class AdvocateTagger(QueryDB):
    """
    The class includes the methods needed for the lawyer's case assignment process
    """

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
        self.cmp_full, self.cmp_normal, self.cmp_reverse, self.cmp_surname = (
                                                                                 0,) * 4
        self.processed, self.failed, self.hit = (0,) * 3
        self.no_match_cache, self.match_cache = {}, {}

    @staticmethod
    def get_name(off_data, court_id):
        """
        prepare information of name from structure specific for court

        :param off_data: structure of data
        :param court_id: id for specific court
        :return: full name of advocate
        """
        if court_id == 1:
            return (off_data["names"]).strip()
        elif court_id == 3:
            return " ".join([off_data["name"].strip(), off_data["surname"].strip()])
        else:
            return None

    @staticmethod
    def print_info(case, text, name, advocate_name, advocate_id, type_of_comparison):
        """
        print info with information about assign

        :param case: registry sign
        :param text: input text of process
        :param name: extracted name
        :param advocate_name: name of advocate which was assigned
        :param advocate_id: id of this advocate
        :param type_of_comparison: which method was used for comparison
        """
        print(
                "{} - '{}' -> {}".format(case["registry_sign"], text, name).encode())
        print("\t {}, {}, >> \"{}\". <<".format(
                advocate_name, advocate_id, type_of_comparison).encode())

    def prepare_tagging(self, cause, status, debug, advocate="NULL", role=2):
        """
        prepare record for saving from input result

        :param cause: dict with information's about case
        :param status: how ended process
        :param debug: information used for manual checking
        :param advocate: id of advocate
        :param role: system role for insert record - tagging
        :return: prepared record
        """
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
        """
        get best match or list of probably names

        :param matches: list of matches
        :param text: full variant of text (with degrees)
        :param name: name of advocate from court data
        :return: one or more the best matches
        """
        records = distance = None
        for i in range(0, threshold):
            if matches.get(i):
                records = matches.get(i)
                distance = i
                break
        # advocate_id, advocate_name, debug, status
        # variants, advocate_id
        if len(records) == 1:
            best = records[0]
            if "normal" in best[-1]:
                self.cmp_normal += 1
            elif "reverse" in best[-1]:
                self.cmp_reverse += 1
            elif "surname" in best[-1]:
                self.cmp_surname += 1
            return best[1], best[0].full, "was tagged from: %s (%s)" % (text, name), states["ok"], best[-1]
        elif len(records) < 6:
            result = []
            names = []
            for variants, advocate_id, note in records:
                if advocate_id not in result:
                    result.append(advocate_id)
                    names.append(variants.full)
            debug = "\"%s\" with levenshtein distance = %d" % (
                "; ".join(names), distance)
            return "NULL", debug, debug, states["no"], "mixed"
        else:
            return (None,) * 5

    def process_case(self, cause, text):
        """
        control process of comparison

        :param cause: cause for assignments
        :param text: full variant of text
        :return: bool
        """
        # check_cache record with the same name in match_cache
        exist = self.match_cache.get(text)
        name = self.cleaner.extract_name(text)[0].title()
        debug = advocate_name = None
        if exist is not None:
            new, advocate_name, status, type_of_comparison = exist
            new["case_id"] = cause["id_case"]
            if "normal" in type_of_comparison:
                self.cmp_normal += 1
            elif "reverse" in type_of_comparison:
                self.cmp_reverse += 1
            elif "surname" in type_of_comparison:
                self.cmp_surname += 1
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
                        matches.setdefault(distance, []).append(
                                (variants, advocate["advocate_id"], "contains normal - levenshtein"))
                # reverse variant of name
                if variants.reverse.title() in text:
                    distance = lev.distance(variants.reverse.title(), name)
                    if distance < threshold:
                        matches.setdefault(distance, []).append(
                                (variants, advocate["advocate_id"], "contains reverse - levenshtein"))
                # special compare only with surname (for ÚS)
                if self.court == 3:
                    if name in variants.normal:
                        distance = lev.distance(
                                variants.normal.split()[-1], name)
                        if distance < threshold:
                            matches.setdefault(distance, []).append(
                                    (variants, advocate["advocate_id"], "contains only surname - levenshtein"))

            if advocate_id:
                advocate_name = variants.full
                debug = "was tagged from: %s (%s)" % (text, name)
            elif matches:
                advocate_id, advocate_name, debug, status, type_of_comparison = self.choice_best_match(
                        matches, text, name)

            if advocate_id is not None:
                new = self.prepare_tagging(
                        cause, status, debug, advocate=advocate_id)
            else:
                return False
        self.match_cache.setdefault(
                text, (new, advocate_name, status, type_of_comparison))
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
        """
        Entry point of this class

        """
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
              "- third arg is path to 'Command' folder")
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
