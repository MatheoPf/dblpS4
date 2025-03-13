<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obThR9BMY="
     crossorigin=""/>

     <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>
    <title>Document</title>

    <table >
        <thead>
            <tr>
                <th>
                    ID DLP
                </th>
                <th>
                    Type
                </th>
                <th>
                    DOI
                </th>
                <th>
                    Tithe
                </th>
                <th>
                    Lieu
                </th>
                <th>
                    Année
                </th>
                <th>
                    Pages
                </th>
                <th>
                    EE
                </th>
                <th>
                    URL DBLP
                </th>
                <th>
                    Volume
                </th>
                <th>
                    Numero de Page
                </th>
                <th colspan = "2">
                    Auteur
                </th>
            </tr>
        </thead>
        <tbody> 
        <?php foreach($publi as $lapubli){?>
            <tr>
                <td>
                    <?php echo $lapubli['id_dblp']?>
                </td>
                <td>
                    <?php echo $lapubli['type']?>
                </td>
                <td>
                    <?php echo $lapubli['doi']?>
                </td>
                <td>
                    <?php echo $lapubli['titre']?>
                </td>
                <td>
                    <?php echo $lapubli['lieu']?>
                </td>
                <td>
                    <?php echo $lapubli['annee']?>
                </td>
                <td>
                    <?php echo $lapubli['page']?>
                </td>
                <td>
                    <?php echo $lapubli['ee']?>
                </td>
                <td>
                    <?php echo $lapubli['url_dblp']?>
                </td>
                <td>
                    <?php echo $lapubli['volume']?>
                </td>
                <td>
                    <?php echo $lapubli['numero']?>
                </td>
                <?php foreach() ?>
            </tr>
            <?php } ?>
            

        </tbody>


    </table
            
    
</head>
<body>
    
</body>
</html>

<?php
require 'vendor/autoload.php'; // Assurez-vous d'avoir installé le driver MongoDB via Composer

// Connexion à MongoDB
$client = new MongoDB\Client("mongodb://localhost:27017"); // Ajustez l'URL si nécessaire
$db = $client->analysegeo;  // Choix de la base de données

// Sélectionner les collections
$auteursCollection = $db->auteurs;  // La collection des auteurs
$publicationsCollection = $db->publications;  // La collection des publications
$aEcritCollection = $db->a_ecrit;  // La collection de l'association "a_ecrit"

// Agrégation pour récupérer les informations des auteurs liés aux publications
$pipeline = [
    [
        '$lookup' => [
            'from' => 'a_ecrit',   // Collection à joindre
            'localField' => '_id',  // Champ de la collection 'publications'
            'foreignField' => 'id_publication',  // Champ de la collection 'a_ecrit'
            'as' => 'auteurs'  // Résultat joint sous le champ 'auteurs'
        ]
    ],
    [
        '$unwind' => '$auteurs'  // Décompose le tableau d'auteurs dans chaque publication
    ],
    [
        '$lookup' => [
            'from' => 'auteurs',  // Joindre la collection des auteurs
            'localField' => 'auteurs.id_auteur',  // Champ dans 'a_ecrit'
            'foreignField' => '_id',  // Champ dans 'auteurs'
            'as' => 'auteur_details'  // Résultat joint sous le champ 'auteur_details'
        ]
    ],
    [
        '$unwind' => '$auteur_details'  // Décompose les informations sur l'auteur
    ],
    [
        '$project' => [
            'titre' => 1,  // Information sur la publication
            'annee' => 1,  // Information sur la publication
            'auteur_nom' => '$auteur_details.nom',  // Nom de l'auteur
            'auteur_prenom' => '$auteur_details.prenom',  // Prénom de l'auteur
            'ordre' => '$auteurs.ordre'  // Position de l'auteur dans l'écriture
        ]
    ]
];

// Exécuter l'agrégation
$resultat = $publicationsCollection->aggregate($pipeline);

// Afficher les résultats
foreach ($resultat as $publication) {
    echo "Publication : " . $publication['titre'] . " (" . $publication['annee'] . ")\n";
    echo "Auteur : " . $publication['auteur_nom'] . " " . $publication['auteur_prenom'] . "\n";
    echo "Ordre de l'auteur : " . $publication['ordre'] . "\n\n";
}

// Fermer la connexion
$client = null;
?>


