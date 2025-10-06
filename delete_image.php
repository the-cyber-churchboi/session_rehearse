<?php
// Include your database configuration
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["userId"]) && isset($_POST["imageUrlToDelete"])) {
        try {
            $userId = $_POST['userId'];
            $imageUrlToDelete = $_POST['imageUrlToDelete'];

            // Check if the user has a customization record
            $stmt = $pdo->prepare("SELECT image_urls FROM usercustomization WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $existingImageUrls = $stmt->fetchColumn();

            if ($existingImageUrls !== false) {
                // Decode the existing JSON-encoded array of image URLs
                $existingImageUrlsArray = json_decode($existingImageUrls, true);

                // Find and remove the specified image URL from the array
                $indexToDelete = array_search($imageUrlToDelete, $existingImageUrlsArray);
                if ($indexToDelete !== false) {
                    unset($existingImageUrlsArray[$indexToDelete]);

                    // Encode the updated array as JSON
                    $updatedImageUrls = json_encode(array_values($existingImageUrlsArray));

                    // Update the record with the updated image URLs
                    $updateStmt = $pdo->prepare("UPDATE usercustomization SET image_urls = :image_urls WHERE user_id = :user_id");
                    $updateStmt->bindParam(':image_urls', $updatedImageUrls, PDO::PARAM_STR);
                    $updateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                    $updateStmt->execute();

                    // Delete the physical image file from the server
                    if (file_exists($imageUrlToDelete)) {
                        unlink($imageUrlToDelete);
                    }

                    $response = ['success' => true];
                } else {
                    $response = ['success' => false, 'message' => 'Image URL not found in user customization.'];
                }
            } else {
                $response = ['success' => false, 'message' => 'User customization record not found.'];
            }
        } catch (PDOException $e) {
            $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }

        echo json_encode($response);
    } else {
        header("HTTP/1.0 405 Method Not Allowed");
        echo "Method Not Allowed";
    }
}
?>
