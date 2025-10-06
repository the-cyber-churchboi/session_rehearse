<?php
require_once "config.php";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Query to fetch new admins and their availability (you might need to adjust this query)
$query = "SELECT id, first_name, last_name, unique_identifier, status, profession FROM admin_registration WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
$stmt = $pdo->prepare($query);
$stmt->execute();
$newAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($newAdmins);

// Close the database connection
$pdo = null;
?>