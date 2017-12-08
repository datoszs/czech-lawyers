# Czech Lawyers

## Requirements

 - PHP >= 7.1
 - PostgreSQL >= 9.5
 - Node Package Manager (aka `npm`)
 - Composer
 - Web server and hosting site configured to allow Nette applications

## On Ubuntu

 - Install packages ``php-pgsql``, ``php-xml`` and ``php-posix``.

## Installation

1. Make directories `temp/` and `log/` writable.
2. Run `composer install` to install backend dependencies.
3. Run `npm install` to install runtime dependencies.
4. Run `gulp`
5. Create local config `app/Config/config.local.neon` (see example file `config.local.neon.example`).
6. Migrate database by running `php www/index.php migrations:continue`
7. Open page at web server.

It is CRITICAL that whole `app/`, `log/` and `temp/` directories are not accessible directly
via a web browser. See [security warning](https://nette.org/security-warning).

### Troubleshooting

1. ``ERROR:  schema "public" already exists in ...``
  - comment out a line 100 in ``vendor/nextras/migrations/src/Drivers/PgSqlDriver.php``
  
2. No user in the database
  - use  `php www/index.php app:user-create` command to create new ordinary user.

## Environment

Basically there are two "featured" GIT branches:

 - `master` acting as stable version (publicly deployed),
 - `develop` acting as development version.

The application (both of the branches) can run in two environments: `development` and `production`... The difference is in error handling, building assets. The application itself uses autodetection (from the http host), however for the processes around the environment needs to be specified explicitly.

Note: branches starting with `wip-` prefix are contains unfinished and incomplete work.

### Development

You can use either full standalone server such as Apache and configure VirtualHost properly, or you can use PHP built-in webserver for which just run `php -S localhost:8000 -t www` in the root of the project. Then the website is available at `http://localhost:8000`.

You can start front-end application in development mode by runnning `npm start` in the project root. Web application is then available at `http://localhost:8080`. 


When having installation done, these steps needs to be performed to get up-to-date version:

1. Run `git pull` of proper branch (`master`, `develop`...)
2. Install PHP dependencies: `composer install`
3. Install building asset dependencies: `npm install`
4. Migrate database: `php index.php migrate-database`
5. Rebuild assets using `gulp development`

The `gulp development` commands automatically builds assets (css, js, images...) and watch for their modification which triggers build immediatelly.

### Production

For production use only a full standalone server such as Apache should be used!

For automated build of dependencies there is prepared script `deploy.php` which take care about everything done by hand in development mode with that difference that migration of database and gulp task are done for production (no dummy data and no watching for changes in assets).

Doing update: just run `php deploy.php` in proper folder (see the configuration section).

### (Example) configuration

* VPS server with PostgreSQL and Apache with `mod_itk` (to run web under given user):
  * Users `foo`, `bar` with sudo access to `cestiadvokati.cz`
  * User `cestiadvokati.cz`
    * Web
  	   * `web-devel` = branch `develop` in production mode for testing before going live.
  	   * `web-production` = branch `master` in production mode for live and public instance.
    * Crawlers - data (working and result directories).


## Developing

### Database migrations

For managing database state the project uses Nextras\Migrations which stores the change scripts in `database/schema` folder.

Warning: do not change scripts that are already applied on production!

See its documentation: https://nextras.org/migrations/docs/3.0/

Types:

  * Structure (s)
  * Basic data (b)
  * Dummy data (d)

Label: short label/tag/name of the change

### Console applications

For execution from command line (for crawlers...) we use Kdyby\Console (using Symphony\Console).

See its documentation: https://github.com/Kdyby/Console/blob/master/docs/en/index.md

### REST API

Project is using Ublaboo API routing (via annotations), see [documentation](https://ublaboo.org/api-router/annotation-routing).
The anotations also works as documentation, see [library documentation](https://ublaboo.org/api-docu/).

API documentation is stored at `/api-doc/` and is not autogenerated (and on production server should be protected by password).

For generating new please open any route with additional parameter such as: `/api/advocate/autocomplete?__apiDocuGenerate`.

### Personal data protection

According to czech laws the system process personal data and therefore is subject of [Law 101/2000](https://www.uoou.cz/files/101_cz.pdf).

Any further modifications needs to take this into account and implement current approach before they can be merged and deployed.

Auditing log is stored in `/log/auditing/YYYY-MM.log` in following format:
```
DATE AND TIME: 2017-09-05 08:41:37
REQUEST: http://localhost:8000/api/advocate-rankings/1
COUNTERPARTY: [26] Jan Novák
IDENTIFICATION: [::1] => Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36 OPR/47.0.2631.71
AUDITED SUBJECT TYPE: advocate_info
OPERATION: access
REASON: Requested in one record (direct access)
DESCRIPTION: Load advocate [JUDr. František Vomáčka - Brno] with ID [123].
```

In application, there is service implementing `ITransactionLogger` which takes care of logging through `logCreate`, `logAccess`, `logChange` and `logRemove` methods.
For all calls these values needs to be provided:

- **audited subject** -- describes type of personal data, please use values from enum `AuditedSubject` only,
- **audited reason** -- describes why this particular record was accessed/changed/... please use values from enum `AuditedReason` only,
- **audited description** -- provide text description what was accessed in human readable form, when change happens include changes (in form of from-to pairs).

Information about user are inferred from the current context of the application, however they can be overriden (necessity for console tasks).
When working with transactions, log immediatelly all access operations, delay all change operation after database transaction is done (use `ITransactionLogger` for that).

Notes:

- Beware of accessing out of array or access to optional entities (such as taggings etc...)
- There is trait `Diffable` usable on Entities for obtaining diff of changes.
- Internal loading of data or usage withing aggregation is ignored as it would make whole thing unusable.
- When downloading exports only one record should be stored due to technical limitations (increase in log size).
