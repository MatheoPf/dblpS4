<?php
// auto_fill.php : Script pour remplir automatiquement la base

require 'config.php';
require 'utils.php';

// Récupérer toutes les publications ayant un DOI dans la table _publications
$sql = "SELECT doi FROM AnalyseGeo._publications WHERE doi IS NOT NULL";
$stmt = $pdo->query($sql);
$publications = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($publications)) {
    die("Aucune publication trouvée avec un DOI.");
}

foreach ($publications as $pub) {
    $doi = $pub['doi'];
    echo "Traitement de la publication avec DOI : $doi<br>";

    // Récupérer la publication via l'API OpenAlex
    $publicationData = recupererPublicationOpenAlex($doi);
    if (!$publicationData) {
        echo "Publication non trouvée pour DOI: $doi<br>";
        continue;
    }
    
    // Extraction de la liste des auteurs
    $authors = extraireAuteurs($publicationData);
    foreach ($authors as $auteur) {
        $nomAuteur = $auteur['nom'];
        echo "Traitement de l'auteur : $nomAuteur<br>";

        // Récupérer le PID via DBLP
        $pid = recupererPidDblp($nomAuteur);
        if (!$pid) {
            echo "PID non trouvé pour l'auteur: $nomAuteur<br>";
            continue;
        }
        
        // Récupérer l'ORCID depuis DBLP
        $orcid = recupererOrcidDepuisDblp($pid);
        
        // Insérer l'auteur dans _auteurs s'il n'existe pas déjà
        if (!auteurExiste($pdo, $pid)) {
            insererAuteur($pdo, $pid, $orcid, $nomAuteur);
            echo "Auteur '$nomAuteur' inséré avec PID $pid et ORCID " . ($orcid ?? "non trouvé") . "<br>";
        } else {
            echo "L'auteur '$nomAuteur' (PID: $pid) existe déjà.<br>";
        }
        
        // Si un ORCID est disponible, récupérer et traiter les affiliations depuis OpenAlex
        if ($orcid) {
            $affiliations = recupererAffiliationsOpenAlex($orcid);
            if ($affiliations) {
                foreach ($affiliations as $affiliation) {
                    if (isset($affiliation['institution'])) {
                        // Insertion (ou mise à jour) de la structure dans _structures
                        insererStructure($pdo, $affiliation['institution']);
                        
                        // Création de la liaison entre l'auteur et la structure dans _affiliation
                        $idInstitutionComplet = $affiliation['institution']['id'];
                        $idInstitution = str_replace("https://openalex.org/", "", $idInstitutionComplet);
                        lierAffiliationAuteur($pdo, $pid, $idInstitution);
                        echo "Affiliation liée : Auteur $pid -> Institution $idInstitution<br>";
                        
                        // Si le ROR est disponible, insérer/mettre à jour la ville via l'API ROR
                        if (isset($affiliation['institution']['ror']) && !empty($affiliation['institution']['ror'])) {
                            $idVille = insererVilleROR($pdo, $affiliation['institution']['ror']);
                            if ($idVille) {
                                // Mise à jour de la structure pour associer la ville si non déjà renseignée
                                $sqlUpdate = "UPDATE AnalyseGeo._structures 
                                              SET id_ville = :id_ville 
                                              WHERE id_struct = :id_struct 
                                              AND (id_ville IS NULL OR id_ville = 0)";
                                $stmtUpdate = $pdo->prepare($sqlUpdate);
                                $stmtUpdate->bindParam(':id_ville', $idVille, PDO::PARAM_INT);
                                $stmtUpdate->bindParam(':id_struct', $idInstitution, PDO::PARAM_STR);
                                $stmtUpdate->execute();
                                echo "Structure $idInstitution mise à jour avec l'ID ville $idVille.<br>";
                            }
                        }
                    }
                }
            } else {
                echo "Aucune affiliation trouvée pour l'auteur $nomAuteur (ORCID: $orcid).<br>";
            }
        }
        echo "<br>";
    }
    echo "<hr>";
}
echo "Remplissage automatique terminé.";
?>
