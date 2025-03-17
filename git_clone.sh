#!/bin/bash

# Aller dans le dossier www
cd www

# Vérifier si le dépôt est déjà cloné
if [ -d ".git" ]; then
    echo "Le dépôt Git existe déjà, mise à jour..."
    git pull origin main
else
    echo "Clonage du dépôt Git avec un dossier spécifique..."

    # Initialiser un dépôt vide
    git init

    # Ajouter le remote avec authentification
    git remote add origin https://$GIT_USER:$GIT_TOKEN@github.com/MatheoPf/dblpS4.git

    # Activer sparse-checkout pour ne récupérer qu'un dossier spécifique
    git config core.sparseCheckout true

    # Définir le dossier à récupérer (remplace `chemin/dossier` par le bon chemin)
    echo "html/*" > .git/info/sparse-checkout

    # Récupérer uniquement ce dossier
    git pull origin main

    # Déplacer les fichiers à la racine de www et nettoyer
    mv html/* .
    rm -rf html
fi
