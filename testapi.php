<?php 

include('/home/etuinfo/capoupon/Téléchargements/connect_params.php');
try {
    $dbh = new PDO("pgsql:host=$server;dbname=$dbname", 
            $user, $pass);
    
    $dbh->prepare("SET SCHEMA 'analysegeo';")->execute();
    
} catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
}

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
        }else {
            $doi = null;
        }
        
        if(isset($publi['info']['title'])){
            $titre = $publi['info']['title'];
            echo "Titre : " . $titre . "<br><br>";
        }else {
            $titre = null;
        }
       

        if (isset($publi['info']['venue'])) {
            $lieu = $publi['info']['venue'];
            echo "Lieu : " . $lieu . "<br><br>";
        }else{
            $lieu = null;
        }
        

        if (isset($publi['info']['year'])) {
            $annee = $publi['info']['year'];
            echo "Année : " . $annee. "<br><br>";
        }else {
            $annee = null;
        }
        
        if (isset($publi['info']['pages'])) {
            $pages = $publi['info']['pages'];
            echo "Pages : " . $pages . "<br><br>";
        }else {
            $pages = null;
        }
        
        
        if(isset($publi['info']['ee'])){
            $ee = $publi['info']['ee'];
            echo "EE : " . $ee . "<br><br>";
        }else {
            $ee = null;
        }
        
        if(isset($publi['info']['url'])){
            $url_dblp = $publi['info']['url'];
            echo "URL DBLP : " . $url_dblp . "<br><br>";
        }else {
            $url_dblp = null;
        }
        
        if ($publi['info']['type'] == "Journal Articles"){

            if(isset($publi['info']['volume'])){
                $volume = $publi['info']['volume'];
                echo "Volume : " . $volume . "<br><br>";
            }else{
                $volume = null;
            }
            
            if(isset($publi['info']['number'])){
                $numero_page = $publi['info']['number'];
                echo "Numero de page : " . $numero_page . "<br><br>";
            }  else {
                $numero_page = null;
            } 
        }

        try {
            switch ($type) {
                case 'Journal Articles':
                    $query = "INSERT INTO AnalyseGeo._revues(id_dblp, type, doi, titre, lieu, annee, pages, ee, url_dblp, volume, numero) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt_publi = $dbh->prepare($query);
                    $stmt_publi->execute([$id_dblp, $type, $doi, $titre, $lieu, $annee, $pages, $ee, $url_dblp, $volume, $numero_page]);
                    break;
            
                case 'Conference and Workshop Papers' :
                    $query = "INSERT INTO AnalyseGeo._conferences(id_dblp, type, doi, titre, lieu, annee, pages, ee, url_dblp) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt_publi = $dbh->prepare($query);
                    $stmt_publi->execute([$id_dblp, $type, $doi, $titre, $lieu, $annee, $pages, $ee, $url_dblp]);
                    break;
                
                default:
                    echo "probleme switch revues ou conference";
                    break;
            }
        } catch (PDOException $e) {
            print "Erreur PDO : " . $e->getMessage() . "<br/>";
        }


        
        $ordre_auteur = 0;
        foreach ($publi['info']['authors']['author'] as $auteur) {
            $ordre_auteur += 1;
            $auteur_pid = $auteur['@pid'];
            echo "Auteur PID : ". $auteur_pid . "<br>";
            
            $auteur_nom = $auteur["text"];
            echo "Auteur Nom : ". $auteur_nom . "<br><br>";
            

            // $auteur_orc_id = $auteur['orcid'];
            

            try {
                $query = "INSERT INTO AnalyseGeo._auteurs(pid, nom) VALUES (?,?) ON CONFLICT (pid) DO NOTHING";
                $stmt_auteur = $dbh->prepare($query);
                $stmt_auteur->execute([$auteur_pid, $auteur_nom]);
            } catch (PDOException $e) {
                print "Erreur PDO auteur: " . $e->getMessage() . "<br/>";
            }

            try {
                $query_a_ecrit = "INSERT INTO AnalyseGeo.a_ecrit(pid, id_dblp, ordre) VALUES (?,?,?);";
                $stmt_a_ecrit = $dbh->prepare($query_a_ecrit);
                $stmt_a_ecrit->execute([$auteur_pid, $id_dblp, $ordre_auteur]);
            } catch (PDOException $e) {
                print "Erreur PDO a ecrit: " . $e->getMessage() . "<br/>";
            }
        }
        echo "<br>";
        
    }
        
        
}






$dbh = null;

?>




        
