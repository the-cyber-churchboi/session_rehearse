<?php
// Start the admin session
session_name("admin_session");
session_start();

require_once "config.php";

// Retrieve the admin's unique identifier from the session
$adminUniqueIdentifier = $_SESSION["admin_unique_id"]; // Replace with the actual session variable name you use

// Include your database configuration (config.php) here

// Use an SQL UPDATE statement to set the admin's status to "offline" in the database
$updateStatusStmt = $pdo->prepare("UPDATE admin_registration SET status = 'offline' WHERE unique_identifier = :uniqueIdentifier");
$updateStatusStmt->execute(['uniqueIdentifier' => $adminUniqueIdentifier]);

// Destroy the session data to log out the admin
session_destroy();

// Redirect the admin back to the login page
header("Location: admin_login.php");
exit();
?>
