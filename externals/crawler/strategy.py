from typing import Protocol
from typing import Protocol
from urllib.parse import urljoin

from httpx import Client, Response, Cookies

from browser_function import BrowserFunction
from constitutional_court.Pages.ResultsPage import ResultsPage
from constitutional_court.Utils import Utils
from file import FileProvider

base_action_url = "http://nalus.usoud.cz/Search/"
search_url = urljoin(base_action_url, "Search.aspx")
results_url = urljoin(base_action_url, "Results.aspx")


class IStrategy(Protocol):
    browser_function: BrowserFunction
    file_provider: FileProvider

    def run(self, result_page: ResultsPage) -> None:
        ...


class StrategyFactory:
    def __init__(self, browser_function: BrowserFunction, file_provider: FileProvider):
        self.browser_function = browser_function
        self.file_provider = file_provider

    def get_strategy(self, count_of_results: int) -> IStrategy:
        if isinstance(count_of_results, int) and count_of_results < 1000:
            return WalkThroughDetailsStrategy(self.browser_function, self.file_provider)
        elif isinstance(count_of_results, int):
            return OpenDetailLinkStrategy(self.browser_function, self.file_provider)
        elif count_of_results is None:
            return UsingViewStateStrategy(self.browser_function, self.file_provider)


class WalkThroughDetailsStrategy(IStrategy):
    def __init__(self, browser_function: BrowserFunction, file_provider: FileProvider):
        self.browser_function = browser_function
        self.file_provider = file_provider

    def run(self, result_page: ResultsPage) -> None:
        link = result_page.get_first_link()
        detail_page = self.browser_function.open_detail_page_from_link(link)
        page_id = detail_page.page_id
        print(page_id)
        for page in detail_page:
            print(page.page_id)  # call save file with __preprocessing
            file_identificator = page.page_id
            content = page.get_content()
            self.file_provider.store_file(file_identificator, content)


class OpenDetailLinkStrategy(IStrategy):
    def __init__(self, browser_function: BrowserFunction, file_provider: FileProvider):
        self.browser_function = browser_function
        self.file_provider = file_provider

    def run(self, result_page: ResultsPage):
        links = self.browser_function.browse_results(result_page)
        for link in links:
            file_identificator = Utils.get_page_id_from_url(link)
            detail_page = self.browser_function.open_detail_page_from_link(
                urljoin(results_url, link))
            content = detail_page.get_content()
            self.file_provider.store_file(file_identificator, content)


class UsingViewStateStrategy(IStrategy):
    def __init__(self, browser_function: BrowserFunction, file_provider: FileProvider):
        self.browser_function = browser_function
        self.file_provider = file_provider

    def run(self, result_page: ResultsPage):
        selenium_cookies = self.browser_function.browser.get_cookies()
        links: list = result_page.get_links()
        # webdriver.close()
        self.browser_function.browser.get(links[0])

        view_state, view_state_generator, event_validation = self.browser_function.get_view_state_variables()
        # self.browser_function.browser.close()

        headers = {
            '__VIEWSTATE': view_state,
            '__VIEWSTATEGENERATOR': view_state_generator,
            '__EVENTVALIDATION': event_validation
        }

        cookies = Cookies()
        cookies.set(name=selenium_cookies[0]['name'],
                    value=selenium_cookies[0]['value'],
                    domain=selenium_cookies[0]['domain'])
        # webdriver.close()
        with Client(headers=headers) as c:
            for link in links:  # debug
                file_identificator = Utils.get_page_id_from_url(link)
                response: Response = c.get(link, cookies=cookies)
                content = response.text
                # f = pd.read_html(content, flavor="bs4")
                # tables.append(f[7])
                self.file_provider.store_file(file_identificator, content)
