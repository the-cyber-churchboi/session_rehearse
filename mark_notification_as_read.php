<?php
// Include your database configuration
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["userId"])) {
        try {
            $userId = $_POST['userId'];

            // Update all notifications for the user as read (mark them with a read_status of 1)
            $stmt = $pdo->prepare("UPDATE notifications SET read_status = 1 WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Invalid request']);
    }
}
?>
