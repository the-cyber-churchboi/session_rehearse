<?php
// Database configuration
$databaseHost = 'localhost';
$databaseName = 'building';
$databaseUsername = 'root';
$databasePassword = '';

// Establish the database connection using PDO
try {
    $pdo = new PDO("mysql:host=$databaseHost;dbname=$databaseName", $databaseUsername, $databasePassword);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If there is an error in the database connection, you can handle it here
    die("Connection failed: " . $e->getMessage());
}
?>
