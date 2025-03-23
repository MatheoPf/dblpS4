<?php
require_once "config.php";
require_once "utils.php";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
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
        <p>Découvrez les dernières publications, explorez les profils des auteurs et des institutions, et suivez l'actualité de la recherche.</p>

        <section>
            <h2>Publications Récentes</h2>
            <?php 
            $publicationsRecente = recuperer5DernieresPublications($pdo);
            if (!empty($publicationsRecente)) { ?>
                <?php foreach ($publicationsRecente as $pub) { ?>
                    <div>
                        <a href="publication.php?id=<?= htmlentities($pub['id_dblp']); ?>">
                            <h3><?= html_entity_decode($pub['titre']); ?></h3>
                        </a>
                        <p>Année : <?= htmlentities($pub['annee']); ?> | Type : <?= htmlentities($pub['type']); ?> | Lieu : <?= htmlentities($pub['lieu']); ?></p>
                    </div>
                    <hr>
                <?php } ?>
            <?php } else { ?>
                <p>Aucune publication récente.</p>
            <?php } ?>
        </section>
        
        <section>
            <h2>Auteurs en Vedette</h2>
            <?php 
            $auteursVedette = recupererAuteursVedette($pdo);
            if (!empty($auteursVedette)) { ?>
                <ul>
                    <?php foreach ($auteursVedette as $auteur) { ?>
                        <li>
                            <a href="auteur.php?pid=<?= htmlentities($auteur['pid']); ?>">
                                <?= html_entity_decode($auteur['nom']); ?>
                            </a>
                            (<?= htmlentities($auteur['nb_publications']); ?> publications)
                        </li>
                    <?php } ?>
                </ul>
            <?php } else { ?>
                <p>Aucun auteur en vedette pour le moment.</p>
            <?php } ?>
        </section>
        
        <section>
            <h2>Statistiques</h2>
            <?php
            $nbPublications = $pdo->query("SELECT COUNT(*) FROM AnalyseGeo._publications")->fetchColumn();
            $nbAuteurs = $pdo->query("SELECT COUNT(*) FROM AnalyseGeo._auteurs")->fetchColumn();
            $nbStructures = $pdo->query("SELECT COUNT(*) FROM AnalyseGeo._structures")->fetchColumn();
            ?>
            <ul>
                <li>Total des publications : <?= htmlentities($nbPublications); ?></li>
                <li>Total des auteurs : <?= htmlentities($nbAuteurs); ?></li>
                <li>Total des structures : <?= htmlentities($nbStructures); ?></li>
            </ul>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2025 Plateforme Académique</p>
    </footer>
</body>
</html>
