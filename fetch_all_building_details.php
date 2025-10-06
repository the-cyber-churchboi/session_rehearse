<?php
// Include your database configuration
require_once "config.php";

// Check if this is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $buildingId = $_POST['building_id'];

    // Prepare a SQL query to fetch building details
    $query = "SELECT district, street_name, district_options, property_name
              FROM property_registration
              WHERE  id = :buildingId";

    // Prepare and execute the query
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':buildingId', $buildingId, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the building details
    $buildingDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if building details were found
    if ($buildingDetails) {
        // Return building details as JSON response
        header('Content-Type: application/json');
        echo json_encode($buildingDetails);
    } else {
        // Building details not found
        http_response_code(404);
        echo json_encode(["message" => "Building details not found."]);
    }
} else {
    // Handle non-POST requests
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed."]);
}
?>
