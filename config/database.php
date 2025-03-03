<?php
// Paramètres de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // À modifier selon votre configuration
define('DB_PASS', 'aaaa');    // À modifier selon votre configuration
define('DB_NAME', 'montres_boutique');

// Établissement de la connexion
try {
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    // Configuration pour afficher les erreurs PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>