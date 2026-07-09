FROM php:8.2-apache

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY request_logger.php index.php health.php ./
COPY Vanguard-Emulator.slnx ./
COPY Vanguard-Emulator ./Vanguard-Emulator

RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

RUN echo "auto_prepend_file=/var/www/html/request_logger.php" > /usr/local/etc/php/conf.d/echo-request-logger.ini
