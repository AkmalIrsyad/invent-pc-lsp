FROM php:8.2-cli

# Install dependensi sistem dan ekstensi PHP untuk MySQL/Laravel
RUN apt-get update && apt-get install -y libzip-dev zip unzip git \
    && docker-php-ext-install pdo_mysql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Perintah bawaan untuk menjalankan server development Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000