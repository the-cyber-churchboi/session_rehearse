<?php
require_once "config.php";

// Function to encrypt a message
function encryptMessage($message, $encryptionKey) {
    $method = 'aes-256-cbc';
    $ivSize = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($ivSize);
    $ciphertext = openssl_encrypt($message, $method, $encryptionKey, 0, $iv);
    return base64_encode($iv . $ciphertext);
}

// Check if the request method is post
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the admin id and user unique identifier are provided
    if (isset($_POST["admin_unique_id"]) && isset($_POST["user_unique_id"])) {
        $adminUniqueIdentifier = $_POST['admin_unique_id'];
        $userId = $_POST["user_unique_id"];
        
        // Check if a file has been uploaded
        if (isset($_FILES["file"])) {
            $file = $_FILES["file"];
            
            // Check if there are no errors in the uploaded file
            if ($file["error"] === UPLOAD_ERR_OK) {
                // Process the uploaded file here
                $upload_dir = "uploads/";

                // Generate a unique filename for the uploaded file (you can customize this part)
                $file_name = $file["name"];
                $uniqueFilename = time() . '_' . $file_name;
                $uploadDirectory = $upload_dir . $uniqueFilename;

                // Move the uploaded file to the desired location
                if (move_uploaded_file($file["tmp_name"], $uploadDirectory)) {
                    // File has been successfully uploaded, you can store the file path in the database or perform other actions
                }
            }
        }
        
        $message = isset($_POST["message"]) ? $_POST["message"] : "";
        
        if (!empty($message) || !empty($uploadDirectory)) {
            // Encrypt the message
            $encryptionKey = "061da93cf44eac5f4f00f39c5933dfb0fbee4c08a93785a8b951a23719b7c0bf"; // Replace with your encryption key
            $encryptedMessage = encryptMessage($message, $encryptionKey);
        
            // Insert the encrypted message with file attachment into the messages table
            $sql = "INSERT INTO admin_messages (receiver_id, sender_id, message, file_path) VALUES (:admin_unique_id, :user_unique_id, :message, :file_path)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'user_unique_id' => $userId,
                'admin_unique_id' => $adminUniqueIdentifier,
                'message' => $encryptedMessage, // Store the encrypted message in the database
                'file_path' => $uploadDirectory,
            ]);
            
            // Insert a new chat notification into the notifications table
            $notificationMessage = "You have a new chat notification from admin.";
            $sql2 = "INSERT INTO notifications (user_id, message, read_status) VALUES (:admin_unique_id, :notification_message, 0)";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute([
                'admin_unique_id' => $adminUniqueIdentifier,
                'notification_message' => $notificationMessage,
            ]);
            
            // Return a success response
            $response = array('status' => 'success', 'message' => 'Message sent successfully');
        } else {
            // No message or file provided, return an error response
            $response = array('status' => 'error', 'message' => 'No message or file provided');
        }

        // Send the response as JSON
        header("Content-Type: application/json");
        echo json_encode($response);
    }
}
?>
