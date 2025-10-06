<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["adminId"])) {
        $adminId = $_POST["adminId"];

        $query = "SELECT f.property_name, u.username, f.defect_percentage, f.defect_details, f.submission_timestamp
                  FROM feedback f
                  INNER JOIN users u ON f.user_id = u.unique_identifier
                  WHERE f.property_name IN (
                    SELECT DISTINCT property_name
                    FROM others_property_registration
                    WHERE user_id = :adminId
                  )";

        try {
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':adminId', $adminId);
            $stmt->execute();
            $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Send the fetched data as JSON response
            echo json_encode($feedbacks);
        } catch (PDOException $e) {
            // Handle any database errors
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo "Admin ID not provided.";
    }
} else {
    echo "Invalid request method.";
}
