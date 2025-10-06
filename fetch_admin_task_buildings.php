<?php
// fetch_admin_task_buildings.php

// Include your database configuration
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming you're passing 'username' in the request
    $selectedUser = $_POST['username'];
    $adminId = $_POST["userId"];

    // Fetch buildings assigned to the admin for the selected user
    $query = "SELECT DISTINCT building, property_id
              FROM tasks
              WHERE user_id = (SELECT unique_identifier FROM users WHERE username = :username)
                AND consultant_id = :admin_id
                AND task_status = 'Not Started'";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $selectedUser, PDO::PARAM_STR);
    $stmt->bindParam(':admin_id', $adminId, PDO::PARAM_STR);
    $stmt->execute();
    $buildings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($buildings);
} else {
    // Handle the request method not being POST
    http_response_code(405); // Method Not Allowed
    echo 'Method Not Allowed';
}
?>
