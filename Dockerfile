FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpq-dev \
    libzip-dev \
    mariadb-client \
    && docker-php-ext-install pdo pdo_mysql

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs && \
    npm install apidoc -g

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /www

COPY . /www

RUN composer install --no-scripts --no-interaction --prefer-dist

RUN mkdir -p /www/docs/api

RUN apidoc -i /www/modules/api/controllers/ -o /www/docs/api

RUN chown -R www-data:www-data /www && \
    chmod -R 755 /www

RUN chown -R www-data:www-data /www/docs/api && \
    chmod -R 775 /www/docs/api

CMD ["php-fpm"]
