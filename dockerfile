# Utilisation d'une image PHP avec Apache
FROM php:8.2-apache

# Activer les modules Apache nécessaires
RUN a2enmod rewrite

# Installer l'extension MongoDB pour PHP
RUN apt-get update && apt-get install -y libssl-dev && \
    pecl install mongodb && \
    docker-php-ext-enable mongodb

# Copier les fichiers de ton application dans le conteneur
COPY . /var/www/html/

# Définir les permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Exposer le port 8000 (doit correspondre à ton docker-compose.yml)
EXPOSE 8000

# Lancer Apache en mode foreground
CMD ["apache2-foreground"]
