Czech Lawyers
=============

Requirements
------------

 - PHP >= 5.3.1
 - Web server and hosting site configured to allow Nette applications

Installation
------------

1. Make directories `temp/` and `log/` writable.
2. Run `composer install` to install backend dependencies.
3. Run `npm update` to install runtime dependencies.
4. Run `gulp`
5. Migrate database by running `php index.php migrate-database`
6. Open page at web server.

It is CRITICAL that whole `app/`, `log/` and `temp/` directories are not accessible directly
via a web browser. See [security warning](https://nette.org/security-warning).

Developing
----------

Run `gulp development` for automatic rebuild of assets (css, js, images, ...)


Using PHP built-in webserver
----------------------------

In the root of the project run `php -S localhost:8000 -t www`

Then visit `http://localhost:8000` in your browser to see the welcome page.