Czech Lawyers
=============

Requirements
------------

 - PHP >= 7.0
 - PostgreSQL >= 9.5
 - Web server and hosting site configured to allow Nette applications

Installation
------------

1. Make directories `temp/` and `log/` writable.
2. Run `composer install` to install backend dependencies.
3. Run `npm update` to install runtime dependencies.
4. Run `gulp`
5. Create local config `app/Config/config.local.neon` (see example file `config.local.neon.example`).
6. Migrate database by running `php index.php migrate-database`
7. Open page at web server.

It is CRITICAL that whole `app/`, `log/` and `temp/` directories are not accessible directly
via a web browser. See [security warning](https://nette.org/security-warning).

Developing
----------

Run `gulp development` for automatic rebuild of assets (css, js, images, ...)
On production there is complementary task `gulp production`.


Using PHP built-in webserver
----------------------------

In the root of the project run `php -S localhost:8000 -t www`

Then visit `http://localhost:8000` in your browser to see the welcome page.

Deploying
---------

On server under the proper user and git branch just run `php deploy.php`.

Database changes
----------------

Perform changes in `database/schema` files and provide migration script in
`database/migrations` (see Nextras\Migrations for documentation).
