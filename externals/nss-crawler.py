#!/usr/bin/env python
# -*- encoding: utf-8 -*-
# coding=utf-8

"""
Crawler of Czech Republic The Supreme Administrative Court
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
import pandas as pd
import re
import shutil
import subprocess
import sys
from bs4 import BeautifulSoup
from collections import OrderedDict
from datetime import datetime
from ghost import Ghost
from optparse import OptionParser
from os.path import join
from tqdm import tqdm
from urllib.parse import urljoin

base_url = "http://nssoud.cz/"
url = "http://nssoud.cz/main0Col.aspx?cls=JudikaturaBasicSearch&pageSource=0"
hash_id = datetime.now().strftime("%d-%m-%Y")
working_dir = "working"
screens_dir = "screens_" + hash_id
documents_dir = "documents"
txt_dir = "txt"
html_dir = "html"
log_dir = "log_nss"

main_timeout = 100000
global_ncols = 90
saved_pages = 0
saved_records = 0

b_screens = False  # capture screenshots?
# precompile regex
p_re_records = re.compile(r'(\d+)$')
p_re_decisions = re.compile(r'[a-z<>]{4}\s+(.+)\s+')


def set_logging():
    # settings of logging
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


#-----------------------------------------------------------------------
def create_directories():
    """
create working directories
"""
    for directory in [out_dir, documents_dir_path, html_dir_path, result_dir_path]:
        os.makedirs(directory, exist_ok=True)
        logger.info("Folder was created '" + directory + "'")

    if b_screens:
        screens_dir_path = join(out_dir, screens_dir)
        if not os.path.exists(screens_dir_path):
            os.mkdir(screens_dir_path)
            logger.info("Folder was created '" + screens_dir_path + "'")
        else:
            logger.debug("Erasing old screens")
            os.system("rm " + join(screens_dir_path, "*"))
        return screens_dir_path


#-----------------------------------------------------------------------
def logging_process(arguments):
    """
settings logging for subprocess
"""
    p = subprocess.Popen(arguments, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    stdout, stderr = p.communicate()
    if stdout:
        logger.debug("%s" % stdout)
    if stderr:
        logger.debug("%s" % stderr)


#-----------------------------------------------------------------------
def parameters():
    usage = "usage: %prog [options]"
    parser = OptionParser(usage)
    parser.add_option("-w", "--without-download", action="store_true", dest="download", default=False,
                      help="Not download PDF documents")
    parser.add_option("-n", "--not-delete", action="store_true", dest="delete", default=False,
                      help="Not delete working directory")
    parser.add_option("-d", "--output-directory", action="store", type="string", dest="dir", default="output_dir",
                      help="Path to output directory")
    parser.add_option("-f", "--date-from", action="store", type="string", dest="date_from", default=None,
                      help="Start date of range (d. m. yyyy)")
    parser.add_option("-t", "--date-to", action="store", type="string", dest="date_to", default=None,
                      help="End date of range (d. m. yyyy)")
    parser.add_option("-c", "--capture", action="store_true", dest="screens", default=False,
                      help="Capture screenshots?")
    parser.add_option("-o", "--output-file", action="store", type="string", dest="filename", default="metadata.csv",
                      help="Name of output CSV file")
    parser.add_option("-e", "--extraction", action="store_true", dest="extraction", default=False,
                      help="Make only extraction without download new data")
    (options, args) = parser.parse_args()
    options = vars(options)

    #print(args,options,type(options))
    return options


#-----------------------------------------------------------------------
def make_soup(path):
    soup = BeautifulSoup(codecs.open(path, encoding="utf-8"), "html.parser")
    return soup


#-----------------------------------------------------------------------
def how_many(str_info, displayed_records):
    """
find number of records and compute count of pages
:str_info: info element as string
:displayed_records: number of displayed records
"""
    m = p_re_records.search(str_info)
    number_of_records = m.group(1)
    count_of_pages = math.ceil(int(number_of_records) / int(displayed_records))
    logger.info("records: %s => pages: %s", number_of_records, count_of_pages)
    return number_of_records, count_of_pages


#-----------------------------------------------------------------------
def first_page():
    """
go to first page on find query
"""
    session.click("#_ctl0_ContentPlaceMasterPage__ctl0_pnPaging1_Repeater2__ctl0_Linkbutton2", expect_loading=True)


#-----------------------------------------------------------------------
def extract_data(response, html_file):
    """
save current page as HTML file for later extraction
:response: HTML code for saving
:html_file: name of saving file
"""
    logger.debug("Save file '%s'" % html_file)
    with codecs.open(join(html_dir_path, html_file), "w", encoding="utf-8") as f:
        f.write(response)


#-----------------------------------------------------------------------
def make_record(soup):
    """
extract relevant data from page
:soup: bs4 soup object
"""
    table = soup.find("table", id="_ctl0_ContentPlaceMasterPage__ctl0_grwA")
    rows = table.findAll("tr")
    logger.debug("Records on pages: %d" % len(rows[1:]))

    for record in rows[1:]:
        columns = record.findAll("td")  # columns of table in the row

        case_number = columns[1].getText().replace("\n", '').strip()
        # extract decision results
        decisions_str = str(columns[2]).replace("\n", '').strip()
        m = p_re_decisions.search(decisions_str)
        line = m.group(1)
        decision_result = [x.replace('\"', '\'').strip() for x in line.split("<br>")]
        form_decision = ""
        decisions = ""
        if len(decision_result) > 1:
            decisions = "|".join(decision_result[1:])
            form_decision = decision_result[0]
        else:
            form_decision = decision_result[0]
        decision_result = decisions

        link_elem = columns[1].select_one('a[href*=SOUDNI_VYKON]')
        link = ""
        # link to the decision's document
        if link_elem is not None:
            link = link_elem['href']
            link = urljoin(base_url, link)
        else:
            continue  # case without document

        mark = case_number.split("-")[0].strip()  # registry mark isn't case number

        court = columns[3].getText().replace("\n", '').strip()

        str_date = columns[4].getText().replace("\n", '').strip()
        date = [x.strip() for x in str_date.split("/ ")]
        if len(date) >= 1:
            date = date[0]
            # convert date from format dd.mm.YYYY to YYYY-mm-dd
            date = datetime.strptime(date, '%d.%m.%Y').strftime('%Y-%m-%d')

        filename = ""
        if link is not None:
            filename = os.path.basename(link)

        item = {
            "registry_mark": mark,
            "record_id": case_number,
            "decision_date": date,
            "court_name": court,
            "web_path": link,
            "local_path": filename,
            "decision_type": form_decision,
            "decision": decision_result,
            "order_number": case_number
        }

        writer_records.writerow(item)  # write item to CSV
        logger.debug(case_number)


#-----------------------------------------------------------------------
# noinspection PyGlobalUndefined,PyGlobalUndefined
def extract_information(saved_pages, extract=None):
    """
extract informations from HTML files and write to CSVs
:saved_pages: number of all saved pages
:extract: boolean flag which indicates type of extraction
"""
    html_files = [join(html_dir_path, fn) for fn in next(os.walk(html_dir_path))[2]]
    if len(html_files) == saved_pages or extract:
        global writer_links
        global writer_records

        fieldnames = ['court_name', 'record_id', 'registry_mark', 'decision_date', 'web_path', 'local_path', 'decision_type',
                      'decision', 'order_number']
        csv_records = open(join(out_dir, output_file), 'w', newline='', encoding="utf-8")

        writer_records = csv.DictWriter(csv_records, fieldnames=fieldnames, delimiter=";", quoting=csv.QUOTE_ALL)
        writer_records.writeheader()

        t = tqdm(html_files, ncols=global_ncols)
        for html_f in t:
            logger.debug(html_f)
            make_record(make_soup(html_f))
            t.update()
            #print(i)
            """i += 1
            if i==80:
                break"""

        csv_records.close()
    else:
        logger.warning("len(html_files) == saved_pages = %s" % len(html_files) == saved_pages)


#-----------------------------------------------------------------------
def view_data(row_count, mark_type, value, date_from=None, date_to=None):
    """
sets forms parameters for viewing data
:mark_type: text identificator of mark type
:value: mark type number identificator for formular
:date_from: start date of range
:date_to: end date of range
"""
    if date_from is not None:
        # setting range search
        logger.info("Records from the period %s -> %s", date_from, date_to)
        # id (input - text) = _ctl0_ContentPlaceMasterPage__ctl0_txtDatumOd
        if session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_txtDatumOd"):
            session.set_field_value("#_ctl0_ContentPlaceMasterPage__ctl0_txtDatumOd", date_from)
    if date_to is not None:
        # id (input - text) = _ctl0_ContentPlaceMasterPage__ctl0_txtDatumDo
        if session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_txtDatumDo"):
            session.set_field_value("#_ctl0_ContentPlaceMasterPage__ctl0_txtDatumDo", date_to)

    # shows several first records
    if session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_ddlRejstrik"):  # change mark type in select
        logger.debug("Change mark type - %s", mark_type)
        session.set_field_value("#_ctl0_ContentPlaceMasterPage__ctl0_ddlRejstrik", value)
    #time.sleep(1)
    if session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_ddlSortName"):
        session.set_field_value("#_ctl0_ContentPlaceMasterPage__ctl0_ddlSortName", "2")
        session.set_field_value("#_ctl0_ContentPlaceMasterPage__ctl0_ddlSortDirection", "0")

    if session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_btnFind"):  # click on find button
        logger.debug("Click - find")
        session.click("#_ctl0_ContentPlaceMasterPage__ctl0_btnFind", expect_loading=True)

        if b_screens:
            logger.debug("\t_find_screen_" + mark_type + ".png")
            session.capture_to(join(screens_dir_path, "_find_screen_" + mark_type + ".png"))
    # change value of row count on page
    if session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_ddlRowCount"):
        value, resources = session.evaluate(
            "document.getElementById('_ctl0_ContentPlaceMasterPage__ctl0_ddlRowCount').value")
        #print("value != '30'",value != "30")
        if value != "30":
            logger.debug("Change row count")
            session.set_field_value("#_ctl0_ContentPlaceMasterPage__ctl0_ddlRowCount", str(row_count))

            if session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_btnChangeCount"):
                logger.debug("Click - Change")
                result, resources = session.click("#_ctl0_ContentPlaceMasterPage__ctl0_btnChangeCount",
                                                  expect_loading=True)
                if b_screens:
                    logger.debug("\tfind_screen_" + mark_type + "_change_row_count.png")
                    session.capture_to(join(screens_dir_path, "/_find_screen_" + mark_type + "_change_row_count.png"))


# -----------------------------------------------------------------------
def walk_pages(count_of_pages, case_type):
    """
make a walk through pages of results
:count_of_pages: over how many pages we have to go
:case_type: name of type for easier identification of problem
"""
    last_file = str(count_of_pages) + "_" + case_type + ".html"
    if os.path.exists(join(html_dir_path, last_file)):
        logger.debug("Skip %s type <-- '%s' exists" % (case_type, last_file))
        return True
    logger.debug("count_of_pages: %d", count_of_pages)
    positions = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
    t = tqdm(range(1, count_of_pages + 1), ncols=global_ncols)
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

        ## TO DO - danger
        if i >= 12 and count_of_pages > 22:
            logger.debug("(%d) - %d < 10 --> %s <== (count_of_pages ) - (i) < 10 = Boolean", count_of_pages, i,
                         count_of_pages - i < 10)
            # special compute for last pages
            if count_of_pages - (i + 1) < 10:
                logger.debug("positions[(i-(count_of_pages))] = %d", positions[(i - count_of_pages)])
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
            link = "_ctl0:ContentPlaceMasterPage:_ctl0:pnPaging1:Repeater2:_ctl" + page_number + ":LinkButton1"
            logger.debug("\tGo to next - Page %d (%s)", (i + 1), link)
            try:
                #result, resources = session.click("#"+link_id, expect_loading=True)
                session.evaluate(
                    "WebForm_DoPostBackWithOptions(new WebForm_PostBackOptions(\"%s\", \"\", true, \"\", \"\", false, true))" % link,
                    expect_loading=True)
                #session.wait_for(page_has_loaded,"Timeout - next page",timeout=main_timeout)
                logger.debug("New page was loaded!")
            except Exception:
                logger.error("Error (walk_pages) - close browser", exc_info=True)
                logger.debug("error_(" + str(i + 1) + ").png")
                session.capture_to(join(screens_dir_path, "error_(" + str(i + 1) + ").png"))
                return False
    return True


#-----------------------------------------------------------------------
def process_court():
    """
creates files for processing and saving data, start point for processing
"""
    d = {"Ads": '10', "Afs": '11', "Ars": '116', "As": '12', "Azs": '9'}
    #d = {"As" : '12'}
    case_types = OrderedDict(sorted(d.items(), key=lambda t: t[0]))
    row_count = 20
    global saved_pages
    global saved_records
    for case_type in case_types.keys():
        logger.info("-----------------------------------------------------")
        logger.info(case_type)
        view_data(row_count, case_type, case_types[case_type], date_from=date_from, date_to=date_to)
        number_of_records, resources = session.evaluate(
            "document.getElementById('_ctl0_ContentPlaceMasterPage__ctl0_ddlRowCount').value")
        #number_of_records = "30" #hack pro testovani
        if number_of_records is not None and int(number_of_records) != row_count:
            logger.warning(int(number_of_records) != row_count)
            logger.error("Failed to display data")
            if b_screens:
                logger.debug("error_" + case_type + ".png")
                session.capture_to(join(screens_dir_path, "error_" + case_type + ".png"))
            return False
        #my_result = session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_pnPaging1_Repeater3__ctl0_Label2")
        #print (my_result)
        if not session.exists("#_ctl0_ContentPlaceMasterPage__ctl0_pnPaging1_Repeater3__ctl0_Label2"):
            logger.info("No records")
            continue
        info_elem, resources = session.evaluate(
            "document.getElementById('_ctl0_ContentPlaceMasterPage__ctl0_pnPaging1_Repeater3__ctl0_Label2').innerHTML")

        if info_elem:
            #number_of_records = "20" #hack pro testovani
            str_info = info_elem.replace("<b>", "").replace("</b>", "")
            number_of_records, count_of_pages = how_many(str_info, number_of_records)
        else:
            return False

        #testing
        #if count_of_pages >=5:
        #    count_of_pages = 5
        result = walk_pages(count_of_pages, case_type)
        saved_pages += count_of_pages
        saved_records += int(number_of_records)
        if not result:
            logger.warning("Result of 'walk_pages' is False")
            return False
        first_page()
    return True


#-----------------------------------------------------------------------
def load_data(csv_file):
    """
load data from csv file
:csv_file: name of CSV file
"""
    data = pd.read_csv(csv_file, sep=";", na_values=['', "nan"])
    return data


#-----------------------------------------------------------------------
def download_pdf(data):
    """
download PDF files
:data: Pandas data frame object
"""
    frame = data[["web_path", "local_path"]].dropna()
    t = tqdm(frame, ncols=global_ncols)
    for row in frame.itertuples():
        filename = row[2]
        if not os.path.exists(join(documents_dir_path, filename)):
            logging_process(["curl", row[1], "-o", join(documents_dir_path, filename)])
        t.update()


#-----------------------------------------------------------------------
def main():
    global ghost
    ghost = Ghost()
    global session
    session = ghost.start(
        download_images=False, show_scrollbars=False,
        wait_timeout=main_timeout, display=False,
        plugins_enabled=False)
    logger.info(u"Start - NSS")
    session.open(url)

    if b_screens:
        logger.debug("_screen.png")
        session.capture_to(join(screens_dir_path, "_screen.png"))
    logger.debug("=" * 20)
    logger.info("Download records")
    result = process_court()
    #print(result)
    if result:
        logger.info("DONE - download records")
        logger.debug("Closing browser")
        logger.info("It was saved %s records on %s pages" % (saved_records, saved_pages))
        #input(":-)")
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
        logger.error("Error (main)- closing browser")
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
    output_file = options["filename"]

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
        shutil.move(join(out_dir, output_file), result_dir_path)
    else:
        if main():
            # move results of crawling
            if not os.listdir(result_dir_path):
                logger.info("Moving files")
                shutil.move(documents_dir_path, result_dir_path)
                shutil.move(join(out_dir, output_file), result_dir_path)
                if not b_delete:
                    logger.debug("I remove working directory")
                    #logging_process(["rm", "-rf", out_dir])
                    shutil.rmtree(out_dir)
                    os.makedirs(out_dir, exist_ok=True)
            else:
                logger.error("Result directory isn't empty.")
                sys.exit(-1)
            sys.exit(42)
        else:
            sys.exit(-1)
