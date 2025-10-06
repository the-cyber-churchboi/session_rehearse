<?php
require_once "config.php";

$adminUniqueIdentifier = $_POST['admin_unique_id'];
$userUniqueIdentifier = $_POST['user_unique_id'];

// Execute a SQL query to delete chat messages
$sql = "DELETE FROM messages WHERE 
        (sender_id = :admin_unique_id AND receiver_id = :user_unique_id) 
        OR 
        (sender_id = :user_unique_id AND receiver_id = :admin_unique_id)";

// Prepare the SQL statement
$stmt = $pdo->prepare($sql);

// Bind parameters
$stmt->bindParam(':admin_unique_id', $adminUniqueIdentifier, PDO::PARAM_STR);
$stmt->bindParam(':user_unique_id', $userUniqueIdentifier, PDO::PARAM_STR);

// Execute the query
if ($stmt->execute()) {
    // Chat messages have been deleted successfully
    $response = [
        'success' => true,
        'message' => 'Chat has been successfully ended, and chat messages have been deleted.'
    ];
} else {
    // Error occurred while deleting chat messages
    $response = [
        'success' => false,
        'message' => 'Failed to end chat or delete chat messages.'
    ];
}

// Send JSON response back to the client
echo json_encode($response);
?>