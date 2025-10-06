<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
// Include the config.php file to establish the database connection
require_once 'config.php';

// Function to perform SQL injection prevention
function sendEmail($email) {
    require './PHPMailer/src/Exception.php';
    require './PHPMailer/src/PHPMailer.php';
    require './PHPMailer/src/SMTP.php';

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'mail.eodplatform.com'; // Replace with your SMTP server
    $mail->Port = 465; // Replace with your SMTP port (e.g., 587 for Gmail)
    $mail->CharSet = "utf-8"; // Set charset to utf8
    $mail->SMTPAuth = true;
    $mail->Username = 'kayode@eodplatform.com'; // Replace with your email address
    $mail->Password = 'Olamilekan5491@12'; // Replace with your email password
    $mail->SMTPSecure = 'ssl';
    $mail->setFrom('kayode@eodplatform.com', 'EOD Platform Team'); // Replace with your email and name
    $mail->addAddress($email); // Recipient's email address
    $mail->isHTML(true);

    $subject = 'Registration Confirmation';
    $message = "Congratulations! Your registration was successful.";
    $message .= "EOD Platform Team";

    $mail->Subject = $subject;
    $mail->Body = $message;

    // Send the email
    if ($mail->send()) {
        // Email sent successfully
        return true;
    } else {
        // Email sending failed
        return false;
    }
}
function generateUniqueID($min, $max) {
    $uniqueID = rand($min, $max);

    try {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_registration WHERE unique_identifier = :uniqueID");
        $stmt->bindParam(':uniqueID', $uniqueID, PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            // ID is unique; you can insert it into the table
            return $uniqueID;
        } else {
            // ID already exists; generate a new one
            return generateUniqueID($min, $max);
        }
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
function sanitizeInput($input)
{
    return htmlspecialchars(stripslashes(trim($input)));
}

// Function to check password requirements
function validatePassword($password)
{
    // Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter,
    // one number, and one special character.
    $pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/';
    return preg_match($pattern, $password) === 1;
}

// Function to check if the username is unique
function isUsernameUnique($pdo, $username)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->bindValue(':username', $username);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    return $count === 0;
}

function isUsernameUnique1($pdo, $username)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_registration WHERE username = :username");
    $stmt->bindValue(':username', $username);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    return $count === 0;
}

function isEmailUnique($pdo, $email)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    return $count === 0;
}

function isEmailUnique1($pdo, $email)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_registration WHERE email = :email");
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    return $count === 0;
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array();

    // Personal Details
    $username = $_POST["username"];
    $password = $_POST['password']; // No need to sanitize password as it will be hashed later
    $confirm_password = $_POST['confirm_password']; // No need to sanitize confirm_password as it will be checked with password later
    $userUniqueID = generateUniqueID(1, 1000000);
    $email = $_POST["email"];
    $hongkong_resident = $_POST["hongkong_resident"];
    if ($hongkong_resident === "yes") {
        // Validate password requirements
        $passwordRequirementsPattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/';
        if (!preg_match($passwordRequirementsPattern, $password)) {
            $errors["password"] = "Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
        }

        // Check if the username is unique
        if (!isUsernameUnique($pdo, $username) || !isUsernameUnique1($pdo, $username)) {
            $errors["username"] = "Username is already taken. Please choose a different username.";
        }

        // Check if the email is unique
        if (!isEmailUnique($pdo, $email) || !isEmailUnique1($pdo, $email)) {
            $errors["email"] = "Email is already taken. Please choose a different email.";
        }

        // Check if password and confirm_password match
        if ($password !== $confirm_password) {
            $errors["confirm_password"] = "Passwords do not match";
        }

        if (empty($_POST["accept_terms"])) {
            $errors["accept_terms"] = "Please accept the terms and conditions before registering.";
        }

        // If there are any errors, display them
        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Prepare and execute the SQL query to insert data into the database using prepared statements
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, password,  unique_identifier, email)
                    VALUES (:username, :password, :unique_identifier, :email)");

                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':unique_identifier', $userUniqueID);
                $stmt->bindParam(':email', $email);

                if ($stmt->execute()) {
                    // Redirect to user_selection.php with the username as a parameter
                    header("Location: user_selection.php?username=" . urlencode($username));
                    echo"<h1>Registration Successful</h1>";
                    echo"<p>Congratulations! Your registration was successful.</p>";
                    sendEmail($email);
                    exit;
                } else {
                    echo "Error: Registration failed. Please try again.";
                }
            } catch (PDOException $e) {
                die("Error: " . $e->getMessage());
            }
        }
    } else {
        // Redirect to the homepage with a message
        echo '<script type="text/javascript">
                    alert("Registration is only for Hong Kong residents.");
                    window.location = "index.html"; // Replace with the actual URL of your homepage
                </script>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration | EOD Platform</title>
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

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
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
            height: 50px;
            width: auto;
        }

        .logo-text {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .home-link {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 12px 24px;
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
            padding: 60px 0;
            position: relative;
        }

        .registration-container {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            padding: 50px;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        .registration-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--accent), var(--secondary));
        }

        .registration-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .registration-icon {
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

        .registration-header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .registration-header p {
            color: var(--gray);
            font-size: 1.1rem;
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

        .input-container {
            position: relative;
        }

        .toggle-button {
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

        .toggle-button:hover {
            color: var(--accent);
        }

        /* Radio Button Styles */
        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .radio-button {
            display: none;
        }

        .radio-custom {
            width: 20px;
            height: 20px;
            border: 2px solid #e8edf5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .radio-custom::after {
            content: '';
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--accent);
            opacity: 0;
            transition: var(--transition);
        }

        .radio-button:checked + .radio-custom {
            border-color: var(--accent);
        }

        .radio-button:checked + .radio-custom::after {
            opacity: 1;
        }

        .radio-label {
            font-weight: 500;
            color: var(--dark);
        }

        /* Checkbox Styles */
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-top: 20px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: var(--accent);
            margin-top: 2px;
        }

        .checkbox-group label {
            font-size: 0.95rem;
            line-height: 1.5;
            color: var(--gray);
        }

        .checkbox-group a {
            color: var(--accent);
            font-weight: 500;
            transition: var(--transition);
        }

        .checkbox-group a:hover {
            color: var(--accent-light);
        }

        /* Error Message */
        .error-message {
            color: #d32f2f;
            font-size: 0.9rem;
            margin-top: 5px;
            display: block;
            font-weight: 500;
        }

        /* Login Link */
        .login-link-container {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e8edf5;
        }

        .login-link-container p {
            color: var(--gray);
            font-size: 0.95rem;
        }

        .login-link-container a {
            color: var(--accent);
            font-weight: 600;
            transition: var(--transition);
        }

        .login-link-container a:hover {
            color: var(--accent-light);
            text-decoration: underline;
        }

        /* Submit Button */
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
            margin-top: 30px;
            box-shadow: 0 8px 25px rgba(99, 91, 255, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(99, 91, 255, 0.4);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 40px;
            border-radius: var(--radius);
            width: 90%;
            max-width: 700px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
            box-shadow: var(--shadow-lg);
        }

        .modal-content h2 {
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 1.8rem;
        }

        .modal-content p {
            color: var(--gray);
            line-height: 1.7;
            margin-bottom: 15px;
        }

        .modal-content b {
            color: var(--primary);
        }

        .close {
            color: var(--gray);
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transition);
        }

        .close:hover {
            color: var(--accent);
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
            
            .registration-container {
                padding: 40px 30px;
            }
            
            .registration-header h1 {
                font-size: 1.8rem;
            }
            
            .modal-content {
                padding: 30px 25px;
                margin: 10% auto;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 0 20px;
            }
            
            .registration-container {
                padding: 30px 20px;
            }
            
            .registration-header h1 {
                font-size: 1.6rem;
            }
            
            .registration-header p {
                font-size: 1rem;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
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
        
        <div class="container">
            <div class="registration-container">
                <div class="registration-header">
                    <div class="registration-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h1>Create Your Account</h1>
                    <p>Join the EOD Platform community today</p>
                </div>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email address" required>
                        <?php if (!empty($errors['email'])) : ?>
                            <span class="error-message"><?php echo $errors['email']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" class="form-control" placeholder="Choose a username" required>
                        <?php if (!empty($errors['username'])) : ?>
                            <span class="error-message"><?php echo $errors['username']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-container">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Create a secure password" required>
                            <button type="button" class="toggle-button" id="togglePassword">Show</button>
                        </div>
                        <?php if (!empty($errors['password'])) : ?>
                            <span class="error-message"><?php echo $errors['password']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-container">
                            <input type="password" name="confirm_password" id="confirmPassword" class="form-control" placeholder="Confirm your password" required>
                            <button type="button" class="toggle-button" id="toggleConfirmPassword">Show</button>
                        </div>
                        <?php if (!empty($errors['confirm_password'])) : ?>
                            <span class="error-message"><?php echo $errors['confirm_password']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Are you living in Hong Kong?</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" class="radio-button" id="hongkong_resident_yes" name="hongkong_resident" value="yes" required>
                                <span class="radio-custom"></span>
                                <span class="radio-label">Yes</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" class="radio-button" id="hongkong_resident_no" name="hongkong_resident" value="no" required>
                                <span class="radio-custom"></span>
                                <span class="radio-label">No</span>
                            </label>
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" name="accept_terms" id="accept_terms" required>
                        <label>
                            I understand and accept the <a href="#" onclick="openModal('privacyPolicyModal')">Privacy Policy</a> and <a href="#" onclick="openModal('termsAndConditionsModal')">Terms and Conditions</a> involved in registering on this platform
                        </label>
                    </div>
                    <?php if (!empty($errors['accept_terms'])) : ?>
                        <span class="error-message"><?php echo $errors['accept_terms']; ?></span>
                    <?php endif; ?>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>

                    <div class="login-link-container">
                        <p>Already have an account? <a href="user_login.php">Sign in here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Modal for Privacy Policy -->
    <div id="privacyPolicyModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('privacyPolicyModal')">&times;</span>
            <h2>Privacy Policy</h2>
            <p>
                At our cloud-based EOD platform, we value your privacy and are committed to protecting your personal information. This privacy policy outlines how we collect, use, and store your data in accordance with our objective, key deliverables, and project aim. By using our platform, you consent to the practices described in this policy.<br><br>

                <b>Information Collection and Use:</b><br>

                We collect personal information from various stakeholders, including building developers, designers, local authorities, building managers, and end-users. This information may include but is not limited to:<br>

                1. Contact Information: Names, email addresses, phone numbers, and job titles of stakeholders involved in the building design and occupancy stages.<br>

                2. Building Details: Information related to the building development, including the location, design plans, sustainability strategies, and end-users' requirements.<br>

                3. Communication Data: All communication exchanged through our platform, including messages, feedback, and discussions between stakeholders.<br>

                4. Usage Information: We may gather information about how you navigate and interact with our platform to enhance user experience and improve our services.<br><br>

                <b>Data Handling and Security:</b><br>

                We are committed to maintaining the confidentiality, integrity, and security of the personal information we collect. We take appropriate measures to protect your data against unauthorized access, disclosure, alteration, or destruction.<br>

                1. Limited Access: Access to your personal information is restricted to authorized personnel who require it to fulfill their responsibilities within the scope of the project.<br>

                2. Data Encryption: We use encryption technology to protect data transmission and storage, ensuring that your information remains confidential.<br> 

                3. Data Retention: We retain your personal information for as long as it is necessary to achieve the objectives and deliverables of the project. Once the project is completed, we will securely dispose of your data in accordance with applicable laws and regulations.<br><br>

                <b>Information Sharing:</b><br>

                We may share your personal information with third-party service providers who assist us in operating our platform or providing related services. These service providers are contractually bound to protect your data and use it solely for the purposes outlined in this privacy policy.<br>

                We may also disclose your personal information if required by law or to enforce our rights and protect the safety of our platform, stakeholders, or others.<br><br>

                <b>Your Rights:</b><br> 

                As a user of our platform, you have certain rights regarding your personal information. These include the right to access, update, correct, or delete your data. If you have any questions or requests regarding your personal information, please contact us using the information provided at the end of this privacy policy<br><br>

                <b>Changes to the Privacy Policy:</b><br>

                We reserve the right to modify or update this privacy policy from time to time. Any changes will be effective immediately upon posting the updated policy on our platform. We encourage you to review this policy periodically to stay informed about how we collect, use, and protect your information.<br><br>

                <b>Contact Us:</b><br> 

                If you have any questions or concerns about this privacy policy or our data practices, please contact us at <a href="mailto:pfresearch@cpce-polyu.edu.hk">pfresearch@cpce-polyu.edu.hk</a><br>

                <b>The Privacy Policy is subject to change from time to time. Any changes made will be posted on this page.</b>
            </p>
        </div>
    </div>

    <!-- Modal for Terms and Conditions -->
    <div id="termsAndConditionsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('termsAndConditionsModal')">&times;</span>
            <h2>Terms and Conditions</h2>
            <p>
                <p>1. <b>Acceptance of Terms:</b> By using our cloud-based EOD platform, you agree to be bound by these Terms and Conditions.</p><br><br>

                <p>2. <b>Intellectual Property:</b> All intellectual property rights, including copyrights and trademarks, associated with our platform and its contents are owned by us or our licensors. You may not reproduce, modify, distribute, or use any copyrighted material without our explicit permission.</p><br><br>

                <p>3. <b>User Responsibilities:</b> As a user of our platform, you agree to: a. Provide accurate and up-to-date information during registration and throughout your use of the platform. b. Use the platform in compliance with applicable laws and regulations. c. Respect the privacy and confidentiality of other users' information and refrain from unauthorized access or sharing of such information. d. Refrain from engaging in any activity that may disrupt or interfere with the operation of the platform or compromise its security.</p><br><br>

                <p>4. <b>Data Usage and Privacy:</b> By using our platform, you consent to the collection, use, and processing of your personal information in accordance with our Privacy Policy. We will take reasonable measures to protect the confidentiality and security of your data according to the Personal Data (Privacy) Ordinance.</p>
            </p>
        </div>
    </div>

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
                toggleButton.textContent = 'Hide';
            } else {
                inputField.type = 'password';
                toggleButton.textContent = 'Show';
            }
        }

        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = "block";
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = "none";
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            });
        }
    </script>
</body>
</html>