<?php

// Nom de l'auteur
$auteur = "Arnaud Delhay"; 
$auteur_enc = urlencode($auteur);

// URL
$url = "https://dblp.org/search/publ/api?q=$auteur_enc&format=json";

// Récupération des données
echo "<pre>";
$response = file_get_contents($url);
print_r($response);
echo "</pre>"
?>