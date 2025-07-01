# Dockerfile
FROM php:8.2-fpm AS base

ENV DEBIAN_FRONTEND=noninteractive

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
    nodejs \
    npm \
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
        zip \
        fileinfo

COPY --from=composer:2.7.7 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# ==============================================================================
FROM base AS builder

COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

RUN composer install --no-interaction --no-scripts --prefer-dist --optimize-autoloader
RUN npm install

COPY . .

RUN npm run build

# ==============================================================================
FROM builder AS dev

WORKDIR /var/www/html

COPY --from=builder /app .

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log /var/lib/php/sessions /var/log && \
    touch /var/log/xdebug.log && \
    chown -R www-data:www-data /var/www/html && \
    chown -R www-data:www-data /var/lib/php/sessions && \
    chown -R www-data:www-data /var/log && \
    chmod -R 777 /var/www/html/var && \
    chmod -R 777 /var/log

EXPOSE 9000
CMD ["php-fpm"]

# ==============================================================================
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
    oniguruma-dev \
    && docker-php-ext-install -j$(nproc) pdo_mysql zip gd intl mbstring fileinfo \
    && rm -rf /var/cache/apk/*

COPY --from=builder /app/src/ /var/www/html/src/
COPY --from=builder /app/Public/ /var/www/html/Public/
COPY --from=builder /app/routes/ /var/www/html/routes/
COPY --from=builder /app/templates/ /var/www/html/templates/
COPY --from=builder /app/composer.json /var/www/html/composer.json

RUN composer install --no-interaction --no-dev --no-scripts --optimize-autoloader

RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log /var/lib/php/sessions && \
    chown -R www-data:www-data /var/www/html && \
    chown -R www-data:www-data /var/lib/php/sessions && \
    chmod -R 775 /var/www/html/var

USER www-data

EXPOSE 9000
CMD ["php-fpm"]