<?php
// Include your database connection code (config.php or similar)
require_once "config.php";

// Check if the userUniqueIdentifier parameter is provided in the POST request
if (isset($_POST['userUniqueIdentifier'])) {
    $userUniqueIdentifier = 20;

    // Query the database to fetch the user's username based on the unique identifier
    $query = "SELECT username FROM users WHERE id = :userUniqueIdentifier";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':userUniqueIdentifier', $userUniqueIdentifier);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Return the username as a JSON response
        echo json_encode(['success' => true, 'username' => $result['username']]);
    } else {
        // User not found or other error
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
} else {
    // Invalid request
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
