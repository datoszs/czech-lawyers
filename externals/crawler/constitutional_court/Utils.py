from datetime import datetime

from typing import Tuple


class Utils:
    @staticmethod
    def is_newer_case(date_from: str) -> bool:
        return Utils.convert_str_to_date(date_from).year > 2006

    @staticmethod
    def convert_str_to_date(date: str) -> datetime:
        return datetime.strptime(date, '%d. %m. %Y')

    @staticmethod
    def convert_date(date: str, formats: Tuple[str, str] = ('%d. %m. %Y', '%Y-%m-%d')) -> str:
        return datetime.strptime(date, formats[0]).strftime(formats[1])

    @staticmethod
    def get_page_id_from_url(url: str) -> str:
        return url.split("?")[-1].split("&")[0]
