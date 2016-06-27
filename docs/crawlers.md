Crawlers
========

Intention: crawl a given resource and obtain certain data from it.

Relevant crawlers:

 - NSS - cases' documents
 - NS - cases' documents 
 - ÚS - cases' documents
 - ČAK - advocates names

General crawler behaviour
-------------------------

0. Each crawler has its own directory where its installed with its `working` and `result` directory.
1. Crawler do its work inside its `working` directory (it maintains it completely).
2. After crawlers prepares `metadata.csv` and `documents` (see below) it moves both of them into `result` directory only if the result directory is empty!
3. At the end of crawler run it disposes its working directory `working`.

Failure of crawler should result in informing an admin (and leave the content of `working` directory as it is strong debug info).

TBD:

 - Should next run automatically fail/skip?

Cases crawler output format
----------------------------

Each crawler should return data with following structure and format:

1. Metadata (named: `metadata.csv`) in following order

    - Court name (e.g. Nejvyšší soud).  
      Name: `court_name`
    - Registry mark (there can be multiple lines with the same registry mark).  
      Name: `registry_mark`
    - Decision date in YYYY-mm-dd format (e.g. 2016-01-12).  
      Name: `decision_date`
    - Absolute path of the document on the web (e.g. source document)  
      Name: `web_path`
    - Relative path to downloaded document inside the `document` folder.    
      Name: `local_path`
    - Extra columns (court dependent) properly named.  
      Name: `<as needed>`

2. Folder with documents (named: `documents`). This directory contains downloaded documents such as PDF, HTML, TXT. We should always store the originals!

Notes:

 - Basicaly `local_path` is the name of downloaded document inside the `documents` directory.  
 - `web_path` a `local_path` are the same documents (one remotely, one localy).
 - OCR, tagging or lematization should be done inside the tagger (in the system).
 - Example of extra columns: additional metadata present when crawling (such as in NSS).

Advocates crawler output format
-------------------------------

TBD by Radim

Importing crawler results
-------------------------

1. There will be a console utility which takes two parameters path to `results` directory inside of crawler installation and `type` which determines what data are being imported.
2. Import will be done.
3. Directory is cleaned on successful import.