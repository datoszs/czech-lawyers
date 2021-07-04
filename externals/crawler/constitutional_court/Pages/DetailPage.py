from selenium.common.exceptions import NoSuchElementException, TimeoutException
from selenium.webdriver.chrome.webdriver import WebDriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.wait import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC


class DetailPage(object):
    __next_page = "GotoNextId"
    __page_id = "docIdHidden"

    def __init__(self, driver: WebDriver):
        self.__driver = driver

    def __iter__(self):
        return self

    def __next__(self):
        try:
            next_button = self.__driver.find_element_by_id(self.__next_page)
        except TimeoutException:
            pass

        next_button.click()
        new_detail_page = DetailPage(self.__driver)

        if self.get_page_id() == new_detail_page.get_page_id():
            raise StopIteration()
        return new_detail_page

    def get_page_id(self):
        return self.__driver.find_element_by_id(self.__page_id).get_attribute("value")

    def walk_details():
        """
        make a walk through pages of detail

        """

        while not session.exists("#lbError"):
            pass
        # response = session.content
        # soup = BeautifulSoup(response, "html.parser")
        # page_id = "id=" + soup.select_one("#docIdHidden").get("value")

        # html_file = page_id + ".html"
        # if not os.path.exists(join(documents_dir_path, html_file)):
        #     logger.debug("Go to link to detail")
        #     process_detail_page(html_file, response=response)
        # session.click("#GotoNextId", expect_loading=True)
