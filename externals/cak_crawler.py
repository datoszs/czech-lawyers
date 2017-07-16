#!/usr/bin/env python3
# -*- encoding: utf-8 -*-
# coding=utf-8

"""
Crawler of Czech Republic The Czech Bar Association

Downloads HTML files, parsing them and produces CSV file with results
"""

__author__ = "Radim Jílek"
__copyright__ = "Copyright 2016, DATOS - data o spravedlnosti, z.s."
__license__ = "GNU GPL"

import codecs
import csv
import logging
import math
import os
import sys
import re
import shutil
import subprocess
from collections import namedtuple
from datetime import datetime
from optparse import OptionParser
from os.path import join

from bs4 import BeautifulSoup, SoupStrainer
from ghost import Ghost
from tqdm import tqdm

try:
    from urllib.parse import urljoin
except ImportError:
    # noinspection PyUnresolvedReferences,PyUnresolvedReferences
    from urlparse import urljoin

import urllib.request

base_url = "http://vyhledavac.cak.cz/"
#url = "http://vyhledavac.cak.cz/Units/_Search/search.aspx"
next_url = join(base_url, "Home/SearchResult")
working_dir = "working"
documents_dir = "documents"
log_dir = "log_cak"
logger, writer_records, ghost, session, list_of_links = (None,) * 5
main_timeout = 5000
main_cols = 80
only_a_tags = SoupStrainer("td > a")
Record = namedtuple('Record', ['url', 'id', 'text'])
# set view progress bar and view browser window
# progress, view = (True, True)

b_screens = False  # capture screenshots?
# precompile regex
p_re_records = re.compile(r'.+: (\d+), [^0-9]+(\d+)\.$')
p_re_psc = re.compile(r'(\d{3}\s?\d{2})')
p_re_name = re.compile(
        r'^((\w+\. )+)?(([A-Ž]\w+ )+)(, (\w+\. ?)+)?$', re.UNICODE)


#
# service functions
#


def set_logging():
    """
    settings of logging
    """
    global logger
    logger = logging.getLogger(__file__)
    logger.setLevel(logging.DEBUG)
    hash_id = datetime.now().strftime("%d-%m-%Y")
    fh_d = logging.FileHandler(join(log_dir, __file__[0:-3] + "_" + hash_id + "_log_debug.txt"), mode="w",
                               encoding='utf-8')
    fh_d.setLevel(logging.DEBUG)
    fh_i = logging.FileHandler(join(log_dir, __file__[0:-3] + "_" + hash_id + "_log.txt"), mode="w",
                               encoding='utf-8')
    fh_i.setLevel(logging.INFO)
    # create console handler
    ch = logging.StreamHandler()
    ch.setLevel(logging.INFO)
    # create formatter and add it to the handlers
    formatter = logging.Formatter(
            u'%(asctime)s - %(funcName)-15s - %(levelname)-8s: %(message)s')
    ch.setFormatter(formatter)
    fh_d.setFormatter(formatter)
    fh_i.setFormatter(formatter)
    # add the handlers to logger
    logger.addHandler(ch)
    logger.addHandler(fh_d)
    logger.addHandler(fh_i)


def logging_process(arguments):
    """
    settings logging for subprocess
    """
    p = subprocess.Popen(arguments, stdout=subprocess.PIPE,
                         stderr=subprocess.PIPE)
    stdout, stderr = p.communicate()
    if stdout:
        logger.debug("{}".format(stdout))
    if stderr:
        logger.debug("{}".format(stderr))


def parameters():
    """
    Get program parameters

    :return: dictionary with all the settings
    """
    usage = "usage: %prog [options]"
    parser = OptionParser(usage)

    parser.add_option("-o", "--output-file", action="store", type="string", dest="filename", default="metadata.csv",
                      help="Name of output CSV file")
    parser.add_option("-e", "--extraction", action="store_true", dest="extraction", default=False,
                      help="Make only extraction without download new data")
    parser.add_option("-d", "--output-directory", action="store", type="string", dest="dir", default="output_dir",
                      help="Path to output directory")
    parser.add_option("--progress-bar", action="store_true", dest="progress", default=False,
                      help="Show progress bar during operations")
    parser.add_option("--view", action="store_true", dest="view", default=False,
                      help="View window during operations")
    (options, args) = parser.parse_args()
    options = vars(options)

    # print(args,options,type(options))
    return options


def create_directories():
    """
    create working directories
    """
    for directory in [out_dir, documents_dir_path, result_dir_path]:
        os.makedirs(directory, exist_ok=True)
        logger.info("Folder was created '" + directory + "'")


#
# help functions
#


def make_soup(path):
    """
    Make soup from file for further processing

    :param path: path to file
    :return: bs4 object Soup
    """
    if os.path.exists(path):
        soup = BeautifulSoup(codecs.open(path, encoding="utf-8"), "html.parser")
        return soup
    return None


def extract_data(response, html_file):
    """
    save current page as HTML file for later extraction

    :param response: HTML code for saving
    :param html_file: name of file for saving
    """
    logger.debug("Save file '%s'" % html_file)
    with codecs.open(join(documents_dir_path, html_file), "w", encoding="utf-8") as f:
        f.write(response)


def how_many(str_info, displayed_records):
    """
    find number of records and compute count of pages

    :param str_info: info element as string
    :param displayed_records: number of displayed records
    :return: tuple number of records and count of pages
    """
    number_of_records, count_of_pages = 0, 0
    m = p_re_records.search(str_info)
    logger.info(str_info)
    if m is not None:
        part_1 = m.group(1)
        part_2 = m.group(2)
        logger.debug("part1: {}, part2: {}".format(part_1, part_2))
        # + int(part_2)  # advocates are not in order
        number_of_records = int(part_1) + int(part_2)
        count_of_pages = math.ceil(number_of_records / int(displayed_records))
        #logger.info("records: %s => pages: %s", number_of_records, count_of_pages)
    return number_of_records, count_of_pages


def split_name(name_label):
    """
    split name to the name, surname, degree before, degree after and evidence number

    :param name_label: BeautifulSoup object - element
    :return: name, surname, degree before, degree after and evidence number

    90227 - JUDr. PhDr. JOSEF NOVÁK LL.M., DBA -> 90227;JUDr. PhDr.;Josef;Novak;LL.M., DBA
    """

    # name, surname, degree_before, degree_after, evidence_number = ("",) * 5
    name, surname, degree_before, degree_after = ("",) * 4
    if name_label is not None:
        name_str = name_label.find_next().getText().strip()
        #m = p_re_name.search(name_str)
        # print("\n",m.groups())
        logger.debug(name_str)
        # try:
        #    evidence_number, name_str = name_str.split(" - ", maxsplit=1)
        # except ValueError:
        #    logger.error("To many '-' in name: {}".format(name_str))

        there_is = "," in name_str

        # remove titles from name
        orig_units = [name.strip()
                      for name in name_str.strip().split()]  # for searching
        # if '' in orig_units:
        #   orig_units.remove('')
        units = orig_units.copy()

        if there_is:  # in text is comma
            # remove titles after name (after comma)
            first = name_str.split(",")[0]
            logger.debug(
                    "\norig_units: {}\nname_str: {}\nfirst: {}({})".format(orig_units, name_str, first, len(first)))
            if len(first) < 10:
                units = orig_units.copy()
                logger.debug("< 10: {}".format(units))
            else:
                # ',' indicate last unit
                units = (first + ",").split()
                logger.debug("> 10: {}".format(units))
        # if '' in units:  # remove empty tail
        #   units.remove('')
        copy = units.copy()
        # print("original",units)
        name = ""
        for unit in units:
            if "." in unit:
                copy.remove(unit)  # remove correct title
            # other: remove all unit what can be title (special or bad writing)
            elif re.compile(r'^(MBA|BA|et|BPA|BBA|MPA|DBA|Mag),?$').match(unit):
                logger.debug("%s --> %s" % (name_str, unit))
                # input(":-)")
                copy.remove(unit)

        try:
            name = copy[0]
        except IndexError:
            for i in [name_str, there_is, orig_units, units, copy]:
                logger.debug("Error - empty copy: {}".format(i))

        surname = " ".join(copy[1:]).replace(",", "")  # remove last artifact
        surname = " ".join([item.capitalize() for item in surname.split()])
        if "-" in surname:  # capitalize every part of surname which is joined by dash
            surname = "-".join([item.strip().capitalize()
                                for item in surname.split("-")])

        # Search probable names in the original string
        try:
            index_name = orig_units.index(name)
        except ValueError:
            logger.error("Index name error: {}".format(copy))
            index_name = 0
        index_surname = orig_units.index(copy[-1])
        # print(index_name, index_surname)

        # titles are other units before/after name
        before = " ".join(orig_units[0:index_name])
        after = " ".join(orig_units[index_surname + 1:])
        name = name.capitalize()  # more readable format

        return name, surname, before, after


#
# process functions
#


def make_record(soup, html_file):
    """
    Find all the information, make a record in CSV file

    :param soup: bs4 object Soup
    :param html_file: path to source file
    """
    if soup is not None:
        name_label = soup.find("td", string=re.compile(
                r"^\s+Jméno\s+$", re.UNICODE))
        # inicialize empty values
        state = data_box = ""
        specialization = email = ""
        city = street = postal_area = ""
        evidence_number = ic = ""
        id = os.path.basename(html_file)[:-5]

        # print("\nname:",name_label.find_next().prettify())
        # split name and other from text
        name, surname, before, after = split_name(name_label)
        # print("'%s', '%s'" % (before,after))

        mailto = soup.select("a[href*=mailto:]")
        emails = [
            mail["href"].split("mailto:")[-1].replace("+",
                                                      '').replace("'", '').split(',')[0]
            for mail in mailto
            ]
        if len(emails) > 0:
            email = ", ".join(list(set(emails)))

        specialization_label = soup.find(
                "td", string=re.compile(r'^\s+Zaměření\s+$', re.UNICODE))
        if specialization_label is not None:
            specializations = specialization_label.parent.find_next_siblings()
            specialization = []
            for one_spec in [spec.getText().strip() for spec in specializations]:
                if re.search(r'^\d{2} .+$', one_spec):
                    specialization.append(one_spec)
            specialization = "|".join(specialization)

        ic_label = soup.find(
                "td", string=re.compile(r'^\s+IČ\s+$', re.UNICODE))
        if ic_label is not None:
            ic = ic_label.find_next().getText().strip()
            if u"číslo" in ic:
                # print("\n")
                # print(ic_label,len(ic_label))
                #print(ic, html_file)
                m = re.compile(r"(\d{8})", re.UNICODE).search(ic)
                if m is not None:
                    # print(m.groups())
                    ic = m.group(1)
        evidence_label = soup.find("td", string=re.compile(
                r"^\s+Evidenční číslo\s+$", re.UNICODE))
        if evidence_label is not None:
            evidence_number = evidence_label.find_next().getText().strip()
        evidence_number = evidence_number.strip()

        state_label = soup.find(
                "td", string=re.compile(r"^\s+Stav\s+$", re.UNICODE))
        if state_label is not None:
            state = state_label.find_next().getText().strip()

        data_box_label = soup.find("td", string=re.compile(
                r"^\s+ID datové schránky\s+$", re.UNICODE))
        if data_box_label is not None:
            data_box = data_box_label.find_next().getText().strip()

        location_label = soup.find(
                "td", string=re.compile(r"^\s+Adresa\s+$", re.UNICODE))
        if location_label is not None:
            street = location_label.find_next().getText().strip()
            city_str = location_label.parent.find_next_sibling().find_next_sibling()
            m_pa = p_re_psc.search(city_str.getText().strip())
            if m_pa is None:  # bad field
                # print(city_str.getText().strip())
                city_str = city_str.find_previous_sibling()
                m_pa = p_re_psc.search(city_str.getText().strip())
            if m_pa is not None:
                postal_area = m_pa.group().replace(' ', '')
                #print("city: ", city_str.getText().strip().split(m_pa.group()), "postal_area:", postal_area)
                city = city_str.getText().strip().split(
                        m_pa.group())[1].strip()

                # print(city_str.getText().strip())
                #city = city_str.getText().strip()
        company = soup.find("td", string=re.compile(
                r"^\s+Název\s+$", re.UNICODE))
        if company is not None:
            company = company.find_next().getText().strip()
        item = {
            "remote_identificator" : id,
            "identification_number": evidence_number,
            "registration_number"  : ic,
            "name"                 : name,
            "surname"              : surname,
            "degree_before"        : before,
            "degree_after"         : after,
            "state"                : state,
            "email"                : email,
            "street"               : street,
            "city"                 : city,
            "postal_area"          : postal_area,
            "specialization"       : specialization,
            "local_path"           : os.path.basename(html_file),
            "company"              : company,
            "data_box"             : data_box
        }
        logger.debug(item)
        writer_records.writerow(item)


def check_records():
    """
    Checking new records across complete database of ČAK

    :param page_size: number of records on page
    :param page_from: number of first page
    :param pages: number of last page
    :return: dictionary with links to new records
    """
    list_of_links = []
    #t = range(page_from, pages + 1)
    #if progress:
    #    t = tqdm(t, ncols=main_cols)
    #for page in t:
    # session.capture_to(str(page)+".png",selector= "#mainContent_gridResult > tbody > tr:nth-child(52) > td")
    # html_file
    # extract_data(session.content,"list_result.html")
    soup = BeautifulSoup(session.content, "html.parser")
    links = soup.select("a[href*=/Contact/Details/]")

    #logger.debug("Links = %s" % len(links))
    if len(links) > 0:
        logger.debug("current page is: %s",
                     session.evaluate("document.querySelector('.pagination > .active a').innerHTML")[0])
        t = links
        if progress:
            t = tqdm(t, ncols=main_cols)
        for link in t:
            #print(U"%s" % link.text.encode("utf-8"))
            original_link = link["href"]
            id = os.path.basename(original_link)

            # if not os.path.exists(join(documents_dir_path, id)):
            jmeno = link.text.strip()
            if jmeno != "":
                #logger.debug(U"%s" % jmeno)
                if not os.path.exists(join(documents_dir_path, id + ".html")):
                    list_of_links.append(
                            Record(urljoin(base_url, original_link), id, jmeno))
                    # if page < pages:
                    #     logger.debug("{}?page={}&pageSize={}".format(
                    #         next_url, page + 1, page_size))
                    #     session.open("{}?page={}&pageSize={}".format(
                    #         next_url, page + 1, page_size), wait=True)

    logger.info("New records %s" % len(list_of_links))
    return list_of_links


def view_data(session, value):
    """
    Go to page with form for searching and fill him.

    :param session: Ghost.py object Session
    :param value: text for searching
    :return: bool
    """

    session.page = None
    session.open(base_url, wait=True)
    # session.set_field_value("input[name=Surname]", value)
    session.call(".form-horizontal", "submit", expect_loading=True)
    if not session.exists(".searchGridTab"):
        return False
    return True


def walk_pages_advanced(session, page_size, limit_size, start_phrase="", ):
    """
    Unused
    :param session: Ghost.py object Session
    :param page_size: how many records would be showing on page
    :param start_phrase: beginning of text for searching (is concatenates on every recursion call)
    """
    for base_letter in [chr(x) for x in range(ord('a'), ord('z') + 1)]:
        if start_phrase[-1] != base_letter and view_data(session, value=start_phrase + base_letter):
            if session.exists("body > div > div > span:nth-of-type(2)"):
                value, resources = session.evaluate(
                        "document.querySelector('body > div > div > span:nth-child(6)').innerHTML")
                records, pages = how_many(value, page_size)
                # pages = 99 # hack for testing
                page_from = 1

                # if records < 900:
                if records < limit_size:
                    logger.info(
                            "{} - for phrase \"{}\"".format(value, start_phrase + base_letter))
                    if records > 10:
                        session.open(
                                "http://vyhledavac.cak.cz/Home/SearchResult?page=1&pageSize={}".format(page_size))
                    list_of_links.extend(check_records(
                            page_from, pages, page_size=page_size))
                elif start_phrase[-1] != base_letter:
                    walk_pages_advanced(session, page_size, limit_size,
                                        start_phrase=start_phrase + base_letter)


def walk_pages(session, page_size):
    """

    :param session:
    :param page_size:
    :return:
    """


def extract_information(list_of_links):
    """
    Choosing relevant files for extraction

    :param list_of_links: list with path to files
    """
    # fieldnames = ['before', 'name', 'surname', 'after', 'state', 'ic', 'evidence_number', 'street', 'city',
    # 'file']
    fieldnames = ["remote_identificator", "identification_number", "registration_number", "name", "surname",
                  "degree_before", "degree_after", "state", "street", "city", "postal_area", "local_path",
                  "email", "specialization", "company", "data_box"]

    global writer_records

    csv_records = open(join(out_dir, output_file), 'w',
                       newline='', encoding="utf-8")

    writer_records = csv.DictWriter(
            csv_records, fieldnames=fieldnames, delimiter=";")
    writer_records.writeheader()

    if list_of_links is None:  # all records in directory
        t = [fn for fn in next(os.walk(documents_dir_path))[2]]
        logger.info("Files to extraction: {}".format(len(t)))
        if progress:
            t = tqdm(t, ncols=main_cols)
        for html_file in t:
            logger.debug(html_file)
            make_record(make_soup(join(documents_dir_path, html_file)),
                        join(documents_dir_path, html_file))
    else:  # only new records
        t = list_of_links
        logger.info("Files to extraction: {}".format(len(t)))
        if progress:
            t = tqdm(t, ncols=main_cols)
        for html_file in t:
            make_record(
                    make_soup(join(documents_dir_path, html_file.id + ".html")),
                    join(documents_dir_path, html_file.id + ".html")
            )
    csv_records.close()


def main():
    """
    Go to across pages
    """
    if not b_extraction:
        global ghost
        ghost = Ghost()
        global session
        session = ghost.start(download_images=False, show_scrollbars=False, wait_timeout=5000,
                              plugins_enabled=False)
        if view:
            session.display = True
            session.set_viewport_size(800, 600)
            session.show()

        global list_of_links
        list_of_links = []

        logger.info("Start - ČAK")
        page_size = 10
        logger.info("Waiting for load all records...")
        if view_data(session, value=""):
            value, resources = session.evaluate(
                    "document.querySelector('body > div > div > span:nth-child(6)').innerHTML")
            records, pages = how_many(value, page_size)

            # for base_letter in reversed([chr(x) for x in range(ord('a'), ord('z') + 1)]):
            #    walk_pages_advanced(session, page_size, limit_size=records,
            #               start_phrase=base_letter)
            logger.debug("Show all records on one page.")
            session.open(
                    "http://vyhledavac.cak.cz/Home/SearchResult?page=1&pageSize={}".format(records),
                    wait=True)
            logger.info("Checking records...")
            list_of_links = check_records()

            logger.info("It was found {} links -> unique: {}.".format(
                    len(list_of_links),
                    len(set(list_of_links))))
            list_of_links = set(list_of_links)
        if len(list_of_links) > 0:
            logger.info("Dowloading new records...")
            t = list_of_links
            if progress:
                t = tqdm(t, ncols=main_cols)
            if view:
                logger.debug("Closing browser")
            session.exit()
            ghost.exit()
            for record in t:
                # may it be wget?
                if not os.path.exists(join(documents_dir_path, "{}.html".format(record.id))):
                    try:
                        urllib.request.urlretrieve(record.url, join(
                                documents_dir_path, record.id + ".html"))

                        # logging_process(
                        #         ["curl", record.url,
                        #          "-o", join(documents_dir_path, record.id + ".html")]
                        # )
                    except urllib.request.HTTPError as ex:
                        logger.debug(record)
                        logger.debug(ex.msg, exc_info=True)
                        if ex.code == 500:
                            logger.error("\nRecord is not loaded ({}).".format(record.text))
                            logger.debug("\n\tURL: {}\n\tid: {}\n\ttext: {}".format(record.url, record.id, record.text))


                            # session.open(record["url"])
                            #response = str(urlopen(record["url"]).read())
                            #response = session.content
                            #extract_data(response, record["id"]+".html")

            logger.info("Download  - DONE")

        if list_of_links is not None:
            logger.info("Extract information...")
            extract_information(list_of_links)
            logger.info("Extraction  - DONE")
    else:
        if b_extraction:
            logger.info("Only extract information...")
            extract_information(list_of_links=None)
            # shutil.copy(join(out_dir, output_file), result_dir_path)
            logger.info("Extraction  - DONE")
    return True


if __name__ == "__main__":
    options = parameters()
    b_extraction = options["extraction"]
    output_file = options["filename"]
    out_dir = options["dir"]
    progress = options["progress"]
    view = options["view"]
    if ".csv" not in output_file:
        output_file += ".csv"

    if not os.path.exists(out_dir):
        os.mkdir(out_dir)
        print("Folder was created '" + out_dir + "'")
    set_logging()
    logger.debug(options)

    result_dir_path = join(out_dir, "result")
    out_dir = join(out_dir, working_dir)  # new outdir is working directory
    documents_dir_path = join(out_dir, documents_dir)
    create_directories()
    try:
        if main():
            # move results of crawling
            # print(os.listdir(result_dir_path))
            if not os.listdir(result_dir_path):
                logger.info("Moving files")
                shutil.move(documents_dir_path, result_dir_path)
                shutil.move(join(out_dir, output_file), result_dir_path)
                sys.exit(0)
            else:
                logger.info("Result directory is not empty.")
                sys.exit(-1)
    except KeyboardInterrupt:
        logger.warning("Interrupt by user")
        sys.exit(-1)
