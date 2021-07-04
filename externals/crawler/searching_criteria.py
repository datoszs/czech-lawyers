class SearchingCriteria(object):
    date_from: str
    date_to: str
    per_page: int
    days: int

    def __init__(self, date_from=None, date_to=None, per_page=None, days=None):
        self.date_from = date_from
        self.date_to = date_to
        self.per_page = per_page
        self.days = days
