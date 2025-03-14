# Utilisation d'une image PHP avec Apache
FROM php:8.2-apache

# Définir le dossier de travail
WORKDIR /var/www/html

# Activer les modules Apache nécessaires
RUN a2enmod rewrite

# Installer les extensions PHP et les dépendances système
RUN apt-get update && apt-get install -y \
    libssl-dev \
    curl \
    gnupg \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Installer Node.js et csvtojson
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g csvtojson

# Copier les fichiers de l'application
COPY . .

# Définir les permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Exposer le bon port (Apache utilise 80, pas 8000)
EXPOSE 80

# Lancer Apache en mode foreground
CMD ["apache2-foreground"]
