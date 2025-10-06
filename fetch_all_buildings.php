<?php
require_once "config.php";

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Prepare and execute a PDO query to fetch the user's buildings
        $query = "SELECT property_id, property_name FROM manager_property_registration";
        $stmt = $pdo->prepare($query);
        $stmt->execute();

        // Fetch the results as an associative array
        $allBuildings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the JSON response
        header('Content-Type: application/json');
        echo json_encode($allBuildings);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    // Handle other types of requests (e.g., GET requests)
    http_response_code(405); // Method Not Allowed
}
?>
