<?php
session_name("admin_session");
session_start();
// Include your database configuration
require_once('config.php');

if (!isset($_SESSION['admin_id']) && $_SESSION['dashboard'] !== 'developer_dashboard') {
    // User is not logged in or not authorized for this dashboard
    header('Location: admin_login.php');
    exit();
}

// Extract data from the form
$user = $_POST['user'];
$building = $_POST['building'];
$propertyId = $_POST["property_id"];
$title = $_POST['title'];
$description = $_POST['description'];
$admin = $_POST['admin'];

// Fetch query_id based on user, building, and title
$query = "SELECT query_id FROM queries WHERE user_id = (SELECT unique_identifier FROM users WHERE username = :user) 
          AND property_id = :property_id AND title = :title";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user', $user, PDO::PARAM_STR);
$stmt->bindParam(':property_id', $propertyId, PDO::PARAM_STR);
$stmt->bindParam(':title', $title, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$queryId = $result['query_id'];

// Insert data into tasks table
$query = "INSERT INTO tasks (user_id, consultant_id, task_title, task_description, task_status, query_identifier, building, property_id)
          VALUES ((SELECT unique_identifier FROM users WHERE username = :user), 
                  :admin, :title, :description, 'Not Started', :query_id, :building, :property_id)";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user', $user, PDO::PARAM_STR);
$stmt->bindParam(':admin', $admin, PDO::PARAM_INT);
$stmt->bindParam(':title', $title, PDO::PARAM_STR);
$stmt->bindParam(':building', $building, PDO::PARAM_STR);
$stmt->bindParam(':property_id', $propertyId, PDO::PARAM_STR);
$stmt->bindParam(':description', $description, PDO::PARAM_STR);
$stmt->bindParam(':query_id', $queryId, PDO::PARAM_INT);

$result = $stmt->execute();

// Update the assigned_to column in the queries table
$updateQuery = "UPDATE queries SET assigned_to = :admin_name WHERE query_id = :query_id";
$updateStmt = $pdo->prepare($updateQuery);
$updateStmt->bindParam(':admin_name', $admin, PDO::PARAM_STR);
$updateStmt->bindParam(':query_id', $queryId, PDO::PARAM_INT);
$updateResult = $updateStmt->execute();

// Provide feedback
$response = [];
if ($result && $updateResult) {
    $response['message'] = '<p style="color: green;">Task assigned successfully.</p>';
} else {
    $response['message'] = '<p style="color: red;">Error assigning task. Please try again.</p>';
}

echo json_encode($response);
?>