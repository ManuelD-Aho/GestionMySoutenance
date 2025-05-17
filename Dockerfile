# 1) Mettre à jour apt et installer les dépendances système
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libonig-dev && \
    rm -rf /var/lib/apt/lists/*

# 2) Installer et activer les extensions PHP nécessaires
RUN docker-php-ext-install \
        pdo \
        pdo_mysql \
        mysqli \
        zip \
        mbstring && \
    a2enmod rewrite

# 3) Copier ton php.ini et Composer
COPY php.ini /usr/local/etc/php/php.ini
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 4) Copier composer.json et composer.lock d’abord
COPY composer.json composer.lock ./

# 5) Lancer l’installation Composer à présent que tout est en place
RUN composer install --no-dev --optimize-autoloader

# 6) Copier le reste du code et régler les permissions
COPY . .
RUN chown -R www-data:www-data /var/www/html
