# PHP 7.4 + Apache — samakan dengan server produksi (cPanel PHP 7.4)
FROM php:7.4-apache

# Ekstensi yang dibutuhkan CodeIgniter 3
RUN docker-php-ext-install mysqli pdo_mysql \
    && docker-php-ext-enable mysqli

# Aktifkan mod_rewrite (untuk .htaccess routing CI)
RUN a2enmod rewrite

# CI3 session driver 'files' butuh session.save_path terisi (image php kosong)
RUN echo 'session.save_path = "/tmp"' > /usr/local/etc/php/conf.d/app-session.ini

# Konfigurasi Apache: izinkan .htaccess + teruskan env DB ke PHP
COPY docker/apache.conf /etc/apache2/conf-available/zz-app.conf
RUN a2enconf zz-app

# Entrypoint: pastikan folder logs/cache writable lalu jalankan Apache
COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint.sh
RUN chmod +x /usr/local/bin/app-entrypoint.sh

WORKDIR /var/www/html

# Salin kode (saat dev biasanya ditimpa volume mount dari docker-compose)
COPY . /var/www/html

ENTRYPOINT ["app-entrypoint.sh"]
CMD ["apache2-foreground"]
