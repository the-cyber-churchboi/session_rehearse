<?php
// Include your database configuration
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["userId"]) && isset($_POST["title"]) && isset($_POST["description"]) && isset($_POST["building"])) {
        try {
            $userId = $_POST['userId'];
            $queryTitle = $_POST['title'];
            $queryDescription = $_POST['description'];
            $building = $_POST['building'];
            $propertyId = $_POST['propertyId'];

            // Insert the new query into the database
            $stmt = $pdo->prepare("INSERT INTO queries (user_id, title, description, building, property_id) VALUES (:user_id, :title, :description, :building, :property_id)");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':title', $queryTitle, PDO::PARAM_STR);
            $stmt->bindParam(':description', $queryDescription, PDO::PARAM_STR);
            $stmt->bindParam(':building', $building, PDO::PARAM_STR);
            $stmt->bindParam(':property_id', $propertyId, PDO::PARAM_STR);
            $stmt->execute();

            $response = ['success' => true];
        } catch (PDOException $e) {
            $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }

        echo json_encode($response);
    } else {
        header("HTTP/1.0 400 Bad Request");
        echo json_encode(['success' => false, 'message' => 'Bad Request']);
    }
}
?>
