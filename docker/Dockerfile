FROM php:8.3-cli-alpine
RUN apk add --no-cache mysql-client icu-dev oniguruma-dev libzip-dev \
    && docker-php-ext-install pdo_mysql intl mbstring
WORKDIR /var/www/html
COPY . /var/www/html
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader --no-interaction || true
EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public", "public/router.php"]
