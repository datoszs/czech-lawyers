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
comparison_types = dict(ic="by IC", exist="now exist", normal="normal compare", reverse="reverse compare")
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
          "Processed: {} ({} %), Failed: {}\n"
          "Size of match_cache: {}, Size of no_match_cache: {}\n"
          "Hit possitive: {}, Hit negative: {}\n"
          "Compare full: {}, Compare normal: {}, Compare reverse: {}\n"
          "Compare by IC: {}\n"
          "Repair records: {}".format(
            len(tagger.causes), len(tagger.advocates),
            tagger.counters["matched"], tagger.counters["no_matched"], tagger.counters["no_change"],
            tagger.counters["bad"], tagger.counters["empty"],
            tagger.counters["ok"], round(tagger.counters["ok"] / len(tagger.causes), 2) * 100,
            tagger.counters["failed"],
            len(tagger.match_cache), len(tagger.no_match_cache),
            tagger.counters["hit_ok"], tagger.counters["hit_no"],
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


def write_to_log(registry_sign, advocate_name, advocate_id, debug):
    """

    :param registry_sign:
    :param advocate_name:
    :param advocate_id:
    :param debug:
    """
    logger.info(
            "{}@Create new advocate tagging of case [{}] to advocate [{}] with ID [{}]. Note [{}].@{}".format(
                    "CASE_TAGGING",
                    registry_sign,
                    advocate_name,
                    advocate_id,
                    debug,
                    "SCHEDULED"))


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
        match = re.compile(r'^([^-]+).*$', re.UNICODE).search(text)
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

    def prepare_name_for_searching(self, tagger, text):
        """
        :param tagger:
        :param text:
        :return:
        """

        try:
            clear_text, status = self.clear(text)
        except (AttributeError, ValueError) as ex:
            tagger.add_to_statistics("bad")
            print(u"Exception: {}".format(ex).encode())
            return None

        try:
            name, length = self.extract_name(clear_text)
        except (ValueError, AttributeError) as ex:
            tagger.add_to_statistics("bad")
            print(u"Exception: {}".format(ex).encode())
            return None

        return name


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
                "ic", "ok", "failed", "hit_ok", "hit_no"]
        self.counters = dict(zip(keys, [0] * len(keys)))
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

    @staticmethod
    def get_pin(official_data):
        """
        get PIN from database structure of official_data
        :param official_data:
        :return:
        """
        try:
            return official_data[0]["PIN"]
        except (KeyError, AttributeError):
            return None

    def look_into_cache(self, key, label="match"):
        """

        :param label:
        :return:
        :param key:
        :return:
        """
        cache = self.match_cache if label == "match" else self.no_match_cache
        try:
            return cache[key]
        except KeyError:
            return None, None

    def add_to_cache(self, key, advocate_id, debug, label="match"):
        """

        :param key:
        :param advocate_id:
        :param full_name:
        :param label:
        """
        cache = self.match_cache if label == "match" else self.no_match_cache
        if key not in cache.keys():
            cache[key] = (advocate_id, debug)

    def add_to_statistics(self, name_of_counter):
        """
        Add data to global statistics
        :param name_of_counter:
        :param type_of_comparison:
        """
        if name_of_counter not in self.counters.keys():
            raise ValueError("Counter s názvem '%s' neexistuje." % name_of_counter)
        self.counters[name_of_counter] += 1

    @staticmethod
    def print_info(case, text, name, advocate_id, type_of_comparison):
        """
        print info with information about assign

        :param case: registry sign
        :param text: input text of process
        :param name: extracted name
        :param advocate_name: name of advocate which was assigned
        :param advocate_id: id of this advocate
        :param type_of_comparison: which method was used for comparison
        """
        print(u"{} - '{}' -> {}\n\t {}, >> \"{}\". <<".format(case["registry_sign"], text, name, advocate_id,
                                                             type_of_comparison).encode())  # /* DEBUG */

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

    def find_advocate_by_ic(self, ic):
        """

        :param ic:
        :return:
        """
        advocate_id, full_name = self.look_into_cache(ic)
        if advocate_id:
            self.add_to_statistics("hit_ok")
            return advocate_id, states["ok"], "{} ({})".format(full_name, str(ic))
        advocate = self.get_advocate_by_ic(ic)
        if advocate:
            advocate_id = advocate[0]["id_advocate"]
            self.add_to_statistics("ic")
            return advocate_id, states["ok"], str(ic)
        else:
            return (None,) * 3

    def find_advocate_by_name(self, name):
        """

        :return:
        :param name:
        """
        status, variants, type_of_comparison = (None,) * 3
        advocate_id, full_name = self.look_into_cache(name)

        if advocate_id:
            self.add_to_statistics("hit_ok")
            return advocate_id, states["ok"], "{} ({})".format(name, comparison_types["exist"]), comparison_types[
                "exist"]
        for advocate in self.advocates:
            variants = self.cleaner.prepare_advocate(advocate["fullname"])
            advocate_id, status = (None,) * 2
            # compare full variant name with extracted name
            if variants.full == name:
                status = states["ok"]
                type_of_comparison = comparison_types["normal"]
                advocate_id = advocate["advocate_id"]
                self.add_to_statistics("normal")
                break
            # reverse variant of name
            if variants.reverse == name:
                status = states["ok"]
                type_of_comparison = comparison_types["reverse"]
                advocate_id = advocate["advocate_id"]
                self.add_to_statistics("reverse")
                break
        if not any([advocate_id, status]):
            raise ValueError("No match found", name)
        return advocate_id, status, "{} ({})".format(variants.full, type_of_comparison), type_of_comparison

    def process_case(self, cause, text, ic):
        """
        control process of comparison

        :param ic: identification number of advocate from cause
        :param cause: cause for assignments
        :param text: full variant of text from court table
        :return: bool
        """

        if not any([ic, text]):
            raise ValueError("Input data are empty - '{}', '{}'".format(text, ic), cause["registry_sign"])

        found = False
        status, debug, advocate_id, name, type_of_comparison = (None,) * 5
        last_tagging = self.get_last_tagging(cause["id_case"])
        # hledej podle IC - koukni do cache a nebo najdi
        if ic and len(ic) == 8:
            advocate_id, status, debug = self.find_advocate_by_ic(ic)
            type_of_comparison = comparison_types["ic"]
            found = all([advocate_id, status, debug])

        if not found:
            # zpracuj jmeno
            name = self.cleaner.prepare_name_for_searching(self, text)
            if name is None:
                raise ValueError("Name is empty - {}".format(text), cause["registry_sign"])

            # koukni do cache a nebo hledej mezi advokátama
            if any(self.look_into_cache(name, label="nomatch")):
                self.add_to_statistics("hit_no")
                raise ValueError("Hit on no_match_cache", "{} in case {}".format(name, cause["registry_sign"]))
            # exception is catched on level up
            advocate_id, status, debug, type_of_comparison = self.find_advocate_by_name(name)
        # priprav tagovani
        tagging_record = self.prepare_tagging(cause, status, debug, advocate=advocate_id)
        # uloz shodu do cache
        self.add_to_cache(ic if found else name, advocate_id, debug)
        # uloz tagovani do DB

        if self.insert_if_differs(tagging_record, last_tagging):
            if tagging_record["status"] == states["ok"]:
                self.add_to_statistics("ok")
                write_to_log(cause["registry_sign"], name, advocate_id, debug)
            else:
                self.add_to_statistics("failed")
            #self.print_info(cause, text, ic if found else name, advocate_id, type_of_comparison)
            return True
        else:
            return False

    def repair(self, last_tagging, cause):
        """
        :param last_tagging:
        """
        empty_record = self.prepare_tagging(cause, status=states["no"], debug="no match")
        if self.insert_if_differs(empty_record, last_tagging):
            self.add_to_statistics("repair")

    def run(self):
        """
        Entry point of this class
        """
        print("Matching...")
        for cause in self.causes:
            # if has last tagging and not found match, must insert empty record for replace old tagging
            official_data = cause["official_data"]
            text, pin, message, arg = (None,) * 4
            # only one name for every cause
            if official_data:
                if len(official_data) != 1:
                    self.add_to_statistics("bad")
                    self.repair(self.get_last_tagging(cause["id_case"]), cause)
                    continue
                # ziskej IC a text
                pin = self.get_pin(official_data)
                text = self.get_name(cause)

                # zavolej hledani - predej IC, text a pripad
                try:
                    ret = self.process_case(cause, text, pin)
                except ValueError as ex:
                    message, arg = ex.args
                    print(u"Exception: {} for '{}'".format(message, arg).encode())
                    self.add_to_cache(arg, None, "no_match", label="nomatch")
                    self.add_to_statistics("no_matched")
                    self.repair(self.get_last_tagging(cause["id_case"]), cause)
                    continue

                if ret is True:  # insert
                    self.add_to_statistics("matched")
                else:  # not insert
                    self.add_to_statistics("no_change")
            else:
                self.add_to_statistics("empty")
                self.repair(self.get_last_tagging(cause["id_case"]), cause)
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
