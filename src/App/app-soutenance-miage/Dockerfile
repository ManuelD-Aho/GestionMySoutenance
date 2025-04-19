FROM php:8.1-fpm

# Variables d'environnement pour le fuseau horaire d'Afrique de l'Ouest
ENV TZ=Africa/Abidjan
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    libzip-dev \
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libonig-dev \
    curl \
    libxml2-dev \
    zip \
    unzip \
    git \
    locales \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql mysqli zip gd exif opcache intl mbstring xml \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configuration pour les caractères français et régionaux
RUN sed -i -e 's/# fr_FR.UTF-8 UTF-8/fr_FR.UTF-8 UTF-8/' /etc/locale.gen && \
    locale-gen

ENV LANG fr_FR.UTF-8
ENV LANGUAGE fr_FR:fr
ENV LC_ALL fr_FR.UTF-8

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration Nginx
RUN rm -rf /etc/nginx/sites-available/* \
    && rm -rf /etc/nginx/sites-enabled/*

# Configuration des fichiers
COPY ./docker/config/nginx.conf /etc/nginx/conf.d/default.conf
COPY ./docker/config/php.ini /usr/local/etc/php/conf.d/custom.ini

# Configuration des permissions
RUN chown -R www-data:www-data /var/www

# Création des répertoires nécessaires
RUN mkdir -p /var/log/nginx /var/log/php /var/lib/php/sessions \
    && chown -R www-data:www-data /var/log/php /var/lib/php/sessions

# Configuration de Supervisor
RUN echo '[supervisord]\nnodaemon=true\nuser=root\nlogfile=/var/log/supervisor/supervisord.log\npidfile=/var/run/supervisord.pid\n\n[program:php-fpm]\ncommand=/usr/local/sbin/php-fpm\nautostart=true\nautorestart=true\n\n[program:nginx]\ncommand=/usr/sbin/nginx -g "daemon off;"\nautostart=true\nautorestart=true' > /etc/supervisor/conf.d/supervisord.conf

# Point d'entrée
WORKDIR /var/www/html

# Script d'entrée
RUN echo '#!/bin/bash\nset -e\n\n# Créer les répertoires si nécessaire\nmkdir -p /var/www/html/public/uploads/rapports\nmkdir -p /var/www/html/public/uploads/documents\nmkdir -p /var/www/html/public/uploads/temp\nchown -R www-data:www-data /var/www/html\n\n# Exécuter Composer si le fichier existe\nif [ -f "/var/www/html/composer.json" ]; then\n    composer install --no-interaction --optimize-autoloader\nfi\n\nexec "$@"' > /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]