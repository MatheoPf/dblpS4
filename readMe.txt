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

Comment utiliser le site : 
    1. Installer Docker et l'ouvrir
    2. Taper "docker-compose up -d --build"
    3. Se connecter à "http://localhost:5050/" avec "Email" : admin@admin.com et "Password" : admin
    4. Clique droit sur "Server" du "Object Explorer" -> "Register" -> "Server..."
    5. Renseigner le champs "Name" : dblp, ensuite dans l'onglet Connection les champs "Host name/address" : db, "Port" : 5432, "Maintenance database" : mydatabase, "Username" : user, "Password" : password
    6. Copier et exécuter "createdb.sql" dans pgAdmin
    7. Sur internet : "http://localhost:8080/remplirDblp.php"
    8. Puis : "http://localhost:8080/remplir.php"
    9. Attendre environ 6min
    10. Rendez-vous sur "http://localhost:8080"