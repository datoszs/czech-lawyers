from dataclasses import dataclass


@dataclass
class SearchingCriteria(object):
    date_from: str
    date_to: str
    per_page: int
    days: int
