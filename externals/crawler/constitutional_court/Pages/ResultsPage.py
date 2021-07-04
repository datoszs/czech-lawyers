from selenium.common.exceptions import NoSuchElementException
from selenium.webdriver.chrome.webdriver import WebDriver

from ..Utils import Utils


class ResultsPage(object):
    __navigation = "(//tr[@class='resultHeaderCount'])[2]"
    __next_page = f"{__navigation}//a[contains(text(),'Další')]"
    __counter = f"{__navigation}//td[not(@align)]"
    __result_link = "//a[contains(@class,'resultData')]"
    __first_result_link = f"({__result_link})[1]"

    def __init__(self, driver: WebDriver):
        self.__driver = driver

    def __iter__(self):
        return self

    def __next__(self):
        try:
            next_button = self.__driver.find_element_by_xpath(self.__next_page)
        except NoSuchElementException:
            raise StopIteration()

        next_button.click()
        return ResultsPage(self.__driver)

    def get_links(self) -> list:
        elements = self.__driver.find_elements_by_xpath(self.__result_link)
        return [link.get_attribute("href") for link in elements]

    def get_first_link(self) -> str:
        return self.__driver.find_element_by_xpath(self.__first_result_link).get_attribute("href")

    def get_count_of_results(self) -> int:
        description = self.__driver.find_element_by_xpath(self.__counter).text
        return Utils.get_sum_from_description(description)
