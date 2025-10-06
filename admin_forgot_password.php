<?php
// Start the session
session_name("admin_pass");
session_start();

?>


<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <!-- Add your CSS styling here or link to an external CSS file -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add custom CSS styling here -->
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-group label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        #resendLink {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Forgot Password</h1>
        <p>Enter your email address below, and we'll send you a link to reset your password.</p>
        <form id="passwordResetForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <div class="form-group mb-3">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary" id="nextButton">Next</button>
        </form>
        <p id="timer" style="display: none;">You can resend the link in <span id="countdown">100</span> seconds.</p><a href="#" id="resendLink">Resend</a>
        <p id="error-message" class="error" style="display: none;">Email not found. Please enter a registered email address.</p>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
        const countdownElement = document.getElementById("countdown");
        const resendLink = document.getElementById("resendLink");
        const nextButton = document.getElementById("nextButton");

        let countdownInterval;
        let seconds = 100;

        // Function to start the countdown
        function startCountdown() {
            countdownElement.textContent = seconds;
            countdownInterval = setInterval(updateCountdown, 1000);
        }

        // Function to update the countdown display
        function updateCountdown() {
            seconds--;

            if (seconds < 0) {
                // Enable the "Resend" link and hide the countdown when the timer reaches 0
                clearInterval(countdownInterval);
                resendLink.style.display = "inline";
                document.getElementById("timer").style.display = "none"; // Hide the countdown
            } else {
                countdownElement.textContent = seconds;
            }
        }

        // Function to handle the countdown timer
        function handleCountdown() {
            // Hide the form and display the timer
            document.getElementById("timer").style.display = "block";
            document.querySelector("form").style.display = "none";

            // Start the countdown
            startCountdown();
        }

        // Attach a click event listener to the "Next" button
        nextButton.addEventListener("click", function (event) {
            event.preventDefault();

            // Get the email address from the input field
            const email = document.getElementById("email").value;

            // Use AJAX to check if the email exists
            checkEmailExists(email)
                .then(emailExists => {
                    if (emailExists) {
                        const errorMessage = document.getElementById("error-message");
                        errorMessage.style.display = "none"; // hide the error message
                        // Hide the form and display the timer message
                        document.querySelector("form").style.display = "none";
                        const timerMessage = document.getElementById("timer");
                        timerMessage.style.display = "block"; // Show the timer message

                        // Start the countdown timer
                        startCountdown();

                        // Add logic to send the email and resend link here
                        sendResetToken(email);
                    } else {
                        // If the email does not exist in the database, show an error message
                        const errorMessage = document.getElementById("error-message");
                        errorMessage.style.display = "block"; // Show the error message
                    }
                })
                .catch(error => {
                    // Handle any errors that occur during the AJAX request
                    console.error('Error:', error);
                    // You can add additional error handling logic here
                });
        });

        // Attach a click event listener to the "Resend" link
        resendLink.addEventListener("click", function (event) {
            event.preventDefault();

            // Reset the timer and start countdown again
            seconds = 100;
            startCountdown();

            // Hide the "Resend" link and show the countdown
            resendLink.style.display = "none";
            document.getElementById("timer").style.display = "block";

            // Add logic to resend the email here
            const email = document.getElementById("email").value;
            sendResetToken(email);
        });

        // Function to check if an email exists using AJAX
        function checkEmailExists(email) {
            const url = 'admin_check_email.php'; // Replace with the actual path to your PHP script

            const formData = new FormData();
            formData.append('email', email);

            return fetch(url, {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                return data.emailExists;
            });
        }

        // Function to send the reset token using AJAX
        function sendResetToken(email) {
            const url = 'admin_send_reset_token.php'; // Replace with the actual path to your PHP script

            const formData = new FormData();
            formData.append('email', email);

            return fetch(url, {
                method: 'POST',
                body: formData,
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to send reset token');
                }
                // You can handle success here if needed
            })
            .catch(error => {
                // Handle any errors that occur during the AJAX request
                console.error('Error:', error);
                // You can add additional error handling logic here
            });
        }
    });
    </script>
</body>
</html>

