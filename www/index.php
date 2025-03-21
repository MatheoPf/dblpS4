<?php
require_once "config.php";
require_once "utils.php";

// Vous pouvez ajouter ici des requêtes pour récupérer des statistiques, 
// les dernières publications, les auteurs en vedette, etc.
$sql = "SELECT * FROM AnalyseGeo._publications ORDER BY annee DESC LIMIT 5";
$stmt = $pdo->query($sql);
$publicationsRecente = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Plateforme Académique</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Exemples de styles pour organiser la page d'accueil */
        .section { margin: 20px auto; max-width: 800px; }
        .publication-item { border-bottom: 1px solid #ddd; padding: 10px 0; }
        .publication-item:last-child { border-bottom: none; }
    </style>
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
        <section class="section intro">
            <h2>Bienvenue sur la Plateforme Académique</h2>
            <p>Découvrez les dernières publications, explorez les profils des auteurs et des institutions, et suivez l'actualité de la recherche.</p>
        </section>
        
        <section class="section publications-recente">
            <h2>Publications Récentes</h2>
            <?php if (!empty($publicationsRecente)) { ?>
                <?php foreach ($publicationsRecente as $pub) { ?>
                    <div class="publication-item">
                        <a href="publication.php?id=<?= urlencode($pub['id_dblp']); ?>">
                            <h3><?= htmlentities($pub['titre']); ?></h3>
                        </a>
                        <p>Année : <?= htmlentities($pub['annee']); ?> | DOI : <?= htmlentities($pub['doi']); ?></p>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>Aucune publication récente.</p>
            <?php } ?>
        </section>
        
        <!-- Vous pouvez ajouter d'autres sections, par exemple : -->
        <section class="section auteurs-vedette">
            <h2>Auteurs en Vedette</h2>
            <p>Découvrez les auteurs les plus actifs ou en vedette sur la plateforme.</p>
            <!-- Vous pouvez créer une requête pour récupérer quelques auteurs aléatoires ou populaires -->
        </section>
        
        <section class="section statistiques">
            <h2>Statistiques</h2>
            <?php
            // Exemple simple pour récupérer quelques statistiques
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
