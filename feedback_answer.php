<?php
session_name("user_session");
session_start();
require_once "config.php";

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    // If the user is not logged in, redirect them to the login page
    header("Location: user_login.php");
    exit();
}

// Check for unanswered feedbacks in the user_invites table
$userId = $_SESSION["user_unique_id"];
$unansweredFeedbacks = array();

try {
    $query = "SELECT DISTINCT property_name FROM user_invites WHERE user_id = :userId AND invite_answered = 0";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $unansweredFeedbacks = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the selected building and feedback data from the form
    $selectedBuilding = $_POST["buildingName"];
    $defectPercentage = $_POST["defectPercentage"];
    $defectDetails = $_POST["defectDetails"];

    // Ensure the selected building is not empty
    if (!empty($selectedBuilding)) {
        // Get the user's unique ID
        $userId = $_SESSION["user_unique_id"];

        // You can process and store the feedback data in your database here
        // For example, insert the data into a feedback table
        try {
            $query = "INSERT INTO feedback (property_name, defect_percentage, defect_details, user_id) VALUES (:buildingName, :defectPercentage, :defectDetails, :userId)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':buildingName', $selectedBuilding);
            $stmt->bindParam(':defectPercentage', $defectPercentage);
            $stmt->bindParam(':defectDetails', $defectDetails);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

        // Mark the feedback as answered in the user_invites table
        try {
            $query = "UPDATE user_invites SET invite_answered = 1, invite_answered_timestamp = NOW() WHERE user_id = :userId AND property_name = :buildingName";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':buildingName', $selectedBuilding);
            $stmt->execute();

            header('Location: feedback_answer.php');
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Feedback Answer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #3f72af;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .back-link {
            font-size: 24px;
            color: white;
            text-decoration: none;
        }

        .header-img {
            max-width: 100px;
            display: block;
            margin: 0 auto;
        }

        main {
            margin: 20px;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        select, input[type="radio"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        label {
            font-weight: bold;
        }

        input[type="submit"] {
            background-color: #3f72af;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #2b5b94;
        }

        .feedback-form {
            display: none;
        }
    </style>
    <script>
        function showFeedbackForm() {
            var selectedBuilding = document.getElementById("buildingName").value;
            var feedbackForms = document.getElementsByClassName("feedback-form");

            for (var i = 0; i < feedbackForms.length; i++) {
                feedbackForms[i].style.display = "none";
            }

            if (selectedBuilding) {
                document.getElementById(selectedBuilding).style.display = "block";
            }
        }
    </script>
</head>
<body>
    <header>
        <a class="back-link" href="user_dashboard.php">&#8678; Back</a>
        <img src="Logo_final.png" alt="Logo" class="header-img">
    </header>
    <main class="container">
        <?php
        if (count($unansweredFeedbacks) > 0) {
            echo "<h2>Select a Building:</h2>";
            echo "<form method='post'>";
            echo "<div class='form-group'>";
            echo "<select id='buildingName' name='buildingName' onchange='showFeedbackForm()' class='form-control'>";
            echo "<option value=''>Select a Building</option>";

            foreach ($unansweredFeedbacks as $buildingName) {
                echo "<option value='$buildingName'>$buildingName</option>";
            }

            echo "</select>";
            echo "</div>";

            foreach ($unansweredFeedbacks as $buildingName) {
                echo "<div class='feedback-form' id='$buildingName'>";
                echo "<h2>Feedback for Building: $buildingName</h2>";
                echo "<div class='form-group'>";
                echo "<label for='defectPercentage'>Percentage of any defect(s) in apartment so far?</label>";
                echo "<div class='form-check'>";
                echo "<input type='radio' id='none' name='defectPercentage' value='None' class='form-check-input'>";
                echo "<label for='none' class='form-check-label'>None</label>";
                echo "</div>";
                echo "<div class='form-check'>";
                echo "<input type='radio' id='lt10' name='defectPercentage' value='Less than 10%' class='form-check-input'>";
                echo "<label for='lt10' class='form-check-label'>Less than 10%</label>";
                echo "</div>";
                echo "<div class='form-check'>";
                echo "<input type='radio' id='10-49' name='defectPercentage' value='10% - 49%' class='form-check-input'>";
                echo "<label for='10-49' class='form-check-label'>10% - 49%</label>";
                echo "</div>";
                echo "<div class='form-check'>";
                echo "<input type='radio' id='50-75' name='defectPercentage' value='50% - 75%' class='form-check-input'>";
                echo "<label for='50-75' class='form-check-label'>50% - 75%</label>";
                echo "</div>";
                echo "<div class='form-check'>";
                echo "<input type='radio' id='gt75' name='defectPercentage' value='Greater than 75%' class='form-check-input'>";
                echo "<label for='gt75' class='form-check-label'>Greater than 75%</label>";
                echo "</div>";
                echo "</div>";
                echo "<div class='form-group'>";
                echo "<label for='defectDetails'>Details of defect(s):</label>";
                echo "<textarea id='defectDetails' name='defectDetails' rows='4' class='form-control'></textarea>";
                echo "</div>";
                echo "<button type='submit' class='btn btn-primary'>Submit feedback</button>";
                echo "</div>";
            }

            echo "</form>";
        } else {
            echo "<h2>No unanswered feedbacks.</h2>";
        }
        ?>
    </main>
</body>
</html>
