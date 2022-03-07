from typing import Dict

from selenium import webdriver
from selenium.webdriver import ChromeOptions
from selenium.webdriver.chrome.webdriver import WebDriver
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from webdriver_manager.chrome import ChromeType

from browser_function import BrowserFunction
from crawler import Crawler
from file import FileProvider
from searching_criteria import SearchingCriteria
from strategy import StrategyFactory


def set_up() -> WebDriver:
    # bez obrazku to neni
    options = ChromeOptions()
    chrome_prefs: Dict[str, any] = {
        # "profile.default_content_settings": {"images": 2},
        # "profile.managed_default_content_settings": {"images": 2}
        # "excludeSwitches": ["enable-logging"]
    }
    # options.add_argument("--headless")
    options.add_argument('--ignore-certificate-errors')
    options.add_argument("--disable-webgl")
    options.add_experimental_option("excludeSwitches", ["enable-logging"])
    options.experimental_options["prefs"] = chrome_prefs
    browser = webdriver.Chrome(service=Service(
        ChromeDriverManager(chrome_type=ChromeType.GOOGLE).install()), options=options)
    browser.implicitly_wait(5)
    return browser


def main():
    criteria = SearchingCriteria(
        date_from="1. 1. 2022", date_to="21. 1. 2022", per_page=80, days=None)
    file_provider = FileProvider("output")
    file_provider.create_directories(b_screens=False)
    webdriver = set_up()
    browser_function = BrowserFunction(webdriver)
    strategy_factory = StrategyFactory(
        browser_function=browser_function, file_provider=file_provider)
    crawler = Crawler(strategy_factory=strategy_factory,
                      searching_criteria=criteria, browser_function=browser_function)
    crawler.run()
    print("Download files - DONE")

    # some processor
    # - make settings by command line parameters
    # - fill search
    # - get count of results
    #  -> get strategy for getting information from web
    # - get content from web
    # - preprocess files before save
    # - save files
    # - close webdriver
    # - process files
    #  -> inject some extractor
    #  -> save records to list
    # - save all information to CSV
    # extract this


if __name__ == "__main__":
    main()
