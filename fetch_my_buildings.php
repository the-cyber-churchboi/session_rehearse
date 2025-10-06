<?php
require_once "config.php";

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $userId = $_POST['userId']; // Get the user ID from the POST data

        // Prepare and execute a PDO query to fetch the user's buildings
        $query = "SELECT property_id, property_name FROM user_property_registration WHERE user_id = :userId";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();

        // Fetch the results as an associative array
        $myBuildings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the JSON response
        header('Content-Type: application/json');
        echo json_encode($myBuildings);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    // Handle other types of requests (e.g., GET requests)
    http_response_code(405); // Method Not Allowed
}
?>
