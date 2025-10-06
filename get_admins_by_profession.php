<?php
session_name("admin_session");
session_start();
// Include your database configuration and setup here
require_once "config.php";

$userId = $_SESSION["admin_unique_id"];

try {
    // Fetch the list of professions
    $query = "SELECT DISTINCT profession FROM admin_registration";
    $stmt = $pdo->query($query);
    $professions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Fetch the admins for each profession (excluding the current admin)
    $adminsByProfession = [];
    foreach ($professions as $profession) {
        $query = "SELECT id, first_name, last_name, unique_identifier, status FROM admin_registration WHERE profession = :profession AND unique_identifier != :currentUniqueIdentifier";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':profession', $profession);
        $stmt->bindParam(':currentUniqueIdentifier', $userId);
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $adminsByProfession[] = ['profession' => $profession, 'admins' => $admins];
    }

    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($adminsByProfession);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
