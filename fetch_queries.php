<?php
// Include your database configuration
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["userId"])) {
        try {
            $userId = $_POST['userId'];
            $title = $_POST['title'];
            $description = $_POST['description'];
            $status = $_POST['status'];

            // Build the SQL query for fetching filtered queries
            $query = "SELECT * FROM queries WHERE user_id = :user_id";

            if (!empty($title)) {
                $query .= " AND title LIKE :title";
            }

            if (!empty($description)) {
                $query .= " AND description LIKE :description";
            }

            if (!empty($status)) {
                $query .= " AND status = :status";
            }

            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

            if (!empty($title)) {
                $titleParam = '%' . $title . '%';
                $stmt->bindParam(':title', $titleParam, PDO::PARAM_STR);
            }

            if (!empty($description)) {
                $descriptionParam = '%' . $description . '%';
                $stmt->bindParam(':description', $descriptionParam, PDO::PARAM_STR);
            }

            if (!empty($status)) {
                $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            }

            $stmt->execute();
            $queries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($queries);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Invalid request']);
    }
}
?>
