<?php
// Add your database connection code here
session_name("admin_session");
session_start();

if (!isset($_SESSION['admin_id'])) {
    // User is not logged in or not authorized for this dashboard
    header('Location: admin_login.php');
    exit();
}
require_once "config.php";

// Check if it's a DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Get the building ID from the request body
    $data = json_decode(file_get_contents('php://input'), true);
    $buildingId = $data['buildingId'];

    // Fetch property_name based on building_id
    $stmtBuilding = $pdo->prepare("SELECT property_name FROM manager_property_registration WHERE property_id = ?");
    $stmtBuilding->execute([$buildingId]);
    $buildingData = $stmtBuilding->fetch(PDO::FETCH_ASSOC);

    if (!$buildingData) {
        // Handle the case where building data is not found
        echo "Building data not found";
        exit;
    }

    $propertyName = $buildingData['property_name'];

    try {
        // Start a transaction
        $pdo->beginTransaction();
    
        // Perform the delete operation on the property_registration table
        $stmtDeleteProperty = $pdo->prepare("DELETE FROM manager_property_registration WHERE property_id = ?");
        $stmtDeleteProperty->execute([$buildingId]);
    
        // Perform the delete operation on the others_property_registration table
        $stmtDeleteOthersProperty = $pdo->prepare("DELETE FROM others_property_registration WHERE property_id = ?");
        $stmtDeleteOthersProperty->execute([$buildingId]);
    
        // Perform the delete operation on the user_property_registration table
        $stmtDeleteUserProperty = $pdo->prepare("DELETE FROM user_property_registration WHERE property_id = ?");
        $stmtDeleteUserProperty->execute([$buildingId]);
    
        // Perform the delete operation on the building_evaluations table
        $stmtDeleteEvaluations = $pdo->prepare("DELETE FROM building_evaluations WHERE property_id = ?");
        $stmtDeleteEvaluations->execute([$buildingId]);

        // Update the property_name to null in the property_registration table
        $stmtUpdateProperty = $pdo->prepare("UPDATE property_registration SET property_name = null WHERE unique_id = ?");
        $stmtUpdateProperty->execute([$buildingId]);
    
        // Commit the transaction
        $pdo->commit();
    
        // Return a JSON response
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Building and evaluations deleted successfully']);
        exit;
    } catch (Exception $e) {
        // An error occurred, rollback the transaction
        $pdo->rollBack();
    
        // Return an error JSON response
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error occurred during deletion']);
        exit;
    }
}
?>
