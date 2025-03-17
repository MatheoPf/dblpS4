# Utilise l'image PHP avec Apache
FROM php:apache

# Installer Git, PostgreSQL client et d'autres outils
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Activer les logs Apache pour le débogage
RUN ln -sf /dev/stdout /var/log/apache2/access.log && \
    ln -sf /dev/stderr /var/log/apache2/error.log
