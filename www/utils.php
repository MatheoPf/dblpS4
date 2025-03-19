<?php
require_once "config.php";

/**
 * Récupère les informations d'un auteur
 */
function getAuteur($pdo, $pid) {
    $query = "SELECT * FROM AnalyseGeo._auteurs WHERE pid = :pid";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':pid', $pid, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Récupère les structures affiliées à un auteur
 */
function getStructuresAffiliees($pdo, $hal_id) {
    $query = "SELECT s.*
              FROM AnalyseGeo._est_affilie e
              JOIN AnalyseGeo._structures s ON e.id_lab = s.id_lab
              WHERE e.hal_id = :hal_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':hal_id', $hal_id, PDO::PARAM_STR);
    $stmt->execute(['hal_id' => $hal_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les publications d'un auteur
 */
function getPublications($pdo, $pid) {
    $query = "SELECT * 
              FROM AnalyseGeo.a_ecrit ae
              JOIN AnalyseGeo._publications p ON ae.id_dblp = p.id_dblp
              WHERE ae.pid = :pid
              ORDER BY p.annee DESC";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':pid', $pid, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les données d'une publication depuis l'API OpenAlex à partir d'un DOI.
 *
 * @param string $doi Le DOI de la publication.
 * @return array|null Le tableau associatif des données ou null en cas d'erreur.
 */
function recupererPublicationOpenAlex($doi) {
    $url = "https://api.openalex.org/works/https://doi.org/" . urlencode($doi);
    $reponse = file_get_contents($url);
    if (!$reponse) {
        return null;
    }
    return json_decode($reponse, true);
}

/**
 * Extrait la liste des auteurs depuis la publication OpenAlex.
 *
 * @param array $publication Le tableau associatif de la publication.
 * @return array Un tableau d'auteurs avec leur nom.
 */
function extraireAuteurs($publication) {
    $listeAuteurs = [];
    if (isset($publication['authorships'])) {
        foreach ($publication['authorships'] as $authorship) {
            if (isset($authorship['author'])) {
                $auteur = $authorship['author'];
                $listeAuteurs[] = [
                    'nom' => $auteur['display_name']
                ];
            }
        }
    }
    return $listeAuteurs;
}

/**
 * Récupère le PID DBLP d'un auteur à partir de son nom via l'API DBLP.
 *
 * @param string $nomAuteur Le nom de l'auteur.
 * @return string|null Le PID (ex. "92/6408") ou null s'il n'est pas trouvé.
 */
function recupererPidDblp($nomAuteur) {
    $url = "https://dblp.org/search/author/api?q=" . urlencode($nomAuteur) . "&format=json";
    
    // Création d'un contexte HTTP avec un User-Agent et timeout
    $options = array(
        'http' => array(
            'method'  => 'GET',
            'header'  => "User-Agent: MonApplication/1.0\r\n",
            'timeout' => 5
        )
    );
    $context  = stream_context_create($options);
    
    // Tenter de récupérer la réponse
    $reponse = @file_get_contents($url, false, $context);
    
    // Si la requête échoue, on peut faire une pause avant de retourner null
    if ($reponse === FALSE) {
        // Attendre 1 seconde pour éviter d'envoyer trop rapidement de nouvelles requêtes
        sleep(1);
        return null;
    }

    $json = json_decode($reponse, true);
    
    // Vérifier la présence d'un résultat et extraire le PID
    if (isset($json['result']['hits']['hit'][0]['info']['url'])) {
        $urlPid = $json['result']['hits']['hit'][0]['info']['url'];
        $pid = str_replace("https://dblp.org/pid/", "", $urlPid);
        return $pid;
    }
    
    return null;
}

/**
 * Récupère l'ORCID depuis DBLP pour un auteur donné à partir de son PID.
 *
 * @param string $pid Le PID de l'auteur.
 * @return string|null L'ORCID sans le préfixe "https://orcid.org/" ou null s'il n'est pas trouvé.
 */
function recupererOrcidDepuisDblp($pid) {
    $url = "https://dblp.org/pid/$pid.xml";
    
    // Création d'un contexte HTTP avec un User-Agent personnalisé et un timeout
    $options = array(
        'http' => array(
            'method'  => 'GET',
            'header'  => "User-Agent: MonApplication/1.0\r\n",
            'timeout' => 5
        )
    );
    $context = stream_context_create($options);
    
    // Utiliser file_get_contents avec le contexte
    $xmlContent = @file_get_contents($url, false, $context);
    
    if ($xmlContent === FALSE) {
        // Attendre une seconde en cas d'échec (429 Too Many Requests)
        sleep(1);
        return null;
    }
    
    $xml = simplexml_load_string($xmlContent);
    if (!$xml) {
        return null;
    }
    
    foreach ($xml->person->url as $urlBalise) {
        if (strpos($urlBalise, 'orcid.org') !== false) {
            return str_replace("https://orcid.org/", "", (string)$urlBalise);
        }
    }
    return null;
}

/**
 * Insère (ou met à jour) un auteur dans la table _auteurs.
 *
 * @param PDO    $pdo   L'objet PDO.
 * @param string $pid   Le PID de l'auteur (clé primaire).
 * @param string $orcid L'ORCID (sans le préfixe "https://orcid.org/").
 * @param string $nom   Le nom complet de l'auteur.
 */
function insererAuteur(PDO $pdo, $pid, $orcid, $nom) {
    $sql = "INSERT INTO AnalyseGeo._auteurs (pid, orc_id, nom)
            VALUES (:pid, :orcid, :nom)
            ON CONFLICT (pid) DO UPDATE 
              SET orc_id = EXCLUDED.orc_id, 
                  nom = EXCLUDED.nom";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);
    $stmt->bindParam(':orcid', $orcid, PDO::PARAM_STR);
    $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
    $stmt->execute();
}

/**
 * Vérifie si un auteur existe déjà dans la base de données.
 *
 * @param PDO $pdo Connexion PDO.
 * @param string $pid Le PID de l'auteur.
 * @return bool True si l'auteur existe, sinon False.
 */
function auteurExiste(PDO $pdo, $pid) {
    $sql = "SELECT COUNT(*) FROM AnalyseGeo._auteurs WHERE pid = :pid";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

/**
 * Ajoute un auteur dans la base de données s'il n'existe pas encore.
 *
 * @param PDO $pdo Connexion PDO.
 * @param string $nomAuteur Le nom complet de l'auteur.
 */
function ajouterAuteurSiNexistePas(PDO $pdo, $nomAuteur) {
    // Récupération du PID depuis DBLP
    $pid = recupererPidDblp($nomAuteur);
    
    if (!$pid) {
        echo "Aucun PID trouvé pour l'auteur : $nomAuteur\n";
        return;
    }

    // Vérifier si l'auteur est déjà en base
    if (auteurExiste($pdo, $pid)) {
        echo "L'auteur '$nomAuteur' (PID: $pid) est déjà en base, aucune insertion.\n";
        return;
    }
    
    // Récupérer l'ORCID depuis DBLP
    $orcid = recupererOrcidDepuisDblp($pid);
    
    // Insertion dans la table _auteurs
    insererAuteur($pdo, $pid, $orcid, $nomAuteur);
    echo "Auteur '$nomAuteur' (PID: $pid) inséré avec succès.\n";
}

/**
 * Récupère les affiliations d'un auteur depuis l'API OpenAlex à partir de son ORCID.
 *
 * @param string $orcid L'ORCID de l'auteur (sans le préfixe "https://orcid.org/").
 * @return array|null Tableau des affiliations ou null en cas d'erreur.
 */
function recupererAffiliationsOpenAlex($orcid) {
    $url = "https://api.openalex.org/authors?filter=orcid:" . urlencode($orcid) . "&format=json";
    $reponse = file_get_contents($url);
    if (!$reponse) {
        return null;
    }
    $json = json_decode($reponse, true);
    if (isset($json['results'][0]['affiliations'])) {
        return $json['results'][0]['affiliations'];
    }
    return null;
}

/**
 * Insère (ou met à jour) une structure dans la table _structures.
 *
 * @param PDO   $pdo L'objet PDO.
 * @param array $institution Tableau associatif représentant l'institution (issu de l'API OpenAlex).
 */
function insererStructure(PDO $pdo, $institution) {
    // Extraction des données utiles
    // L'id de l'institution est de la forme "https://openalex.org/I2802519937"
    $idInstitutionComplet = $institution['id'];
    $idInstitution = str_replace("https://openalex.org/", "", $idInstitutionComplet);
    $nomInstitution = $institution['display_name'];
    $rorInstitution = isset($institution['ror']) ? $institution['ror'] : null;
    
    $sql = "INSERT INTO AnalyseGeo._structures (id_struct, nom_struct, ror)
            VALUES (:id_struct, :nom_struct, :ror)
            ON CONFLICT (id_struct) DO UPDATE SET nom_struct = EXCLUDED.nom_struct, ror = EXCLUDED.ror";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_struct', $idInstitution, PDO::PARAM_STR);
    $stmt->bindParam(':nom_struct', $nomInstitution, PDO::PARAM_STR);
    $stmt->bindParam(':ror', $rorInstitution, PDO::PARAM_STR);
    $stmt->execute();
    
    // Stocker également le lineage de l'institution si présent
    if (isset($institution['lineage']) && is_array($institution['lineage'])) {
        stockerLineage($pdo, $idInstitution, $institution['lineage']);
    }
}

/**
 * Stocke la hiérarchie (lineage) d'une institution dans la table _lineage.
 *
 * @param PDO    $pdo        L'objet PDO.
 * @param string $id_struct  L'identifiant de l'institution (ex. "I2802519937").
 * @param array  $lineage    Tableau des institutions parentes (URLs).
 */
function stockerLineage(PDO $pdo, $id_struct, $lineage) {
    $sql = "INSERT INTO AnalyseGeo._lineage (id_struct, parent_lab, position)
            VALUES (:id_struct, :parent_lab, :position)
            ON CONFLICT (id_struct, parent_lab) DO UPDATE SET position = EXCLUDED.position";
    $stmt = $pdo->prepare($sql);
    
    foreach ($lineage as $position => $parentUrl) {
        $parent_lab = str_replace("https://openalex.org/", "", $parentUrl);
        $stmt->bindParam(':id_struct', $id_struct, PDO::PARAM_STR);
        $stmt->bindParam(':parent_lab', $parent_lab, PDO::PARAM_STR);
        $stmt->bindParam(':position', $position, PDO::PARAM_INT);
        $stmt->execute();
    }
}

/**
 * Lie un auteur à une structure dans la table _affiliation.
 *
 * @param PDO    $pdo L'objet PDO.
 * @param string $pid Le PID de l'auteur.
 * @param string $idInstitution L'identifiant de l'institution (ex. "I2802519937").
 */
function lierAffiliationAuteur(PDO $pdo, $pid, $idInstitution) {
    $sql = "INSERT INTO AnalyseGeo._affiliation (pid, id_struct)
            VALUES (:pid, :id_struct)
            ON CONFLICT (pid, id_struct) DO NOTHING";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);
    $stmt->bindParam(':id_struct', $idInstitution, PDO::PARAM_STR);
    $stmt->execute();
}

/**
 * Interroge l'API ROR pour récupérer l'adresse d'une institution et l'insère dans la table _adresses.
 *
 * @param PDO    $pdo L'objet PDO.
 * @param string $ror L'URL ROR de l'institution (ex. "https://ror.org/00myn0z94").
 * @return bool True en cas de succès, false sinon.
 */
function insererAdresseROR(PDO $pdo, $ror) {
    // Extraire l'identifiant ROR de l'URL
    $ror_id = str_replace("https://ror.org/", "", $ror);
    $url = "https://api.ror.org/organizations/" . $ror_id;
    
    $response = file_get_contents($url);
    if (!$response) {
        echo "Erreur lors de la récupération de l'API ROR.";
        return false;
    }
    
    $json = json_decode($response, true);
    if (isset($json['addresses'][0])) {
        $adresse = $json['addresses'][0];
        $cp = isset($adresse['postal_code']) ? (int)$adresse['postal_code'] : null;
        $rue = isset($adresse['street']) ? $adresse['street'] : null;
        $nom_ville = isset($adresse['city']) ? $adresse['city'] : null;
        
        $sql = "INSERT INTO AnalyseGeo._adresses (cp, rue, nom_ville)
                VALUES (:cp, :rue, :nom_ville);";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':cp', $cp, PDO::PARAM_INT);
        $stmt->bindParam(':rue', $rue, PDO::PARAM_STR);
        $stmt->bindParam(':nom_ville', $nom_ville, PDO::PARAM_STR);
        $stmt->execute();
        
        echo "Adresse insérée pour le ROR $ror.";
        return true;
    }
    
    echo "Aucune adresse trouvée pour le ROR $ror.";
    return false;
}

?>