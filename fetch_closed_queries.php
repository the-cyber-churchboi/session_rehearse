<?php
// Include your database configuration
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["userId"])) {
        try {
            $userId = $_POST['userId'];

            // Fetch closed queries for the user from the database
            $stmt = $pdo->prepare("SELECT * FROM queries WHERE user_id = :user_id AND status = 'Closed'");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $closedQueries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Send the fetched data as JSON response
            echo json_encode($closedQueries);
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
