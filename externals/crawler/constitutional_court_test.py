from typing import Dict

from selenium.webdriver.chrome import webdriver
from selenium.webdriver import ChromeOptions
from selenium.webdriver.chrome.webdriver import WebDriver

from externals.crawler.constitutional_court.Pages import ResultsPage


def set_up():
    # bez obrazku to neni
    option = ChromeOptions()
    chrome_prefs: Dict[str, Dict[str, int]] = {
        "profile.default_content_settings": {"images": 2},
        "profile.managed_default_content_settings": {"images": 2}
    }
    option.add_argument("--headless")
    option.experimental_options["prefs"] = chrome_prefs
    browser = webdriver.Chrome(options=option)
    browser.implicitly_wait(5)
    return browser


def open_main_page(browser: WebDriver, url: str) -> ResultsPage:
    browser.get(url)
    return ResultsPage(browser)


def test_open_homepage():
    browser = set_up()
    result_page = open_main_page(browser, "http://nalus.usoud.cz/Search/")
    assert isinstance(result_page, ResultsPage)
