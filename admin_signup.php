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
    $userUniqueID = generateUniqueID(1000001, 10000000);
    $email = $_POST["email"];
    $profession = $_POST['profession'];
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
            $stmt = $pdo->prepare("INSERT INTO admin_registration (username, password,  unique_identifier, email, profession)
                VALUES (:username, :password, :unique_identifier, :email, :profession)");

            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':unique_identifier', $userUniqueID);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':profession', $profession);

            if ($stmt->execute()) {
                header("refresh:3;url=admin_login.php");
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
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professionals Registration Form</title>
    <style>
        /* CSS styles go here */
        * {
            padding: 0;
            margin: 0;
        }
    
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        header {
            background-color: #007bff;
            color: #fff;
            padding: 20px;
        }

        .header-img {
            width: 80px;
            height: 70px;
        }

        .form-container {
            background-color: #fff;
            border-radius: 5px;
            margin-top: 20px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 400px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .row {
            display: flex;
            align-items: center;
        }

        .label {
            flex: 1;
            font-weight: bold;
            background-color: #06BD23; 
            color: #fff;
            padding: 10px;
            border-radius: 5px 0 0 5px;
            white-space: nowrap;
        }

        .input-container {
            flex: 2;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-left: 15px;
        }

        input[type="email"],
        input[type="text"],
        input[type="password"],
        input[type="radio"],
        input[type="checkbox"],
        select {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 0 5px 5px 0;
        }

        .toggle-button {
            background: transparent;
            border: none;
            cursor: pointer;
            color: #DC7777;
        }

        .error-message {
            color: red;
        }

        #submit-button {
            background-color: #DC7777;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        header {
            background-color: #DC7777;
            padding: 0;
            margin: 0;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .close-button-container {
            position: absolute;
            bottom: 10px;
            right: 10px;
        }

        .close-button {
            cursor: pointer;
            padding: 5px 10px;
            background-color: #f44336;
            color: white;
            font-size: 14px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .close-button:hover {
            background-color: #d32f2f;
        }

        main {
            display: flex;
            flex-direction: row;
            gap: 20px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .h1-container {
            padding-top: 200px;
            color: #fff;
        }

        .h1-container {
            margin-top: 20px;
            margin-left: 80px;
            border-right: 1px solid #009688;
            background-color: #DC7777;
        }

        main::-webkit-scrollbar {
            width: 0;
            height: 0;
        }

        main::-webkit-scrollbar-thumb {
            background: transparent;
        }

        .form-container::-webkit-scrollbar {
            width: 0;
            height: 0;
        }

        .form-container::-webkit-scrollbar-thumb {
            background: transparent;
        }
    </style>
</head>
<body>
    <header>
        <a href="index.html"><img src="Logo_final.png" alt="Logo" class="header-img"></a>
    </header>
    <main>
        <div class="h1-container">
            <h1 class="h1-text">Professionals Registration Form</h1>
        </div>
        <div class="form-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <div class="row">
                        <label for="email" class="label">Email:</label>
                        <div class="input-container">
                            <input type="email" name="email" id="email" required>
                        </div>
                    </div>
                    <?php if (!empty($errors['email'])) : ?>
                        <span class="error-message"><?php echo $errors['email']; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <div class="row">
                        <label for="username" class="label">Username:</label>
                        <div class="input-container">
                            <input type="text" name="username" id="username" required>
                        </div>
                    </div>
                    <?php if (!empty($errors['username'])) : ?>
                        <span class="error-message"><?php echo $errors['username']; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <div class="row">
                        <label for="password" class="label">Password:</label>
                        <div class="input-container">
                            <input type="password" name="password" id="password" required>
                            <button type="button" class="toggle-button" id="togglePassword">Show Password</button>
                        </div>
                    </div>
                    <?php if (!empty($errors['password'])) : ?>
                        <span class="error-message"><?php echo $errors['password']; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <div class="row">
                        <label for="confirm_password" class="label">Confirm Password:</label>
                        <div class="input-container">
                            <input type="password" name="confirm_password" id="confirmPassword" required>
                            <button type="button" class="toggle-button" id="toggleConfirmPassword">Show Password</button>
                        </div>
                    </div>
                    <?php if (!empty($errors['confirm_password'])) : ?>
                        <span class="error-message"><?php echo $errors['confirm_password']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <div class="row">
                        <label class="label" for="profession">Profession:</label>
                        <div class="input-container">
                        <select name="profession" required>
                            <option value="">Select Profession</option>
                            <option value="Architect">Architect</option>
                            <option value="Developer">Developer</option>
                            <option value="Property Manager">Property Manager</option>
                            <option value="Others">Others</option>
                        </select><br>
                    </div>
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
                        toggleButton.textContent = 'Hide Password';
                        } else {
                        inputField.type = 'password';
                        toggleButton.textContent = 'Show Password';
                        }
                    }
                </script>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="accept_terms" id="accept_terms">
                        I understand and accept the <a href="#" onclick="openModal('privacyPolicyModal')">Privacy Policy</a> and <a href="#" onclick="openModal('termsAndConditionsModal')">Terms and Conditions</a> involved in registering on this platform
                    </label>
                    <?php if (!empty($errors['accept_terms'])) : ?>
                        <span class="error-message"><?php echo $errors['accept_terms']; ?></span><br>
                    <?php endif; ?>
                </div>
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
                    function openModal(modalId) {
                        const modal = document.getElementById(modalId);
                        modal.style.display = "block";
                    }

                    function closeModal(modalId) {
                        const modal = document.getElementById(modalId);
                        modal.style.display = "none";
                    }
                </script>
                <button type="submit" id="submit-button">Submit</button>
            </form>
        </div>
    </main>
</body>
</html>
