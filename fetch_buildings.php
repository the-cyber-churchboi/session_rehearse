<?php
// Include your database configuration
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'])) {
        $selectedUser = $_POST['username'];

        // Fetch distinct buildings and property_ids submitted by the selected user
        $query = "SELECT DISTINCT building, property_id
                  FROM queries
                  WHERE user_id = (SELECT unique_identifier FROM users WHERE username = :username)
                  AND assigned_to IS NULL";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $selectedUser, PDO::PARAM_STR);
        $stmt->execute();
        $buildings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($buildings);
    } else {
        echo json_encode(['error' => 'Invalid request']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
