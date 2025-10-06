<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Assuming you have a user identifier stored in the session
    $userUniqueIdentifier = $_POST["userId"];

    try {
        // Fetch the unread message counts for each sender with statuses "sent" or "delivered"
        $query = "SELECT sender_id, COUNT(*) AS unread_count
        FROM messages
        WHERE receiver_id = :user_unique_id
        AND (message_status = 'sent' OR message_status = 'delivered')
        GROUP BY sender_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_unique_id', $userUniqueIdentifier, PDO::PARAM_INT);
        $stmt->execute();
        $unreadCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare an array to store the counts for each sender
        $countsBySender = [];
        foreach ($unreadCounts as $count) {
            $countsBySender[$count['sender_id']] = $count['unread_count'];
        }

        // Return the counts as JSON response
        header('Content-Type: application/json');
        echo json_encode($countsBySender);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
