<?php
require 'vendor/autoload.php';

$client = new MongoDB\Client("mongodb://root:example@mongodb:27017");
$databases = $client->listDatabases();

echo "<h2>MongoDB fonctionne ! Voici les bases de données :</h2>";
foreach ($databases as $db) {
    echo $db['name'] . "<br>";
}
?>