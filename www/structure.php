<?php
require_once "config.php";
require_once "utils.php";

$id_struct = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id_struct) { ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Liste des Structures</title>
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
                <?php
                $listeStructures = recupererToutesStructure($pdo);
                if (!empty($listeStructures)) {
                    foreach ($listeStructures as $structure) { ?>
                            <div>
                                <a href="structure.php?id=<?= htmlentities($structure['id_struct']); ?>">
                                    <h3><?= html_entity_decode($structure['nom_struct']); ?></h3>
                                </a>
                                <p>Ville : <?= htmlentities($structure['nom_ville']); ?> | Pays : <?= htmlentities($structure['nom_pays']); ?></p>
                            </div>
                            <hr>
                    <?php } } else { ?>
                    <p>Aucune structure trouvée.</p>
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

// Récupérer les informations de la structure
$structure = recupererStructure($pdo, $id_struct);
if (!$structure) {
    die("Structure non trouvée !");
}

// Récupérer les informations de la ville associée (si renseignée)
$ville = null;
if (!empty($structure['id_ville'])) {
    $ville = recupererVille($pdo, $structure['id_ville']);
}

// Récupérer les auteurs affiliés à cette structure
$auteurs = recupererAuteursAffiliesStructure($pdo, $id_struct);

// Récupérer les publications associées à cette structure
$publications = recupererPublicationsStructure($pdo, $id_struct);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de la Structure</title>
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
            <a href="carte.php">Carte</a>
        </nav>
    </header>
    <main>
        <section>
            <h2><?= html_entity_decode($structure["nom_struct"]); ?></h2>
            <?php if (!empty($structure["acronyme"])) { ?>
                <p><strong>Acronyme :</strong> <?= htmlentities($structure["acronyme"]); ?></p>
            <?php } ?>
            <?php if (!empty($structure["ror"])) { ?>
                <p><strong>ROR :</strong> <a href="<?= htmlentities($structure["ror"]); ?>" target="_blank"><?= htmlentities($structure["ror"]); ?></a></p>
            <?php } ?>
            <?php if ($ville) { ?>
                <p><strong>Localisation :</strong> <?= html_entity_decode($ville["nom_ville"]); ?>, <?= html_entity_decode($ville["nom_pays"]); ?></p>
            <?php } else { ?>
                <p><strong>Localisation :</strong> Non renseignée</p>
            <?php } ?>
            <?php if (isset($structure["lineage"]) && !empty($structure["lineage"])) { ?>
                <p><strong>Lineage :</strong> <?= htmlentities(implode(" > ", $structure["lineage"])); ?></p>
            <?php } ?>
        </section>
        
        <section>
            <h3>Auteurs affiliés</h3>
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
                <p>Aucun auteur affilié trouvé.</p>
            <?php } ?>
        </section>
        
        <section>
            <h3>Publications associées</h3>
            <?php if (!empty($publications)) { ?>
                <ul>
                    <?php foreach ($publications as $publication) { ?>
                        <li>
                            <a href="publication.php?id=<?= htmlentities($publication["id_dblp"]); ?>">
                                <?= html_entity_decode($publication["titre"]); ?>
                            </a> (<?= htmlentities($publication["annee"]); ?>)
                        </li>
                    <?php } ?>
                </ul>
            <?php } else { ?>
                <p>Aucune publication trouvée pour cette structure.</p>
            <?php } ?>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2025 Plateforme Académique</p>
    </footer>
</body>
</html>
