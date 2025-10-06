<?php
// check_property_registration.php

require_once 'config.php'; // Adjust the path based on your actual file structure

// Get the property ID from the AJAX request
$propertyId = isset($_POST['propertyId']) ? $_POST['propertyId'] : null;

if ($propertyId !== null) {
    try {
        // Assuming $pdo is your PDO instance from config.php
        $query = $pdo->prepare("SELECT COUNT(*) AS count FROM manager_property_registration WHERE property_id = :propertyId");
        $query->bindParam(':propertyId', $propertyId, PDO::PARAM_INT);
        $query->execute();

        $result = $query->fetch(PDO::FETCH_ASSOC);

        // Send a JSON response indicating whether the property is registered
        echo json_encode(['registered' => $result['count'] > 0 ? 1 : 0]);
    } catch (PDOException $e) {
        // Handle database connection or query error
        echo json_encode(['error' => 'Database error']);
    }
} else {
    // Handle invalid or missing propertyId
    echo json_encode(['error' => 'Invalid property ID']);
}
?>
