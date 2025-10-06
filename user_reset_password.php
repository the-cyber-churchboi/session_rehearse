<?php
// Start the session
session_name("user_pass");
session_start();

// Assuming you have already established a database connection
require_once "config.php";

// Function to validate the reset token and check if it is not expired
function validateResetToken($token) {
    global $pdo;
    $sql = "SELECT email, token_expiration FROM users WHERE reset_token = :token";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['token' => $token]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $email = $result['email'];
        $expiration = strtotime($result['token_expiration']);
        $current_time = time();

        return ($email && $expiration > $current_time);
    }

    return false;
}

// Function to reset the password for the user with the given token
function resetPasswordWithToken($token, $password) {
    global $pdo;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "UPDATE users SET password = :password, reset_token = NULL, token_expiration = NULL WHERE reset_token = :token";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'password' => $hashedPassword,
        'token' => $token
    ]);
}

if (isset($_GET['token'])) {
    $token = $_GET["token"];
    $errors = array();
    if (validateResetToken($token)) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $password = $_POST["password"];
            $confirmPassword = $_POST["confirm_password"];

            // Validate and sanitize the password (add your own password validation code here)
            if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[\W]/', $password)) {
                // Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one digit, and one special character.
                // You can customize this error message to fit your requirements.
                $errors["password_err"] =  'Error: Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one digit, and one special character.';
            }

            // Sanitize the password (optional, but recommended to prevent potential issues)
            $password = filter_var($password, FILTER_SANITIZE_STRING);

            // Validate and sanitize the confirm password field (add your own validation code here if needed)
            if ($password !== $confirmPassword) {
                // Password and Confirm Password do not match
                // You can customize this error message to fit your requirements.
                $errors["confirm_pass_err"] = 'Error: Password and Confirm Password do not match.';
            }


            // Check if the token exists and is not expired
            if (empty($errors)){
                if (validateResetToken($token)) {
                    // Reset the password for the user with the given token
                    resetPasswordWithToken($token, $password);

                    // Show a success message or redirect to a success page
                    echo '<p class="success">Password reset successful! You can now log in with your new password.</p>';
                    header("refresh:3;url=user_login.php");
                } else {
                    // If the token is invalid or expired, show an error message
                    echo '<p class="error">Error: Invalid or expired reset token. Please request a new password reset.</p>';
                    header("refresh:3;url=user_login.php");
                }
            }
        }
    } else {
        echo '<p class="error">Invalid or expired reset token. Please request a new password reset.</p>';
        header("refresh:3;url=user_login.php");
    }
    }else {
        // The 'token' key is not set in the $_POST array
        // Handle the case when the token is not provided in the form submission
        // For example, you can display an error message or redirect the user back to the previous page
        echo "Error: Token not provided. Please check your email and click the reset link again.";
        header("refresh:3;url=user_login.php");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Password Reset</title>
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
        .show-password-button {
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php if (validateResetToken($token)) : ?>
    <div class="container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?token=' . urlencode($token); ?>" method="post">
            <h1>Reset Password</h1>
            <div class="form-group mb-3">
                <label for="password">Password (min. Length (8), uppercase[A-Z] (at least 1), lowercase [a-z] (at least 1), numbers [0-9] (at least 1), special characters [e.g !, %, #, $, @, e.t.c] (at least 1)):</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" required>
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">Show Password</button>
                    <?php if (!empty($errors['password_err'])) : ?>
                        <span style="color: red;"><?php echo $errors['password_err']; ?></span><br>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="confirm_password">Confirm Password:</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirmPassword" class="form-control" required>
                    <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPassword">Show Password</button>
                    <?php if (!empty($errors['confirm_pass_err'])) : ?>
                        <span style="color: red;"><?php echo $errors['confirm_pass_err']; ?></span><br>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Reset</button>
        </form>
        <?php endif; ?>
    </div>

    <!-- Optional: Add Bootstrap JS and jQuery for additional functionalities -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        const passwordField = document.getElementById('password');
        const confirmPasswordField = document.getElementById('confirmPassword');
        const togglePasswordButton = document.getElementById('togglePassword');
        const toggleConfirmPasswordButton = document.getElementById('toggleConfirmPassword');

        togglePasswordButton.addEventListener('click', function() {
            togglePasswordVisibility(passwordField, togglePasswordButton);
        });

        toggleConfirmPasswordButton.addEventListener('click', function() {
            togglePasswordVisibility(confirmPasswordField, toggleConfirmPasswordButton);
        });

        function togglePasswordVisibility(inputField, toggleButton) {
            if (inputField.type === 'password') {
                inputField.type = 'text';
                toggleButton.textContent = 'Hide Password';
            } else {
                inputField.type = 'password';
                toggleButton.textContent = 'Show Password';
            }
        }
    </script>
</body>
</html>
