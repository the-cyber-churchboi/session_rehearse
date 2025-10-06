<?php
session_name("admin_session");
session_start();
require_once('config.php');

if (!isset($_SESSION['admin_id']) && $_SESSION['dashboard'] == 'developer_dashboard') {
    // User is not logged in or not authorized for this dashboard
    header('Location: admin_login.php');
    exit();
}

$adminId = $_SESSION["admin_unique_id"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskId = isset($_POST['taskId']) ? $_POST['taskId'] : '';

    if (empty($taskId)) {
        // Handle invalid input or show an error message
        echo json_encode(['message' => 'Invalid task ID']);
        exit();
    }

    // Check if the task belongs to the logged-in admin
    $query = "SELECT * FROM tasks WHERE task_id = :taskId AND consultant_id = :adminId";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':taskId', $taskId, PDO::PARAM_INT);
    $stmt->bindParam(':adminId', $adminId, PDO::PARAM_STR);
    $stmt->execute();
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        // Task not found or doesn't belong to the logged-in admin
        echo json_encode(['message' => 'Task not found or unauthorized']);
        exit();
    }

    // Get the query_identifier value from the tasks table
    $queryIdentifier = $task['query_identifier'];

    // Start a transaction to ensure both updates succeed or fail together
    $pdo->beginTransaction();

    try {
        // Update the task status to 'Completed'
        $updateTaskQuery = "UPDATE tasks SET task_status = 'Completed' WHERE task_id = :taskId";
        $updateTaskStmt = $pdo->prepare($updateTaskQuery);
        $updateTaskStmt->bindParam(':taskId', $taskId, PDO::PARAM_INT);
        $updateTaskStmt->execute();

        // Update the status column in the 'queries' table to 'Close' where query_id matches query_identifier
        $updateQueriesQuery = "UPDATE queries SET status = 'closed', closed_at = NOW()  WHERE query_id = :queryIdentifier";
        $updateQueriesStmt = $pdo->prepare($updateQueriesQuery);
        $updateQueriesStmt->bindParam(':queryIdentifier', $queryIdentifier, PDO::PARAM_STR);
        $updateQueriesStmt->execute();

        // Commit the transaction if both updates were successful
        $pdo->commit();
        
        echo json_encode(['message' => 'Task completed successfully']);
    } catch (Exception $e) {
        // Rollback the transaction if an error occurred
        $pdo->rollBack();
        echo json_encode(['message' => 'Error completing the task']);
    }
} else {
    // Invalid request method
    echo json_encode(['message' => 'Invalid request method']);
}
?>
