<?php

$server = 'db';
$dbname = 'mydatabase';
$user = 'user';
$pass = 'password';


 $req_revues = "SELECT * FROM analysegeo._revues";
 $req_conferences = "SELECT * FROM analysegeo._conferences";
 $req_a_ecrit = "SELECT * FROM analysegeo.a_ecrit";
 $req_auteur = "SELECT * FROM analysegeo._auteurs";
    
 try {
     $conn = new PDO("pgsql:host=$server;dbname=$dbname", $user, $pass);
     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Ajout pour gérer les erreurs proprement
     $conn->prepare("SET SCHEMA 'analysegeo';")->execute();
     
     // Exécuter les requêtes
     $stmt_revues = $conn->prepare($req_revues);
     $stmt_conferences = $conn->prepare($req_conferences);
     $stmt_a_ecrit = $conn->prepare($req_a_ecrit);
     $stmt_auteur = $conn->prepare($req_auteur);
 
     $stmt_revues->execute();
     $stmt_conferences->execute();
     $stmt_a_ecrit->execute();
     $stmt_auteur->execute();
 
     // Récupération des données sous forme de tableau associatif
     $revues = $stmt_revues->fetchAll(PDO::FETCH_ASSOC);
     $conferences = $stmt_conferences->fetchAll(PDO::FETCH_ASSOC);
     $a_ecrit = $stmt_a_ecrit->fetchAll(PDO::FETCH_ASSOC);
     $auteurs = $stmt_auteur->fetchAll(PDO::FETCH_ASSOC);

     $conn = null; 

     echo '<h1>Ludovic Liétard</h1>';

    }catch(PDOExeception $e){
        print "Erreur PDO : " . $e->getMessage() . "<br/>";
    }

    if (isset($_GET['nom'])) {
        $nom_auteur = urldecode($_GET['nom']); // Décodage pour afficher correctement
        echo "<h1>Détails de l'auteur : $nom_auteur</h1>";
        // Tu peux maintenant rechercher l'auteur dans la base et afficher plus d'infos
    } else {
        echo "<h1>Aucun auteur sélectionné.</h1>";
    }

    // Fonction pour obtenir les noms des auteurs d'une publication
    function getAuteursForPublication($id_dblp, $a_ecrit, $auteurs) {
        $noms_auteurs = [];

        foreach ($a_ecrit as $relation) {
            if ($relation['id_dblp'] == $id_dblp) {
                foreach ($auteurs as $auteur) {
                    if ($auteur['pid'] == $relation['pid']) {
                        // Encodage du nom pour éviter les problèmes d'URL
                        $nom_enc = urlencode($auteur['nom']);
                        // Création d'un lien cliquable vers une page auteur.php
                        $noms_auteurs[] = "<a href='auteur.php?nom={$nom_enc}'>{$auteur['nom']}</a>";
                    }
                }
            }
        }

        return implode(", ", $noms_auteurs); // Convertit en chaîne séparée par des virgules
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obThR9BMY="
     crossorigin=""/>

     <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>
    <title>Document</title>
    </head>
    <body>

    <table border="1">
        <thead>
            <tr>
                <th>
                    ID DLP
                </th>
                <th>
                    Type
                </th>
                <th>
                    DOI
                </th>
                <th>
                    Titre
                </th>
                <th>
                    Lieu
                </th>
                <th>
                    Année
                </th>
                <th>
                    Pages
                </th>
                <th>
                    EE
                </th>
                <th>
                    URL DBLP
                </th>
                <th>
                    Volume
                </th>
                <th>
                    Numero de Page
                </th>
                <th>
                    Auteur
                </th>
            </tr>
        </thead>
        <tbody> 
        <?php foreach($revues as $larevue){?>
            <tr>
                <td>
                    <?php echo $larevue['id_dblp']?>
                </td>
                <td>
                    <?php echo $larevue['type']?>
                </td>
                <td>
                    <?php echo $larevue['doi']?>
                </td>
                <td>
                    <?php echo $larevue['titre']?>
                </td>
                <td>
                    <?php echo $larevue['lieu']?>
                </td>
                <td>
                    <?php echo $larevue['annee']?>
                </td>
                <td>
                    <?php echo $larevue['pages']?>
                </td>
                <td>
                    <?php echo $larevue['ee']?>
                </td>
                <td>
                    <?php echo $larevue['url_dblp']?>
                </td>
                <td>
                    <?php echo $larevue['volume']?>
                </td>
                <td>
                    <?php echo $larevue['numero']?>
                </td>
                <td>
                    <?php echo getAuteursForPublication($larevue['id_dblp'], $a_ecrit, $auteurs);  ?>
                </td>
            <?php }
                
                ?>
                
            </tr>
            <?php  foreach($conferences as $laconference){?>
            <tr>
                <td>
                    <?php echo $laconference['id_dblp']?>
                </td>
                <td>
                    <?php echo $laconference['type']?>
                </td>
                <td>
                    <?php echo $laconference['doi']?>
                </td>
                <td>
                    <?php echo $laconference['titre']?>
                </td>
                <td>
                    <?php echo $laconference['lieu']?>
                </td>
                <td>
                    <?php echo $laconference['annee']?>
                </td>
                <td>
                    <?php echo $laconference['pages']?>
                </td>
                <td>
                    <?php echo $laconference['ee']?>
                </td>
                <td>
                    <?php echo $laconference['url_dblp']?>
                </td>
                <td>    
                </td> <!-- Pas de volume pour les conférences -->
                <td>
                </td> <!-- Pas de numéro pour les conférences -->
                <td>
                <?php echo getAuteursForPublication($laconference['id_dblp'], $a_ecrit, $auteurs); ?>
                    
                </td>
      
            </tr>
            <?php } ?>
            

        </tbody>


        </table>
    

    
</body>
</html>

