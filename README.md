# Czech Lawyers

## Requirements

 - PHP >= 7.0
 - PostgreSQL >= 9.5
 - Node Package Manager (aka `npm`)
 - Comopser
 - Web server and hosting site configured to allow Nette applications

## On Ubuntu

 - Install packages ``php-pgsql`` and ``php-xml``.

## Installation

1. Make directories `temp/` and `log/` writable.
2. Run `composer install` to install backend dependencies.
3. Run `npm update` to install runtime dependencies.
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


When having installation done, these steps needs to be performed to get up-to-date version:

1. Run `git pull` of proper branch (`master`, `develop`...)
2. Install PHP dependencies: `composer install`
3. Install building asset dependencies: `npm update`
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

### Console applications

For execution from command line (for crawlers...) we use Kdyby\Console (using Symphony\Console).

See its documentation: https://github.com/Kdyby/Console/blob/master/docs/en/index.md
