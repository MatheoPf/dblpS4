<?php
phpinfo();

// Nom de l'auteur
$auteur = "Arnaud Delhay"; 
$auteur_enc = urlencode($auteur);

// URL
$url = "https://dblp.org/search/publ/api?q=$auteur_enc&format=json";

// Récupération des données
$response = file_get_contents($url);
echo "</pre>"
?>