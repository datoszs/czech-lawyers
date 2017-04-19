#!/usr/bin/env python
# -*- encoding: utf-8 -*-
# coding=utf-8

"""
Crawler of Czech Republic The Constitutional Court
Downloads HTML files and produces CSV file with results
"""

__author__ = "Radim Jílek"
__copyright__ = "Copyright 2016, DATOS - data o spravedlnosti, z.s."
__license__ = "GNU GPL"

import codecs
import csv
import json
import logging
import math
import os
import re
import shutil
import subprocess
import sys
from datetime import datetime
from optparse import OptionParser
from os.path import join
from urllib.parse import urljoin

from bs4 import BeautifulSoup, SoupStrainer
from ghost import Ghost
from tqdm import tqdm

only_a_tags = SoupStrainer("a")

base_action_url = "http://nalus.usoud.cz/Search/"
search_url = urljoin(base_action_url, "Search.aspx")
results_url = urljoin(base_action_url, "Results.aspx")
hash_id = datetime.now().strftime("%d-%m-%Y")

working_dir = "working"
screens_dir = "screens"
documents_dir = "documents"
log_dir = "log_us"

global_ncols = 120
main_timeout = 10000
USERAGENT = "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0"


def convert_date(date, formats=('%d. %m. %Y', '%Y-%m-%d')):
    return datetime.strptime(date, formats[0]).strftime(formats[1])


def itemize_text(item):
    buffer = []
    for child in item.contents:
        if "<br>" in str(child):
            clear_child = str(child).replace("</br>", "").strip()
            if clear_child == '':
                continue
            items = [item.strip().replace('"',"'") for item in clear_child.split("<br>") if len(item) > 1]
            buffer.extend(items)
        else:
            child = child.strip().replace('"',"'")
            if child != '':
                buffer.append(child)
    if len(buffer):
        return json.dumps(dict(zip(range(1, len(buffer) + 1), buffer)), sort_keys=True,
                          ensure_ascii=False)
    else:
        return ""


def itemize_list(item):
    buffer = []
    if item.ul is not None:
        for child in item.ul.children:
            buffer.append(child.text.strip())
        return json.dumps(dict(zip(range(1, len(buffer) + 1), buffer)), sort_keys=True,
                          ensure_ascii=False)
    else:
        return ''


def extract_name(text):
    if text:
        buffer = []
        m = re.findall(
                r'zast[.|\w]+(?<!zastupitelství)\s([^A-Z]+)?\s?(([A-Ž]\w+\.?\s?)+)(?=se\ssídlem|,)',
                text.contents[0], re.UNICODE | re.MULTILINE)
        if m:
            buffer = [x[1] for x in m]
            return json.dumps(dict(zip(range(1, len(buffer) + 1), buffer)), sort_keys=True,
                              ensure_ascii=False)
        else:
            return ""


def set_logging():
    # settings of logging
    global logger
    logger = logging.getLogger(__file__)
    logger.setLevel(logging.DEBUG)
    fh_d = logging.FileHandler(join(log_dir, "{}_{}_log_debug.txt".format(__file__[0:-3], hash_id)),
                               mode="w", encoding='utf-8')
    fh_d.setLevel(logging.DEBUG)
    fh_i = logging.FileHandler(join(log_dir, "{}_{}_log.txt".format(__file__[0:-3], hash_id)),
                               mode="w", encoding='utf-8')
    fh_i.setLevel(logging.INFO)
    # create console handler
    ch = logging.StreamHandler()
    ch.setLevel(logging.INFO)
    # create formatter and add it to the handlers
    formatter = logging.Formatter(u'%(asctime)s - %(funcName)-20s - %(levelname)-5s: %(message)s',
                                  datefmt='%H:%M:%S')
    ch.setFormatter(formatter)
    fh_d.setFormatter(formatter)
    fh_i.setFormatter(formatter)
    # add the handlers to logger
    logger.addHandler(ch)
    logger.addHandler(fh_d)
    logger.addHandler(fh_i)


def logging_process(arguments):
    """settings logging for subprocess"""
    p = subprocess.Popen(arguments, stdout=subprocess.PIPE,
                         stderr=subprocess.PIPE)
    stdout, stderr = p.communicate()
    if stdout:
        logger.debug("%s" % stdout)
    if stderr:
        logger.debug("%s" % stderr)


def create_directories():
    """create working directories"""
    for directory in [out_dir, documents_dir_path, result_dir_path]:
        os.makedirs(directory, exist_ok=True)
        logger.info("Folder was created '" + directory + "'")

    if b_screens:
        screens_dir_path = join(out_dir, screens_dir)
        if not os.path.exists(screens_dir_path):
            os.mkdir(screens_dir_path)
            logger.info("Folder was created '" + screens_dir_path + "'")
        else:
            logger.debug("Erasing old screens")
            shutil.rmtree(screens_dir_path)
        return screens_dir_path


def parameters():
    usage = "usage: %prog [options]"
    parser = OptionParser(usage)
    parser.add_option("-n", "--not-delete", action="store_true", dest="delete", default=False,
                      help="Not delete working directory")
    parser.add_option("-d", "--output-directory", action="store", type="string", dest="dir", default="output_dir",
                      help="Path to output directory")
    parser.add_option("-f", "--date-from", action="store", type="string", dest="date_from", default='1. 1. 2007',
                      help="Start date of range (d. m. yyyy)")
    parser.add_option("-l", "--last-days", action="store", type="string", dest="last", default=None,
                      help="Increments for the last N days (N in <1,60>)")
    parser.add_option("-t", "--date-to", action="store", type="string", dest="date_to", default=None,
                      help="End date of range (d. m. yyyy)")
    parser.add_option("-c", "--capture", action="store_true", dest="screens", default=False,
                      help="Capture screenshots?")
    parser.add_option("-o", "--output-file", action="store", type="string", dest="filename", default="metadata.csv",
                      help="Name of output CSV file")
    parser.add_option("-e", "--extraction", action="store_true", dest="extraction", default=False,
                      help="Make only extraction without download new data")
    (run_options, args) = parser.parse_args()
    run_options = vars(run_options)

    return run_options


def view_data(date_from, records_per_page, date_to=None, days=None):
    """set form for searching

    :param date_from: start date of range
    :param date_to: end date of range
    :param records_per_page: how many records is on one page
    :return: Bool
    """
    if days and session.exists("#ctl00_MainContent_dle_data_zpristupneni"):
        logger.debug("Select check button")
        #session.set_field_value("#ctl00_MainContent_dle_data_zpristupneni", True)
        session.click("#ctl00_MainContent_dle_data_zpristupneni", expect_loading=False)
        if session.exists("#ctl00_MainContent_zpristupneno_pred"):
            logger.info("Select last %s days" % days)
            session.set_field_value("#ctl00_MainContent_zpristupneno_pred", days)
    else:
        if session.exists("#ctl00_MainContent_availableFrom"):
            logger.debug("Set date_from '%s'" % date_from)
            session.set_field_value("#ctl00_MainContent_availableFrom", date_from)
            # datetime.strptime(date_from, '%d.%m.%Y').strftime('%Y/%m/%d'))
            if b_screens:
                session.capture_to(join(screens_dir_path, "set_from.png"))
            if date_to is not None:  # ctl00_MainContent_decidedFrom
                logger.debug("Set date_to '%s'" % date_to)
                session.set_field_value("#ctl00_MainContent_availableTo", date_to)
                # datetime.strptime(date_to, '%d.%m.%Y').strftime('%Y/%m/%d'))
            logger.info("Records from the period %s -> %s", date_from, date_to)
            if b_screens:
                session.capture_to(join(screens_dir_path, "set_to.png"))
    logger.debug("Set sorting criteria")
    session.set_field_value("#ctl00_MainContent_razeni", "3")
    logger.debug("Set counter records per page")
    session.set_field_value(
            "#ctl00_MainContent_resultsPageSize", str(records_per_page))
    if b_screens:
        session.capture_to(join(screens_dir_path, "set_form.png"))

    if session.exists("#ctl00_MainContent_but_search"):
        try:
            logger.debug("Click to search button")
            session.click("#ctl00_MainContent_but_search", expect_loading=True)
        except Exception:
            logger.warning("Exception")
            return False
    if b_screens:
        session.capture_to(join(screens_dir_path, "result.png"))
    return True


def how_many(response, records_per_page):
    """find number of records and compute count of pages

    :response: HTML code
    :records_per_page: number of displayed records
    """
    soup = BeautifulSoup(response, "html.parser")
    # print(soup.prettify())
    result_table = None
    pages = None
    number_of_records = None
    result_table = soup.select_one("#Content")
    if result_table is not None:
        info = result_table.select_one(
                "table > tbody > tr:nth-of-type(1) > td > table > tbody > tr > td")
        if info is not None:
            pages = info.text
            m = re.compile("\w+ (\d+) - (\d+) z \w+ (\d+).*").search(pages)
            if m is not None:
                # print(m.group(3))
                number_of_records = m.group(3)
                count_of_pages = math.ceil(
                        int(number_of_records) / records_per_page)
                # print(count_of_pages)
                if pages is not None:
                    pages = int(count_of_pages)
    return pages, number_of_records


def make_soup(path):
    soup = BeautifulSoup(codecs.open(path, encoding="utf-8"), "html.parser")
    return soup


def make_record(soup, id):
    """extract relevant informations from document

    :soup: bs4 soup object
    :id: indetificator of record
    """

    item = {}
    only_text_elements = ['decision_result', 'proceedings_subject', 'proposer',
                          'subject_index', 'institution_concerned', 'contested_act', 'dissenting_opinion']
    only_list_elements = ['concerned_laws', 'concerned_other']

    entities = [
        'ecli', 'court_name', 'registry_mark', 'paralel_reference_laws', 'paralel_reference_judgements',
        'popular_title', 'decision_date', 'delivery_date', 'filing_date', 'publication_date', 'form_decision',
        'proceedings_type', 'importance', 'proposer', 'institution_concerned', 'justice_rapporteur', 'contested_act',
        'decision_result', 'concerned_laws', 'concerned_other', 'dissenting_opinion', 'proceedings_subject',
        'subject_index', 'ruling_language', 'note', 'web_path', 'local_path', 'record_id', 'names', 'case_year']

    table = soup.find("div", id="recordCardPanel")
    if "NALUS" in soup.title.text:
        logger.debug("%s, %s, %s" % (soup.title.text, id, type(table)))
        return
    if (table is not None) and (table.tbody is not None):
        for key, index in zip(entities[:-4], range(1, 27)):
            item[key] = table.select_one(
                    "table > tbody > tr:nth-of-type({}) > td:nth-of-type(2)".format(index)).text.strip()
            if 'date' in key and item[key]:
                item[key] = convert_date(item[key]) if item[key] != '' else ''
            elif key in only_text_elements or key in only_list_elements:
                item[key] = table.select_one(
                        "table > tbody > tr:nth-of-type({}) > td:nth-of-type(2)".format(index))
                # print('{}, {} - {}'.format(key, item[key], index))
        item['record_id'] = item['ecli']
        item['local_path'] = id
        item['case_year'] = item['filing_date'].split('-')[0]

        # extract:
        # Ruling Type - decisions
        # Proceedings Subject
        # Subject Index
        # Institution Concerned
        # Contested Act
        # Proposer
        # Dissenting Opinion
        for key in only_text_elements:
            item[key] = itemize_text(item[key])

        # extract:
        # Constitutional Laws and International Agreements Concerned
        # Other Regulations Concerned
        for key in only_list_elements:
            item[key] = itemize_list(item[key])

        # extract names from text
        text = soup.select_one("#uc_vytah_cellContent > span")
        item['names'] = extract_name(text)
    logger.debug(item)
    writer_records.writerow(item)  # write item to CSV


def extract_information(records):
    """extract informations from HTML files and write to CSV

    :records: number of all records
    """
    html_files = [join(documents_dir_path, fn)
                  for fn in next(os.walk(documents_dir_path))[2]]
    # print(len(html_files))
    if records is None:
        records = len(html_files)
    fieldnames = ['court_name', 'record_id', 'registry_mark', 'decision_date', 'case_year', 'web_path', 'local_path',
                  'form_decision', 'decision_result', 'ecli', 'paralel_reference_laws', 'paralel_reference_judgements',
                  'popular_title', 'delivery_date', 'filing_date', 'publication_date', 'proceedings_type', 'importance',
                  'proposer', 'institution_concerned', 'justice_rapporteur',
                  'contested_act', 'concerned_laws', 'concerned_other', 'dissenting_opinion',
                  'proceedings_subject', 'subject_index', 'ruling_language', 'note', 'names']

    global writer_records

    if len(html_files) == int(records):
        logger.info("%s files to extract" % len(html_files))
        csv_records = open(join(out_dir, output_file), 'w',
                           newline='', encoding="utf-8")

        writer_records = csv.DictWriter(
                csv_records, fieldnames=fieldnames, delimiter=";", quoting=csv.QUOTE_ALL, quotechar='"')
        writer_records.writeheader()

        from tqdm import tqdm
        i = 0
        t = html_files
        for html_f in t:
            id = os.path.basename(html_f)
            make_record(make_soup(html_f), id)
            # t.update()
            # print(i)
            """i += 1
            if i==30:
                break"""
        # t.close()
        csv_records.close()
        return True
    else:
        logger.info("{} != {} ({})".format(len(html_files), records, type(records)))
        return False


def extract_data(html_file, response):
    """save detail page as HTML file for later extraction

    :html_file: name of saving file
    :response: HTML code for saving
    """
    response = BeautifulSoup(response, "html.parser")
    del response.find("div", id="docContentPanel")["style"]  # remove inline style
    del response.find("div", id="recordCardPanel")["style"]  # remove inline style
    link_elem = response.select_one(
            "#recordCardPanel > table > tbody > tr:nth-of-type(26) > td:nth-of-type(2)")
    link_elem.string.wrap(response.new_tag("a", href=link_elem.string))
    response.body.script.decompose()  # remove script, which manipulate size of document
    del response.body["onload"]  # remove calling script on loading page
    logger.debug("Saving file '%s'" % html_file)
    with codecs.open(join(documents_dir_path, html_file), "w", encoding="utf-8") as f:
        f.write(str(response))


def get_links(response):
    """get all relevant links to detail's page of record

    :response: HTML code
    :return: list of links to detail
    """
    list_of_links = []
    soup = BeautifulSoup(response, "html.parser", parse_only=only_a_tags)

    links = soup.find_all("a", class_=re.compile("resultData[0,1]"))

    if len(links) > 0:
        logger.debug("Found links on page (%s)" % len(links))
        for link in links:
            # list_of_links.append(urljoin(base_action_url, link.get('href')))
            list_of_links.append(link.get('href'))
            # session.open(urljoin(results_url, "?page="+str(page)))
    else:
        logger.warning("Not found links on page")

    return list_of_links


def walk_pages(page_from, pages):
    """make a walk through pages of results

    :page_from: from which page start the walk
    :pages: over how many pages we have to go
    """
    t = tqdm(range(page_from, pages), ncols=global_ncols, position=0)
    page = -1
    links_to_info = []
    for page in t:
        t.set_description("({}/{} => {:3f}%%)".format(
                          page + 1, pages, (page + 1) / pages * 100))
        logger.debug("-------------------------")
        logger.debug("page={} <=> Page: {}".format(page, page + 1))
        response = session.content
        links_to_info.extend(get_links(response))

        session.open(urljoin(results_url, "?page={}".format(str(page + 1))))  # go to next page
        # save number of processing page
        with codecs.open(join(out_dir, "current_page.ini"), "w", encoding="utf-8") as f:
            f.write(str(page))

    if len(links_to_info) > 0:
        for link in links_to_info:
            #element = "a[href=\"{}\"]".format(link)
            # print(element)
            id = link.split("?")[1].split("&")[0]
            html_file = id + ".html"
            if not os.path.exists(join(documents_dir_path, html_file)):
                try:
                    #if session.exists(element):
                    logger.debug("Go to link to detail")
                    #session.click(element, expect_loading=True)
                    #print(urljoin(results_url, link))
                    session.open(urljoin(results_url, link), wait=True)

                    # else:
                    #     logger.warning(
                    #             "Save file with nonexist element '%s'" % element)
                    #     with codecs.open(join(out_dir, "real" + str(time.time()) + ".html"), "w",
                    #                      encoding="utf-8") as e_f:
                    #         e_f.write(response)
                except Exception:
                    logger.error("ERROR - click to detail (%s)" % element)
                    logger.info(response)
                    if b_screens:
                        session.capture_to(
                                join(screens_dir_path, "error-page=%s.png" % str(page)))
                    sys.exit(-1)
                # print(session.content)
                title, resources = session.evaluate("document.title")
                if "NALUS" not in title:
                    extract_data(html_file, response=session.content)
                    # print(ecli)
                    # f.write(ecli+"\n")
                    #logger.debug("Back to result page")

                    # back to results
                    #session.evaluate(
                    #        "window.history.back()", expect_loading=True)
    return page


def main():
    global ghost
    ghost = Ghost()
    global session
    session = ghost.start(download_images=False, java_enabled=False,
                          show_scrollbars=False, wait_timeout=main_timeout,
                          display=False, plugins_enabled=False, user_agent=USERAGENT)
    session.open(search_url)
    # print(session.content)
    records_per_page = 20
    if view_data(date_from, records_per_page, date_to, days):
        response = session.content
        if b_screens:
            session.capture_to(
                    join(screens_dir_path, "errors.png"), selector=".searchValidator")
        records = 0
        if not session.exists("#ctl00_MainContent_lbError"):
            pages, records = how_many(response, records_per_page)
            # print(pages)
            logger.info("pages: {}, records {}".format(pages, records))

            page_from = 0
            # pages = 20

            # load starting page
            logger.debug("Search file 'current_page.ini'...")
            if os.path.exists(join(out_dir, "current_page.ini")):
                with codecs.open(join(out_dir, "current_page.ini"), "r") as cr:
                    page_from = int(cr.read().strip())
                logger.debug("'current_page.ini' found")
                logger.debug("Start on page {}".format(page_from))

            if pages is not None and records is not None:
                if (page_from + 1) > pages:
                    logger.debug(
                            "Loaded page number is greater than count of pages")
                    page_from = 0
                if pages != (page_from + 1):  # parameter page is from zero
                    last_page = page_from
                    while (last_page + 1) != pages:
                        last_page = walk_pages(last_page, pages)
                    print("\n")
                    logger.info("DONE - download")
                else:
                    logger.debug("I am complete!")
            else:
                logger.error("View error - 'pages' or 'records' are None")
                return False
        else:
            logger.info("Not found new records")
        logger.info("Extract information...")
        result = extract_information(records)
        if result and os.path.exists(join(out_dir, "current_page.ini")):
            logger.info("DONE - extraction")
            os.remove(join(out_dir, "current_page.ini"))

        return True


if __name__ == "__main__":
    options = parameters()
    out_dir = options["dir"]
    date_from = options["date_from"]
    date_to = options["date_to"]
    b_screens = options["screens"]
    b_delete = options["delete"]
    output_file = options["filename"]
    days = options["last"]

    if ".csv" not in output_file:
        output_file += ".csv"

    if not os.path.exists(out_dir):
        os.mkdir(out_dir)
        print("Folder was created '{}'".format(out_dir))
    set_logging()
    logger.info(hash_id)
    logger.info(U"Start US")
    logger.debug(options)

    result_dir_path = join(out_dir, "result")
    out_dir = join(out_dir, working_dir)  # new out_dir is working directory
    documents_dir_path = join(out_dir, documents_dir)

    screens_dir_path = create_directories()

    if options["extraction"]:
        logger.info("Only extract informations")
        extract_information(records=None)
        logger.info("DONE - extraction")
        logger.info("Moving files")
        shutil.copy(join(out_dir, output_file), result_dir_path)

    else:
        if main():
            # move results of crawling
            if not os.listdir(result_dir_path):
                if os.path.exists(join(out_dir, output_file)):
                    logger.info("Moving files")
                    shutil.move(documents_dir_path, result_dir_path)
                    shutil.move(join(out_dir, output_file), result_dir_path)
                    if not b_delete:
                        logger.debug("Cleaning working directory")
                        shutil.rmtree(out_dir)
                        os.makedirs(out_dir, exist_ok=True)

            else:
                logger.error("Result directory isn't empty.")
                sys.exit(-1)
            sys.exit(0)
        else:
            sys.exit(-1)