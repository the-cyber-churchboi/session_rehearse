<?php
require_once "config.php";

function encryptMessage($message, $encryptionKey) {
    $method = 'aes-256-cbc';
    $ivSize = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($ivSize);
    $ciphertext = openssl_encrypt($message, $method, $encryptionKey, 0, $iv);
    return base64_encode($iv . $ciphertext);
}


// Check if the request method is post
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the admin id and user unique ID are provided
    if (isset($_POST["admin_unique_id"]) && isset($_POST["user_unique_id"])) {
        // Get the admin unique identifier and user unique ID from the AJAX request
        $adminUniqueIdentifier = $_POST['admin_unique_id'];
        $userId = $_POST["user_unique_id"];

        // Initialize the file path
        $file_path = "";

        // Check if a file was uploaded
        if (isset($_FILES["file"]) && $_FILES["file"]["error"] == UPLOAD_ERR_OK) {
            // Define the upload directory on your server
            $upload_dir = "uploads/";

            // Create a unique file name to prevent overwriting
            $file_name = $_FILES["file"]["name"];
            $unique_file_name = time() . '_' . $file_name;
            $file_path = $upload_dir . $unique_file_name;

            // Move the uploaded file to the upload directory
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $file_path)) {
                // File was successfully uploaded, and its path can be stored in the database
            }
        }

        // Get the message, if it exists
        $message = isset($_POST["message"]) ? $_POST["message"] : "";

        // Check if either message or file_path is not empty before inserting into the messages table
        if (!empty($message) || !empty($file_path)) {

            // Encrypt the message
            $encryptionKey = "061da93cf44eac5f4f00f39c5933dfb0fbee4c08a93785a8b951a23719b7c0bf"; // Replace with your encryption key
            $encryptedMessage = encryptMessage($message, $encryptionKey);
         
            $sql = "INSERT INTO messages (receiver_id, sender_id, message, file_path) 
                    VALUES (:admin_unique_id, :user_unique_id, :message, :file_path)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'user_unique_id' => $userId,
                'admin_unique_id' => $adminUniqueIdentifier,
                'message' => $encryptedMessage,
                'file_path' => $file_path
            ]);
        }

        // Insert a new chat notification into the notifications table
        $notificationMessage = "You have a new chat notification from User.";
        $sql2 = "INSERT INTO notifications (user_id, message, read_status) VALUES (:admin_unique_id, :notification_message, 0)";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([
            'admin_unique_id' => $adminUniqueIdentifier,
            'notification_message' => $notificationMessage,
        ]);

        // Return a success response
        $response = array('status' => 'success', 'message' => 'Message sent successfully');
        echo json_encode($response);

        // Send the response as JSON
        header("Content-Type: application/json");
        exit; // Make sure to exit the script after sending the response
    }
}
?>
