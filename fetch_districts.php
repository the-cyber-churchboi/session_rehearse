<?php
require_once 'config.php';

try {
    $stmt = $pdo->prepare("SELECT DISTINCT district FROM property_registration WHERE property_name IS NULL");
    $stmt->execute();
    $districts = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($districts);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
