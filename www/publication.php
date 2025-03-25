<?php
require_once "config.php";
require_once "utils.php";

$idPublication = isset($_GET['id']) ? $_GET['id'] : null;

if (!$idPublication) { ?>
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
            <section class="liste-publications">
                <?php 
                
                $listePublications = recupererToutesPublications($pdo);
                if (!empty($listePublications)) {
                    foreach ($listePublications as $pub) { 

                        if(isset($pub['ee'])){
                            $lien = $pub['ee'];
                        }else {
                            $lien = $pub['url_dblp'];
                        }
                        
                        ?>
                        <div>
                           <a href="publication.php?id=<?= htmlentities($publication['id_dblp']); ?>">
                                <h3><?= html_entity_decode($pub['titre']); ?></h3>
                            </a>
                            <p>Année : <?= htmlentities($pub['annee']); ?>
                             | Type : <?= htmlentities($pub['type']); ?> 
                             | Parue dans : <?= htmlentities($pub['lieu']); ?>
                             | Lien : <a href="publication.php?id=<?= htmlentities($publication['id_dblp']); ?>">
                            </p>
                        </div>
                        <hr>
                <?php }  } else { ?>
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
        <section>
            <h2><?= html_entity_decode($publication["titre"]); ?></h2>
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
        
        <section>
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
