from typing import List, Tuple

from selenium.webdriver.chrome.webdriver import WebDriver

from constitutional_court.Pages.DetailPage import DetailPage
from constitutional_court.Pages.ResultsPage import ResultsPage


class BrowserFunction:
    def __init__(self, driver: WebDriver):
        self.browser = driver

    def open_detail_page_from_link(self, link: str) -> DetailPage:
        self.browser.get(link)
        detail_page = DetailPage(self.browser)
        return detail_page

    @staticmethod
    def browse_results(result_page: ResultsPage) -> List[str]:
        links: List[str] = result_page.get_links()
        links.extend([page.get_links() for page in result_page])
        return links

    def get_view_state_variables(self) -> Tuple[str, str, str]:
        view_state: str = self.browser.find_element_by_id('__VIEWSTATE').get_property('value')
        view_state_generator: str = self.browser.find_element_by_id('__VIEWSTATEGENERATOR').get_attribute('value')
        event_validation: str = self.browser.find_element_by_id('__EVENTVALIDATION').get_attribute('value')
        return view_state, view_state_generator, event_validation
