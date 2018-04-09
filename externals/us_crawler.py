#!usr/bin/env python
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
from urllib.parse import urljoin

from os.path import join
from tqdm import tqdm

try:
    from bs4 import BeautifulSoup, SoupStrainer
except ImportError:
    from beautifulsoup4 import BeautifulSoup, SoupStrainer

from ghost import Ghost

only_a_tags = SoupStrainer("a")

base_action_url = "http://nalus.usoud.cz/Search/"
search_url = urljoin(base_action_url, "Search.aspx")
results_url = urljoin(base_action_url, "Results.aspx")
hash_id = datetime.now().strftime("%d-%m-%Y")
# set view progress bar and view browser window
progress, view = (False, False)
working_dir = "working"
screens_dir = "screens"
documents_dir = "documents"
out_dir, documents_dir_path, result_dir_path, screens_dir_path, b_screens = (None,) * 5
output_file = None
log_dir = "log_us"
logger, writer_records, ghost, session, list_of_links = (None,) * 5
global_ncols = 120  # width of progressbar
main_timeout = 10000
records_per_page = 80


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
    """
    settings logging for subprocess

    :param arguments: program arguments from commandline
    """
    p = subprocess.Popen(arguments, stdout=subprocess.PIPE,
                         stderr=subprocess.PIPE)
    stdout, stderr = p.communicate()
    if stdout:
        logger.debug("%s" % stdout)
    if stderr:
        logger.debug("%s" % stderr)


def clean_directory(root):
    """
    clear directory (after successfully run)

    :param root: path to directory
    """
    for f in os.listdir(root):
        try:
            shutil.rmtree(join(root, f))
        except NotADirectoryError:
            os.remove(join(root, f))


def create_directories(b_screens):
    """
    create working directories
    """
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
            clean_directory(screens_dir_path)
        return screens_dir_path


def parameters():
    """

    :return: dict with all options
    """
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
    parser.add_option("--progress-bar", action="store_true", dest="progress", default=False,
                      help="Show progress bar during operations")
    parser.add_option("--view", action="store_true", dest="view", default=False,
                      help="View window during operations")
    (run_options, args) = parser.parse_args()
    run_options = vars(run_options)

    return run_options


#
# help functions
#


def convert_date(date, formats=('%d. %m. %Y', '%Y-%m-%d')):
    """
    Converts a date from one format to another - specified by the format parameter

    :param date: string with date to convert
    :type date: str
    :param formats: input and output format
    :type formats: tuple
    :return: converted date
    """
    return datetime.strptime(date, formats[0]).strftime(formats[1])


def itemize_text(item):
    """
    Converts the text with the <br> element to the list of individual rows

    :param item: BeautifulSoup object - element
    :return: list of individual rows
    """
    buffer = []
    for child in item.contents:
        if "<br>" in str(child):
            clear_child = str(child).replace("</br>", "").strip()
            clear_child = clear_child.replace("<br/>", "").strip()
            if clear_child == '':
                continue
            items = [item.strip().replace('"', "'")
                     for item in clear_child.split("<br>") if len(item) > 1]
            buffer.extend(items)
        else:
            try:
                child = child.strip().replace('"', "'")
            except TypeError:
                child = ''
            if child != '':
                buffer.append(child)
    if len(buffer):
        return json.dumps(dict(zip(range(1, len(buffer) + 1), buffer)), sort_keys=True,
                          ensure_ascii=False)
    else:
        return ""


def itemize_list(item):
    """
    Converts the children of the <ul > element to the list

    :param item: BeautifulSoup object - element
    :return: list of texts with individual rows
    """
    buffer = []
    if item.ul is not None:
        for child in item.ul.children:
            buffer.append(child.text.strip())
        return json.dumps(dict(zip(range(1, len(buffer) + 1), buffer)), sort_keys=True,
                          ensure_ascii=False)
    else:
        return ''


def make_soup(path):
    """
    make BeautifulSoup object Soup from input HTML file

    :param path: path to HTMl file
    :return: BeautifulSoup object Soup
    """
    soup = BeautifulSoup(codecs.open(path, encoding="utf-8"), "html.parser")
    return soup


def extract_name(text):
    """
    Extracts the name of the lawyer from the decision text
    *Not used*

    :param text: decision text
    :return: list of extracted names
    """
    if text and text.contents[0]:
        m = re.findall(
                # r'zast[.|\w]+(?<!zastupitelství)\s([^A-Z]+)?\s?(([A-Ž]\w+\.?\s?)+)(?=se\ssídlem|,)',
                # r'zast[.|\w]+(?<![zastupitelství,zastavením])\s([^A-Z]+)?\s?(([A-Ž]\w+\.?\s?)+)(?=se\ssídlem|,)',
                # r'(?<!ne)zast(\.|(oupen(ého|ých|ým|ými|ému|ém|ou|é|ý|i)?))\s*(advokát(em|kou)\s*)?(([A-Ž]\w+\.?,?\s?)+)(?=se\ssídlem|,)',
                # r'(?<!ne)zast(\.|(oupen(ého|ých|ým|ými|ému|ém|ou|é|ý|i)?))\s*(advokát(em|kou)\s*)?(([A-Ž]\w+\.?\s?)+)(?=se\ssídlem|,)',
                r'(?<!ne)zast(\.|(oupen(ého|ých|ým|ými|ému|ém|ou|é|ý|i)?))\s*(advokát(em|kou)\s*)?(([A-Ž]\w+\.?\s?)+)(?=sídlem|,)',
                text.contents[0], re.UNICODE | re.MULTILINE)
        if m:
            logger.debug("re.findall() -> {}".format(m))
            buffer = [x[5] for x in m]
            logger.debug(buffer)

            return json.dumps(dict(zip(range(1, len(buffer) + 1), buffer)), sort_keys=True,
                              ensure_ascii=False)

        else:
            return ""


def process_detail_page(html_file: str, response: object):
    """save detail page as HTML file for later extraction

    :param html_file: name of saving file
    :param response: HTML code for saving
    """
    response = BeautifulSoup(response, "html.parser")

    del response.find("div", id="docContentPanel")["style"]  # remove inline style
    del response.find("div", id="recordCardPanel")["style"]  # remove inline style

    link_elem = response.select_one("#recordCardPanel > table > tbody > tr:nth-of-type(26) > td:nth-of-type(2)")

    if link_elem and link_elem.string:
        link_elem.string.wrap(response.new_tag("a", href=link_elem.string))
    else:
        logger.warning(link_elem)

    # remove script, which manipulate size of document
    response.body.script.decompose()
    del response.body["onload"]  # remove calling script on loading page

    logger.debug("Saving file '%s'" % html_file)
    with codecs.open(join(documents_dir_path, html_file), "wb", encoding="utf-8") as f:
        f.write(str(response))


def get_links(response):
    """
    get all relevant links to detail's page of record

    :param response: HTML code current page
    :return: last page

    """
    list_of_links = []
    soup = BeautifulSoup(response, "html.parser", parse_only=only_a_tags)

    links = soup.find_all("a", class_=re.compile("resultData[0,1]"))

    if len(links) == 0:
        logger.warning("Not found links on page")
        return []

    logger.debug("Found links on page (%s)" % len(links))
    for link in links:
        list_of_links.append(link.get('href'))

    return list_of_links


def how_many(response: object, records_per_page: int):
    """
    find number of records and compute count of pages

    :param response: HTML code
    :param records_per_page: number of displayed records
    """
    soup = BeautifulSoup(response, "html.parser")
    # print(soup.prettify())
    label = None
    number_of_records = None
    count_of_pages = None
    result_table = soup.select_one("#Content")
    if result_table is None:
        return count_of_pages, number_of_records

    info = result_table.select_one("table > tbody > tr:nth-of-type(1) > td > table > tbody > tr > td")
    if info is None:
        logger.error("info element is None")
        return count_of_pages, number_of_records

    label = info.text

    if label is None:
        logger.error("")
        return count_of_pages, number_of_records

    m = re.compile("\w+ (\d+) - (\d+) z \w+ (\d+).*").search(label)

    if m is None:
        logger.error("Not found numbers about records")
        return count_of_pages, number_of_records

    number_of_records = m.group(3)
    count_of_pages = math.ceil(int(number_of_records) / records_per_page)

    return int(count_of_pages), int(number_of_records)


def make_screen(name: str, selector: str = None):
    if b_screens:
        session.capture_to(join(screens_dir_path, "{}.png".format(name)), selector=selector)


def save_record(record: dict):
    logger.debug("{} - {}".format(record["local_path"], record["record_id"]))
    writer_records.writerow(record)  # write record to CSV


def go_to_next_page(page: int):
    session.open(urljoin(results_url, "?page={}".format(page + 1)), wait=True)  # go to next page


def get_application_options(options):
    out_dir = options["dir"]
    date_from = options["date_from"]
    date_to = options["date_to"]
    b_screens = options["screens"]
    b_delete = options["delete"]
    output_file = options["filename"]
    days = options["last"]
    return b_delete, date_from, date_to, days, out_dir, output_file, b_screens


def get_running_options(options):
    progress = options["progress"]
    view = options["view"]
    return view, progress


def get_directory_path(out_dir):
    result_dir_path = join(out_dir, "result")
    out_dir = join(out_dir, working_dir)  # new out_dir is working directory
    documents_dir_path = join(out_dir, documents_dir)
    return documents_dir_path, out_dir, result_dir_path


def only_extract():
    logger.info("Only extract informations")
    extract_information(records=None)
    logger.info("DONE - extraction")


def set_environment(view):
    global ghost
    ghost = Ghost()
    global session
    session = ghost.start(download_images=False, java_enabled=False,
                          show_scrollbars=False, wait_timeout=main_timeout,
                          display=False, plugins_enabled=False)
    if view:
        session.display = True
        session.show()


#
# process functions
#


def fill_form(date_from: str, records_per_page: int, date_to=None, days=None):
    """
    set form for searching

    :param days: how many days ago
    :param date_from: start date of range
    :param date_to: end date of range
    :param records_per_page: how many records is on one page
    :return: Bool
    """
    session.open(search_url, wait=True)
    if days or int(date_from.split(".")[-1].strip()) > 2006:
        # print(session.content)
        logger.debug("Set typ_rizeni as 'O ústavních stížnostech'")
        session.open(
                "http://nalus.usoud.cz/dialogs/PopupCiselnik.aspx?control=ctl00_MainContent_typ_rizeni&type=typ_rizeni")
        session.evaluate("javascript:saveSelected('0')", expect_loading=True)
        make_screen("dialog_check")
        result, resources = session.click("#bSave", expect_loading=True)
        session.open(search_url)

    if days and session.exists("#ctl00_MainContent_dle_data_zpristupneni"):
        logger.debug("Select check button")
        session.click("#ctl00_MainContent_dle_data_zpristupneni", expect_loading=False)
        if session.exists("#ctl00_MainContent_zpristupneno_pred"):
            logger.info("Select last %s days" % days)
            session.set_field_value("#ctl00_MainContent_zpristupneno_pred", days)
    else:
        if session.exists("#ctl00_MainContent_availableFrom"):
            logger.debug("Set date_from '%s'" % date_from)
            session.set_field_value("#ctl00_MainContent_submissionFrom", date_from)
            make_screen("set_from")

            if date_to is not None:  # ctl00_MainContent_decidedFrom
                logger.debug("Set date_to '%s'" % date_to)
                session.set_field_value("#ctl00_MainContent_submissionTo", date_to)
            logger.info("Records from the period %s -> %s", date_from, date_to)
            make_screen("set_to")

    logger.debug("Set sorting criteria")
    session.set_field_value("#ctl00_MainContent_razeni", "3")

    logger.debug("Set counter records per page")
    session.set_field_value("#ctl00_MainContent_resultsPageSize", str(records_per_page))
    make_screen("set_form")

    if session.exists("#ctl00_MainContent_but_search"):
        logger.debug("Click to search button")
        session.click("#ctl00_MainContent_but_search", expect_loading=True)
    make_screen("results")


def make_record(soup: object, file_name: str):
    """
    extract relevant informations from document and writing on CSV file

    :param soup: bs4 soup object
    :param file_name: indetificator of record
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
    try:
        if "NALUS" in soup.title.string:  # soup.string
            logger.debug("{}, {}, {}".format(soup.title.string, file_name, type(table)))
            return
    except AttributeError:
        logger.error("{} (soup.title) - {}".format(soup.title, file_name))
        return

    if (table is None) and (table.tbody is None):
        logger.error("table is None")
        return

    for key, index in zip(entities[:-4], range(1, 27)):
        path_to_element = "table > tbody > tr:nth-of-type({}) > td:nth-of-type(2)".format(index)
        item[key] = table.select_one(path_to_element).text.strip()
        if 'date' in key and item[key]:
            item[key] = convert_date(item[key]) if item[key] != '' else ''
        elif key in only_text_elements or key in only_list_elements:
            item[key] = table.select_one(path_to_element)
    item['record_id'] = item['ecli']
    item['local_path'] = file_name
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

    item['names'] = None  #extract_name(text)
    return item


def process_link(link: str):
    """
    Go to link and save page
    :param link: detail page link
    :return:
    """
    id = link.split("?")[1].split("&")[0]
    html_file = id + ".html"
    if not os.path.exists(join(documents_dir_path, html_file)):
        logger.debug("Go to link to detail")
        session.open(urljoin(results_url, link), wait=True)
        process_detail_page(html_file, response=session.content)


def extract_information(records):
    """
    extract informations from HTML files and write to CSV

    :param records: number of all records
    """
    html_files = [join(documents_dir_path, file_name) for file_name in next(os.walk(documents_dir_path))[2]]

    if records is None:
        records = len(html_files)

    if len(html_files) != int(records):
        logger.info("{} != {} ({})".format(len(html_files), records, type(records)))
        return False

    fieldnames = ['court_name', 'record_id', 'registry_mark', 'decision_date', 'case_year', 'web_path', 'local_path',
                  'form_decision', 'decision_result', 'ecli', 'paralel_reference_laws', 'paralel_reference_judgements',
                  'popular_title', 'delivery_date', 'filing_date', 'publication_date', 'proceedings_type', 'importance',
                  'proposer', 'institution_concerned', 'justice_rapporteur',
                  'contested_act', 'concerned_laws', 'concerned_other', 'dissenting_opinion',
                  'proceedings_subject', 'subject_index', 'ruling_language', 'note', 'names']

    global writer_records

    logger.info("%s files to extract" % len(html_files))
    csv_records = open(join(out_dir, output_file), 'w', newline='', encoding="utf-8")

    writer_records = csv.DictWriter(
            csv_records, fieldnames=fieldnames, delimiter=";", quoting=csv.QUOTE_ALL, quotechar='"')
    writer_records.writeheader()

    t = html_files
    if progress:
        t = tqdm(html_files, ncols=global_ncols)  # view progress bar

    for html_f in t:
        file_name = os.path.basename(html_f)
        record = make_record(make_soup(html_f), file_name)
        save_record(record)

    csv_records.close()
    return True


def walk_details():
    """
    make a walk through pages of detail
    
    """
    links_to_info = get_links(session.content)
    session.open(urljoin(results_url, links_to_info[0]), wait=True)
    while not session.exists("#lbError"):
        response = session.content
        soup = BeautifulSoup(response, "html.parser")
        page_id = "id=" + soup.select_one("#docIdHidden").get("value")

        html_file = page_id + ".html"
        if not os.path.exists(join(documents_dir_path, html_file)):
            logger.debug("Go to link to detail")
            process_detail_page(html_file, response=response)
        session.click("#GotoNextId", expect_loading=True)


def walk_pages(page_from: int, pages: int):
    """
    make a walk through pages of results

    :param page_from: from which page start the walk
    :param pages: over how many pages we have to go

    """
    logger.debug("{}, {} -> {}".format(page_from, pages, range(page_from, pages)))
    t = range(page_from, pages)
    if progress:
        t = tqdm(t, ncols=global_ncols)  # view progress bar

    for page in t:
        process_records_page(page)
        go_to_next_page(page)


def process_records_page(page: int):
    """
    Process page with all records links

    :param page: index of page
    :return:

    """
    logger.debug("-" * 25)
    logger.debug("page={} <=> Page: {}".format(page, page + 1))
    response = session.content
    links_to_info = get_links(response)
    if len(links_to_info) > 0:
        for link in links_to_info:
            process_link(link)


def process_web():
    pages, records = how_many(session.content, records_per_page)
    logger.info("pages: {}, records {}".format(pages, records))
    page_from = 0
    if records < 1000:
        logger.info("walk details")
        walk_details()
    elif pages is not None and records is not None:
        logger.info("walk pages")
        walk_pages(page_from, pages)
        logger.info("DONE - download")
    else:
        logger.debug("I am complete!")
    logger.info("Extract information...")
    result = extract_information(records)
    if result:
        logger.info("DONE - extraction")


def main():
    """
    main function of this program

    :return: bool
    """
    global out_dir
    global documents_dir_path
    global result_dir_path
    global screens_dir_path
    global b_screens
    global output_file
    global progress

    options = parameters()
    b_delete, date_from, date_to, days, out_dir, output_file, b_screens = get_application_options(options)
    view, progress = get_running_options(options)

    if ".csv" not in output_file:
        output_file += ".csv"

    if not os.path.exists(out_dir):
        os.mkdir(out_dir)
        # on this time is not 'logger' ready
        print("Folder was created '{}'".format(out_dir))
    set_logging()
    logger.info(hash_id)
    logger.info(U"Start US")
    logger.debug(options)

    documents_dir_path, out_dir, result_dir_path = get_directory_path(out_dir)
    screens_dir_path = create_directories(b_screens)
    if options["extraction"]:
        only_extract()
        logger.info("Moving CSV file")
        shutil.copy(join(out_dir, output_file), result_dir_path)
        sys.exit(0)
    else:
        set_environment(view)
        try:
            fill_form(date_from, records_per_page, date_to, days)
        except Exception:
            logger.error("View error - 'pages' or 'records' are None", exc_info=True)
            make_screen("view_error")

        if session.exists("#ctl00_MainContent_lbError"):
            logger.info("Not found new records")
            make_screen("error", ".searchValidator")
        try:
            process_web()
        except Exception:
            sys.exit(-1)
        # move results of crawling
        if not os.listdir(result_dir_path):
            if os.path.exists(join(out_dir, output_file)):
                logger.info("Moving files")
                shutil.copytree(documents_dir_path, join(result_dir_path, documents_dir))
                shutil.move(join(out_dir, output_file), result_dir_path)
                if not b_delete:
                    logger.debug("Cleaning working directory")
                    clean_directory(out_dir)
                sys.exit(0)
        else:
            logger.error("Result directory isn't empty.")
            sys.exit(-1)


if __name__ == "__main__":
    main()
