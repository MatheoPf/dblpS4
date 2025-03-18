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

    <table >
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
                    Tithe
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
                <th colspan = "2">
                    Auteur
                </th>
            </tr>
        </thead>
        <tbody> 
        <?php foreach($publi as $lapubli){?>
            <tr>
                <td>
                    <?php echo $lapubli['id_dblp']?>
                </td>
                <td>
                    <?php echo $lapubli['type']?>
                </td>
                <td>
                    <?php echo $lapubli['doi']?>
                </td>
                <td>
                    <?php echo $lapubli['titre']?>
                </td>
                <td>
                    <?php echo $lapubli['lieu']?>
                </td>
                <td>
                    <?php echo $lapubli['annee']?>
                </td>
                <td>
                    <?php echo $lapubli['page']?>
                </td>
                <td>
                    <?php echo $lapubli['ee']?>
                </td>
                <td>
                    <?php echo $lapubli['url_dblp']?>
                </td>
                <td>
                    <?php echo $lapubli['volume']?>
                </td>
                <td>
                    <?php echo $lapubli['numero']?>
                </td>
      
            </tr>
            <?php } ?>
            

        </tbody>


    </table
            
    

    
</body>
</html>

<?php
include('/home/etuinfo/capoupon/Téléchargements/connect_params.php');
 $reqPubli = "SELECT * FROM sae._publications";
    
 try {
     $conn = new PDO("pgsql:host=$server;dbname=$dbname", $user, $pass);
     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Ajout pour gérer les erreurs proprement
     $conn->prepare("SET SCHEMA 'analysegeo';")->execute();
     
     $stmtPubli= $conn->prepare($reqPubli);
     $stmtPubli->execute();
     
     $publications = $stmtPubli->fetchAll(PDO::FETCH_ASSOC); // Utilisation de fetchAll pour obtenir toutes les lignes
     
     $conn = null; 

     print_r($publications);
    }catch(PDOExeception $e){
        print "Erreur PDO : " . $e->getMessage() . "<br/>";
    }

?>


