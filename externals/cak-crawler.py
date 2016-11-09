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
import re
from datetime import datetime
from optparse import OptionParser
from bs4 import BeautifulSoup, SoupStrainer
from ghost import Ghost
from tqdm import tqdm
from os.path import join

try:
    from urllib.parse import urljoin
except ImportError:
    from urlparse import urljoin

base_url = "http://vyhledavac.cak.cz/"
url = "http://vyhledavac.cak.cz/Units/_Search/search.aspx"
hash_id = datetime.now().strftime("%d-%m-%Y")
working_dir = "working"
documents_dir = "documents"
log_dir = "log_cak"

main_timeout = 5000
only_a_tags = SoupStrainer("td > a")

b_screens = False  # capture screenshots?
# precompile regex
p_re_records = re.compile(r'.+: (\d+), .+ (\d+)\.$')
p_re_decisions = re.compile(r'[a-z<>]{4}\s+(.+)\s+')
p_re_psc = re.compile(r'(\d{3}\s?\d{2})')
p_re_name = re.compile(r'^((\w+\. )+)?(([A-Ž]\w+ )+)(, (\w+\. ?)+)?$', re.UNICODE)


def set_logging():
    """settings of logging"""
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
    formatter = logging.Formatter(u'%(asctime)s - %(funcName)-15s - %(levelname)-8s: %(message)s')
    ch.setFormatter(formatter)
    fh_d.setFormatter(formatter)
    fh_i.setFormatter(formatter)
    # add the handlers to logger
    logger.addHandler(ch)
    logger.addHandler(fh_d)
    logger.addHandler(fh_i)


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
    (options, args) = parser.parse_args()
    options = vars(options)

    #print(args,options,type(options))
    return options


def create_directories():
    """
    create working directories
    """
    for directory in [out_dir, documents_dir_path, result_dir_path]:
        os.makedirs(directory, exist_ok=True)
        logger.info("Folder was created '" + directory + "'")


def make_soup(path):
    """
    Make soup from file for further processing
    :param path: path to file
    :return: bs4 object Soup
    """
    soup = BeautifulSoup(codecs.open(path, encoding="utf-8"), "html.parser")
    return soup


def make_record(soup, html_file, id=None):
    """
    Find all the information, make a record in CSV file
    :param soup: bs4 object Soup
    :param html_file: path to source file
    """
    if soup is not None:
        name_label = soup.find("td", string=re.compile(r"^\s+Jméno\s+$", re.UNICODE))
        name = surname = ""
        after = before = ""
        state = ""
        specialization = email = ""
        city = street = postal_area = ""
        ic = evidence_number = ""
        id = os.path.basename(html_file)[:-5]

        #print("\nname:",name_label.find_next().prettify())
        if name_label is not None:
            name_str = name_label.find_next().getText().strip()
            #m = p_re_name.search(name_str)
            #print("\n",m.groups())

            there_is = "," in name_str

            ### remove titles from name ###
            orig_units = [name.strip() for name in name_str.split(' ')]  # for searching
            if '' in orig_units:
                orig_units.remove('')
            units = orig_units.copy()

            if there_is:  # in text is comma
                first = name_str.split(",")[0]
                if len(first) < 10:
                    units = orig_units.copy()
                else:
                    units = (first + ",").split(" ")

            if '' in units:
                units.remove('')
            copy = units.copy()
            #print("original",units)
            for unit in units:
                if "." in unit:
                    copy.remove(unit)
                elif re.compile(r'^(MBA|BA|et|BPA|BBA|MPA|DBA|Mag),?$').match(unit):
                    #print("\n")
                    #logger.info("%s --> %s" % (name_str,unit))
                    #input(":-)")
                    copy.remove(unit)

            try:
                name = copy[0]
            except IndexError:
                for i in [name_str, there_is, orig_units, units, copy]:
                    logger.debug(i)

            surname = " ".join(copy[1:]).replace(",", "")
            index_name = orig_units.index(name)
            index_surname = orig_units.index(copy[-1])
            #print(index_name, index_surname)
            before = " ".join(orig_units[0:index_name])
            after = " ".join(orig_units[index_surname + 1:])
            name = name.capitalize()
            surname = " ".join([item.capitalize() for item in surname.split(" ")])
        #print("'%s', '%s'" % (before,after))

        mailto = soup.select("a[href*=mailto:]")
        emails = [mail.getText().strip().replace('  ', '@') for mail in mailto]
        if len(emails) > 0:
            email = ", ".join(list(set(emails)))

        specialization_label = soup.find("td", string=re.compile(r'^\s+Zaměření\s+$', re.UNICODE))
        if specialization_label is not None:
            specializations = specialization_label.parent.find_next_siblings()
            specialization = []
            for one_spec in [spec.getText().strip() for spec in specializations]:
                if re.search(r'^\d{2} .+$', one_spec):
                    specialization.append(one_spec)
            specialization = "|".join(specialization)

        ic_label = soup.find("td", string=re.compile(r'^\s+IČ\s+$', re.UNICODE))
        if ic_label is not None:
            ic = ic_label.find_next().getText().strip()
            if u"číslo" in ic:
                #print("\n")
                #print(ic_label,len(ic_label))
                #print(ic, html_file)
                m = re.compile(r"(\d{8})", re.UNICODE).search(ic)
                if m is not None:
                    #print(m.groups())
                    ic = m.group(1)
        evidence_label = soup.find("td", string=re.compile(r"^\s+Evidenční číslo\s+$", re.UNICODE))
        if evidence_label is not None:
            evidence_number = evidence_label.find_next().getText().strip()

        state_label = soup.find("td", string=re.compile(r"^\s+Stav\s+$", re.UNICODE))
        if state_label is not None:
            state = state_label.find_next().getText().strip()

        location_label = soup.find("td", string=re.compile(r"^\s+Adresa\s+$", re.UNICODE))
        if location_label is not None:
            street = location_label.find_next().getText().strip()
            city_str = location_label.parent.find_next_sibling().find_next_sibling()
            m_pa = p_re_psc.search(city_str.getText().strip())
            if m_pa is None:  # bad field
                #print(city_str.getText().strip())
                city_str = city_str.find_previous_sibling()
                m_pa = p_re_psc.search(city_str.getText().strip())
            if m_pa is not None:
                postal_area = m_pa.group().replace(' ', '')
                #print("city: ", city_str.getText().strip().split(m_pa.group()), "postal_area:", postal_area)
                city = city_str.getText().strip().split(m_pa.group())[1].strip()

                #print(city_str.getText().strip())
                #city = city_str.getText().strip()

        item = {
            "remote_identificator": id,
            "identification_number": evidence_number,
            "registration_number": ic,
            "name": name,
            "surname": surname,
            "degree_before": before,
            "degree_after": after,
            "state": state,
            "email": email,
            "street": street,
            "city": city,
            "postal_area": postal_area,
            "specialization": specialization,
            "local_path": os.path.basename(html_file)
        }
        logger.debug(item)
        writer_records.writerow(item)


def check_records(page_from, pages):
    """
    Checking new records across complete database of ČAK
    :param page_from: number of first page
    :param pages: number of last page
    :return: dictionary with links to new records
    """
    list_of_links = []
    for page in tqdm(range(page_from, pages + 1)):
        #session.capture_to(str(page)+".png",selector= "#mainContent_gridResult > tbody > tr:nth-child(52) > td")
        #html_file
        #extract_data(session.content,"list_result.html")
        soup = BeautifulSoup(session.content, "html.parser")
        links = soup.select("a[href*=/Units/_Search/Details/detailAdvokat]")

        #logger.debug("Links = %s" % len(links))
        if len(links) > 0:
            logger.debug("current page is: %d", page)
            for link in links:
                #print(U"%s" % link.text.encode("utf-8"))
                original_link = link["href"]
                id = original_link.split("?")[1][3:]

                #if not os.path.exists(join(documents_dir_path, id)):
                jmeno = link.text.strip()
                if jmeno != "":
                    #logger.debug(U"%s" % jmeno)
                    if not os.path.exists(join(documents_dir_path, id + ".html")):
                        #import urllib.request
                        #urllib.request.urlretrieve(urljoin(base_url, original_link), join(documents_dir_path, id + ".html"))
                        list_of_links.append({"url": urljoin(base_url, original_link), "id": id, "text": jmeno})
        session.evaluate("javascript:__doPostBack('ctl00$mainContent$gridResult','Page$" + str(page + 1) + "')",
                         expect_loading=True)  #  go to next page
    #logger.info("New records %s" % len(list_of_links))
    return list_of_links


def extract_data(response, html_file):
    """save current page as HTML file for later extraction"""
    logger.debug("Save file '%s'" % html_file)
    with codecs.open(join(documents_dir_path, html_file), "w", encoding="utf-8") as f:
        f.write(response)


def how_many(str_info, displayed_records):
    """find number of records and compute count of pages
    :param str_info: info element as string
    :param displayed_records: number of displayed records
    :return: tuple number of records and count of pages
    """
    number_of_records, count_of_pages = 0, 0
    m = p_re_records.search(str_info)
    if m is not None:
        part_1 = m.group(1)
        part_2 = m.group(2)
        number_of_records = int(part_1) + int(part_2)  # advocates are not in order
        count_of_pages = math.ceil(number_of_records / int(displayed_records))
        logger.info("records: %s => pages: %s", number_of_records, count_of_pages)
    return number_of_records, count_of_pages


def extract_information(list_of_links):
    """
    Choosing relevant files for extraction
    :param list_of_links:
    """
    # fieldnames = ['before', 'name', 'surname', 'after', 'state', 'ic', 'evidence_number', 'street', 'city',
    # 'file']
    fieldnames = ["remote_identificator", "identification_number", "registration_number", "name", "surname",
                  "degree_before", "degree_after", "state", "street", "city", "postal_area", "local_path",
                  'email', "specialization"]

    global writer_records

    csv_records = open(join(out_dir, output_file), 'w', newline='', encoding="utf-8")

    writer_records = csv.DictWriter(csv_records, fieldnames=fieldnames, delimiter=";")
    writer_records.writeheader()

    if list_of_links is None:  # all records in directory
        list_of_links = [fn for fn in next(os.walk(documents_dir_path))[2]]
        for html_file in tqdm(list_of_links):
            logger.debug(html_file)
            make_record(make_soup(join(documents_dir_path, html_file)), join(documents_dir_path, html_file))
    else:  # only new records
        for html_file in tqdm(list_of_links):
            make_record(
                make_soup(join(documents_dir_path, html_file["id"] + ".html")),
                join(documents_dir_path, html_file["id"] + ".html"),
                id=html_file["id"]
            )


def main():
    """
    Go to across pages
    """
    if not b_extraction:
        global ghost
        ghost = Ghost()
        global session
        session = ghost.start(download_images=False, show_scrollbars=False, wait_timeout=5000, display=False,
                              plugins_enabled=False)
        logger.info("Start - ČAK")
        session.open(url)
        list_of_links = None
        if session.exists("#mainContent_btnSearch") and not b_extraction:
            session.click("#mainContent_btnSearch", expect_loading=True)
            if session.exists("#mainContent_gridResult"):
                value, resources = session.evaluate("document.getElementById('mainContent_lblResult').innerHTML")
                logger.debug(value)
                records, pages = how_many(value, 50)
                #pages = 99 # hack for testing
                page_from = 1
                logger.info("Checking records...")
                list_of_links = check_records(page_from, pages)
        if len(list_of_links) > 0:
            logger.info("Dowload new records")
            for record in tqdm(list_of_links):
                #print(record)#,record["url"],record["id"])
                # may it be wget?
                if not os.path.exists(join(documents_dir_path, record["id"] + ".html")):
                    import urllib.request
                    urllib.request.urlretrieve(record["url"], join(documents_dir_path, record["id"] + ".html"))
                    #session.open(record["url"])
                    #response = str(urlopen(record["url"]).read())
                    #response = session.content
                    #extract_data(response, record["id"]+".html")

            logger.info("Download  - DONE")
            session.exit()
            ghost.exit()
        else:
            logger.info("Not found new records")
            list_of_links = None

        if list_of_links is not None:
            logger.info("Extract information...")
            extract_information(list_of_links)
            logger.info("Extraction  - DONE")
    else:
        if b_extraction:
            logger.info("Only extract information...")
            extract_information(list_of_links=None)
            logger.info("Extraction  - DONE")
    return True

if __name__ == "__main__":
    options = parameters()
    b_extraction = options["extraction"]
    output_file = options["filename"]
    out_dir = options["dir"]
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

    if main():
        # move results of crawling
        if not os.listdir(result_dir_path):
            logger.info("Moving files")
            shutil.move(documents_dir_path, result_dir_path)
            shutil.move(join(out_dir, output_file), result_dir_path)
            return 0
        else:
            return -1
