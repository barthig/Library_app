
FROM php:8.1-fpm


RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
    && rm -rf /var/lib/apt/lists/*


RUN docker-php-ext-install pdo_pgsql



WORKDIR /var/www/html


