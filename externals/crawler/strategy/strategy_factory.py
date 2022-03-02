from .strategies import IStrategy, WalkThroughDetailsStrategy, OpenDetailLinkStrategy, UsingViewStateStrategy
from ..browser_function import BrowserFunction
from ..file import FileProvider


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
