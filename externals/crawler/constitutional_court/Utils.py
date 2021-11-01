from datetime import datetime


class Utils:
    @staticmethod
    def is_newer_case(date_from: str) -> bool:
        return Utils.convert_str_to_date(date_from).year > 2006

    @staticmethod
    def convert_str_to_date(date: str) -> datetime:
        return datetime.strptime(date, '%d. %m. %Y')

    @staticmethod
    def convert_date(date, formats=('%d. %m. %Y', '%Y-%m-%d')):
        """
        Converts a date from one format to another - specified by the format parameter

        :param date: string with date to convert
        :type date: str
        :param formats: input and output format
        :type formats: tuple
        :return: converted date
        """
        return datetime.strptime(date, formats[0]).strftime(formats[1])

    @staticmethod
    def get_sum_from_description(description: str) -> int:
        text_without_pagination = description.split(";")[0]
        sum_of_records = text_without_pagination.split()[-1]
        return int(sum_of_records)

    @staticmethod
    def get_page_id_from_url(url: str):
        return url.split("?")[-1].split("&")[0]
