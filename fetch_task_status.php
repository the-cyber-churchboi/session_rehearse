<?php
// Include your database configuration
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["userId"])) {
        try {
            $userId = $_POST['userId'];

            // Fetch task status for the user's apartment, including assigned person's information
            $stmt = $pdo->prepare("SELECT t.task_id, t.task_title, t.task_description, t.task_status, t.building, c.full_name AS assigned_person, t.created_at, t.updated_at, c.profession
                FROM tasks t
                LEFT JOIN admin_registration c ON t.consultant_id = c.unique_identifier
                WHERE t.user_id = :user_id");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Send the fetched data as JSON response
            echo json_encode($tasks);
        } catch (PDOException $e) {
            // Handle any database errors
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        header("HTTP/1.0 400 Bad Request");
        echo json_encode(['error' => 'Bad Request']);
    }
} else {
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode(['error' => 'Method Not Allowed']);
}
?>