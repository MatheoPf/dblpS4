<?php
require_once 'utils.php';

$pdo = getDBConnection();
$pid = isset($_GET['pid']) ? (int)$_GET['pid'] : null;

if ($pid) {
    $auteur = getAuteur($pdo, $pid);
    if (!$auteur) {
        die("Auteur non trouvé !");
    }

    $structures = getStructuresAffiliees($pdo, $auteur['hal_id']);
    $publications = getPublications($pdo, $pid);
    $nbPublications = count($publications);
} else {
    die("Aucun auteur spécifié.");
}
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
            <a href="structure">Structures</a>
            <a href="#">Soumettre un article</a>
        </nav>
    </header>
    <main>
        <section class="auteur-info"> 
            <h2><?= htmlentities($auteur["nom"]);?></h2>
            <article>
                <h4>Affilié à :</h4>
                <ul>
                    <?php foreach ($structures as $structure) { ?>
                        <li><a href="structure.php?nom=<?= urlencode($structure['nom_lab']); ?>">
                            <?= htmlentities($structure['nom_lab']); ?>
                        </a></li>
                    <?php } ?>
                </ul>
                <p>A écrit / co-écrit : <?= htmlentities($nbPublications); ?> articles</p>
            </article>
        </section>
        
        <section class="publications">
            <h3>Publications</h3>
            <ul>
                <?php foreach ($publications as $publication) : ?>
                    <li>
                        <a href="publication.php?id=<?= urlencode($publication['id_dblp']); ?>">
                            <?= htmlentities($publication['titre']); ?>
                        </a> - <?= htmlentities($publication['annee']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2025 Plateforme Académique</p>
    </footer>
</body>
</html>