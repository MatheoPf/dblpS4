<?php 

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

        $type = $publi['info']['type'];
        echo "Type : " . $type . "<br><br>";

        
        $doi = $publi['info']['doi']; 
        echo "DOI : " . $doi . "<br><br>";

        
        $titre = $publi['info']['title'];
        echo "Titre : " . $titre . "<br><br>";

        
        $lieu = $publi['info']['venue'];
        echo "Lieu : " . $lieu . "<br><br>";

       
        $annee = $publi['info']['year'];
        echo "Année : " . $annee. "<br><br>";

        
        $pages = $publi['info']['pages'];
        echo "Pages : " . $pages . "<br><br>";

        
        $ee = $publi['info']['ee'];
        echo "EE : " . $ee . "<br><br>";

        
        $url_dblp = $publi['info']['url'];
        echo "URL DBLP : " . $url_dblp . "<br><br>";

        if ($publi['info']['type'] == "Journal Articles"){
            $volume = $publi['info']['volume'];
            echo "Volume : " . $volume . "<br><br>";

            $numero_page = $publi['info']['number'];

            if(isset($numero_page)){
                echo "Numero de page : " . $numero_page . "<br><br>";
            }

           
            
        }

        
        foreach ($publi['info']['authors']['author'] as $auteur) {
            $auteur_pid = $auteur["@pid"];
            echo "Auteur PID : ". $auteur_pid. "<br>";
            
            $auteur_nom = $auteur["text"];
            echo "Auteur Nom : ". $auteur_nom. "<br><br>";
        }
        echo "<br>";
        
    }
        
        
}


?>




require_once('../php/connect_params.php');
        $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
        $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $dbh->prepare("SET SCHEMA 'sae';")->execute();
        try{
            switch ($_POST['type-compte']) {
                case 'membre':
                    $pseudo = $_POST['pseudo'];
                    if ($name === '') $name = null;
                    if ($first_name === '') $first_name = null;
                    if ($tel === '') $tel = null;
                    $query = "INSERT INTO sae.compte_membre (nom_compte, prenom, email, tel, mot_de_passe, pseudo) VALUES (?, ?, ?, ?, ?, ?) RETURNING id_compte;";
                    $stmt = $dbh->prepare($query);
                    $stmt->execute([$name, $first_name, $email, $tel, $password_hash, $pseudo]);
                    $_SESSION['id'] = $stmt->fetch()['id_compte'];
