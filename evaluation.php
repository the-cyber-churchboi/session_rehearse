<?php
session_name("user_session");
session_start();
require_once "config.php";

// Function to perform SQL injection prevention
function sanitizeInput($input)
{
    return htmlspecialchars(stripslashes(trim($input)));
}

// Check if the user is logged in
$userId = isset($_SESSION["id"]) ? $_SESSION["id"] : null;

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Insert the evaluation data into the database
    try {
        $feedback = $_POST["feedback"];
        $scale_of_renovation = $_POST['scale_of_renovation'];

        // Apartment Type
        $apartment_type = sanitizeInput($_POST['apartment_type']);

        // Amenities in Your Apartment
        $apartment_amenities = isset($_POST['apartment_amenities']) ? $_POST['apartment_amenities'] : array();
        if (in_array('Others', $apartment_amenities)) {
            // If "Others" is selected, get the specified other amenities
            $apartment_amenities_other = sanitizeInput($_POST['apartment_amenities_other']);
            $apartment_amenities[] = $apartment_amenities_other;
        }

        // Major requirements in apartment
        $major_requirements = isset($_POST['major_requirements']) ? $_POST['major_requirements'] : array();
        if (in_array('Others', $major_requirements)) {
            // If "Others" is selected, get the specified other major requirements
            $major_requirements_other = sanitizeInput($_POST['major_requirements_other']);
            $major_requirements[] = $major_requirements_other;
        }

        // Defects Noticed in Your Apartment
        $apartment_defects = isset($_POST['apartment_defects']) ? $_POST['apartment_defects'] : array();
        if (in_array('Others', $apartment_defects)) {
            // If "Others" is selected, get the specified other defects
            $apartment_defects_other = sanitizeInput($_POST['apartment_defects_other']);
            $apartment_defects[] = $apartment_defects_other;
        }

        // Convert arrays to strings
        $apartment_amenities_str = implode(', ', $apartment_amenities);
        $major_requirements_str = implode(', ', $major_requirements);
        $apartment_defects_str = implode(', ', $apartment_defects);

        if ($userId !== null) {
            // Check if the user's data exists in the table
            $sqlCheckUser = "SELECT user_id FROM evaluation_responses WHERE user_id = :userId";
            $stmtCheckUser = $pdo->prepare($sqlCheckUser);
            $stmtCheckUser->bindParam(":userId", $userId);

            if ($stmtCheckUser->execute()) {
                $userExists = $stmtCheckUser->fetch(PDO::FETCH_ASSOC);
            } else {
                $userExists = false;
            }
        } else {
            $userExists = false;
        }

        if (!$userExists) {
            // Insert the values to the table
            $query = "INSERT INTO evaluation_responses (user_id, scale_of_renovation, apartment_type, amenities, major_requirements, defects, feedbacks) 
                        VALUES (:userId, :scale_of_renovation, :apartment_type, :apartment_amenities, :major_requirements, :apartment_defects, :feedbacks)";

            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':scale_of_renovation', $scale_of_renovation);
            $stmt->bindParam(':apartment_type', $apartment_type);
            $stmt->bindParam(':apartment_amenities', $apartment_amenities_str);
            $stmt->bindParam(':major_requirements', $major_requirements_str);
            $stmt->bindParam(':apartment_defects', $apartment_defects_str);
            $stmt->bindParam(':feedbacks', $feedback);
            $stmt->execute();

            // Fetch the current value of evaluation_completed_count
            $sqlFetchCount = "SELECT evaluations_completed_count FROM users WHERE id = :user_id";
            $stmtFetchCount = $pdo->prepare($sqlFetchCount);
            $stmtFetchCount->bindParam(":user_id", $userId, PDO::PARAM_INT);

            // Check if the query executed successfully
            if ($stmtFetchCount->execute()) {
                $currentCount = $stmtFetchCount->fetchColumn(); // Get the current count
            } else {
                $currentCount = 0; // Default to 0 if there's an error
            }

            // Increment the count by one
            $newCount = $currentCount + 1;

            // Prepare and execute a SQL query to update evaluation_completed and evaluation_completed_count
            $sql = "UPDATE users SET evaluation_completed = 1, evaluations_completed_count = :new_count WHERE id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->bindParam(":new_count", $newCount, PDO::PARAM_INT);

            // Check if the query executed successfully
            if ($stmt->execute()) {
                $response = ["success" => true, "message" => "Evaluation submitted successfully. Count updated."];
            } else {
                $response = ["success" => false, "message" => "Database error: " . $stmt->errorInfo()[2]];
            }
        } else {
            // Insert the values to the table
            $query = "UPDATE evaluation_responses SET 
                      scale_of_renovation = :scale_of_renovation, 
                      apartment_type = :apartment_type, 
                      amenities = :apartment_amenities, 
                      major_requirements = :major_requirements, 
                      defects = :apartment_defects,
                      feedbacks = :feedbacks
                      WHERE user_id = :userId";

            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':scale_of_renovation', $scale_of_renovation);
            $stmt->bindParam(':apartment_type', $apartment_type);
            $stmt->bindParam(':apartment_amenities', $apartment_amenities_str);
            $stmt->bindParam(':major_requirements', $major_requirements_str);
            $stmt->bindParam(':apartment_defects', $apartment_defects_str);
            $stmt->bindParam(':feedbacks', $feedback);
            $stmt->execute();

            // Fetch the current value of evaluation_completed_count
            $sqlFetchCount = "SELECT evaluations_completed_count FROM users WHERE id = :user_id";
            $stmtFetchCount = $pdo->prepare($sqlFetchCount);
            $stmtFetchCount->bindParam(":user_id", $userId, PDO::PARAM_INT);

            // Check if the query executed successfully
            if ($stmtFetchCount->execute()) {
                $currentCount = $stmtFetchCount->fetchColumn(); // Get the current count
            } else {
                $currentCount = 0; // Default to 0 if there's an error
            }

            // Increment the count by one
            $newCount = $currentCount + 1;

            // Prepare and execute a SQL query to update evaluation_completed and evaluation_completed_count
            $sql = "UPDATE users SET evaluation_completed = 1, evaluations_completed_count = :new_count WHERE id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->bindParam(":new_count", $newCount, PDO::PARAM_INT);

            // Check if the query executed successfully
            if ($stmt->execute()) {
                $response = ["success" => true, "message" => "Evaluation submitted successfully. Count updated."];
            } else {
                $response = ["success" => false, "message" => "Database error: " . $stmt->errorInfo()[2]];
            }
        }

        // Redirect back to the evaluation page after form submission
        if (isset($_SESSION['followed_specific_feedback']) && $_SESSION['followed_specific_feedback'] === true) {
            // Redirect to user_specific_feedback.php
            header("Location: user_specific_feedback.php");
            exit();
        } else {
            // Use JavaScript alert and then redirect
            echo '<script>alert("Answers submitted succesfully");</script>';
            echo '<script>window.location.href = "index.html";</script>';
            exit();
        }

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
