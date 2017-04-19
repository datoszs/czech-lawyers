#!/usr/bin/env python
# -*- encoding: utf-8 -*-
# coding=utf-8

"""
Base class for crawlers

"""

import codecs
import logging
import os
import shutil
import subprocess
from datetime import datetime
from optparse import OptionParser
from os.path import join
from ghost import Ghost
from bs4 import BeautifulSoup

def parameters():
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

    #print(args,options,type(options))
    return options


class BaseCrawler(object):
    """
    Base class for crawlers

    """

    def __init__(self, court, options, html=None):
        self.court = court
        self.hash_id = datetime.now().strftime("%d-%m-%Y")
        self.options = options
        self.dir_path = {}
        self.url = {
            "nss": {
                "base": 'http://nssoud.cz',
                "form": 'http://nssoud.cz/main0Col.aspx?cls=JudikaturaBasicSearch&pageSource=0'
            },
            "us": {
                "base": 'http://nalus.usoud.cz/',
                "form": 'http://nalus.usoud.cz/Search/Search.aspx',
                "result": 'http://nalus.usoud.cz/Search/Result.aspx'
            }
        }

    def make_connection(self):
        global ghost
        ghost = Ghost()
        global session
        session = ghost.start(
                download_images=False, show_scrollbars=False,
                wait_timeout=10000, display=False,
                plugins_enabled=False)
        logger.info(u"Start - %s" % str(self.court).upper())
        session.open(self.url[self.court]['form'])
        self.ghost = ghost
        self.session = session

    def set_logging(self, log_dir):
        """
        settings of logging

        """
        file = self.__class__.__name__
        global logger
        logger = logging.getLogger(file)
        self.logger = logger
        logger.setLevel(logging.DEBUG)
        fh_d = logging.FileHandler(join(log_dir, file + "_" + self.hash_id + "_log_debug.txt"),
                                   mode="w", encoding='utf-8')
        fh_d.setLevel(logging.DEBUG)
        fh_i = logging.FileHandler(join(log_dir, file + "_" + self.hash_id + "_log.txt"),
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

    @staticmethod
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

    def create_directories(self, log_dir, html, out_dir="output_dir", result="result", documents="documents",
                           working="working"):
        """
        create working directories

        :param log_dir: Name of folder for logs
        :param html:  Name of folder with processing HTML documents
        :param out_dir: Path to output folder
        :param result: Name of folder with results
        :param documents: Name of folder with documents
        :param working: Name of working directory

        """
        log_dir = join(os.path.dirname(os.path.abspath(__file__)), log_dir)
        if not os.path.exists(log_dir):
            os.mkdir(log_dir)
            print("Log folder was created '" + log_dir + "'")
            self.dir_path["log_dir"] = log_dir
        self.set_logging(log_dir)

        if not os.path.exists(out_dir):
            os.mkdir(out_dir)
            logger.info("Output folder was created '" + out_dir + "'")
        self.dir_path["out_dir"] = out_dir

        for directory in [result, working]:
            dir = join(out_dir, directory)
            os.makedirs(dir, exist_ok=True)
            logger.info("Folder was created '" + dir + "'")
            self.dir_path[directory] = dir
            if directory == working:
                subdir = join(dir, documents)
                os.makedirs(subdir, exist_ok=True)
                logger.info("Subfolder was created '" + subdir + "'")
                self.dir_path[documents] = subdir
                if html is not None:
                    subdir = join(out_dir, join(directory, html))
                    os.makedirs(subdir, exist_ok=True)
                    logger.info("Subfolder was created '" + subdir + "'")
                self.dir_path[html] = subdir

        if self.options.screens:
            dir = join(out_dir, "screens")
            if not os.path.exists(dir):
                os.mkdir(dir)
                logger.info("Folder was created '" + dir + "'")
            else:
                logger.debug("Erasing old screens")
                shutil.rmtree(dir)
                os.makedirs(dir, exist_ok=True)
            self.dir_path["screens"] = dir

    @staticmethod
    def make_soup(path):
        """
        make soup object from file

        """
        soup = BeautifulSoup(codecs.open(path, encoding="utf-8"), "html.parser")
        return soup

    def move_files(self):
        if not os.listdir(self.dir_path["result"]):
            logger.info("Moving files")
            shutil.move(self.dir_path["documents"], self.dir_path["result"])
            shutil.move(join(self.dir_path["working"], self.options.filename), self.dir_path["result"])
            if not self.options.delete:
                logger.debug("Cleaning working directory")
                # logging_process(["rm", "-rf", out_dir])
                shutil.rmtree(self.dir_path["html"])
                # os.makedirs(out_dir, exist_ok=True)
        else:
            logger.error("Result directory isn't empty.")
            raise SystemError("Result directory isn't empty.")

    def extract_data(self, html_file, response):
        """
        save detail page as HTML file for later extraction

        :html_file: name of saving file
        :response: HTML code for saving

        """
        logger.debug("Save file '%s'" % html_file)
        with codecs.open(join(self.dir_path["html"], html_file), "w", encoding="utf-8") as f:
            f.write(response)

    def how_many(self):
        """
        Find number of records and compute count of pages.

        :type str_info: string
        :param str_info: info element as string

        :type displayed_records: int
        :param displayed_records: number of displayed records

        """
        raise NotImplementedError("Please Implement this method.")

    def prepare_record(self, soup, id=None):
        """
        extract relevant data from page

        :param soup: bs4 soup object

        """
        raise NotImplementedError("Please Implement this method.")

    def save_record(self):
        # write item to CSV
        raise NotImplementedError("Please Implement this method.")


