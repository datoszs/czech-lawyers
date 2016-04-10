Crawler output format
---------------------

Each crawler should return data with following structure and format:

1. Metadata (file: `metadata.csv`) in following order

    - Court name (e.g. Nejvyšší soud).  
      Name: "court_name"
    - Registry mark (there can be multiple lines with the same registry mark).  
      Name: "registry_mark"
    - Decision date in YYYY-mm-dd format (e.g. 2016-01-12).  
      Name: "decision_name"
    - Absolute path of the document on the web (e.g. source document)  
      Name: "web_path"
    - Relative path to downloaded document inside the `document` folder.  
      Name: "local_path"
    - Extra columns (court dependent) properly named.

2. Folder with documents (directory: `documents`) 