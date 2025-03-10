<?php 
/*
include('connect_params.php');
try {
    $dbh = new PDO("pgsql:host=$server;dbname=$dbname", 
            $user, $pass);
    
} catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
}
*/
stream_context_set_default([
    'http' => [
        'proxy' => '129.20.239.11:3128'
    ] 
]);

$pays = file_get_contents('https://dblp.org/search/publ/api?q=author%3ALudovic_Li%C3%A9tard%3A&format=json');
$pays_decode=json_decode($pays, true);
echo '<pre>';
print_r($pays_decode);
echo '</pre>';


foreach ($pays_decode['result']['hits']['hit'] as $publi) {
    if ($publi['info']['type'] == "Journal Articles" ||$publi['info']['type'] == "Conference and Workshop Papers") {
        
        $id_dblp = $publi['@id'];
        echo "ID DBLP : " . $id_dblp . "<br><br>";

        if (isset($publi['info']['type'])) {
            $type = $publi['info']['type'];
            echo "Type : " . $type . "<br><br>";       
        }
        
        if ($publi['info']['doi']) {
            $doi = $publi['info']['doi']; 
            echo "DOI : " . $doi . "<br><br>";
        }
        
        if(isset($publi['info']['title'])){
            $titre = $publi['info']['title'];
            echo "Titre : " . $titre . "<br><br>";
        }
       

        if (isset($publi['info']['venue'])) {
            $lieu = $publi['info']['venue'];
            echo "Lieu : " . $lieu . "<br><br>";
        }
        

        if (isset($publi['info']['year'])) {
            $annee = $publi['info']['year'];
            echo "Année : " . $annee. "<br><br>";
        }
        
        if (isset($publi['info']['pages'])) {
            $pages = $publi['info']['pages'];
            echo "Pages : " . $pages . "<br><br>";
        }
        
        
        if(isset($publi['info']['ee'])){
            $ee = $publi['info']['ee'];
            echo "EE : " . $ee . "<br><br>";
        }
        
        if(isset($publi['info']['url'])){
            $url_dblp = $publi['info']['url'];
            echo "URL DBLP : " . $url_dblp . "<br><br>";
        }
        
        if ($publi['info']['type'] == "Journal Articles"){

            if(isset($publi['info']['volume'])){
                $volume = $publi['info']['volume'];
                echo "Volume : " . $volume . "<br><br>";
            }
            
            if(isset($publi['info']['number'])){
                $numero_page = $publi['info']['number'];
                echo "Numero de page : " . $numero_page . "<br><br>";
            }   
        }
        
        foreach ($publi['info']['authors']['author'] as $auteur) {
            $auteur_pid = $auteur["@pid"];
            echo "Auteur PID : ". $auteur_pid. "<br>";
            
            $auteur_nom = $auteur["text"];
            echo "Auteur Nom : ". $auteur_nom. "<br><br>";

            // $auteur_orc_id = $auteur['orcid'];
            /*
            try {
                $query = "INSERT INTO AnalyseGeo._auteurs(pid, nom) VALUES (?,?)";
                $stmt = $dbh->prepare($query);
                $stmt->execute([$auteur_pid, $auteur_nom]);
            } catch (PDOException $e) {
                print "Erreur PDO auteur: " . $e->getMessage() . "<br/>";
            }
            */
        }
        echo "<br>";
        
    }
        
        
}


try {
    switch ($type) {
        case 'Journal Articles':
            $query = "INSERT INTO AnalyseGeo._revues(id_dblp, type, doi, titre, lieu, annee, pages, ee, url_dblp, volume, numero) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $dbh->prepare($query);
            $stmt->execute([$id_dblp, $type, $doi, $titre, $lieu, $annee, $pages, $ee, $url_dblp, $volume, $numero]);
            break;
    
        case 'Conference and Workshop Papers' :
            $query = "INSERT INTO AnalyseGeo._conferences(id_dblp, type, doi, titre, lieu, annee, pages, ee, url_dblp) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $dbh->prepare($query);
            $stmt->execute([$id_dblp, $type, $doi, $titre, $lieu, $annee, $pages, $ee, $url_dblp]);
            break;
        
        default:
            echo "probleme switch revues ou conference";
            break;
    }
} catch (PDOException $e) {
    print "Erreur PDO : " . $e->getMessage() . "<br/>";
}

$dbh = null;

?>






require_once('../php/connect_params.php');
        $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
        $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $dbh->prepare("SET SCHEMA 'sae';")->execute();
        
