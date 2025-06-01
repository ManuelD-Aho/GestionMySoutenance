FROM php:8.2-apache AS app_builder

LABEL maintainer="GestionMySoutenance <contact@votredomaine.com>"
# Correction du LABEL description pour refléter PHP 8.2
LABEL description="GestionMySoutenance PHP 8.2 Apache Environment"

ENV DEBIAN_FRONTEND=noninteractive
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APACHE_DOCUMENT_ROOT=/var/www/html/Public

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
        zip

COPY php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite headers

COPY --from=composer:2.7.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-interaction --no-progress --verbose

COPY . .

# Section Permissions Révisée
RUN mkdir -p /var/www/html/Public/uploads \
             /var/www/html/var/cache \
             /var/www/html/var/log && \
    # Le dossier de session est géré par l'image PHP de base,
    # mais s'il y a un doute, on s'assure que www-data en est propriétaire
    # ou qu'il est créé avec les bons droits par l'image.
    # Si l'image de base le crée bien, un chown ici n'est pas toujours nécessaire
    # mais ne devrait pas nuire.
    # Tentons de créer /var/lib/php/sessions s'il n'existe VRAIMENT pas
    if [ ! -d "/var/lib/php/sessions" ]; then mkdir -p /var/lib/php/sessions; fi && \
    chown -R www-data:www-data /var/www/html && \
    chown -R www-data:www-data /var/lib/php/sessions && \
    # Permissions d'écriture pour les dossiers d'application
    chmod -R 775 /var/www/html/Public/uploads && \
    chmod -R 775 /var/www/html/var/cache && \
    chmod -R 775 /var/www/html/var/log && \
    chmod -R 775 /var/lib/php/sessions && \
    # Permissions par défaut pour le reste du code
    find /var/www/html -type d -exec chmod 755 {} \; && \
    find /var/www/html -type f -exec chmod 644 {} \; && \
    chmod +x /usr/bin/composer