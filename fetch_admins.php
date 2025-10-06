<?php
// Include your database configuration
require_once('config.php');

// Get the selected profession from the POST request
$selectedProfession = isset($_POST['profession']) ? $_POST['profession'] : '';

if (!empty($selectedProfession)) {
    // Fetch admins under the selected profession
    $query = "SELECT * FROM admin_registration WHERE profession = :profession";
    $statement = $pdo->prepare($query);
    $statement->bindParam(':profession', $selectedProfession, PDO::PARAM_STR);
    $statement->execute();

    $admins = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Return the admins in JSON format
    header('Content-Type: application/json');
    echo json_encode($admins);
} else {
    // Handle the case when no profession is selected
    echo json_encode([]);
}
?>
