<?php
// Include your database configuration
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'], $_POST['building'])) {
        $selectedUser = $_POST['username'];
        $selectedBuilding = $_POST['building'];

        // Fetch query titles for the selected user and building
        $query = "SELECT title
                  FROM queries
                  WHERE user_id = (SELECT unique_identifier FROM users WHERE username = :username)
                  AND property_id = :building
                  AND assigned_to is NULL";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $selectedUser, PDO::PARAM_STR);
        $stmt->bindParam(':building', $selectedBuilding, PDO::PARAM_STR);
        $stmt->execute();
        $titles = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode($titles);
    } else {
        echo json_encode(['error' => 'Invalid request']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
