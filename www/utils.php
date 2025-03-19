<?php
// Configuration de la connexion
define('DB_HOST', 'db');
define('DB_NAME', 'mydatabase');
define('DB_USER', 'user');
define('DB_PASS', 'password');

/**
 * Connexion à la base de données PostgreSQL
 */
function getDBConnection() {
    try {
        $pdo = new PDO("pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }
}

/**
 * Récupère les informations d'un auteur
 */
function getAuteur($pdo, $pid) {
    $query = "SELECT nom, hal_id FROM AnalyseGeo._auteurs WHERE pid = :pid";
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
    $query = "SELECT p.id_dblp, p.titre, p.annee 
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
 * Récupère les auteurs DBLP qui n'ont pas encore de hal_id.
 */
function getDblpAuthorsWithoutHalId($pdo) {
    $stmt = $pdo->query("SELECT pid, nom FROM AnalyseGeo._auteurs WHERE hal_id IS NULL");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Recherche un auteur sur HAL à partir de son nom.
 */
function searchAuthorOnHal($nom) {
    $url = "https://api.archives-ouvertes.fr/search/?q=" . urlencode($nom) . "&fl=authIdHal_s&rows=1&wt=json";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (!empty($data['response']['docs'])) {
        return $data['response']['docs'][0]['authIdHal_s'] ?? null;
    }
    return null;
}
?>