from typing import Dict

from selenium.webdriver import ChromeOptions
from selenium.webdriver.chrome.webdriver import WebDriver
from selenium import webdriver
from webdriver_manager.chrome import ChromeDriverManager

from externals.crawler.constitutional_court.Pages.DetailPage import DetailPage
from externals.crawler.constitutional_court.Pages.HomePage import HomePage
from externals.crawler.constitutional_court.Pages.ResultsPage import ResultsPage
from externals.crawler.searching_criteria import SearchingCriteria


def set_up():
    # bez obrazku to neni
    option = ChromeOptions()
    chrome_prefs: Dict[str, Dict[str, int]] = {
        # "profile.default_content_settings": {"images": 2},
        # "profile.managed_default_content_settings": {"images": 2}
    }
    # option.add_argument("--headless")
    option.experimental_options["prefs"] = chrome_prefs
    browser = webdriver.Chrome(ChromeDriverManager().install(), options=option)
    browser.implicitly_wait(5)
    return browser


def open_main_page(browser: WebDriver, url: str) -> HomePage:
    browser.get(url)
    return HomePage(browser)


def prepare_searching_by_criteria(browser: WebDriver, criteria: SearchingCriteria) -> ResultsPage:
    home_page = open_main_page(browser, "http://nalus.usoud.cz")
    home_page.fill_form(criteria)
    return home_page.search()


def browse_results(result_page: ResultsPage) -> list:
    links = result_page.get_links()
    [links.extend(page.get_links() for page in resul_page)]
    return links


def walk_throught_details(browser: WebDriver, link: str):
    detail_page = open_detail_page_from_link(browser, link)
    page_id = detail_page.get_page_id()
    print(page_id)
    for page in detail_page:
        print(page.get_page_id())


def open_detail_page_from_link(browser, link):
    browser.get(link)
    detail_page = DetailPage(browser)
    return detail_page


def main():
    browser = set_up()
    criteria = SearchingCriteria(date_from="1. 1. 2006", date_to="2. 1. 2006", per_page=80)
    result_page = prepare_searching_by_criteria(browser, criteria)
    count_of_results = result_page.get_count_of_results()
    print(count_of_results)
    if count_of_results < 1000:
        walk_throught_details(browser, result_page.get_first_link())
    else:
        links = browse_results(result_page)


if __name__ == "__main__":
    main()
