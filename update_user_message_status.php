<?php
session_name("user_session");
session_start();
require_once "config.php";

// Check if the required parameters are present
if (!isset($_POST["user_unique_id"])) {
    header("Content-Type: application/json");
    echo json_encode(["success" => false, "error" => "Invalid request"]);
    exit;
}

try {
    $userUniqueId = $_POST["user_unique_id"];

    // Update the message status to 'delivered' for messages sent by the admin to the user
    $sql = "UPDATE messages SET message_status = 'delivered' WHERE receiver_id = :userUniqueId AND message_status = 'sent'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":userUniqueId", $userUniqueId, PDO::PARAM_INT);
    $stmt->execute();

    $response = [
        "success" => true
    ];
} catch (PDOException $e) {
    $response = [
        "success" => false,
        "error" => "Database error: " . $e->getMessage()
    ];
}

header("Content-Type: application/json");
echo json_encode($response);
?>
