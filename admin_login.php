<?php
session_name("admin_session");
session_start();

require_once "config.php";

// Initialize variables
$selected_dashboard = ""; // You can set this to 'developer_dashboard' or 'admin_dashboard' based on your requirements.

if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"]) {
    // Check the session to determine the user's dashboard
    $selected_dashboard = $_SESSION['dashboard'];
    if ($selected_dashboard === 'developer_dashboard') {
        header('Location: developer_dashboard.php');
        exit();
    } elseif ($selected_dashboard === 'manager_dashboard') {
        header('Location: manager_dashboard.php');
        exit();
    } else {
        header('location: admin_dashboard.php');
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array();
    $email_or_username = $_POST["email_or_username"];
    $password = $_POST["password"];

    // Validate and sanitize the input (add your own validation code here if needed)
    $email_or_username = filter_var($email_or_username, FILTER_SANITIZE_STRING);
    $password = filter_var($password, FILTER_SANITIZE_STRING);

    // Determine if the provided input contains "@" to decide whether it's an email
    $is_email = (strpos($email_or_username, '@') !== false);

    // Initialize the SQL statement and the parameters array
    $sql = "";
    $params = array(':email_or_username' => $email_or_username);

    if ($is_email) {
        // Input appears to be an email, validate it as an email
        if (filter_var($email_or_username, FILTER_VALIDATE_EMAIL)) {
            $sql = "SELECT email, password, profession, unique_identifier FROM admin_registration WHERE email = :email_or_username";
        } else {
            $errors['error'] = "Invalid email format.";
        }
    } else {
        // Input is assumed to be a username
        $sql = "SELECT username, password, profession, unique_identifier FROM admin_registration WHERE username = :email_or_username";
    }

    if (!empty($sql)) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $email_or_username = $is_email ? $result['email'] : $result['username'];
            $password_hash = $result['password'];
            $profession = $result['profession'];
            $unique_identifier = $result['unique_identifier'];

            if (password_verify($password, $password_hash)) {
                $_SESSION["logged_in"] = true;
                $_SESSION["email_or_username"] = $email_or_username;
                if ($profession === 'Developer') {
                    $_SESSION["dashboard"] = 'developer_dashboard';
                } elseif ($profession === 'Property Manager') {
                    $_SESSION["dashboard"] = 'manager_dashboard';
                } else {
                    $_SESSION["dashboard"] = 'admin_dashboard';
                }

                $updateStatusStmt = $pdo->prepare("UPDATE admin_registration SET status = 'online' WHERE unique_identifier = :unique_identifier");
                $updateStatusStmt->execute(['unique_identifier' => $unique_identifier]);

                $_SESSION["admin_id"] = $email_or_username;
                $_SESSION["admin_unique_id"] = $unique_identifier;

                $updateSql = "UPDATE messages SET message_status = 'delivered' WHERE receiver_id = :userId AND message_status = 'sent'";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->bindParam(":userId", $unique_identifier, PDO::PARAM_INT);
                $updateStmt->execute();

                // Redirect to the appropriate dashboard based on the profession
                if ($_SESSION["dashboard"] == 'developer_dashboard') {
                    header("Location: developer_dashboard.php");
                    exit();
                } elseif ($_SESSION["dashboard"] == 'manager_dashboard') {
                    header("Location: manager_dashboard.php");
                    exit();
                } else {
                    header("Location: admin_dashboard.php");
                    exit();
                }
            } else {
                $errors["error"] = 'Error: Invalid email/username or password.';
            }
        } else {
            $errors["error"] = 'Error: Invalid email/username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professionals Login | EOD Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0a2540;
            --secondary: #00d4aa;
            --accent: #635bff;
            --accent-light: #8a85ff;
            --light: #f6f9fc;
            --dark: #0a2540;
            --success: #00c9a7;
            --warning: #ffb800;
            --info: #14b8ff;
            --gray: #7c8ca1;
            --transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 25px 50px rgba(0, 0, 0, 0.15);
            --radius: 16px;
            --glass: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.18);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background: linear-gradient(135deg, #f6f9fc 0%, #f0f4f8 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3, h4, h5 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            line-height: 1.2;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
        }

        /* Header Styles */
        header {
            background: linear-gradient(135deg, var(--primary) 0%, #1a365d 100%);
            color: white;
            padding: 20px 0;
            position: relative;
            overflow: hidden;
        }

        .header-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-section img {
            height: 40px;
            width: auto;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .home-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 500;
            transition: var(--transition);
            border: 1px solid var(--glass-border);
        }

        .home-link:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(-5px);
        }

        /* Main Content */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
        }

        .login-container {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            padding: 50px;
            width: 100%;
            max-width: 480px;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--accent), var(--secondary));
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(99, 91, 255, 0.1), rgba(138, 133, 255, 0.2));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            color: var(--accent);
        }

        .login-header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .login-header p {
            color: var(--gray);
            font-size: 1.1rem;
        }

        /* Error Message */
        .error-message {
            background: rgba(255, 87, 87, 0.1);
            border: 1px solid rgba(255, 87, 87, 0.3);
            color: #d32f2f;
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 25px;
            text-align: center;
            font-weight: 500;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary);
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e8edf5;
            border-radius: var(--radius);
            font-size: 1rem;
            transition: var(--transition);
            background: white;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99, 91, 255, 0.1);
        }

        .form-control-wrapper {
            position: relative;
        }

        .toggle-password-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .toggle-password-btn:hover {
            color: var(--accent);
        }

        .submit-btn {
            background: linear-gradient(90deg, var(--accent), var(--secondary));
            color: white;
            border: none;
            padding: 16px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            display: block;
            width: 100%;
            margin-top: 10px;
            box-shadow: 0 8px 25px rgba(99, 91, 255, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(99, 91, 255, 0.4);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        /* Link Container */
        .link-container {
            margin-top: 30px;
            text-align: center;
        }

        .link-container p {
            margin-bottom: 15px;
        }

        .link-container a {
            color: var(--accent);
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        .link-container a:hover {
            color: var(--accent-light);
            transform: translateX(5px);
        }

        /* Floating Elements */
        .floating-shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            opacity: 0.05;
            z-index: -1;
        }

        .shape-1 {
            width: 200px;
            height: 200px;
            top: 10%;
            right: 10%;
        }

        .shape-2 {
            width: 150px;
            height: 150px;
            bottom: 20%;
            left: 10%;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .login-container {
                padding: 40px 30px;
            }
            
            .login-header h1 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 576px) {
            .header-container {
                padding: 0 20px;
            }
            
            .login-container {
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: 1.6rem;
            }
            
            .login-header p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-section">
                <img src="Logo_final.png" alt="EOD Platform">
                <span class="logo-text">EOD Platform</span>
            </div>
            
            <a href="index.html" class="home-link">
                <i class="fas fa-arrow-left"></i> Back to Homepage
            </a>
        </div>
    </header>
    <main>
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="login-container">
            <div class="login-header">
                <div class="login-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h1>Welcome Professionals</h1>
                <p>Sign in to your EOD Platform account</p>
            </div>

            <?php if (!empty($errors['error'])) : ?>
                <div class="error-message"><?php echo $errors['error']; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
                <div class="form-group">
                    <label for="email_or_username">Email or Username:</label>
                    <input type="text" name="email_or_username" id="email_or_username" class="form-control" placeholder="Enter your username or email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <div class="form-control-wrapper">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                        <span class="toggle-password-btn" id="togglePassword">Show Password</span>
                    </div>  
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-sign-in-alt"></i> Login to Your Account
                </button>
            </form>

            <div class="link-container">
                <p class="forgot-password">
                    <a href="admin_forgot_password.php">
                        <i class="fas fa-key"></i> Forgot Password?
                    </a>
                </p>
                <p class="registration-link">
                    <a href="admin_signup.php">
                        <i class="fas fa-user-plus"></i> New registration
                    </a>
                </p>
            </div>
        </div>
    </main>
    <script>
        const passwordField = document.getElementById('password');
        const togglePasswordButton = document.getElementById('togglePassword');

        togglePasswordButton.addEventListener('click', function() {
            togglePasswordVisibility(passwordField, togglePasswordButton);
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
