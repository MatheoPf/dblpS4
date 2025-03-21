<?php
require_once "config.php";
require_once "utils.php";

// Récupérer l'identifiant de la publication depuis l'URL
$idPublication = isset($_GET['id']) ? $_GET['id'] : null;

if (!$idPublication) {
    // Aucun identifiant de publication fourni : affichage de la liste de toutes les publications
    $sql = "SELECT * FROM AnalyseGeo._publications ORDER BY annee DESC";
    $stmt = $pdo->query($sql);
    $listePublications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Liste des Publications</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header>
            <h1>Liste des Publications</h1>
            <nav>
                <a href="index.php">Accueil</a>
                <a href="auteur.php">Auteurs</a>
                <a href="publication.php">Publications</a>
                <a href="structure.php">Structures</a>
            </nav>
        </header>
        <main>
            <section class="liste-publications">
                <?php if (!empty($listePublications)) { ?>
                    <ul>
                        <?php foreach ($listePublications as $pub) { ?>
                            <li>
                                <a href="publications.php?id=<?= urlencode($pub['id_dblp']); ?>">
                                    <?= htmlentities($pub['titre']); ?>
                                </a> - <?= htmlentities($pub['annee']); ?>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p>Aucune publication trouvée.</p>
                <?php } ?>
            </section>
        </main>
        <footer>
            <p>&copy; 2025 Plateforme Académique</p>
        </footer>
    </body>
    </html>
    <?php
    exit;
}

// Récupérer les informations de la publication
$publication = recupererUnePublication($pdo, $idPublication);
if (!$publication) {
    die("Publication non trouvée !");
}

// Récupérer la liste des auteurs de la publication
$auteurs = recupererListeAuteurs($pdo, $idPublication);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail de la Publication</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Plateforme Académique</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="auteur.php">Auteurs</a>
            <a href="publication.php">Publications</a>
            <a href="structure.php">Structures</a>
        </nav>
    </header>
    <main>
        <section class="publication-info">
            <h2><?= htmlentities($publication["titre"]); ?></h2>
            <p><strong>Année :</strong> <?= htmlentities($publication["annee"]); ?></p>
            <?php if (!empty($publication["doi"])) { ?>
                <p><strong>DOI :</strong> <?= htmlentities($publication["doi"]); ?></p>
            <?php } ?>
            <?php if (!empty($publication["lieu"])) { ?>
                <p><strong>Lieu :</strong> <?= htmlentities($publication["lieu"]); ?></p>
            <?php } ?>
            <?php if (!empty($publication["type"])) { ?>
                <p><strong>Type :</strong> <?= htmlentities($publication["type"]); ?></p>
            <?php } ?>
        </section>
        
        <section class="auteurs-publication">
            <h3>Auteurs</h3>
            <?php if (!empty($auteurs)) { ?>
                <ul>
                    <?php foreach ($auteurs as $auteur) { ?>
                        <li>
                            <a href="auteur.php?pid=<?= htmlentities($auteur["pid"]); ?>">
                                <?= html_entity_decode($auteur["nom"], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            <?php } else { ?>
                <p>Aucun auteur associé à cette publication.</p>
            <?php } ?>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2025 Plateforme Académique</p>
    </footer>
</body>
</html>
