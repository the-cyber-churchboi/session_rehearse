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

$userId = $_SESSION["user_unique_id"];

$query = "SELECT title, first_name, last_name FROM users WHERE unique_identifier = :userId";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':userId', $userId);
$stmt->execute();
$userProfile = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if any of the profile fields are empty or null
$incompleteProfile = false;
foreach ($userProfile as $field) {
    if (empty($field) || is_null($field)) {
        $incompleteProfile = true;
        break;
    }
}

// Check if the user's profile is already complete
if (!$incompleteProfile) {
    // If the profile is already complete, redirect them to the user dashboard
    header("Location: user_dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve and sanitize the user's input
    $title = filter_var($_POST["title"], FILTER_SANITIZE_STRING);
    $first_name = filter_var($_POST["first_name"], FILTER_SANITIZE_STRING);
    $last_name = filter_var($_POST["last_name"], FILTER_SANITIZE_STRING);

    // Update the user's profile in the database
    $userId = $_SESSION["user_unique_id"];
    $updateQuery = "UPDATE users SET title = :title, first_name = :first_name, last_name = :last_name  WHERE unique_identifier = :userId";
    $stmt = $pdo->prepare($updateQuery);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':userId', $userId);
    
    if ($stmt->execute()) {
        header("Location: user_dashboard.php");
        exit();
    } else {
        $error_message = "Error: Profile update failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile | EOD Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0a2540;
            --secondary: #00d4aa;
            --accent: #635bff;
            --light: #f6f9fc;
            --dark: #0a2540;
            --gray: #7c8ca1;
            --transition: all 0.3s ease;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --radius: 12px;
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
            background: linear-gradient(135deg, #f6f9fc 0%, #eef2f7 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3, h4, h5 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            line-height: 1.2;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo img {
            height: 48px;
            width: auto;
        }

        .logo-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 26px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.5px;
        }

        .logo-text span {
            color: var(--accent);
        }

        /* Main Content */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }

        .profile-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 50px;
            width: 100%;
            max-width: 600px;
            position: relative;
            overflow: hidden;
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--accent), var(--secondary));
        }

        .profile-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .profile-header h1 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .profile-header p {
            color: var(--gray);
            font-size: 1.1rem;
        }

        .profile-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(99, 91, 255, 0.1), rgba(0, 212, 170, 0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 32px;
            color: var(--accent);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary);
        }

        input, select {
            width: 100%;
            padding: 15px 20px;
            border: 1px solid #e1e5ee;
            border-radius: var(--radius);
            font-size: 16px;
            transition: var(--transition);
            background-color: white;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99, 91, 255, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 16px 32px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            font-size: 16px;
            width: 100%;
            margin-top: 10px;
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--accent), var(--secondary));
            color: white;
            box-shadow: 0 8px 25px rgba(99, 91, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(99, 91, 255, 0.4);
        }

        .error-message {
            background-color: #ffe6e6;
            color: #d63031;
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 25px;
            text-align: center;
            border-left: 4px solid #d63031;
        }

        .progress-container {
            margin-bottom: 30px;
        }

        .progress-bar {
            height: 6px;
            background-color: #e1e5ee;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent), var(--secondary));
            width: 50%;
            border-radius: 3px;
        }

        .progress-text {
            text-align: right;
            font-size: 14px;
            color: var(--gray);
            margin-top: 5px;
        }

        /* Footer */
        footer {
            background-color: var(--dark);
            color: white;
            padding: 40px 0 20px;
            margin-top: auto;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 30px;
        }

        .footer-column h3 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-column h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--secondary));
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--secondary);
        }

        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .profile-card {
                padding: 30px 25px;
            }
            
            .profile-header h1 {
                font-size: 2rem;
            }
            
            .profile-icon {
                width: 70px;
                height: 70px;
                font-size: 28px;
            }
        }

        @media (max-width: 576px) {
            .profile-card {
                padding: 25px 20px;
            }
            
            .profile-header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <div class="logo">
                <img src="Logo_final.png" alt="EOD Platform">
                <span class="logo-text">EOD<span>Platform</span></span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <div class="profile-card">
                <div class="profile-icon">
                    <i class="fas fa-user-edit"></i>
                </div>
                
                <div class="profile-header">
                    <h1>Complete Your Profile</h1>
                    <p>We need a few more details to personalize your experience</p>
                </div>
                
                <div class="progress-container">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <div class="progress-text">50% Complete</div>
                </div>
                
                <?php
                if (isset($error_message)) {
                    echo "<div class='error-message'><i class='fas fa-exclamation-circle'></i> $error_message</div>";
                }
                ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <select name="title" id="title" required>
                            <option value="">Select Title</option>
                            <option value="Mr">Mr</option>
                            <option value="Mrs./Ms.">Mrs./Ms.</option>
                            <option value="Dr.">Dr.</option>
                            <option value="Prof.">Prof.</option>
                            <option value="Ir.">Ir.</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" name="first_name" id="first_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" name="last_name" id="last_name" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-circle"></i>
                        <span>Complete Profile</span>
                    </button>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>About EOD Platform</h3>
                    <p>We're revolutionizing sustainable building design by creating a collaborative ecosystem that connects all stakeholders from design to occupancy.</p>
                </div>
                
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="guide.php">Step-by-step Guide</a></li>
                        <li><a href="#">Documentation</a></li>
                        <li><a href="LPD_Scheme_Guidelines.pdf">Lean Premise Design</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul class="footer-links">
                        <li><a href="user_login.php">End-user Login</a></li>
                        <li><a href="user_signup.php">Register</a></li>
                        <li><a href="admin_login.php">Professional Login</a></li>
                        <li><a href="browse_new_development.php">Browse Developments</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2023 EOD Platform. All rights reserved. | Building the Future, Together</p>
            </div>
        </div>
    </footer>

    <script>
        // Add some interactivity to the form
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input, select');
            
            inputs.forEach(input => {
                // Add focus effect
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    if (this.value === '') {
                        this.parentElement.classList.remove('focused');
                    }
                });
                
                // Add validation styling
                input.addEventListener('input', function() {
                    if (this.checkValidity()) {
                        this.style.borderColor = '#00d4aa';
                    } else {
                        this.style.borderColor = '#e1e5ee';
                    }
                });
            });
        });
    </script>
</body>
</html>