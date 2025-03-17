#!/bin/bash

# Aller dans le dossier www
cd www

# Vérifier si le dépôt Git est déjà cloné
if [ -d ".git" ]; then
    echo "Le dépôt Git existe déjà, mise à jour..."
    git pull origin main
else
    echo "Clonage du dépôt Git avec un dossier spécifique..."

    # Initialiser un dépôt vide
    git init

    # Ajouter le remote avec authentification (utilise les variables du .env)
    git remote add origin https://$GIT_USER:$GIT_TOKEN@github.com/ton-utilisateur/ton-repo.git

    # Activer sparse-checkout
    git config core.sparseCheckout true

    # Définir le dossier à récupérer (remplace `mon_dossier` par le bon chemin)
    echo "mon_dossier/*" > .git/info/sparse-checkout

    # Récupérer uniquement ce dossier
    git pull origin main
fi
