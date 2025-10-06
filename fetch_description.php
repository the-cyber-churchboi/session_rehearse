<?php
// Include your database configuration
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'], $_POST['building'], $_POST['title'])) {
        $selectedUser = $_POST['username'];
        $selectedBuilding = $_POST['building'];
        $selectedTitle = $_POST['title'];

        // Fetch query description for the selected user, building, and title
        $query = "SELECT description
                  FROM queries
                  WHERE user_id = (SELECT unique_identifier FROM users WHERE username = :username)
                  AND property_id = :building
                  AND title = :title
                  AND assigned_to is NULL";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $selectedUser, PDO::PARAM_STR);
        $stmt->bindParam(':building', $selectedBuilding, PDO::PARAM_STR);
        $stmt->bindParam(':title', $selectedTitle, PDO::PARAM_STR);
        $stmt->execute();
        $description = $stmt->fetch(PDO::FETCH_COLUMN);

        echo json_encode($description);
    } else {
        echo json_encode(['error' => 'Invalid request']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
