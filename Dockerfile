FROM php:8.2-apache

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY index.php health.php ./
COPY Vanguard-Emulator.slnx ./
COPY Vanguard-Emulator ./Vanguard-Emulator

RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html
