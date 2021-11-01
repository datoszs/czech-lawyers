from typing import Dict

from selenium.webdriver import ChromeOptions
from selenium.webdriver.chrome.webdriver import WebDriver
from selenium import webdriver
from webdriver_manager.chrome import ChromeDriverManager

from externals.crawler.constitutional_court.Pages.DetailPage import DetailPage
from externals.crawler.constitutional_court.Pages.HomePage import HomePage
from externals.crawler.constitutional_court.Pages.ResultsPage import ResultsPage
from externals.crawler.searching_criteria import SearchingCriteria


def set_up() -> WebDriver:
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
    return [links.extend(page.get_links() for page in result_page)]


def walk_throught_details(browser: WebDriver, link: str) -> None:
    detail_page = open_detail_page_from_link(browser, link)
    page_id = detail_page.page_id
    print(page_id)
    for page in detail_page:
        print(page.page_id)  # call save file with preprocessing


def process_link(browser: WebDriver, link: str):
    html_file = Utils.get_page_id_from_url(link) + ".html"
    if not os.path.exists(join(documents_dir_path, html_file)):  # validation
        logger.debug("Go to link to detail")
        detail_page = open_detail_page_from_link(browser, urljoin(results_url, link))
        return detail_page.get_content()


def open_detail_page_from_link(browser, link) -> DetailPage:
    browser.get(link)
    detail_page = DetailPage(browser)
    return detail_page


def main():
    browser = set_up()
    criteria = SearchingCriteria(date_from="1. 1. 2006", date_to="2. 1. 2006", per_page=80)
    # some processor
    # - make settings by command line parameters
    # - fill search
    # - get count of results
    #  -> get strategy for getting information from web
    # - get content from web
    # - preprocess files before save
    # - save files
    # - close browser
    # - process files
    #  -> inject some extractor
    #  -> save records to list
    # - save all information to CSV
    # extract this
    result_page = prepare_searching_by_criteria(browser, criteria)
    count_of_results = result_page.get_count_of_results()
    print(count_of_results)
    # move to strategy
    if count_of_results < 1:
        walk_throught_details(browser, result_page.get_first_link())
    else:
        links = browse_results(result_page)
        for link in links:
            content = process_link(browser, link)


if __name__ == "__main__":
    main()
