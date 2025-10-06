<?php
require_once "config.php";
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $json_data = file_get_contents('php://input');
    
    $data = json_decode($json_data);

    if (isset($data->property_id, $data->user_id, $data->gmail, $data->text)) {

        $property_id = intval($data->property_id);
        $user_id = intval($data->user_id);
        $gmail = filter_var($data->gmail, FILTER_SANITIZE_STRING);
        $text = filter_var($data->text, FILTER_SANITIZE_STRING);

        try {
            // Check if feedback already exists for the user and property
            $stmt_check = $pdo->prepare("SELECT * FROM feedbacks WHERE user_id = :user_id AND property_id = :property_id");
            $stmt_check->bindParam(':user_id', $user_id);
            $stmt_check->bindParam(':property_id', $property_id);
            $stmt_check->execute();
            $existingFeedback = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($existingFeedback) {
                $stmt_update = $pdo->prepare("UPDATE feedbacks SET email = :gmail, text = :text WHERE user_id = :user_id AND property_id = :property_id");
                $stmt_update->bindParam(':user_id', $user_id);
                $stmt_update->bindParam(':property_id', $property_id);
                $stmt_update->bindParam(':gmail', $gmail);
                $stmt_update->bindParam(':text', $text);
                $stmt_update->execute();


            } else {
                $stmt_insert = $pdo->prepare("INSERT INTO feedbacks (user_id, property_id, email, text) VALUES (:user_id, :property_id, :gmail, :text)");
                $stmt_insert->bindParam(':user_id', $user_id);
                $stmt_insert->bindParam(':property_id', $property_id);
                $stmt_insert->bindParam(':gmail', $gmail);
                $stmt_insert->bindParam(':text', $text);
                $stmt_insert->execute();
            }

            $response = array('success' => true, 'message' => 'Feedback saved successfully');
            echo json_encode($response);
        } catch (PDOException $e) {
            $response = array('success' => false, 'message' => 'Error saving feedback');
            echo json_encode($response);
        }

    } else {
        $response = array('success' => false, 'message' => 'Missing required fields');
        echo json_encode($response);
    }

} else {
    $response = array('success' => false, 'message' => 'Invalid request method');
    echo json_encode($response);
}

?>
