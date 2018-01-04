# encoding=utf-8
#!/usr/bin/env python
# -*- encoding: utf-8 -*-

"""
This program assigns cases to lawyers

according to the name of the decision and the Czech Bar Association
"""

__author__ = "Radim Jílek"
__copyright__ = "Copyright 2017, DATOS - data o spravedlnosti, z.s."
__license__ = "GNU GPL"

import logging
import os
import re
import sys
from collections import OrderedDict, namedtuple
from datetime import datetime
from os.path import join

import neon
import psycopg2
from psycopg2 import extras

bad_word = [")", "agentura", "asociace", "advokátní", "koncipient"]
skip_word = ["sama", "§", "s.r.o.", "a.s.", "o.s.", "v.o.s.", "kancelář"]
states = dict(ok="processed", no="failed")
Variants = namedtuple('Variants', ["full", "normal", "reverse"])
config_local_neon_path = '/../Config/config.local.neon'
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
          "Processed: {} ({} %), Failed: {}, Hited: {}\n"
          "Size of match_cache: {}, Size of no_match_cache: {}\n"
          "Compare full: {}, Compare normal: {}, Compare reverse: {}\n"
          "Compare by IC: {}\n"
          "Repair records: {}".format(
            len(tagger.causes), len(tagger.advocates),
            tagger.counters["matched"], tagger.counters["no_matched"], tagger.counters["no_change"],
            tagger.counters["bad"], tagger.counters["empty"],
            tagger.counters["ok"], round(tagger.counters["ok"] / len(tagger.causes), 2) * 100, tagger.counters["failed"], tagger.counters["hit"],
            len(tagger.match_cache), len(tagger.no_match_cache),
            tagger.counters["full"], tagger.counters["normal"], tagger.counters["reverse"],
            tagger.counters["ic"], tagger.counters["repair"])
    )


def set_logging():
    """
    settings of logging
    """
    log_dir = join(os.path.dirname(os.path.abspath(__file__)), "log_tagger")
    #print(__file__)
    #print(os.path.dirname(__file__))
    #print(os.path.abspath(__file__))
    #print(os.path.dirname(os.path.abspath(__file__)))
    #print(log_dir)
    global logger
    logger = logging.getLogger(__file__)
    logger.setLevel(logging.DEBUG)
    #hash_id = datetime.now().strftime("%d-%m-%Y")
    #fh_d = logging.FileHandler(join(log_dir, __file__[0:-3] + "_" + hash_id + "_log_debug.txt"), mode="w",
    #                           encoding='utf-8')
    #fh_d.setLevel(logging.DEBUG)
    fh_i = logging.FileHandler(join(log_dir, os.path.basename(__file__[0:-3]) + "_log.csv"), mode="w",
                               encoding='utf-8')
    fh_i.setLevel(logging.INFO)
    # // create console handler
    ch = logging.StreamHandler()
    ch.setLevel(logging.INFO)
    # // create formatter and add it to the handlers
    formatter = logging.Formatter(
            u'%(message)s')
    ch.setFormatter(formatter)
    #fh_d.setFormatter(formatter)
    fh_i.setFormatter(formatter)
    # // add the handlers to logger
    #logger.addHandler(ch)
    #logger.addHandler(fh_d)
    logger.addHandler(fh_i)
    logger.info("audited_subject@description@reason")
    return logger


class QueryDB(object):
    """
    The class that caters to the database and defines database queries

    """

    def __init__(self, cursor):
        self.cur = cursor

    def find_unique_advocates(self):
        """
        Find unique 'name, surname' in the database

        :return:
        """
        self.cur.execute(
                "SELECT concat(name,' ',surname) AS fullname, string_agg(DISTINCT advocate_id::text, ' ') AS advocate_id "
                "FROM advocate_info "
                "GROUP BY name, surname "
                "HAVING array_length(array_agg(DISTINCT advocate_id), 1) = 1"
                ";")
        return self.cur.fetchall()

    def find_for_advocate_tagging(self, court):
        """
        Find all cases to be tagged by an advocate
        """
        self.cur.execute(
                "SELECT *"
                "FROM \"vw_case_for_advocates\""
                "WHERE court_id = %s AND id_case NOT IN (SELECT case_id FROM vw_latest_tagging_advocate WHERE is_final);" % court)
        # AND year=2010
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
                "FROM vw_latest_tagging_advocate "
                "WHERE case_id=%i"
                "ORDER BY inserted DESC "
                "LIMIT 1" % int(cause_id))
        return self.cur.fetchone()

    # for tagging special -start
    def get_last_document(self, cause_id):
        """
        fgfgf
        """
        self.cur.execute(
                "SELECT * "
                "FROM document "
                "WHERE case_id=%i "
                "ORDER BY decision_date DESC "
                "LIMIT 1" % int(cause_id))
        return self.cur.fetchone()

    def get_extra(self, document_id, court_id):
        """
        get
        """
        court = "document_law_court" if court_id == 3 else "document_supreme_administrative_court"
        self.cur.execute(
                "SELECT * "
                "FROM  {} "
                "WHERE document_id={} ".format(court, document_id))
        return self.cur.fetchone()

    # for tagging special - end

    def get_advocate_by_ic(self, ic):
        """
        get advocate record with searching IC

        :param ic: IC from court data
        :return: founded advocate(s)
        """
        self.cur.execute(
                "SELECT id_advocate "
                "FROM advocate "
                "WHERE registration_number='{0!s}' ".format(str(ic)))
        return self.cur.fetchall()

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
            #print(old)
            #print(new)
            if diff:
                self.insert_to_db(new)  # /* DEBUG */
                return True
            return False
        else:
            self.insert_to_db(new)  # /* DEBUG */
            return True


class NameCleaner:
    """
    This class takes care of string cleaning
    """

    @staticmethod
    def contains_skip_words(text):
        return any(sw in text.lower() for sw in skip_word)

    @staticmethod
    def contains_bad_words(text):
        return any(bw in text.lower() for bw in bad_word)

    def extract_name(self, text):
        """
        extract only name and surname from text

        :param text: text for extraction
        :return: extracted name and his length - tuple
        """
        if text is None:
            raise ValueError("Input argument is None")
        if type(text) is not str:
            raise ValueError("Input argument is not string")
        if len(text) == 0:
            raise ValueError("Input argument is empty string")

        text = text.replace(".", ". ")
        text = text.replace(',', " ,")

        tails = text.split()
        only_name = [t for t in tails if '.' not in t]

        #print(text, only_name)
        name = []
        for n in only_name:
            first_is_upper = n[0].isupper()

            if first_is_upper and len(n) > 2 and n is not "MBA" and not self.contains_bad_words(n.lower()):
                name.append(n.strip().replace(',', ''))

        extracted_name = " ".join(list(name))

        length = len(name)

        return extracted_name.title(), length

    def advance(self, text):
        """
        extract name(s) from complicated text

        :param text: text for extraction
        :return: one or more names and type of extracted data
        """
        parts = text.split(',')
        names = []
        for part in parts:
            part = part.replace(')', '').replace('(', '')
            name, length = self.extract_name(part)
            # print(name)
            if len(name) > 0 and name.lower() not in bad_word and length > 1:
                names.append(name)
        # print(names)

        if len(names) > 1:
            raise AttributeError("This text contains more names: '%s'." % text)
        elif len(names) == 0:
            raise AttributeError("This text not contains correct names: '%s'." % text)

        extracted_name = names[0]

        return extracted_name

    def clear(self, text):
        # print(text.lower())
        """
        clear text with many spaces and other irrelevant information's

        :param text: text for cleaning
        :return: clear name
        """
        if text is None:
            raise ValueError("Input argument is None.")
        if type(text) is not str:
            raise ValueError("Input argument is not string.")
        if len(text) == 0:
            raise ValueError("Input argument is empty string.")

        if self.contains_skip_words(text):
            raise AttributeError("This text contains skip words: '%s'." % text)
        elif self.contains_bad_words(text):
            return self.advance(text), "EXTRACT"

        # remove all after last '-' or ','
        match = re.compile(r'^([^,-]+).*$', re.UNICODE).search(text)
        #print(match)
        clean_text = None
        if match:
            result = match.group(1)
            if len(result) >= 6:
                clean_text = result.strip()
        return clean_text, "EXTRACT"

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
        #print(parts)  # /* DEBUG */
        normalize = list(filter(str.strip, parts))
        variants = Variants(
                " ".join(normalize),
                normalize[-1],
                " ".join(reversed(normalize[-1].split(" ")))
        )
        #print(variants)  # /* DEBUG */
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
        self.advocates_names = [item['fullname'] for item in self.advocates]
        t1 = datetime.now()
        print(t1 - t0)
        self.cleaner = NameCleaner()
        keys = ["bad", "empty", "repair", "matched", "no_matched", "no_change", "m_full", "full", "normal", "reverse",
                "ic", "ok", "failed", "hit"]
        self.counters = dict(zip(keys, [0]*len(keys)))
        self.no_match_cache, self.match_cache = {}, {}

    def get_name_from_document_info(self, cause_id):
        """
        not used
        :param cause_id:
        :return:
        """
        last_document = self.get_last_document(cause_id)
        if last_document is None:
            return None
        #print(last_document)
        string = self.get_extra(last_document["id_document"], self.court)
        if string is None or string["names"] is None:
            return None
        #print(string["names"], len(string["names"]), len(string["names"]) == 1)

        if len(string["names"]) == 1:
            return string["names"]["1"]
        else:
            return None

    def get_name(self, cause):
        """
        prepare information of name from structure specific for court

        :param cause: structure of cause
        :return: full name of advocate
        """
        if self.court == 1:
            try:
                return (cause["official_data"][0]["names"]).strip()
            except KeyError:
                return None
        elif self.court == 3:
            return " ".join([cause["official_data"][0]["name"].strip(),
                             cause["official_data"][0]["surname"].strip()])  # from court table
            # return self.get_name_from_document_info(cause["id_case"])  # from text
        else:
            return None

    def add_to_statistics(self, name_of_counter):
        """
        Add data to global statistics
        :param type_of_comparison:
        """
        if name_of_counter not in self.counters.keys():
            raise ValueError("Counter s názvem '%s' neexistuje." % name_of_counter)
        self.counters[name_of_counter] += 1
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
                "{} - '{}' -> {}".format(case["registry_sign"], text, name).encode())  # /* DEBUG */
        print("\t {}, {}, >> \"{}\". <<".format(
                advocate_name, advocate_id, type_of_comparison).encode())  # /* DEBUG */

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

    def process_case(self, cause, text, ic):
        """
        control process of comparison

        :param ic: identification number of advocate from cause
        :param cause: cause for assignments
        :param text: full variant of text from court table
        :return: bool
        """

        if text is None and ic is None:
            return False

        # check_cache record with the same name in match_cache
        if text is not None:
            exist = self.match_cache.get(text)
        else:
            exist = None

        name = self.cleaner.extract_name(text)[0].title()

        debug = advocate_name = None
        found = False
        # /todo priority?
        if exist and ic is None:
            new, advocate_name, status, type_of_comparison = exist
            new["case_id"] = cause["id_case"]
            type_of_comparison = "now exist"
            self.add_to_statistics("hit")
            found = True
        elif ic:
            advocates = self.get_advocate_by_ic(ic)  # may be more
            # print(text, cause["id_case"], ic, advocates) # /* DEBUG */
            # input(":-)")
            if advocates:
                advocate_id = advocates[0]["id_advocate"]
                type_of_comparison = "by IC"
                status = states["ok"]
                self.add_to_statistics("ic")
                new = self.prepare_tagging(
                        cause, status, ic, advocate=advocate_id)
                found = True
                # print(ic, found) # /* DEBUG */

        if not found:
            if text is None or name is None:
                return False
            type_of_comparison, status, variants, advocate_id = (None,) * 4
            matches = {}
            for advocate in self.advocates:
                variants = self.cleaner.prepare_advocate(advocate["fullname"])
                # /todo priority? [priority/type][distance] = best matches
                # compare full variant name with 'text' - is text the same?
                if variants.full == text:
                    status = states["ok"]
                    type_of_comparison = "full compare"
                    advocate_id = advocate["advocate_id"]
                    self.add_to_statistics("full")
                    break  # /todo is it already?
                # compare full variant name with extracted name
                if variants.full == name:
                    status = states["ok"]
                    type_of_comparison = "normal compare"
                    advocate_id = advocate["advocate_id"]
                    self.add_to_statistics("normal")
                    break
                # reverse variant of name
                if variants.reverse == name:
                    status = states["ok"]
                    type_of_comparison = "reverse compare"
                    advocate_id = advocate["advocate_id"]
                    self.add_to_statistics("reverse")
                    break

            if advocate_id:
                # found only one match
                advocate_name = variants.full
                debug = "was tagged from: %s (%s)" % (text, name)
            elif matches:
                pass
                #advocate_id, advocate_name, debug, status, type_of_comparison = self.choice_best_match(
                #        matches, text, name)

            if advocate_id is not None:
                new = self.prepare_tagging(
                        cause, status, debug, advocate=advocate_id)
            else:
                return False
        if "ic" not in type_of_comparison.lower():
            self.match_cache.setdefault(
                text, (new, advocate_name, status, type_of_comparison))
        if self.insert_if_differs(new, self.get_last_tagging(cause["id_case"])):
            if new["status"] == states["ok"]:
                self.add_to_statistics("ok")
                logger.info(
                        "{}@Create new advocate tagging of case [{}] to advocate [{}] with ID [{}]. Note [{}].@{}".format(
                                "CASE_TAGGING",
                                cause["registry_sign"],
                                advocate_name,
                                new["advocate_id"],
                                new["debug"],
                                "SCHEDULED"))
            else:
                self.add_to_statistics("failed")
            self.print_info(cause, text, name, advocate_name,
                            new["advocate_id"], type_of_comparison)
            return True
        else:
            return None

    def repair(self, last_tagging, cause):
        """

        :param last_tagging:
        """
        # print(last_tagging) # /* DEBUG */
        if last_tagging and last_tagging["advocate_id"]:
            empty_record = self.prepare_tagging(cause, status="failed", debug="no match")
            self.add_to_statistics("repair")
            self.insert_to_db(empty_record)

    def run(self):
        """
        Entry point of this class

        """
        bad = []
        print("Matching...")
        for cause in self.causes:
            last_tagging = self.get_last_tagging(cause["id_case"])
            # if has last tagging and not found match, must insert empty record for replace old tagging
            official_data = cause["official_data"]
            # only one name for every cause
            if official_data:
                if len(official_data) != 1:
                    self.add_to_statistics("bad")
                    self.repair(last_tagging, cause)
                    continue
                try:
                    pin = official_data[0]["PIN"]
                    ic = pin if pin != "NA" and len(pin) == 8 else None
                    #ic = None
                except KeyError:
                    ic = None

                string = self.get_name(cause)

                try:
                    string, status = self.cleaner.clear(string)
                    #print(string, status)
                except (AttributeError, ValueError) as ex:
                    self.add_to_statistics("bad")
                    print("Exception: {}".format(ex).encode())
                    continue

                name, length = self.cleaner.extract_name(string) if string is not None else (None, None)
                if status in ["WRONG", "MORE_NAMES", "SKIP"]:
                    self.no_match_cache[string] = 1
                    self.add_to_statistics("bad")
                    bad.append((string, status))
                    self.repair(last_tagging, cause)
                    continue
                if name == '' or string == '':
                    self.add_to_statistics("bad")
                    self.repair(last_tagging, cause)
                    continue
                try:
                    if self.no_match_cache[name]:
                        self.add_to_statistics("no_matched")
                        self.repair(last_tagging, cause)
                        continue
                    elif self.no_match_cache[string]:
                        self.add_to_statistics("no_matched")
                        self.repair(last_tagging, cause)
                        continue
                except KeyError:
                    pass

                ret = self.process_case(cause, string, ic=ic)
                if ret is True:  # insert
                    self.add_to_statistics("matched")
                elif ret is None:  # not insert
                    self.add_to_statistics("no_change")
                else:  # not match
                    self.add_to_statistics("no_matched")
                    self.no_match_cache[name] = 1
                    self.repair(last_tagging, cause)
            else:
                self.add_to_statistics("empty")
                self.repair(last_tagging, cause)
        self.conn.commit()


if __name__ == "__main__":
    if len(sys.argv) == 4:
        # print(sys.argv)
        job_id = sys.argv[1]
        court = sys.argv[2]
        path = sys.argv[3] + config_local_neon_path
    else:
        print("Usage:\n"
              "- first arg is jobID\n"
              "- second arg is courtID\n"
              "- third arg is path to 'Command' folder")
        sys.exit(-1)
    t0 = datetime.now()
    print(t0.replace(microsecond=0))
    logger = set_logging()
    tagger = AdvocateTagger(path, court, job_id)
    tagger.run()
    #pprint(tagger.no_match_cache)
    #print(tagger.no_match_cache)

    print_statistic(tagger)
    t2 = datetime.now()
    # print(t2.replace(microsecond=0))
    print("Duration: {}".format((t2 - t0)))
    sys.exit(0)
