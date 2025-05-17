FROM php:8.2-apache

RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html && \
    chmod 664 /var/www/html/users.json && \
    chmod 664 /var/www/html/error.log && \
    touch /var/www/html/last_report.txt && \
    chmod 664 /var/www/html/last_report.txt

EXPOSE 80
CMD ["apache2-foreground"]