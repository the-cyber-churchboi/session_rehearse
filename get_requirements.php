<?php
// Include your database configuration
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if userId is provided in the request
    if (isset($_POST['userId']) && isset($_POST['property_id'])) {
        try {
            $userID = $_POST["userId"];
            $building = $_POST['property_id'];
            // Check if the user has customization data
            $stmt = $pdo->prepare("SELECT * FROM usercustomization WHERE user_id = :user_id AND property_id = :property_id");
            $stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
            $stmt->bindParam(':property_id', $building, PDO::PARAM_STR);
            $stmt->execute();
            $customizationData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($customizationData) {
                // User has customization data; return it as JSON
                echo json_encode($customizationData);
            } else {
                // No customization data found; return an empty JSON object
                echo json_encode([]);
            }
        } catch (PDOException $e) {
            // Handle database errors here
            echo json_encode(['error' => 'Database error']);
        }
    } else {
        // Handle the case where the user is not logged in or the user ID is not available
        echo json_encode(['error' => 'User not authenticated']);
    }
}
?>