<?php
session_name("user_session");
session_start();
require_once "config.php"; // Include your database configuration

// Function to perform SQL injection prevention
function sanitizeInput($input)
{
    return htmlspecialchars(stripslashes(trim($input)));
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Insert or update the evaluation data in the database
    try {
        $userId = $_SESSION["id"];
        $scale_of_renovation = $_POST['scale_of_renovation'];
        $apartment_type = sanitizeInput($_POST['apartment_type']);
        $apartment_amenities = isset($_POST['apartment_amenities']) ? $_POST['apartment_amenities'] : array();

        // Convert arrays to strings
        $apartment_amenities_str = implode(', ', $apartment_amenities);
        // ... (rest of your code for processing form inputs)

        // Check if the user's data exists in the table
        $sqlCheckUser = "SELECT user_id FROM evaluation_responses WHERE user_id = :userId";
        $stmtCheckUser = $pdo->prepare($sqlCheckUser);
        $stmtCheckUser->bindParam(":userId", $userId);

        if ($stmtCheckUser->execute()) {
            $userExists = $stmtCheckUser->fetch(PDO::FETCH_ASSOC);
        } else {
            $userExists = false;
        }

        if ($userExists) {
            // User's data already exists, update the existing entry
            $query = "UPDATE evaluation_responses SET 
                      scale_of_renovation = :scale_of_renovation, 
                      apartment_type = :apartment_type, 
                      amenities = :apartment_amenities, 
                      major_requirements = :major_requirements, 
                      defects = :apartment_defects 
                      WHERE user_id = :userId";

            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':scale_of_renovation', $scale_of_renovation);
            $stmt->bindParam(':apartment_type', $apartment_type);
            $stmt->bindParam(':apartment_amenities', $apartment_amenities_str);
            $stmt->bindParam(':major_requirements', $major_requirements_str);
            $stmt->bindParam(':apartment_defects', $apartment_defects_str);
            $stmt->execute();
        } else {
            // User's data does not exist, insert a new entry
            $query = "INSERT INTO evaluation_responses (user_id, scale_of_renovation, apartment_type, amenities, major_requirements, defects) 
                      VALUES (:userId, :scale_of_renovation, :apartment_type, :apartment_amenities, :major_requirements, :apartment_defects)";

            // ... (Bind parameters and execute the insert query as in your original code)
        }

        // The rest of your code to update the user's count, timestamp, and other operations
        // ... (Your existing code for updating user count, timestamp, etc.)

        header("Location: user_dashboard.php");
        $_SESSION["show_evaluation_model"] = false;
        exit(); // Make sure to exit after the header redirect
    } catch (PDOException $e) {
        // Handle database errors here
        $error_message = strip_tags($e->getMessage());
        $response = ["success" => false, "message" => "Database error: " . $error_message];
        echo json_encode($response);
    }
} else {
    // Handle invalid requests
    $response = ["success" => false, "message" => "Invalid request"];
    echo json_encode($response);
}
?>
