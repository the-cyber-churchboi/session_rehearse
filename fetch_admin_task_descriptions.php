<?php
// fetch_admin_task_descriptions.php

// Include your database configuration
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming you're passing 'username', 'building', and 'title' in the request
    $selectedUser = $_POST['username'];
    $selectedBuilding = $_POST['building'];
    $selectedTitle = $_POST['title'];
    $adminId = $_POST["userId"];

    // Fetch description assigned to the admin for the selected user, building, and title
    $query = "SELECT task_description
              FROM tasks
              WHERE user_id = (SELECT unique_identifier FROM users WHERE username = :username)
                AND consultant_id = :admin_id
                AND property_id = :building
                AND task_title = :title
                AND task_status = 'Not Started'";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $selectedUser, PDO::PARAM_STR);
    $stmt->bindParam(':admin_id', $adminId, PDO::PARAM_STR);
    $stmt->bindParam(':building', $selectedBuilding, PDO::PARAM_STR);
    $stmt->bindParam(':title', $selectedTitle, PDO::PARAM_STR);
    $stmt->execute();
    $description = $stmt->fetchColumn();

    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($description);
} else {
    // Handle the request method not being POST
    http_response_code(405); // Method Not Allowed
    echo 'Method Not Allowed';
}
?>
