<?php
// Include your config.php file for database connection
include 'config.php';

// Check if the required parameter is set
if (isset($_GET['unique_id'])) {
    // Access the 'unique_id' property of the object
    $uniqueId = $_GET['unique_id'];

    try {
        $query = "SELECT property_id, apartment_type, other_details, image_path FROM propertyadvertisements WHERE property_id = :uniqueId";

        // Prepare the statement
        $statement = $pdo->prepare($query);

        // Bind parameters
        $statement->bindParam(':uniqueId', $uniqueId);

        // Execute the query
        $statement->execute();

        // Fetch building details and images
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Check if the result is empty
        if (empty($result)) {
            echo json_encode(array("error" => "No results found for unique_id: $uniqueId"));
        } else {
            // Return the result in JSON format
            echo json_encode($result);
        }
    } catch (PDOException $e) {
        // Handle PDO exceptions
        echo json_encode(array("error" => "PDO Exception: " . $e->getMessage()));
    } 
} else {
    // Handle the case where parameter is not set
    echo json_encode(array("error" => "Missing parameter"));
}

?>
