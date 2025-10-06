<?php
session_name("user_session");
session_start();
require_once "config.php";
// Your existing PHP code for session and database connection here

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    // If the user is not logged in, redirect them to the login page
    header("Location: user_login.php");
    exit();
}

// Get user_id and admin_id from query parameters
if (isset($_GET["admin_id"])) {
    $admin_id = $_GET["admin_id"];
} else {
    // Handle the case where user_id and admin_id are not provided
    echo "Admin ID are required.";
    exit();
}

$userId = $_SESSION["user_unique_id"];
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process the feedback form submission here
    // You can access user_id and admin_id using $user_id and $admin_id variables

    // Example: Save feedback to the database
    $rating = $_POST["rating"];
    $feedback_text = $_POST["feedback_text"];

    // Replace 'your_db_table' with the actual name of your feedback table
    $sql = "INSERT INTO feedback_ratings (user_id, admin_id, rating, feedback_text) VALUES (?, ?, ?, ?)";

    // Assuming you're using prepared statements
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $admin_id, $rating, $feedback_text]);

    // Check if the insert was successful
    if ($stmt->rowCount() > 0) {
        // Redirect the user to a thank you page or dashboard after submitting feedback
        header("Location: feedback_thank_you.php");
        exit();
    } else {
        // Handle the case where the database insert fails
        echo "Error: Feedback submission failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Form</title>
    <link rel="stylesheet" href="feedback.css">
    <link rel="stylesheet" href="rating.css"> <!-- Include your star rating CSS file -->
</head>
<body>
    <header>
        <div class="header-content">
            <!-- Your header content here -->
            <h1>Feedback Form</h1>
        </div>
    </header>
    <main>
        <div class="feedback-container">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?admin_id=" . urlencode($_GET["admin_id"]); ?>">
                <!-- Star rating input -->
                <div class="form-group">
                    <label for="rating">Rating:</label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5">
                        <label for="star5">☆</label>
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4">☆</label>
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3">☆</label>
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2">☆</label>
                        <input type="radio" id="star1" name="rating" value="1">
                        <label for="star1">☆</label>
                    </div>
                </div>
                <!-- Feedback text input -->
                <div class="form-group">
                    <label for="feedback_text">Feedback:</label>
                    <textarea name="feedback_text" id="feedback_text" rows="4" cols="50"></textarea>
                </div>
                <div class="form-group">
                    <!-- You can include additional form fields here -->
                </div>
                <div class="form-group">
                    <button type="submit" id="submit-button">Submit Feedback</button>
                </div>
            </form>
        </div>
    </main>
    <footer>
        <div class="footer-content">
            <!-- Your footer content here -->
        </div>
    </footer>
    <script src="star-rating.js"></script> <!-- Include your star rating JavaScript file -->
    <script>
        const starRating = document.querySelector(".star-rating");
        const ratingInputs = starRating.querySelectorAll('input[type="radio"]');
        const feedbackForm = document.getElementById("feedback-form");
        const submitButton = document.getElementById("submit-button");

        ratingInputs.forEach((input) => {
            input.addEventListener("change", () => {
                const rating = input.value;
                console.log("Selected rating:", rating); // You can replace this with your desired action, like sending the rating to the server.
            });
        });

        // Add an animation when submitting the form (you can customize this further)
        feedbackForm.addEventListener("submit", (event) => {
            event.preventDefault();
            submitButton.innerText = "Submitting...";
            setTimeout(() => {
                submitButton.innerText = "Submitted!";
                feedbackForm.reset();
                // You can redirect or perform other actions here after submission.
            }, 2000); // Change the time as needed
        });
    </script>
</body>
</html>
