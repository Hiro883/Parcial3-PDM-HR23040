FROM php:8.1-apache

RUN docker-php-ext-install pdo pdo_mysql

COPY . /var/www/html/

RUN a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -i 's|/var/www/html|${APACHE_DOCUMENT_ROOT}|g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80 
