<?php
// Include your config.php file for database connection
require_once 'config.php';

// Check if the required parameters are set
if (isset($_GET['district']) && isset($_GET['district_options'])) {
    $district = $_GET['district'];
    $districtOptions = $_GET['district_options'];

    try {
        $query = "SELECT unique_id FROM property_registration WHERE district = :district AND district_options = :districtOptions";

        // Prepare the statement
        $statement = $pdo->prepare($query);

        // Bind parameters
        $statement->bindParam(':district', $district);
        $statement->bindParam(':districtOptions', $districtOptions);

        // Execute the query
        $statement->execute();

        // Fetch unique_id values
        $uniqueIds = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Return the unique_id values in JSON format
        echo json_encode($uniqueIds);
    } catch (PDOException $e) {
        // Handle PDO exceptions
        // Ensure only JSON is echoed in case of an exception
        echo json_encode(array("error" => $e->getMessage()));
    }
} else {
    // Handle the case where parameters are not set
    echo json_encode(array("error" => "Missing parameters"));
}
?>
