FROM php:8.2-fpm AS builder

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libssl-dev \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        ctype \
        gd \
        intl \
        mbstring \
        mysqli \
        pdo \
        pdo_mysql \
        simplexml \
        xml \
        zip \
        fileinfo

COPY --from=composer:2.7.7 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install --no-interaction --no-plugins --no-scripts --no-dev --prefer-dist --optimize-autoloader \
    && cp -R vendor /app/vendor_prod

RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist --optimize-autoloader

COPY . .

FROM php:8.2-fpm AS dev

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libssl-dev \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        ctype \
        gd \
        intl \
        mbstring \
        mysqli \
        opcache \
        pdo \
        pdo_mysql \
        simplexml \
        xml \
        zip \
        fileinfo

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY --from=builder /app/src/ /var/www/html/src/
COPY --from=builder /app/Public/ /var/www/html/Public/
COPY --from=builder /app/routes/ /var/www/html/routes/
COPY --from=builder /app/vendor/ /var/www/html/vendor/
COPY --from=builder /app/composer.json /var/www/html/composer.json
COPY --from=builder /app/composer.lock /var/www/html/composer.lock

RUN mkdir -p /var/www/html/Public/uploads \
             /var/www/html/var/cache \
             /var/www/html/var/log \
             /var/lib/php/sessions && \
    chown -R www-data:www-data /var/www/html && \
    chown -R www-data:www-data /var/lib/php/sessions && \
    chmod -R 775 /var/www/html/Public/uploads && \
    chmod -R 775 /var/www/html/var/cache && \
    chmod -R 775 /var/www/html/var/log && \
    chmod -R 775 /var/lib/php/sessions

EXPOSE 9000

CMD ["php-fpm"]

FROM php:8.2-fpm-alpine AS final

WORKDIR /var/www/html

RUN apk add --no-cache \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    libxml2-dev \
    libintl \
    icu-dev \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        zip \
        gd \
        intl \
        mbstring \
        simplexml \
        xml \
        fileinfo \
    && rm -rf /var/cache/apk/*

COPY --from=builder /app/src/ /var/www/html/src/
COPY --from=builder /app/Public/ /var/www/html/Public/
COPY --from=builder /app/routes/ /var/www/html/routes/
COPY --from=builder /app/vendor_prod/ /var/www/html/vendor/

RUN mkdir -p /var/www/html/Public/uploads \
             /var/www/html/var/cache \
             /var/www/html/var/log \
             /var/lib/php/sessions && \
    chown -R www-data:www-data /var/www/html && \
    chown -R www-data:www-data /var/lib/php/sessions && \
    chmod -R 775 /var/www/html/Public/uploads && \
    chmod -R 775 /var/www/html/var/cache && \
    chmod -R 775 /var/www/html/var/log && \
    chmod -R 775 /var/lib/php/sessions

USER www-data

EXPOSE 9000

CMD ["php-fpm"]