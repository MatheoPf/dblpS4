#!/bin/bash

# Définition des variables
GIT_DIR="/var/www/html"
HTML_DIR="/var/www/html_deploy"

# Vérifier si le dépôt Git existe
if [ ! -d "$GIT_DIR/.git" ]; then
  echo "Erreur : Aucun dépôt Git trouvé dans $GIT_DIR."
  exit 1
fi

# Récupérer les variables d'environnement
GIT_USER=${GIT_USER:-"ton-utilisateur-github"}
GIT_TOKEN=${GIT_TOKEN:-"ton-access-token"}
GIT_REPO="github.com/ton-utilisateur-github/ton-repo.git"

# Configurer les credentials Git
cd "$GIT_DIR" || { echo "Erreur : Impossible d'accéder à $GIT_DIR."; exit 1; }
git remote set-url origin "https://${GIT_USER}:${GIT_TOKEN}@${GIT_REPO}"

# Mise à jour du dépôt
echo "Mise à jour du dépôt Git..."
git pull origin main || { echo "Erreur lors du git pull."; exit 1; }

# Copier les fichiers
echo "Copie des fichiers vers $HTML_DIR..."
rsync -av --delete "$GIT_DIR/" "$HTML_DIR/"

echo "Mise à jour terminée."
