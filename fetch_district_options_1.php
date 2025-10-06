<?php
require_once 'config.php';

$selectedDistrict = $_GET['district'];

try {
    $stmt = $pdo->prepare("SELECT DISTINCT district_options FROM manager_property_registration WHERE district = :district");
    $stmt->bindParam(':district', $selectedDistrict);
    $stmt->execute();
    $options = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($options);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
