<?php
// Include your database configuration
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["userId"]) && isset($_FILES["images"]) && isset($_POST["building"])) {
        try {
            $userId = $_POST['userId'];
            $building = $_POST["building"];
            $propertyId = $_POST['propertyId'];

            // Specify the directory where you want to save the uploaded images
            $uploadDir = 'user_uploads/';

            // Create the directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Loop through the uploaded images
            $uploadedImagePaths = [];
            foreach ($_FILES["images"]["error"] as $key => $error) {
                if ($error == UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES["images"]["tmp_name"][$key];
                    $name = basename($_FILES["images"]["name"][$key]);
                    $targetPath = $uploadDir . $name;

                    // Move the uploaded file to the specified directory
                    move_uploaded_file($tmp_name, $targetPath);

                    // Check if the user already has a customization record
                    $stmt = $pdo->prepare("SELECT image_urls FROM usercustomization WHERE user_id = :user_id AND property_id = :property_id");
                    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                    $stmt->bindParam(':property_id', $propertyId, PDO::PARAM_STR);
                    $stmt->execute();
                    $existingImageUrls = $stmt->fetchColumn();

                    // Decode the existing JSON-encoded array of image URLs or create an empty array
                    $existingImageUrlsArray = json_decode($existingImageUrls, true) ?? [];

                    // Append the new image URL to the array
                    $existingImageUrlsArray[] = $targetPath;

                    // Encode the updated array as JSON
                    $updatedImageUrls = json_encode($existingImageUrlsArray);

                    // Update the record with the updated image URLs
                    $updateStmt = $pdo->prepare("UPDATE usercustomization SET image_urls = :image_urls WHERE user_id = :user_id AND property_id = :property_id");
                    $updateStmt->bindParam(':image_urls', $updatedImageUrls, PDO::PARAM_STR);
                    $updateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                    $updateStmt->bindParam(':property_id', $propertyId, PDO::PARAM_STR);
                    $updateStmt->execute();

                    // Save the file path in an array
                    $uploadedImagePaths[] = $targetPath;
                }
            }

            // You can now process or use the file paths in your database or perform other actions as needed

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
