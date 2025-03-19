<?php
require 'config.php';

$sql = "SELECT s.id_struct, s.nom_struct, v.latitude, v.longitude 
        FROM AnalyseGeo._structures s
        JOIN AnalyseGeo._adresses a ON a.id_adresse = s.id_adresse
        JOIN AnalyseGeo._villes v ON a.nom_ville = v.nom_ville";
$stmt = $pdo->query($sql);
$structures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Globe des Structures</title>
    <link href="https://cesium.com/downloads/cesiumjs/releases/1.104/Build/Cesium/Widgets/widgets.css" rel="stylesheet">
    <style>
        html, body, #cesiumContainer {
            width: 100%; height: 100%; margin: 0; padding: 0; overflow: hidden;
        }
    </style>
</head>
<body>
    <div id="cesiumContainer"></div>
    
    <!-- CesiumJS -->
    <script src="https://cesium.com/downloads/cesiumjs/releases/1.104/Build/Cesium/Cesium.js"></script>
    <script>
        // Transmet les données PHP en JSON
        const structures = <?php echo json_encode($structures); ?>;
        
        // Initialisation de Cesium
        Cesium.Ion.defaultAccessToken = 'YOUR_CESIUM_ION_ACCESS_TOKEN'; // Remplacez par votre token
        const viewer = new Cesium.Viewer('cesiumContainer', {
            terrainProvider: Cesium.createWorldTerrain()
        });

        // Ajouter des entités pour chaque structure avec coordonnées
        structures.forEach(structure => {
            if(structure.latitude && structure.longitude) {
                viewer.entities.add({
                    name: structure.nom_struct,
                    position: Cesium.Cartesian3.fromDegrees(parseFloat(structure.longitude), parseFloat(structure.latitude)),
                    point: {
                        pixelSize: 10,
                        color: Cesium.Color.RED,
                        outlineColor: Cesium.Color.WHITE,
                        outlineWidth: 2
                    },
                    description: `<strong>${structure.nom_struct}</strong><br>ID: ${structure.id_struct}`
                });
            }
        });

        // Zoom automatique sur toutes les entités
        viewer.zoomTo(viewer.entities);
    </script>
</body>
</html>
