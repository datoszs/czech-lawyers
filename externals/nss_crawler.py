#!/usr/bin/env python
# -*- encoding: utf-8 -*-
# coding=utf-8

"""
Crawler of Czech Republic The Supreme Administrative Court.

Downloads HTML files, PDF files and produces CSV file with results

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
from collections import OrderedDict
from datetime import datetime
from optparse import OptionParser
from os.path import join
from urllib.parse import urljoin
from tqdm import tqdm
import pandas as pd
from bs4 import BeautifulSoup
from ghost import Ghost

base_url = "http://nssoud.cz/"
url = "http://nssoud.cz/main0Col.aspx?cls=JudikaturaBasicSearch&pageSource=0"
hash_id = datetime.now().strftime("%d-%m-%Y")
working_dir = "working"
screens_dir = "screens_" + hash_id
documents_dir = "documents"
txt_dir = "txt"
html_dir = "html"
log_dir = "log_nss"
logger, writer_records, ghost, session, list_of_links = (None,) * 5
main_timeout = 100000
global_ncols = 90
saved_pages = 0
saved_records = 0
# set view progress bar and view browser window
#progress, view = (False, False)
b_screens = False  # capture screenshots?
# precompile regex
p_re_records = re.compile(r'(\d+)$')
p_re_decisions = re.compile(r'[a-z<>]{4}\s+(.+)\s+')

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
    fh_d = logging.FileHandler(join(log_dir, __file__[0:-3] + "_" + hash_id + "_log_debug.txt"),
                               mode="w", encoding='utf-8')
    fh_d.setLevel(logging.DEBUG)
    fh_i = logging.FileHandler(join(log_dir, __file__[0:-3] + "_" + hash_id + "_log.txt"),
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


def create_directories():
    """
    create working directories
    """
    for directory in [out_dir, documents_dir_path, html_dir_path, result_dir_path]:
        os.makedirs(directory, exist_ok=True)
        logger.info("Folder was created '" + directory + "'")

    if b_screens:
        screens_dir_path = join(join(out_dir, ".."), screens_dir)

        if os.path.exists(screens_dir_path):
            logger.debug("Erasing old screens")
            shutil.rmtree(screens_dir_path)
        os.mkdir(screens_dir_path)
        logger.info("Folder was created '{}'".format(screens_dir_path))
        return screens_dir_path


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


def logging_process(arguments):
    """
    settings logging for subprocess
    """
    p = subprocess.Popen(arguments, stdout=subprocess.PIPE,
                         stderr=subprocess.PIPE)
    stdout, stderr = p.communicate()
    if stdout:
        logger.debug("%s" % stdout)
    if stderr:
        logger.debug("%s" % stderr)


def parameters():
    """

    :return: dict with all options
    """
    usage = "usage: %prog [options]"
    parser = OptionParser(usage)
    parser.add_option("-w", "--without-download", action="store_true", dest="download", default=False,
                      help="Not download PDF documents")
    parser.add_option("-n", "--not-delete", action="store_true", dest="delete", default=False,
                      help="Not delete working directory")
    parser.add_option("-d", "--output-directory", action="store", type="string", dest="dir", default="output_dir",
                      help="Path to output directory")
    parser.add_option("-l", "--last-days", action="store", type="string", dest="last", default=None,
                      help="Increments for the last N days (N in <1,60>)")
    parser.add_option("-f", "--date-from", action="store", type="string", dest="date_from", default="1. 1. 2006",
                      help="Start date of range (d. m. yyyy)")
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
    (options, args) = parser.parse_args()
    options = vars(options)

    print(args, options, type(options))
    return options

#
# help functions
#


def make_json(collection):
    """
    make correct json format

    :param collection:
    :return: JSON
    """
    return json.dumps(dict(zip(range(1, len(collection) + 1), collection)), sort_keys=True,
                      ensure_ascii=False)


def make_soup(path):
    """
    make BeautifulSoup object Soup from input HTML file

    :param path: path to HTMl file
    :return: BeautifulSoup object Soup
    """
    soup = BeautifulSoup(codecs.open(path, encoding="utf-8"), "html.parser")
    return soup


def how_many(str_info, displayed_records):
    """
    Find number of records and compute count of pages.

    :type str_info: string
    :param str_info: info element as string

    :type displayed_records: int
    :param displayed_records: number of displayed records
    """

    m = p_re_records.search(str_info)
    number_of_records = m.group(1)
    count_of_pages = math.ceil(int(number_of_records) / int(displayed_records))
    logger.info("records: %s => pages: %s", number_of_records, count_of_pages)
    return number_of_records, count_of_pages


def first_page():
    """
    go to first page on find query
    """
    session.click(
        "#_ctl0_ContentPlaceMasterPage__ctl0_pnPaging1_Repeater2__ctl0_Linkbutton2", expect_loading=True)


def extract_data(response, html_file):
    """
    save current page as HTML file for later extraction

    :type response: string
    :param response: HTML code for saving

    :type html_file: string
    :param html_file: name of saving file

    """

    logger.debug("Save file '%s'" % html_file)
    with codecs.open(join(html_dir_path, html_file), "w", encoding="utf-8") as f:
        f.write(response)


def load_data(csv_file):
    """
    load data from csv file

    :param csv_file: name of CSV file

    """

    data = pd.read_csv(csv_file, sep=";", na_values=['', "nan"])
    return data


def download_pdf(data):
    """
    download PDF files

    :type data: DataFrame
    :param data: Pandas object

    """

    frame = data[["web_path", "local_path"]].dropna()
    t = frame.itertuples()
    if progress:
        t = tqdm(t, ncols=global_ncols)  # view progress bar
    for row in t:
        filename = row[2]
        if not os.path.exists(join(documents_dir_path, filename)):
            logging_process(
                ["curl", row[1], "-o", join(documents_dir_path, filename)])
        if progress:
            t.update()  # update progress bar

#
# process functions
#


def make_record(soup):
    """
    extract relevant data from page

    :param soup: bs4 soup object

    """

    table = soup.find("table", id="_ctl0_ContentPlaceMasterPage__ctl0_grwA")
    rows = table.findAll("tr")
    logger.debug("Records on pages: %d" % len(rows[1:]))
    count_records_with_document = 0

    for record in rows[1:]:
        columns = record.findAll("td")  # columns of table in the row

        case_number = columns[1].getText().replace("\n", '').strip()
        # extract decision results
        decisions_str = str(columns[2]).replace("\n", '').strip()
        m = p_re_decisions.search(decisions_str)
        line = m.group(1)
        decision_result = [x.replace('\"', '\'').strip()
                           for x in re.split("</?br/?>", line)]
        decisions = ""
        if len(decision_result) > 1:
            decisions = "|".join(decision_result[1:])
            form_decision = decision_result[0]
        else:
            form_decision = decision_result[0]
        decision_result = decisions

        link_elem = columns[1].select_one('a[href*=SOUDNI_VYKON]')
        # link to the decision's document
        if link_elem is not None:
            link = link_elem['href']
            link = urljoin(base_url, link)
            count_records_with_document += 1
        else:
            continue  # case without document

        # registry mark isn't case number
        mark = case_number.split("-")[0].strip()

        court = columns[3].getText().replace("\n", '').strip()

        str_date = columns[4].getText().replace("\n", '').strip()

        #  extract sides to list
        sides = []
        for side in columns[5].contents:
            if side.string is not None:
                text = side.string.strip().replace('"', "'")
                if text != '':
                    sides.append(text)
            else:
                sides.extend([x.strip().replace('"', "'")
                              for x in re.split(r"</?br/?>", str(side)) if x != ''])

        complaint = columns[6].getText().strip()
        prejudicate = [x.text.strip() for x in columns[7].findAll("a")]
        prejudicate = [x for x in prejudicate if x != '']

        date = [x.strip() for x in str_date.split("/ ")]
        if len(date) >= 1:
            date = date[0]
            # convert date from format dd.mm.YYYY to YYYY-mm-dd
            date = datetime.strptime(date, '%d.%m.%Y').strftime('%Y-%m-%d')

        filename = ""
        if link is not None:
            case_year = link.split('/')[-2]
            filename = os.path.basename(link)
        logger.debug(
            "Contents: {}\nSides: {}; Complaint: {}; Year: {}; Prejudicate: {}\n{}".format(columns[5].contents,
                                                                                           sides,
                                                                                           complaint, case_year,
                                                                                           prejudicate, link))

        item = {
            "registry_mark": mark,
            "record_id": case_number,
            "decision_date": date,
            "court_name": court,
            "web_path": link,
            "local_path": filename,
            "decision_type": form_decision,
            "decision": decision_result,
            "order_number": case_number,
            "sides": make_json(sides) if len(sides) else None,
            "prejudicate": make_json(prejudicate) if len(prejudicate) else None,
            "complaint": complaint if len(complaint) else None,
            "case_year": case_year
        }

        writer_records.writerow(item)  # write item to CSV
        logger.debug(case_number)
    logger.debug("Find %s records with document on this page" %
                 count_records_with_document)
    return count_records_with_document


def extract_information(saved_pages, extract=None):
    """
    extract informations from HTML files and write to CSVs

    :param saved_pages: number of all saved pages
    :type extract: bool
    :param extract: flag which indicates type of extraction

    """

    html_files = [join(html_dir_path, fn)
                  for fn in next(os.walk(html_dir_path))[2]]
    if len(html_files) == saved_pages or extract:
        global writer_records

        fieldnames = ['court_name', 'record_id', 'registry_mark', 'decision_date',
                      'web_path', 'local_path', 'decision_type', 'decision',
                      'order_number', 'sides', 'complaint', 'prejudicate', 'case_year']
        csv_records = open(join(out_dir, output_file), 'w',
                           newline='', encoding="utf-8")

        writer_records = csv.DictWriter(
            csv_records, fieldnames=fieldnames, delimiter=";", quoting=csv.QUOTE_ALL)
        writer_records.writeheader()

        t = html_files
        count_documents = 0
        if progress:
            t = tqdm(t, ncols=global_ncols)
        for html_f in t:
            logger.debug(html_f)
            count_documents += make_record(make_soup(html_f))
        logger.info("%s records had a document" % count_documents)
        csv_records.close()
    else:
        logger.warning("Count of 'saved_pages'({}) and saved files({}) is differrent!".format(
            saved_pages, len(html_files)))


def view_data(row_count, mark_type, value, date_from=None, date_to=None, last=None):
    """
    sets forms parameters for viewing data

    :param row_count: haw many record would be showing on page
    :param last: how many days ago
    :type mark_type: text
    :param mark_type: text identificator of mark type
    :param value: mark type number identificator for formular
    :param date_from: start date of range
    :param date_to: end date of range

    """

    if last and session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_chkPrirustky"):
        logger.debug("Select check button")
        session.set_field_value(
            "#_ctl0_ContentPlaceMasterPage__ctl0_chkPrirustky", True)
        if session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_ddlPosledniDny"):
            logger.info("Select last %s days" % last)
            session.set_field_value(
                "#_ctl0_ContentPlaceMasterPage__ctl0_ddlPosledniDny", last)

    else:
        if date_from is not None:
            # setting range search
            logger.info("Records from the period %s -> %s", date_from, date_to)
            # id (input - text) = _ctl0_ContentPlaceMasterPage__ctl0_txtDatumOd
            if session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_txtDatumOd"):
                session.set_field_value(
                    "#_ctl0_ContentPlaceMasterPage__ctl0_txtDatumOd", date_from)
        if date_to is not None:
            # id (input - text) = _ctl0_ContentPlaceMasterPage__ctl0_txtDatumDo
            if session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_txtDatumDo"):
                session.set_field_value(
                    "#_ctl0_ContentPlaceMasterPage__ctl0_txtDatumDo", date_to)

    # shows several first records
    # change mark type in select
    if session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_ddlRejstrik"):
        logger.debug("Change mark type - %s", mark_type)
        session.set_field_value(
            "#_ctl0_ContentPlaceMasterPage__ctl0_ddlRejstrik", value)
    # time.sleep(1)
    if session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_ddlSortName"):
        session.set_field_value(
            "#_ctl0_ContentPlaceMasterPage__ctl0_ddlSortName", "2")
        session.set_field_value(
            "#_ctl0_ContentPlaceMasterPage__ctl0_ddlSortDirection", "0")

    if session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_rbTypDatum_1"):
        session.set_field_value(
            "#_ctl0_ContentPlaceMasterPage__ctl0_rbTypDatum_1", True)

    if session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_btnFind"):  # click on find button
        logger.debug("Click - find")
        session.click(
            "#_ctl0_ContentPlaceMasterPage__ctl0_btnFind", expect_loading=True)

        if b_screens:
            logger.debug("\t_find_screen_" + mark_type + ".png")
            session.capture_to(
                join(screens_dir_path, "_find_screen_" + mark_type + ".png"))
    # change value of row count on page
    if session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_ddlRowCount"):
        value, resources = session.evaluate(
            "document.getElementById('_ctl0_ContentPlaceMasterPage__ctl0_ddlRowCount').value")
        #print("value != '30'",value != "30")
        if value != "30":
            logger.debug("Change row count")
            session.set_field_value(
                "#_ctl0_ContentPlaceMasterPage__ctl0_ddlRowCount", str(row_count))

            if session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_btnChangeCount"):
                logger.debug("Click - Change")
                result, resources = session.click("#_ctl0_ContentPlaceMasterPage__ctl0_btnChangeCount",
                                                  expect_loading=True)
                if b_screens:
                    logger.debug("\tfind_screen_" + mark_type +
                                 "_change_row_count.png")
                    session.capture_to(
                        join(screens_dir_path, "/_find_screen_" + mark_type + "_change_row_count.png"))


def walk_pages(count_of_pages, case_type):
    """
    make a walk through pages of results

    :param count_of_pages: over how many pages we have to go
    :param case_type: name of type for easier identification of problem

    """

    last_file = str(count_of_pages) + "_" + case_type + ".html"
    if os.path.exists(join(html_dir_path, last_file)):
        logger.debug("Skip %s type <-- '%s' exists" % (case_type, last_file))
        return True
    logger.debug("count_of_pages: %d", count_of_pages)
    positions = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
    t = range(1, count_of_pages + 1)
    if progress:
        t = tqdm(t, ncols=global_ncols)  # progress progress bar
    for i in t:  # walk pages
        response = session.content
        #soup = BeautifulSoup(response,"html.parser")
        html_file = str(i) + "_" + case_type + ".html"
        if not os.path.exists(join(html_dir_path, html_file)):
            if session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_grwA"):
                extract_data(response, html_file)
                pass
        else:
            logger.debug("Skip file '%s'" % html_file)

        # TO DO - danger
        if i >= 12 and count_of_pages > 22:
            logger.debug("(%d) - %d < 10 --> %s <== (count_of_pages ) - (i) < 10 = Boolean", count_of_pages, i,
                         count_of_pages - i < 10)
            # special compute for last pages
            if count_of_pages - (i + 1) < 10:
                logger.debug(
                    "positions[(i-(count_of_pages))] = %d", positions[(i - count_of_pages)])
                page_number = str(positions[(i - count_of_pages)] + 12)
            else:
                page_number = "12"  # next page element has constant ID
        else:
            page_number = str(i + 1)  # few first pages

        logger.debug("Number = %s", page_number)

        if b_screens:
            session.capture_to(join(screens_dir_path, "find_screen_" + case_type + "_0" + str(i) + ".png"), None,
                               selector="#pagingBox0")

        if session.exists(
                "#_ctl0_ContentPlaceMasterPage__ctl0_pnPaging1_Repeater2__ctl" + page_number + "_LinkButton1") and i + 1 < (
                count_of_pages + 1):
            #link_id = "_ctl0_ContentPlaceMasterPage__ctl0_pnPaging1_Repeater2__ctl"+page_number+"_LinkButton1"
            link = "_ctl0:ContentPlaceMasterPage:_ctl0:pnPaging1:Repeater2:_ctl" + \
                page_number + ":LinkButton1"
            logger.debug("\tGo to next - Page %d (%s)", (i + 1), link)
            try:
                # result, resources = session.click("#"+link_id,
                # expect_loading=True)
                session.evaluate(
                    "WebForm_DoPostBackWithOptions(new WebForm_PostBackOptions(\"%s\", \"\", true, \"\", \"\", false, true))" % link,
                    expect_loading=True)
                #session.wait_for(page_has_loaded,"Timeout - next page",timeout=main_timeout)
                logger.debug("New page was loaded!")
            except Exception:
                logger.error(
                    "Error (walk_pages) - close browser", exc_info=True)
                logger.debug("error_(" + str(i + 1) + ").png")
                session.capture_to(
                    join(screens_dir_path, "error_(" + str(i + 1) + ").png"))
                return False
    return True


def process_court():
    """
    creates files for processing and saving data, start point for processing
    """
    d = {"Ads": '10', "Afs": '11', "Ars": '116', "As": '12',
         "Azs": '9', "Aos": '115', "Ans": '13', "Aps": '14'}
    #d = {"As" : '12'}
    case_types = OrderedDict(sorted(d.items(), key=lambda t: t[0]))
    row_count = 20
    global saved_pages
    global saved_records
    for case_type in case_types.keys():
        logger.info("-----------------------------------------------------")
        logger.info(case_type)
        view_data(row_count, case_type, case_types[
                  case_type], date_from=date_from, date_to=date_to, last=last)
        number_of_records, resources = session.evaluate(
            "document.getElementById('_ctl0_ContentPlaceMasterPage__ctl0_ddlRowCount').value")
        # number_of_records = "30" #hack pro testovani
        if number_of_records is not None and int(number_of_records) != row_count:
            logger.warning(int(number_of_records) != row_count)
            logger.error("Failed to display data")
            if b_screens:
                logger.debug("error_" + case_type + ".png")
                session.capture_to(
                    join(screens_dir_path, "error_" + case_type + ".png"))
            return False
        # my_result = session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_pnPaging1_Repeater3__ctl0_Label2")
        #print (my_result)
        if not session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_pnPaging1_Repeater3__ctl0_Label2"):
            logger.info("No records")
            continue
        info_elem, resources = session.evaluate(
            "document.getElementById('_ctl0_ContentPlaceMasterPage__ctl0_pnPaging1_Repeater3__ctl0_Label2').innerHTML")

        if info_elem:
            # number_of_records = "20" #hack pro testovani
            str_info = info_elem.replace("<b>", "").replace("</b>", "")
            number_of_records, count_of_pages = how_many(
                str_info, number_of_records)
        else:
            return False

        # testing
        # if count_of_pages >=5:
        #    count_of_pages = 5
        result = walk_pages(count_of_pages, case_type)
        saved_pages += count_of_pages
        saved_records += int(number_of_records)
        if not result:
            logger.warning("Result of 'walk_pages' is False")
            return False
        first_page()
    return True


def main():
    """
    main function of this program
    :return:
    """
    global ghost
    ghost = Ghost()
    global session
    session = ghost.start(
        download_images=False, show_scrollbars=False,
        wait_timeout=main_timeout, display=False,
        plugins_enabled=False)
    logger.info(u"Start - NSS")
    if view:
        session.display = True
        session.show()
    session.open(url)

    if b_screens:
        logger.debug("_screen.png")
        session.capture_to(join(screens_dir_path, "_screen.png"))
    logger.debug("=" * 20)
    logger.info("Download records")
    result = process_court()
    # print(result)
    if result:
        logger.info("DONE - download records")
        logger.debug("Closing browser")
        logger.info("It was saved {} records on {} pages".format(
            saved_records, saved_pages))
        # input(":-)")
        logger.debug("=" * 20)
        logger.info("Extract informations")
        extract_information(saved_pages)
        logger.info("DONE - extraction")
        logger.debug("=" * 20)
        if not b_download:  # debug without download
            logger.info("Download original files")
            data = load_data(join(out_dir, output_file))
            download_pdf(data)
            logger.info("DONE - Download files")
    else:
        logger.error("Error (main)- closing browser, exiting")
        return False

    return True


if __name__ == "__main__":
    options = parameters()
    out_dir = options["dir"]
    b_download = options["download"]
    date_from = options["date_from"]
    date_to = options["date_to"]
    b_screens = options["screens"]
    b_delete = options["delete"]
    last = options["last"]
    output_file = options["filename"]
    progress = options["progress"]
    view = options["view"]

    if ".csv" not in output_file:
        output_file += ".csv"

    if not os.path.exists(out_dir):
        os.mkdir(out_dir)
        print("Folder was created '" + out_dir + "'")
    set_logging()
    logger.info(hash_id)
    logger.debug(options)

    result_dir_path = join(out_dir, "result")
    out_dir = join(out_dir, working_dir)  # new outdir is working directory
    documents_dir_path = join(out_dir, documents_dir)
    html_dir_path = join(out_dir, html_dir)
    # result_dir_path = os.path.normpath(join(out_dir,join(os.pardir,"result")))

    screens_dir_path = create_directories()

    if options["extraction"]:
        logger.info("Only extract informations")
        extract_information(saved_pages, extract=True)
        logger.info("DONE - extraction")
        logger.info("Moving files")
        shutil.copy(join(out_dir, output_file), result_dir_path)
    else:
        if main():
            # move results of crawling
            if not os.listdir(result_dir_path):
                logger.info("Moving files")
                shutil.move(documents_dir_path, result_dir_path)
                shutil.move(join(out_dir, output_file), result_dir_path)
                if not b_delete:  # debug without cleaning
                    logger.info("Cleaning working directory")
                    clean_directory(out_dir)
            else:
                logger.error("Result directory isn't empty.")
                sys.exit(-1)
            sys.exit(0)
        else:
            sys.exit(-1)
