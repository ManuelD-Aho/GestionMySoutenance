
FROM php:8.2-apache

# 1. Installer les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql mysqli

# 2. Activer mod_rewrite pour Apache
RUN a2enmod rewrite

# 3. Définir le répertoire public comme DocumentRoot Apache
#ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# 4. Mettre à jour la config Apache pour utiliser ce nouveau DocumentRoot
#RUN sed -ri \
#    -e 's!DocumentRoot /var/www/html!DocumentRoot ${APACHE_DOCUMENT_ROOT}!g' \
#    -e 's!<Directory /var/www/html>!<Directory ${APACHE_DOCUMENT_ROOT}>!g' \
#    /etc/apache2/sites-available/000-default.conf \
#    && sed -ri \
#    -e 's!<Directory /var/www/html>!<Directory ${APACHE_DOCUMENT_ROOT}>!g' \
#    /etc/apache2/apache2.conf

# 5. Copier le php.ini personnalisé depuis la racine du projet vers l'emplacement PHP attendu
COPY php.ini /usr/local/etc/php/php.ini

# 6. Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 7. Copier d'abord composer.json et composer.lock pour optimiser le cache Docker
COPY composer.json composer.lock ./

# 8. Installer les dépendances PHP (en mode production)
RUN composer install --no-dev --optimize-autoloader

# 9. Copier le reste du code (y compris src/, routes.php, public/, etc.)
COPY . .

WORKDIR /var/www/html

# 10. Donner les droits à www-data
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80