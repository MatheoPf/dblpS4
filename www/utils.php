<?php
require_once "config.php";

/**
 * Récupère les informations d'un auteur.
 *
 * @param PDO    $pdo
 * @param string $pid Le PID de l'auteur.
 * @return array|null Les données de l'auteur ou null si non trouvé.
 */
function recupererAuteur(PDO $pdo, $pid) {
    $query = "SELECT DISTINCT * FROM analysegeo._auteurs WHERE pid = :pid";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Récupère les informations d'un auteur.
 *
 * @param PDO    $pdo
 * @return array|null La liste des auteurs ou null si non trouvé.
 */
function recupererToutAuteurs(PDO $pdo) {
    $query = "SELECT DISTINCT * FROM analysegeo._auteurs ORDER BY nom ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les structures affiliées à un auteur.
 * 
 * Mise à jour pour utiliser le PID (car la liaison se fait via la table _affiliation qui lie pid et id_struct).
 *
 * @param PDO    $pdo
 * @param string $pid Le PID de l'auteur.
 * @return array Liste des structures affiliées.
 */
function recupererStructuresAffiliees(PDO $pdo, $pid) {
    $query = "SELECT DISTINCT s.*
              FROM analysegeo._affiliation a
              JOIN analysegeo._structures s ON a.id_struct = s.id_struct
              WHERE a.pid = :pid";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les publications d'un auteur.
 *
 * @param PDO    $pdo
 * @param string $pid Le PID de l'auteur.
 * @return array Liste des publications.
 */
function recupererToutesPublications(PDO $pdo) {
    $query = "SELECT DISTINCT * FROM analysegeo._publications ORDER BY annee DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les publications d'un auteur.
 *
 * @param PDO    $pdo
 * @param string $pid Le PID de l'auteur.
 * @return array Liste des publications.
 */
function recupererPublicationsParAuteur(PDO $pdo, $pid) {
    $query = "SELECT DISTINCT * 
              FROM analysegeo.a_ecrit ae
              JOIN analysegeo._publications p ON ae.id_dblp = p.id_dblp
              WHERE ae.pid = :pid
              ORDER BY p.annee DESC";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère la liste des auteurs ayant écrit ou co-écrit une publication.
 *
 * @param PDO    $pdo      L'objet PDO.
 * @param string $id_dblp  L'identifiant de la publication.
 * @return array La liste des auteurs associés à cette publication.
 */
function recupererListeAuteurs(PDO $pdo, $id_dblp) {
    $query = "SELECT a.*
              FROM analysegeo.a_ecrit ae
              JOIN analysegeo._auteurs a ON ae.pid = a.pid
              WHERE ae.id_dblp = :id_dblp
              ORDER BY ae.ordre ASC";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_dblp', $id_dblp, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les informations d'une publication depuis la table _publications.
 *
 * @param PDO    $pdo      L'objet PDO.
 * @param string $id_dblp  L'identifiant de la publication (ex. "10.1007/xxx" ou autre identifiant DBLP).
 * @return array|null Les données de la publication ou null si non trouvée.
 */
function recupererUnePublication(PDO $pdo, $id_dblp) {
    $query = "SELECT DISTINCT * FROM analysegeo._publications WHERE id_dblp = :id_dblp";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_dblp', $id_dblp, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Récupère les informations des 5 dernières publications par ordre décroissant
 *
 * @param PDO $pdo    L'objet PDO pour la connexion à la base de données.
 * @param int $limite Le nombre maximum de publications à récupérer (par défaut 5).
 * @return array|null Liste des publications ou null si non trouvée.
 */
function recuperer5DernieresPublications(PDO $pdo, $limite = 5) {
    $query = "SELECT DISTINCT * FROM analysegeo._publications ORDER BY annee DESC LIMIT :limite";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les données d'une publication depuis l'API OpenAlex à partir d'un DOI.
 *
 * @param string $doi Le DOI de la publication.
 * @return array|null Tableau associatif des données ou null en cas d'erreur.
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
 * @param array $publication Tableau associatif de la publication.
 * @return array Liste d'auteurs avec leur nom.
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
    
    $options = array(
        'http' => array(
            'method'  => 'GET',
            'header'  => "User-Agent: MonApplication/1.0\r\n",
            'timeout' => 5
        )
    );
    $context  = stream_context_create($options);
    $reponse = @file_get_contents($url, false, $context);
    if ($reponse === FALSE) {
        sleep(1);
        return null;
    }
    $json = json_decode($reponse, true);
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
 * @return string|null L'ORCID (sans le préfixe "https://orcid.org/") ou null s'il n'est pas trouvé.
 */
function recupererOrcidDepuisDblp(PDO $pdo, $pid) {
    // Vérifier si l'ORCID est déjà en base
    $query = "SELECT DISTINCT orc_id FROM analysegeo._auteurs WHERE pid = :pid AND orc_id IS NOT NULL";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);
    $stmt->execute();
    $orcid = $stmt->fetchColumn();
    
    if ($orcid) {
        return $orcid; // Évite une requête inutile sur DBLP
    }

    $url = "https://dblp.org/pid/$pid.xml";
    
    $options = array(
        'http' => array(
            'method'  => 'GET',
            'header'  => "User-Agent: MonApplication/1.0\r\n",
            'timeout' => 5
        )
    );
    $context = stream_context_create($options);
    $xmlContent = @file_get_contents($url, false, $context);
    if ($xmlContent === FALSE) {
        sleep(1);
        return null;
    }
    $xml = simplexml_load_string($xmlContent);
    if (!$xml) {
        return null;
    }
    
    // Le XML retourné a pour racine <dblpperson> contenant un noeud <person>
    if (isset($xml->person)) {
        foreach ($xml->person->url as $urlBalise) {
            // On vérifie si l'URL contient "orcid.org"
            if (strpos($urlBalise, 'orcid.org') !== false) {
                // Retourne l'ORCID en retirant le préfixe "https://orcid.org/"
                return str_replace("https://orcid.org/", "", trim((string)$urlBalise));
            }
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
    $sql = "INSERT INTO analysegeo._auteurs (pid, orc_id, nom)
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
 * @param PDO    $pdo Connexion PDO.
 * @param string $pid Le PID de l'auteur.
 * @return bool True si l'auteur existe, sinon False.
 */
function auteurExiste(PDO $pdo, $pid) {
    $sql = "SELECT COUNT(*) FROM analysegeo._auteurs WHERE pid = :pid";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

/**
 * Ajoute un auteur dans la base de données s'il n'existe pas déjà.
 *
 * @param PDO    $pdo Connexion PDO.
 * @param string $nomAuteur Le nom complet de l'auteur.
 */
function ajouterAuteurSiNexistePas(PDO $pdo, $nomAuteur) {
    $pid = recupererPidDblp($nomAuteur);
    if (!$pid) {
        echo "Aucun PID trouvé pour l'auteur : $nomAuteur\n";
        return;
    }
    if (auteurExiste($pdo, $pid)) {
        echo "L'auteur '$nomAuteur' (PID: $pid) est déjà en base, aucune insertion.\n";
        return;
    }
    $orcid = recupererOrcidDepuisDblp($pdo, $pid);
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
    $idInstitutionComplet = $institution['id'];
    $idInstitution = str_replace("https://openalex.org/", "", $idInstitutionComplet);
    $nomInstitution = $institution['display_name'];
    $rorInstitution = isset($institution['ror']) ? $institution['ror'] : null;
    
    $sql = "INSERT INTO analysegeo._structures (id_struct, nom_struct, ror)
            VALUES (:id_struct, :nom_struct, :ror)
            ON CONFLICT (id_struct) DO UPDATE SET nom_struct = EXCLUDED.nom_struct, ror = EXCLUDED.ror";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_struct', $idInstitution, PDO::PARAM_STR);
    $stmt->bindParam(':nom_struct', $nomInstitution, PDO::PARAM_STR);
    $stmt->bindParam(':ror', $rorInstitution, PDO::PARAM_STR);
    $stmt->execute();
}

/**
 * Lie un auteur à une structure dans la table _affiliation.
 *
 * @param PDO    $pdo L'objet PDO.
 * @param string $pid Le PID de l'auteur.
 * @param string $idInstitution L'identifiant de l'institution (ex. "I2802519937").
 */
function lierAffiliationAuteur(PDO $pdo, $pid, $idInstitution) {
    $sql = "INSERT INTO analysegeo._affiliation (pid, id_struct)
            VALUES (:pid, :id_struct)
            ON CONFLICT (pid, id_struct) DO NOTHING";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);
    $stmt->bindParam(':id_struct', $idInstitution, PDO::PARAM_STR);
    $stmt->execute();
}

/**
 * Interroge l'API ROR pour récupérer les informations d'adresse d'une institution et
 * insère ou met à jour la ville dans la table _villes.
 *
 * Si une ville portant le même nom existe déjà avec des coordonnées valides
 * (latitude différente de 0 et nom_pays différent de "Inconnu"), retourne son ID.
 * Sinon, insère une nouvelle ville avec des valeurs par défaut pour latitude, longitude et iso.
 *
 * @param PDO    $pdo L'objet PDO.
 * @param string $ror L'URL ROR de l'institution (ex. "https://ror.org/00myn0z94").
 * @return int|null L'ID de la ville dans _villes, ou null en cas d'échec.
 */
function insererVilleROR(PDO $pdo, $ror) {
    // Extraire l'identifiant ROR
    $ror_id = str_replace("https://ror.org/", "", $ror);
    $url = "https://api.ror.org/organizations/" . $ror_id;

    // Utiliser cURL pour récupérer les données avec un timeout
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout de 5s
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // Timeout de connexion de 3s
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Vérifier si la requête a échoué
    if ($response === false || $httpCode !== 200) {
        echo "Erreur API ROR (Code HTTP: $httpCode, Erreur: $curlError)<br>";
        return null;
    }

    $json = json_decode($response, true);
    if (!isset($json['addresses'][0])) {
        echo "Aucune adresse trouvée pour le ROR $ror.<br>";
        return null;
    }

    $adresse = $json['addresses'][0];
    $nom_ville = isset($adresse['city']) ? $adresse['city'] : null;
    $pays = !empty($adresse['country']) ? $adresse['country'] : 'Inconnu';

    if (!$nom_ville) {
        echo "Aucune ville trouvée pour le ROR $ror.<br>";
        return null;
    }

    // Vérifier si la ville existe déjà
    $stmt = $pdo->prepare("SELECT id, latitude, nom_pays FROM analysegeo._villes WHERE nom_ville = :nom_ville");
    $stmt->bindParam(':nom_ville', $nom_ville, PDO::PARAM_STR);
    $stmt->execute();
    $villeExistante = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($villeExistante && floatval($villeExistante['latitude']) != 0 && trim($villeExistante['nom_pays']) !== "Inconnu") {
        return $villeExistante['id'];
    }

    if ($villeExistante) {
        // Mise à jour de la ville existante
        $updateStmt = $pdo->prepare("UPDATE analysegeo._villes SET nom_pays = :nom_pays WHERE id = :id");
        $updateStmt->bindParam(':nom_pays', $pays, PDO::PARAM_STR);
        $updateStmt->bindParam(':id', $villeExistante['id'], PDO::PARAM_INT);
        $updateStmt->execute();
        return $villeExistante['id'];
    }

    // Insertion d'une nouvelle ville
    $stmt = $pdo->prepare("INSERT INTO analysegeo._villes (nom_ville, latitude, longitude, iso, nom_pays)
                           VALUES (:nom_ville, 0.0, 0.0, 'XX', :nom_pays) RETURNING id");
    $stmt->bindParam(':nom_ville', $nom_ville, PDO::PARAM_STR);
    $stmt->bindParam(':nom_pays', $pays, PDO::PARAM_STR);
    $stmt->execute();
    
    return $stmt->fetchColumn();
}

/**
 * Récupère une structure depuis la table _structures.
 *
 * @param PDO    $pdo       L'objet PDO.
 * @param string $id_struct L'identifiant de la structure (ex. "I2802519937").
 * @return array|null Les données de la structure ou null si non trouvée.
 */
function recupererStructure(PDO $pdo, $id_struct) {
    $query = "SELECT DISTINCT * FROM analysegeo._structures WHERE id_struct = :id_struct";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_struct', $id_struct, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Récupère toutes les structures avec les informations de sa ville depuis la table _structures et _villes.
 *
 * @param PDO    $pdo       L'objet PDO.
 * @return array|null La liste des structures et les villes ou null si non trouvée.
 */
function recupererToutesStructure(PDO $pdo) {
    $query = "SELECT * FROM analysegeo._structures s join analysegeo._villes v on s.id_ville = v.id ORDER BY s.nom_struct ASC;";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les informations d'une ville depuis la table _villes.
 *
 * @param PDO $pdo L'objet PDO pour la connexion à la base de données.
 * @param int $id_ville L'ID de la ville.
 * @return array|null Les données de la ville ou null si non trouvées.
 */
function recupererVille(PDO $pdo, $id_ville) {
    $query = "SELECT DISTINCT * FROM analysegeo._villes WHERE id = :id_ville";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_ville', $id_ville, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Récupère les auteurs affiliés à une structure via la table _affiliation.
 *
 * @param PDO    $pdo       L'objet PDO.
 * @param string $id_struct L'identifiant de la structure.
 * @return array Liste des auteurs affiliés.
 */
function recupererAuteursAffiliesStructure(PDO $pdo, $id_struct) {
    $query = "SELECT DISTINCT a.*
              FROM analysegeo._affiliation af
              JOIN analysegeo._auteurs a ON af.pid = a.pid
              WHERE af.id_struct = :id_struct";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_struct', $id_struct, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les publications associées aux auteurs affiliés à une structure.
 *
 * Cette fonction retourne les publications des auteurs qui sont liés à la structure.
 *
 * @param PDO    $pdo       L'objet PDO.
 * @param string $id_struct L'identifiant de la structure.
 * @return array Liste des publications.
 */
function recupererPublicationsStructure(PDO $pdo, $id_struct) {
    $query = "SELECT DISTINCT p.*
              FROM analysegeo.a_ecrit ae
              JOIN analysegeo._publications p ON ae.id_dblp = p.id_dblp
              JOIN analysegeo._affiliation af ON ae.pid = af.pid
              WHERE af.id_struct = :id_struct
              ORDER BY p.annee DESC";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_struct', $id_struct, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Insère (ou met à jour) une publication dans la table _publications.
 *
 * @param PDO   $pdo                L'objet PDO.
 * @param array $publicationData    Tableau associatif contenant les champs de la publication :
 *                                  - id_dblp
 *                                  - type
 *                                  - doi
 *                                  - titre
 *                                  - lieu
 *                                  - annee
 *                                  - pages
 *                                  - ee
 *                                  - url_dblp
 */
function insererPublication(PDO $pdo, $publicationData) {
    $sql = "INSERT INTO analysegeo._publications 
                (id_dblp, type, doi, titre, lieu, annee, pages, ee, url_dblp)
            VALUES 
                (:id_dblp, :type, :doi, :titre, :lieu, :annee, :pages, :ee, :url_dblp)
            ON CONFLICT (id_dblp) DO UPDATE SET
                type     = EXCLUDED.type,
                doi      = EXCLUDED.doi,
                titre    = EXCLUDED.titre,
                lieu     = EXCLUDED.lieu,
                annee    = EXCLUDED.annee,
                pages    = EXCLUDED.pages,
                ee       = EXCLUDED.ee,
                url_dblp = EXCLUDED.url_dblp";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_dblp', $publicationData['id_dblp'], PDO::PARAM_STR);
    $stmt->bindParam(':type', $publicationData['type'], PDO::PARAM_STR);
    $stmt->bindParam(':doi', $publicationData['doi'], PDO::PARAM_STR);
    $stmt->bindParam(':titre', $publicationData['titre'], PDO::PARAM_STR);
    $stmt->bindParam(':lieu', $publicationData['lieu'], PDO::PARAM_STR);
    $stmt->bindParam(':annee', $publicationData['annee'], PDO::PARAM_INT);
    $stmt->bindParam(':pages', $publicationData['pages'], PDO::PARAM_STR);
    $stmt->bindParam(':ee', $publicationData['ee'], PDO::PARAM_STR);
    $stmt->bindParam(':url_dblp', $publicationData['url_dblp'], PDO::PARAM_STR);
    $stmt->execute();
}

/**
 * Récupère les auteurs ayant le plus de publications.
 *
 * Cette fonction retourne une liste des auteurs triés par nombre de publications décroissant.
 *
 * @param PDO $pdo L'objet PDO pour la connexion à la base de données.
 * @param int $limite Le nombre maximum d'auteurs à récupérer (par défaut 5).
 * @return array Liste des auteurs vedette avec leur nombre de publications.
 */
function recupererAuteursVedette(PDO $pdo, $limite = 5) {
    $sql = "SELECT DISTINCT a.pid, a.nom, COUNT(e.id_dblp) AS nb_publications 
            FROM analysegeo._auteurs a
            JOIN analysegeo.a_ecrit e ON a.pid = e.pid
            GROUP BY a.pid
            ORDER BY nb_publications DESC
            LIMIT :limite";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/**
 * Récupère le nombre de publications d'un auteur.
 *
 * Cette fonction retourne le nombre de publications associées à un auteur.
 *
 * @param PDO $pdo L'objet PDO pour la connexion à la base de données.
 * @param string $pid L'identifiant de l'auteur.
 * @return int Le nombre de publications de l'auteur.
 */
function recupererNbPublicationsAuteur(PDO $pdo, $pid) {
    $sql = "SELECT COUNT(*) 
            FROM analysegeo.a_ecrit 
            WHERE pid = :pid";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);
    $res = $stmt->execute();
    
    return $res;
}

/**
 * Récupère le nombre de structures affiliées d'un auteur.
 *
 * Cette fonction retourne le nombre de structures auxquelles un auteur est affilié.
 *
 * @param PDO $pdo L'objet PDO pour la connexion à la base de données.
 * @param string $pid L'identifiant de l'auteur.
 * @return int Le nombre de structures affiliées de l'auteur.
 */
function recupererNbStructuresAffiliesAuteur(PDO $pdo, $pid) {
    $sql = "SELECT COUNT(*) 
            FROM analysegeo._affiliation 
            WHERE pid = :pid";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':pid', $pid, PDO::PARAM_STR);
    $res = $stmt->execute();
    
    return $res;
}

/**
 * Affiche une carte interactive des structures affiliées d'un auteur.
 *
 * Cette fonction récupère les structures affiliées (ainsi que leurs coordonnées géographiques)
 * pour un auteur donné et retourne un code HTML intégrant une carte Leaflet qui affiche ces structures.
 *
 * @param PDO $pdo L'objet PDO pour la connexion à la base de données.
 * @param string $pid L'identifiant de l'auteur.
 * @return string Code HTML complet contenant la carte interactive.
 */
function afficherCarteStructuresAffilies(PDO $pdo) {
    // Requête pour récupérer les structures affiliées et leurs coordonnées
    $sql = "SELECT s.id_struct, s.nom_struct, v.latitude, v.longitude
            FROM analysegeo._affiliation a
            JOIN analysegeo._structures s ON a.id_struct = s.id_struct
            JOIN analysegeo._villes v ON s.id_ville = v.id;
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $structures = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Encodage des données en JSON pour utilisation dans la partie JavaScript
    return json_encode($structures);
}

?>