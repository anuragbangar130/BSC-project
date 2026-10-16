FROM php:8.3-apache

RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pgsql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . /var/www/html/

RUN cp /var/www/html/backend/db.production.php /var/www/html/backend/db.php \
    && rm /var/www/html/backend/db.production.php \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
