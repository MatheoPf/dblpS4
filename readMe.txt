Comment se connecté au projet :

Aller sur vsc 3ème onglet puis 1er bouton
3petit point puis clone coller https://github.com/MatheoPf/dblpS4.git
puis pacer dans votre dossier de travail
dans le terminal :

git config --global user.name "<nomGithub>"
git config --global user.email "<emailGithub>"
Pour changer de branche :

git pull
puis en bas a gauche on peut changer de branche

PHP/Apache -> http://localhost:8080
pgAdmin -> http://localhost:5050

Pour docker : 
docker-compose up -d --build

API Hal :
Rechercher un auteur par son nom => "https://api.archives-ouvertes.fr/search/?q=authFullName_s:%22Laurent%20d%27Orazio%22&fl=authFullName_s,authIdHal_s,labStructName_s"
Pour récupérer plus d'informations sur un laboratoire => "https://api.archives-ouvertes.fr/search/?q=authFullName_s:%22Laurent%20d%27Orazio%22&fl=labStructName_s,labStructId_i"
Pour obtenir les détails d'un laboratoire via son labStructId_i => "https://api.archives-ouvertes.fr/search/?q=labStructId_i:%22486345%22&fl=labStructName_s,labStructAcronym_s,labStructAddress_s"