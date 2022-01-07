FROM php:7.1-apache
MAINTAINER Jan Drábek <me@jandrabek.cz>

# Enable various PHP extensions
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libpq-dev \
        unzip \
        git \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql mysqli opcache gd pgsql pdo_pgsql

# Enable XDebug
RUN pecl install xdebug-2.6.1 \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_connect_back=off" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_host=docker.for.mac.localhost" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.profiler_remote_trigger=On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install self-signed SSL certificate and enable SSL in Apache
RUN mkdir -p /etc/ssl/localcerts \
    && openssl req -new -x509 -days 365 -nodes -out /etc/ssl/localcerts/apache.pem -keyout /etc/ssl/localcerts/apache.key -subj "/C=CZ/O=Zvěřinec/OU=InterLoS/CN=localhost" \
    && chmod 600 /etc/ssl/localcerts/apache* \
    && sed -i "s#/etc/ssl/certs/ssl-cert-snakeoil.pem#/etc/ssl/localcerts/apache.pem#g" /etc/apache2/sites-available/default-ssl.conf \
    && sed -i "s#/etc/ssl/private/ssl-cert-snakeoil.key#/etc/ssl/localcerts/apache.key#g" /etc/apache2/sites-available/default-ssl.conf \
    && a2enmod ssl rewrite && a2ensite default-ssl

# Fix apache document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/www
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
