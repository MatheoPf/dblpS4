<?php
require_once "config.php";
require_once "utils.php";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Carte des Structures Affiliées</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>R4.C.10</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="auteur.php">Auteurs</a>
            <a href="publication.php">Publications</a>
            <a href="structure.php">Structures</a>
            <a href="carte.php">Carte</a>
        </nav>
    </header>
    
    <main>
    <h2>Carte des Structures Affiliées</h2>
    <div id="map"></div>
    </main>
    
    <footer>
        <p>Mathéo PFRANGER  --  Camille POUPON</p>
    </footer>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Récupération des données JSON des structures depuis PHP
        // Assurez-vous que la fonction afficherCarteStructuresAffilies($pdo) est incluse et accessible
        var structures = <?php echo afficherCarteStructuresAffilies($pdo); ?>;
        
        // Initialisation de la carte centrée globalement
        var map = L.map('map').setView([20, 0], 2);
        
        // Ajout d'une couche OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Parcours des structures pour ajouter des marqueurs sur la carte
        structures.forEach(function(structure) {
            if (structure.latitude && structure.longitude) {
                var marker = L.marker([structure.latitude, structure.longitude]).addTo(map);
                marker.bindPopup("<strong>" + structure.nom_struct + "</strong>");
            }
        });
    </script>
</body>
</html>
