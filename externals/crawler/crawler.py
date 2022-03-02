from selenium.webdriver.chrome.webdriver import WebDriver

from externals.crawler.browser_function import BrowserFunction
from externals.crawler.constitutional_court.Pages.HomePage import HomePage
from externals.crawler.constitutional_court.Pages.ResultsPage import ResultsPage
from externals.crawler.searching_criteria import SearchingCriteria
from externals.crawler.strategy.strategies import IStrategy
from externals.crawler.strategy.strategy_factory import StrategyFactory


class Crawler:
    START_URL = "http://nalus.usoud.cz"
    __browser: WebDriver
    __strategy_factory: StrategyFactory
    __strategy: IStrategy
    __searching_criteria: SearchingCriteria

    def __init__(self, strategy_factory: StrategyFactory, searching_criteria: SearchingCriteria,
                 browser_function: BrowserFunction):
        self.__strategy_factory = strategy_factory
        self.__searching_criteria = searching_criteria
        self.__browser = browser_function.browser

    def run(self):
        result_page: ResultsPage = self.__prepare_searching_by_criteria(self.__searching_criteria)
        count_of_results = None
        #  count_of_results = result_page.get_count_of_results()
        self.__strategy = self.__strategy_factory.get_strategy(count_of_results)
        self.__strategy.run(result_page)

    def __open_main_page(self, url: str) -> HomePage:
        self.__browser.get(url)
        return HomePage(self.__browser)

    def __prepare_searching_by_criteria(self, criteria: SearchingCriteria) -> ResultsPage:
        home_page = self.__open_main_page(self.START_URL)
        home_page.fill_form(criteria)
        return home_page.search()
