<?php
$host = "db";
$dbname = "mydatabase";
$user = "user";
$password = "password";

/**
 * Connexion à la base de données PostgreSQL
 */
try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
