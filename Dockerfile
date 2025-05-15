
FROM php:8.2-apache

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Activer mod_rewrite pour Apache
RUN a2enmod rewrite

# Copier le php.ini personnalisé depuis la racine du projet vers l'emplacement PHP attendu
COPY php.ini /usr/local/etc/php/php.ini

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier d'abord composer.json et composer.lock pour optimiser le cache Docker
COPY composer.json composer.lock ./

# Installer les dépendances PHP (en mode production)
RUN composer install --no-dev --optimize-autoloader

WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80