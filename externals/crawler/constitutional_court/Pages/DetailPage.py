from selenium.common.exceptions import TimeoutException
from selenium.webdriver.chrome.webdriver import WebDriver
from selenium.webdriver.support.wait import WebDriverWait


class DetailPage:
    __next_page = "GotoNextId"
    __page_id = "docIdHidden"
    __error_label = "lbError"

    def __init__(self, driver: WebDriver):
        self.__driver = driver
        self.page_id = self.__get_page_id()

    def __iter__(self):
        return self

    def __next__(self):
        next_button = self.__driver.find_element_by_id(self.__next_page)

        next_button.click()
        self._stop_if_error_present()
        return DetailPage(self.__driver)

    def get_content(self) -> str:
        return self.__driver.page_source

    def __get_page_id(self) -> str:
        return self.__driver.find_element_by_id(self.__page_id).get_attribute("value")

    def _stop_if_error_present(self):
        try:
            error_label = WebDriverWait(self.__driver, 0).until(
                lambda x: self.__driver.find_element_by_id(self.__error_label)
            )
            if error_label:
                raise StopIteration()
        except TimeoutException:
            pass
