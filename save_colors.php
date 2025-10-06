<?php
// Include your database configuration
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["userId"]) && isset($_POST["wallColor"]) && isset($_POST["furnitureColor"]) && isset($_POST["building"])) {
        try {
            $userId = $_POST['userId'];
            $selectedWallColor = $_POST['wallColor'];
            $selectedFurnitureColor = $_POST['furnitureColor'];
            $building = $_POST["building"];
            $propertyId = $_POST['propertyId'];

            // Check if the user already has a customization record
            $stmt = $pdo->prepare("SELECT id FROM usercustomization WHERE user_id = :user_id AND property_id = :property_id");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':property_id', $propertyId, PDO::PARAM_STR);
            $stmt->execute();
            $existingRecord = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingRecord) {
                // Update the existing record with the selected colors
                $updateStmt = $pdo->prepare("UPDATE usercustomization SET wall_color = :wall_color, furniture_color = :furniture_color WHERE id = :id");
                $updateStmt->bindParam(':wall_color', $selectedWallColor, PDO::PARAM_STR);
                $updateStmt->bindParam(':furniture_color', $selectedFurnitureColor, PDO::PARAM_STR);
                $updateStmt->bindParam(':id', $existingRecord['id'], PDO::PARAM_INT);
                $updateStmt->execute();
            } else {
                // Create a new record with the selected colors
                $insertStmt = $pdo->prepare("INSERT INTO UserCustomization (user_id, wall_color, furniture_color, building, property_id) VALUES (:user_id, :wall_color, :furniture_color, :building, :property_id)");
                $insertStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $insertStmt->bindParam(':wall_color', $selectedWallColor, PDO::PARAM_STR);
                $insertStmt->bindParam(':furniture_color', $selectedFurnitureColor, PDO::PARAM_STR);
                $insertStmt->bindParam(':building', $building, PDO::PARAM_STR);
                $insertStmt->bindParam(':property_id', $propertyId, PDO::PARAM_STR);
                $insertStmt->execute();
            }

            $response = ['success' => true];
        } catch (PDOException $e) {
            $response = ['success' => false];
        }

        echo json_encode($response);
    } else {
        header("HTTP/1.0 405 Method Not Allowed");
        echo "Method Not Allowed";
    }
}
?>
