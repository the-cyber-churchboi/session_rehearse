<?php
// Start the session
session_name("user_pass");
session_start();

// Include your database connection code or configuration here
require_once "config.php";

// Function to check if an email exists in the database
function emailExists($email) {
    global $pdo; // Assuming you have a PDO database connection

    $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $count = $stmt->fetchColumn();

    return $count > 0;
}

// Check if the email parameter is set in the POST request
if (isset($_POST['email'])) {
    $email = $_POST['email'];

    // Call the emailExists function to check if the email exists
    $exists = emailExists($email);

    // Return a JSON response indicating whether the email exists
    echo json_encode(['emailExists' => $exists]);
} else {
    // Return an error response if the email parameter is not set
    echo json_encode(['error' => 'Email parameter not provided']);
}
?>
