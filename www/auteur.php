<?php
require_once "config.php";
require_once "utils.php";

$pid = isset($_GET['pid']) ? $_GET['pid'] : null;

if (!$pid) {
    // Aucun auteur spécifié : on affiche la liste de tous les auteurs
    $sql = "SELECT * FROM AnalyseGeo._auteurs ORDER BY nom ASC";
    $stmt = $pdo->query($sql);
    $listeAuteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Liste des Auteurs</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header>
            <h1>R4.C10</h1>
            <nav>
                <a href="index.php">Accueil</a>
                <a href="auteur.php">Auteurs</a>
                <a href="publication.php">Publications</a>
                <a href="structure.php">Structures</a>
            </nav>
        </header>
        <main>
            <section>
                <?php if (!empty($listeAuteurs)) { 
                    foreach ($listeAuteurs as $auteur) { ?>
                        <div>
                            <a href="auteur.php?pid=<?= htmlentities($auteur['pid']); ?>">
                                <?= html_entity_decode($auteur['nom']); ?>
                            </a>
                        </div>
                        <hr>
                <?php } } else { ?>
                    <p>Aucun auteur trouvé.</p>
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

// Récupérer les informations de l'auteur
$auteur = recupererAuteur($pdo, $pid);
if (!$auteur) {
    die("Auteur non trouvé !");
}

// Récupérer les structures affiliées et les publications
$structures = recupererStructuresAffiliees($pdo, $pid);
$publications = recupererPublicationsParAuteur($pdo, $pid);
$nbPublications = count($publications);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de l'Auteur</title>
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
        </nav>
    </header>
    <main>
        <section class="auteur-info"> 
            <h2><?= html_entity_decode($auteur["nom"], ENT_QUOTES, 'UTF-8'); ?></h2>
            <article>
                <h4>Affilié à :</h4>
                <?php if (!empty($structures)) { ?>
                    <ul>
                        <?php foreach ($structures as $structure) { ?>
                            <li>
                                <a href="structure.php?id=<?= htmlentities($structure['id_struct']); ?>">
                                    <?= html_entity_decode($structure['nom_struct']); ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p>Aucune affiliation trouvée.</p>
                <?php } ?>
                <p>A écrit / co-écrit : <?= htmlentities($nbPublications); ?> articles</p>
            </article>
        </section>
        
        <section>
            <h3>Publications</h3>
            <?php if (!empty($publications)) { ?>
                <ul>
                    <?php foreach ($publications as $publication) { ?>
                        <li>
                            <a href="publication.php?id=<?= htmlentities($publication['id_dblp']); ?>">
                                <?= html_entity_decode($publication['titre']); ?>
                            </a> - <?= htmlentities($publication['annee']); ?>
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
