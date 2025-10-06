<?php
// Start the session
session_name("user_session");
session_start();

require_once "config.php";

// Retrieve the user's unique identifier from the session
$userUniqueIdentifier = $_SESSION["user_unique_id"]; // Replace with the actual session variable name you use

// Include your database configuration (config.php) here

// Use an SQL UPDATE statement to set the user's status to "offline" in the database
$updateStatusStmt = $pdo->prepare("UPDATE users SET status = 'offline' WHERE unique_identifier = :uniqueIdentifier");
$updateStatusStmt->execute(['uniqueIdentifier' => $userUniqueIdentifier]);

// Destroy the session data to log out the user
session_destroy();

// Redirect the user back to the login page
header("Location: user_login.php");
exit();
?>
