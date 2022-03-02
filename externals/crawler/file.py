import codecs
import os
import shutil

from bs4 import BeautifulSoup


class FileProvider:
    working_dir = "working"
    screens_dir = "screens"
    documents_dir = "documents"
    log_dir = "log_us"
    result = "result"

    def __init__(self, output_dir_path: str):
        self.out_dir = output_dir_path

        self.result_dir_path = os.path.join(output_dir_path, self.result)
        self.out_dir = os.path.join(output_dir_path, self.working_dir)  # new out_dir is working directory
        self.documents_dir_path = os.path.join(self.out_dir, self.documents_dir)

    def store_file(self, file_name: str, content: str) -> None:
        document_file_path = os.path.join(self.documents_dir_path, file_name)
        if self.__is_file_stored(document_file_path):
            return

        response = self.__preprocessing(content)

        # logger.debug("Saving file '%s'" % file_name)
        self.__save_file(document_file_path, str(response))

    @staticmethod
    def __is_file_stored(file_path: str) -> bool:
        return os.path.exists(file_path)

    @staticmethod
    def __preprocessing(source_code: str) -> BeautifulSoup:
        response = BeautifulSoup(source_code, "html.parser")

        del response.find("div", id="docContentPanel")["style"]  # remove inline style
        del response.find("div", id="recordCardPanel")["style"]  # remove inline style

        link_elem = response.select_one("#recordCardPanel > table > tbody > tr:nth-of-type(26) > td:nth-of-type(2)")
        if link_elem and link_elem.string:
            link_elem.string.wrap(response.new_tag("a", href=link_elem.string))

        # remove script, which manipulate size of document
        response.body.script.decompose()
        del response.body["onload"]  # remove calling script on loading page
        return response

    @staticmethod
    def __save_file(path: str, content: str):
        with codecs.open(path, "wb", encoding="utf-8") as f:
            f.write(str(content))

    @staticmethod
    def clean_directory(root: str):
        """
        clear directory (after successfully run)

        :param root: path to directory
        """
        for f in os.listdir(root):
            try:
                shutil.rmtree(os.path.join(root, f))
            except NotADirectoryError:
                os.remove(os.path.join(root, f))

    def create_directories(self, b_screens: bool):
        """
        create working directories
        """
        for directory in [self.out_dir, self.documents_dir_path, self.result_dir_path]:
            os.makedirs(directory, exist_ok=True)
            # logger.info("Folder was created '" + directory + "'")

        if b_screens:
            self.screens_dir_path = os.path.join(self.out_dir, self.screens_dir)
            if not os.path.exists(self.screens_dir_path):
                os.mkdir(self.screens_dir_path)
                # logger.info("Folder was created '" + self.screens_dir_path + "'")
            else:
                # logger.debug("Erasing old screens")
                self.clean_directory(self.screens_dir_path)
            return self.screens_dir_path
