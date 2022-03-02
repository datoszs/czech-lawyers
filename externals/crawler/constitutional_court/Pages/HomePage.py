from logging import Logger

from selenium.webdriver.support.select import Select
from selenium.webdriver.chrome.webdriver import WebDriver
from .ResultsPage import ResultsPage
from ...searching_criteria import SearchingCriteria


class HomePage:
    def __init__(self, driver: WebDriver):
        self.__driver = driver
        # self.__logger = logger

    def fill_form(self, criteria: SearchingCriteria):
        # if days or Utils.is_newer_case(date_from):
        #     self.set_a_special_type()

        if criteria.days:
            self.__set_for_last_days(criteria.days)
        else:
            self.__set_date_range(criteria.date_from, criteria.date_to)

        self.__set_ordering()
        self.__set_count_of_records_per_page(criteria.per_page)

    def search(self) -> ResultsPage:
        # logger.debug("Click to search button")
        self.__driver.find_element_by_id("ctl00_MainContent_but_search").click()
        return ResultsPage(self.__driver)

    def __set_count_of_records_per_page(self, records_per_page: int):
        # logger.debug("Set counter records per page")

        sel = Select(self.__driver.find_element_by_id("ctl00_MainContent_resultsPageSize"))
        sel.select_by_value(str(records_per_page))

    def __set_ordering(self):
        # logger.debug("Set sorting criteria")
        sel = Select(self.__driver.find_element_by_id("ctl00_MainContent_razeni"))
        sel.select_by_value("3")

    def __set_date_range(self, date_from: str, date_to: str):
        self.__set_date_from(date_from)
        # make_screen("set_from")
        if date_to is not None:  # ctl00_MainContent_decidedFrom
            self.__set_date_to(date_to)
        # logger.info("Records from the period %s -> %s", date_from, date_to)

        # make_screen("set_to")

    def __set_date_to(self, date_to):
        # logger.debug("Set date_to '%s'" % date_to)
        self.__driver.find_element_by_id("ctl00_MainContent_submissionTo").send_keys(date_to)

    def __set_date_from(self, date_from):
        # logger.debug("Set date_from '%s'" % date_from)
        self.__driver.find_element_by_id("ctl00_MainContent_submissionFrom").send_keys(date_from)

    def __set_for_last_days(self, days):
        # logger.debug("Select check button")
        self.__driver.find_element_by_id("ctl00_MainContent_dle_data_zpristupneni").click()
        # logger.info("Select last %s days" % days)
        self.__driver.find_element_by_id("ctl00_MainContent_zpristupneno_pred").send_keys(days)

    def __set_a_special_type(self):
        # print(session.content)
        # logger.debug("Set typ_rizeni as 'O ústavních stížnostech'")

        self.__driver.find_element_by_id("bSave").click()
        # session.open(search_url)
