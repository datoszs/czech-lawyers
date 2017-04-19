from externals.baseCrawler import *


class NssCrawler(BaseCrawler):
    def __init__(self, court, html, options):
        super().__init__(court=court, html=html, options=options)
        self.create_directories(out_dir=options.dir, log_dir="log_" + court, html=html)
        logger = logging.getLogger(self.__class__.__name__)
        logger.debug(options)

    def prepare_record(self, soup, id=None):
        pass

    def how_many(self):
        print(BeautifulSoup(self.session.content, "html.parser").body.findAll("script"))





def parameters():
    usage = "usage: %prog [options]"
    parser = OptionParser(usage)
    parser.add_option("-w", "--without-download", action="store_true", dest="download", default=False,
                      help="Not download PDF documents")
    parser.add_option("-n", "--not-delete", action="store_true", dest="delete", default=False,
                      help="Not delete working directory")
    parser.add_option("-d", "--output-directory", action="store", type="string", dest="dir", default="output_dir",
                      help="Path to output directory")
    parser.add_option("-l", "--last-days", action="store", type="string", dest="last", default=None,
                      help="Increments for the last N days (N in <1,60>)")
    parser.add_option("-f", "--date-from", action="store", type="string", dest="date_from", default=None,
                      help="Start date of range (d. m. yyyy)")
    parser.add_option("-t", "--date-to", action="store", type="string", dest="date_to", default=None,
                      help="End date of range (d. m. yyyy)")
    parser.add_option("-c", "--capture", action="store_true", dest="screens", default=False,
                      help="Capture screenshots?")
    parser.add_option("-o", "--output-file", action="store", type="string", dest="filename", default="metadata.csv",
                      help="Name of output CSV file")
    parser.add_option("-e", "--extraction", action="store_true", dest="extraction", default=False,
                      help="Make only extraction without download new data")
    (options, args) = parser.parse_args()

    #print(args,options,type(options))
    return options

if __name__ == "__main__":
    out_dir = "D:/test"
    crawler = NssCrawler(court="nss", html="html", options=parameters())
    logger = crawler.logger

    print(crawler.dir_path)
    crawler.make_connection()
    crawler.logging_process(["touch", join(crawler.dir_path["working"], crawler.options.filename)])
    #crawler.move_files()
    crawler.how_many()
    logger.info("pes")
