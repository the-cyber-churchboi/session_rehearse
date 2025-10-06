<?php
session_name("admin_session");
session_start();
require_once "config.php";

if (!isset($_SESSION['admin_id']) && $_SESSION['dashboard'] !== 'manager_dashboard') {
    // User is not logged in or not authorized for this dashboard
    header('Location: admin_login.php');
    exit();
}

// Function to sanitize and validate user inputs
function sanitizeInput($input) {
    // Add your input validation and sanitization logic here
    return $input;
}

// Function to hash a password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Function to validate a password
function validatePassword($password) {
    // Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter,
    // one number, and one special character.
    $pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/';
    return preg_match($pattern, $password) === 1;
}

// Function to check if the username is unique, excluding the current user's username
function isUsernameUnique($pdo, $newUsername, $currentUsername) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_registration WHERE username = :newUsername AND username != :currentUsername");
    $stmt->bindValue(':newUsername', $newUsername);
    $stmt->bindValue(':currentUsername', $currentUsername);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    return $count === 0;
}

// Fetch the user's data for displaying in the form
$user_id = $_SESSION['admin_unique_id']; // Replace with your authentication logic
$sql = "SELECT * FROM admin_registration WHERE unique_identifier = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the user's data

if ($user) {
    $title = $user['title'];
    $first_name = $user['first_name'];
    $last_name = $user['last_name'];
    $username = $user['username'];
} else {
    // Handle the case where the user is not found
    echo "User not found";
}

// Check if the form is submitted for updating user information or changing password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION["admin_unique_id"];

if (isset($_POST['update_profile'])) {
    $newUsername = sanitizeInput($_POST['username']);
    if ($newUsername !== $username && !isUsernameUnique($pdo, $newUsername, $username)) {
        $updateProfileErrors['username'] = "Username is already in use.";
    }

    if (empty($updateProfileErrors)) {
        // Update user information
        $title = sanitizeInput($_POST['title']);
        $first_name = sanitizeInput($_POST['first_name']);
        $last_name = sanitizeInput($_POST['last_name']);
        $username = sanitizeInput($_POST['username']);

        // Prepare and execute an SQL statement to update user information
        $stmt = $pdo->prepare("UPDATE admin_registration SET 
                title = :title,
                first_name = :first_name,
                last_name = :last_name,
                username = :username
                WHERE unique_identifier = :user_id");

        $stmt->execute([
            ':title' => $title,
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':username' => $username,
            ':user_id' => $user_id,
        ]);
        // Successful update, you can redirect the user or show a success message
        header('Location: manager_profile.php');
        $_SESSION["user_id"] = $username;
        exit();
    }
} elseif (isset($_POST['change_password'])) {
        // Change password
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];

        // Fetch the current hashed password from the database
        $stmt = $pdo->prepare("SELECT password FROM admin_registration WHERE unique_identifier = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $row = $stmt->fetch();

        if ($row) {
            $hashed_password = $row['password'];

            // Verify the current password
            if (password_verify($current_password, $hashed_password)) {
                // Check if the new password is valid
                if (validatePassword($new_password)) {
                    // Hash the new password
                    $new_password_hashed = hashPassword($new_password);

                    // Update the password in the database
                    $stmt = $pdo->prepare("UPDATE admin_registration SET password = :new_password WHERE unique_identifier = :user_id");
                    $stmt->execute([':new_password' => $new_password_hashed, ':user_id' => $user_id]);

                    // Successful password change, you can redirect the user or show a success message
                    header('Location: manager_profile.php');
                    exit();
                } else {
                    // Password does not meet the required criteria
                    $password_error = "New password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
                }
            } else {
                // Password verification failed, display an error message
                $password_error = "Current password is incorrect.";
            }
        } else {
            // Handle the case where the user is not found
            $password_error = "User not found";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Reset some default browser styles */
body, h1, h2, p {
    margin: 0;
    padding: 0;
}

/* Basic styles for the page */
body {
    font-family: Arial, sans-serif;
    background-color: #f7f7f7;
    color: #333;
}

header {
    background-color: #66A7D8;
    color: #fff;
    padding: 10px 0;
    text-align: center;
}

.header-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-container h1 {
    font-size: 24px;
}

.header-container a {
    text-decoration: none;
    color: #fff;
    font-weight: bold;
}

.header-container a:hover {
    text-decoration: underline;
}

/* Profile form container */
.profile-container {
    max-width: 800px;
    margin: 20px auto;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    border-bottom: 1px solid black;
}

/* Profile sections */
.profile-section {
    margin-bottom: 20px;
    border-top:  1px solid black;
}

.profile-section h2 {
    font-size: 20px;
    margin-bottom: 10px;
}

/* Form fields */
label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

input[type="text"],
input[type="password"],
select {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

/* Error message */
.error {
    color: #ff0000;
    font-size: 14px;
}

/* Buttons */
button[type="submit"] {
    background-color: #007BFF;
    color: #fff;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s;
}

button[type="submit"]:hover {
    background-color: #0056b3;
}

/* Show/hide password button */
#showHidePassword,
#showHidePassword1 {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    background-color: #007BFF;
    color: #fff;
    border: none;
    border-radius: 5px;
    padding: 5px 10px;
    font-size: 14px;
    cursor: pointer;
}

/* Responsive styles for smaller screens */
@media (max-width: 600px) {
    .profile-container {
        padding: 10px;
    }

    .header-container {
        padding: 10px;
        flex-direction: column;
    }

    .header-container h1 {
        font-size: 20px;
    }
}

    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>User Profile</h1>
            <a href="manager_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </header>
    <form method="POST" action="manager_profile.php">
        <div class="profile-container">
            <div class="profile-section">
                <h2>Personal Information</h2>
                <label for="title">Title:</label>
                <select id="title" name="title">
                    <option value="Mr" <?php if ($title === 'Mr') echo 'selected'; ?>>Mr</option>
                    <option value="Mrs." <?php if ($title === 'Mrs.') echo 'selected'; ?>>Mrs.</option>
                    <option value="Ms." <?php if ($title === 'Ms.') echo 'selected'; ?>>Ms.</option>
                    <option value="Dr." <?php if ($title === 'Dr.') echo 'selected'; ?>>Dr.</option>
                    <option value="Prof." <?php if ($title === 'Prof.') echo 'selected'; ?>>Prof.</option>
                    <option value="Ir." <?php if ($title === 'Ir.') echo 'selected'; ?>>Ir.</option>
                </select>
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo $first_name; ?>" required>
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo $last_name; ?>" required>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo $username; ?>" required>
                <?php if (isset($updateProfileErrors['username'])) : ?>
                    <p class="error"><?php echo $updateProfileErrors['username']; ?></p>
                <?php endif; ?>
                <button type="submit" name="update_profile">Save Changes</button>
            </div>
            <div class="profile-section">
                <h2>Change Password</h2>
                <?php if (isset($password_error)) : ?>
                    <p class="error"><?php echo $password_error; ?></p>
                <?php endif; ?>
                <label for="current_password">Current Password:</label>
                <div style="position: relative;">
                    <input type="password" id="current_password" name="current_password">
                    <button type="button" id="showHidePassword" style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); background-color: #007BFF; color: #fff; border: none; border-radius: 5px; padding: 5px 10px; font-size: 14px; cursor: pointer;">Show Password</button>
                </div>
                <label for="new_password">New Password:</label>
                <div style="position: relative;">
                    <input type="password" id="new_password" name="new_password">
                    <button type="button" id="showHidePassword1" style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); background-color: #007BFF; color: #fff; border: none; border-radius: 5px; padding: 5px 10px; font-size: 14px; cursor: pointer;">Show Password</button>
                </div>
                <button type="submit" name="change_password">Change Password</button>
            </div>
        </div>
    </form>
    <script>
        const currentPasswordInput = document.getElementById("current_password");
        const newPasswordInput = document.getElementById("new_password");
        const showHidePasswordButton = document.getElementById("showHidePassword");
        const showHidePasswordButton1 = document.getElementById("showHidePassword1");

        showHidePasswordButton.addEventListener("click", function () {
            if (currentPasswordInput.type === "password") {
                currentPasswordInput.type = "text";
                showHidePasswordButton.textContent = "Hide Password";
            } else {
                currentPasswordInput.type = "password";
                showHidePasswordButton.textContent = "Show Password";
            }
        });

        showHidePasswordButton1.addEventListener("click", function () {
            if (newPasswordInput.type === "password") {
                newPasswordInput.type = "text";
                showHidePasswordButton1.textContent = "Hide Password";
            } else {
                newPasswordInput.type = "password";
                showHidePasswordButton1.textContent = "Show Password";
            }
        });
    </script>
</body>
</html>

