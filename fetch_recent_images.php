<?php
require_once "config.php";

// Define the maximum number of recent images to fetch
$maxRecentImages = 5;

try {    
    // Prepare and execute the SQL query
    $sql = "SELECT id, property_id, apartment_type, image_path, other_details, created_at FROM propertyadvertisements ORDER BY created_at DESC LIMIT :maxRecentImages";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':maxRecentImages', $maxRecentImages, PDO::PARAM_INT);
    $stmt->execute();
    
    $recentImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return the recent images as JSON data
    echo json_encode($recentImages);
} catch (PDOException $e) {
    // Handle any errors here
    echo "Error: " . $e->getMessage();
}
?>
