# ==============================================================================
# STAGE 1 : BASE - Un socle commun pour le développement et la production
# Installe les dépendances système et les extensions PHP communes.
# ==============================================================================
FROM php:8.2-fpm AS base

# Variables d'environnement pour l'installation non-interactive
ENV DEBIAN_FRONTEND=noninteractive

# Installation des dépendances système requises
# AJOUT de nodejs et npm pour la compilation des assets frontend
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

# Configuration et installation des extensions PHP
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

# Installation de Composer
COPY --from=composer:2.7.7 /usr/bin/composer /usr/bin/composer

# Définition du répertoire de travail
WORKDIR /var/www/html

# ==============================================================================
# STAGE 2 : BUILDER - Construction des dépendances applicatives
# Utilise le socle 'base' et installe les dépendances Composer et NPM.
# ==============================================================================
FROM base AS builder

# Copie des fichiers de dépendances pour profiter du cache de Docker
COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

# Installation des dépendances PHP (avec les dépendances de développement)
RUN composer install --no-interaction --no-scripts --prefer-dist --optimize-autoloader

# Installation des dépendances Node.js
RUN npm install

# Copie de l'intégralité du code de l'application
COPY . .

# Optionnel : build des assets frontend si nécessaire
# RUN npm run build

# ==============================================================================
# STAGE 3 : DEVELOPMENT - L'image pour le développement local
# Hérite du builder pour avoir toutes les dépendances et active Xdebug.
# ==============================================================================
FROM builder AS dev

# Installer et activer Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Créer les répertoires de l'application et définir les bonnes permissions
RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log /var/lib/php/sessions && \
    chown -R www-data:www-data /var/www/html && \
    chown -R www-data:www-data /var/lib/php/sessions && \
    chmod -R 775 /var/www/html/var

# La commande par défaut pour démarrer le service
EXPOSE 9000
CMD ["php-fpm"]

# ==============================================================================
# STAGE 4 : FINAL (PRODUCTION) - L'image optimisée pour la production
# Utilise une base Alpine légère et ne copie que le strict nécessaire.
# ==============================================================================
FROM php:8.2-fpm-alpine AS final

WORKDIR /var/www/html

# Installation des dépendances système minimales pour la production sur Alpine
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

# Copie des fichiers de l'application et des dépendances de production
COPY --from=builder /app/src/ /var/www/html/src/
COPY --from=builder /app/Public/ /var/www/html/Public/
COPY --from=builder /app/routes/ /var/www/html/routes/
COPY --from=builder /app/templates/ /var/www/html/templates/
COPY --from=builder /app/composer.json /var/www/html/composer.json

# Installation des dépendances Composer en mode production (sans les --dev)
RUN composer install --no-interaction --no-dev --no-scripts --optimize-autoloader

# Créer les répertoires de l'application et définir les bonnes permissions
RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log /var/lib/php/sessions && \
    chown -R www-data:www-data /var/www/html && \
    chown -R www-data:www-data /var/lib/php/sessions && \
    chmod -R 775 /var/www/html/var

# Passer à l'utilisateur non-root pour plus de sécurité
USER www-data

# Commande par défaut
EXPOSE 9000
CMD ["php-fpm"]