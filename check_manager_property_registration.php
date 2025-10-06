<?php
session_name("admin_session");
session_start();
require_once "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['unique_id'])) {
    $uniqueId = $_GET['unique_id'];

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM manager_property_registration WHERE property_id = ?");
        $stmt->execute([$uniqueId]);
        $exists = $stmt->fetchColumn();

        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode(['exists' => $exists]);
        exit;
    } catch (PDOException $e) {
        // Handle the error, log it, or return an error response
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}
?>
