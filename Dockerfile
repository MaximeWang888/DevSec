# Utiliser l'image PHP avec Apache
FROM php:8.0-apache

# Installer les dépendances nécessaires
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Copier les fichiers de l'application dans le conteneur
COPY . /var/www/html/

# Exposer le port 80
EXPOSE 80
