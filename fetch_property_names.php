<?php
require_once 'config.php';

$selectedDistrict = $_GET['district'];
$selectedDistrictOptions = $_GET['district_options'];

try {
    $stmt = $pdo->prepare("SELECT property_id, property_name, street_name, address FROM manager_property_registration WHERE district = :district AND district_options = :options");
    $stmt->bindParam(':district', $selectedDistrict);
    $stmt->bindParam(':options', $selectedDistrictOptions);
    $stmt->execute();
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($properties);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
