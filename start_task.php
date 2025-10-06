<?php
session_name("admin_session");
session_start();

// Include your database configuration
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the POST parameters
    $username = $_POST['username'];
    $buildingName = $_POST['buildingName'];
    $title = $_POST['title'];
    $userId = $_POST['userId'];
    $description = $_POST["description"];
    $propertyId = $_POST["building"];

    // Check if the user is authorized to start the task (add more checks if needed)
    if (!isset($_SESSION['admin_id']) || $_SESSION['admin_unique_id'] !== $userId) {
        // Unauthorized access
        echo json_encode(['error' => 'Unauthorized access']);
        exit();
    }

    // Update the task status and set the updated_at column to the current time
    $query = "UPDATE tasks
              SET task_status = 'In Progress',
                  updated_at = NOW()
              WHERE consultant_id = :userId
                AND user_id = (SELECT unique_identifier FROM users WHERE username = :username)
                AND property_id = :property_id
                AND building = :building
                AND task_title = :title
                AND task_description = :description";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':property_id', $propertyId, PDO::PARAM_STR);
    $stmt->bindParam(':building', $buildingName, PDO::PARAM_STR);
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);

    $result = $stmt->execute();

    // Provide feedback
    $response = [];
    if ($result) {
        $response['message'] = '<p style="color: green;">Task assigned successfully.</p>';
    } else {
        $response['message'] = '<p style="color: red;">Error assigning task. Please try again.</p>';
    }

    echo json_encode($response);
}
?>
