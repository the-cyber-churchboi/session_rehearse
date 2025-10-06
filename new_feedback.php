<?php
require_once "config.php";

function sanitizeInput($input)
{
    return htmlspecialchars(trim($input));
}

function isEmpty($value)
{
    return empty($value) && $value !== '0';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $property_id = sanitizeInput($_POST['property_id']);

    // Validate other fields and ensure they are not empty
    $requiredFields = array(
        'kitchen_floor_finish',
        'kitchen_wall_finish',
        'kitchen_cabinet_color',
        'bathroom_floor_finish',
        'bathroom_wall_finish',
        'bathroom_cabinet_color',
        'opt_for_lpd'
    );

    foreach ($requiredFields as $field) {
        if (isEmpty($_POST[$field])) {
            echo '<script>';
            echo 'alert("All fields must be filled out!");';
            echo 'window.location.href = "feedbackform.php?property_id=' . $property_id . '";';
            echo '</script>';
            exit();
        }
    }

    $kitchenFloorFinish = sanitizeInput($_POST['kitchen_floor_finish']);
    $kitchenWallFinish = sanitizeInput($_POST['kitchen_wall_finish']);
    $kitchenCabinetColor = sanitizeInput($_POST['kitchen_cabinet_color']);
    $bathroomFloorFinish = sanitizeInput($_POST['bathroom_floor_finish']);
    $bathroomWallFinish = sanitizeInput($_POST['bathroom_wall_finish']);
    $bathroomCabinetColor = sanitizeInput($_POST['bathroom_cabinet_color']);
    $optForLPD = sanitizeInput($_POST['opt_for_lpd']);

    $apartment_amenities = isset($_POST['apartment_amenities']) ? $_POST['apartment_amenities'] : array();
    if (in_array('Others', $apartment_amenities)) {
        $apartment_amenities_other = sanitizeInput($_POST['apartment_amenities_other']);
        if (isEmpty($apartment_amenities_other)) {
            echo '<script>';
            echo 'alert("All fields must be filled out!");';
            echo 'window.location.href = "feedbackform.php?property_id=' . $property_id . '";';
            echo '</script>';
            exit();
        }
        $apartment_amenities[] = $apartment_amenities_other;
    }
    $apartment_amenities_str = !empty($apartment_amenities) ? implode(', ', $apartment_amenities) : null;

    $additionalFeedback = isset($_POST['additional_feedback']) ? sanitizeInput($_POST['additional_feedback']) : '';

    $sql = "INSERT INTO property_preferences 
            (property_id, provisions, kitchen_floor_finish, kitchen_wall_finish, kitchen_cabinet_color, bathroom_floor_finish, bathroom_wall_finish, bathroom_cabinet_color, opt_for_lpd, additional_feedback) 
            VALUES 
            (:propertyId, :provisions, :kitchenFloorFinish, :kitchenWallFinish, :kitchenCabinetColor, :bathroomFloorFinish, :bathroomWallFinish, :bathroomCabinetColor, :optForLPD, :additional_feedback)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':propertyId', $property_id);
        $stmt->bindParam(':provisions', $apartment_amenities_str);
        $stmt->bindParam(':kitchenFloorFinish', $kitchenFloorFinish);
        $stmt->bindParam(':kitchenWallFinish', $kitchenWallFinish);
        $stmt->bindParam(':kitchenCabinetColor', $kitchenCabinetColor);
        $stmt->bindParam(':bathroomFloorFinish', $bathroomFloorFinish);
        $stmt->bindParam(':bathroomWallFinish', $bathroomWallFinish);
        $stmt->bindParam(':bathroomCabinetColor', $bathroomCabinetColor);
        $stmt->bindParam(':optForLPD', $optForLPD);
        $stmt->bindParam(':additional_feedback', $additionalFeedback);

        $stmt->execute();

        echo '<script>';
        echo 'alert("Submitted successfully!");';
        echo 'window.location.href = "index.html";';
        echo '</script>';
    } catch (PDOException $e) {
        echo '<script>';
        echo 'alert("Error inserting data. Please try again later.");';
        echo 'window.location.href = "feedbackform.php?property_id=' . $property_id . '";';
        echo '</script>';
    }
} else {
    header("Location: index.html");
    exit();
}
?>
