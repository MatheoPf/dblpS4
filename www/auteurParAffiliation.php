<?php
$lab_id = "1"; // Remplace avec l'ID de la structure
$url = "https://api.archives-ouvertes.fr/search/?q=labStructId_i:$lab_id&fl=authFullName_s&rows=10&wt=json";

$response = file_get_contents($url);
$data = json_decode($response, true);

$unique_authors = []; // Tableau pour stocker les auteurs uniques
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Auteurs</title>
</head>
<body>

<h1>Auteurs affiliés à la structure (ID: <?php echo $lab_id; ?>)</h1>

<ul>
    <?php
    if (isset($data['response']['docs'])) {
        foreach ($data['response']['docs'] as $doc) {
            $authors = $doc['authFullName_s'] ?? [];

            foreach ($authors as $author) {
                $unique_authors[$author] = true; // Stocke le nom comme clé (élimine les doublons)
            }
        }

        // Affichage des auteurs uniques
        foreach (array_keys($unique_authors) as $name) {
            echo "<li>$name</li>";
        }
    } else {
        echo "<li>Aucun auteur trouvé</li>";
    }
    ?>
</ul>
</body>
</html>
