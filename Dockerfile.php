# Gunakan image PHP 8.1 FPM resmi (yang berbasis Debian)
FROM php:8.1-fpm

# Instal ekstensi mysqli yang diperlukan oleh aplikasi Anda
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli