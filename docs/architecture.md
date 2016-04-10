Architecture
------------

See `architecture.png` for overall architecture.

Notes:
- Crawlers are developed in separate repositories as they should be usable in standalone mode. 
- When the cron jobs are executed explicitly check whether the previous run has finished.
- When importing the duplicites should be (silently) ignored (but basically that should not happen).