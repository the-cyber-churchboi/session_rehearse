<?php
// Start the session
session_name("admin_pass");
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
// Assuming you have already established a database connection
require_once "config.php";

// Function to generate a reset token and update the token_expiration in the database
function generateResetToken($email) {
    global $pdo;
    $token = bin2hex(random_bytes(32)); // Generate a random token
    $expiration = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expiration set to 1 hour from now

    // Store the token and expiration in the database
    $sql = "UPDATE admin_registration SET reset_token = :token, token_expiration = :expiration WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'token' => $token,
        'expiration' => $expiration,
        'email' => $email
    ]);

    return $token;
}

// Function to send the reset token and return a JSON response


function sendPasswordResetLink($email, $resetToken) {
    require './PHPMailer/src/Exception.php';
    require './PHPMailer/src/PHPMailer.php';
    require './PHPMailer/src/SMTP.php';

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
    $mail->Port = 465; // Replace with your SMTP port (e.g., 587 for Gmail)
    $mail->CharSet = "utf-8"; // Set charset to utf8
    $mail->SMTPAuth = true;
    $mail->Username = 'ayoobad512@gmail.com'; // Replace with your email address
    $mail->Password = 'zkukazrbopoldifj'; // Replace with your email password
    $mail->SMTPSecure = 'ssl';
    $mail->setFrom('ayoobad512@gmail.com', 'Your Platform Team'); // Replace with your email and name
    $mail->addAddress($email); // Recipient's email address
    $mail->isHTML(true);

    $subject = 'Password Reset';
    $message = "Hello,\n\n";
    $message .= 'Click on the link below to reset your password: <br>'
    . '<a href="http://localhost/building_work/admin_reset_password.php?token=' . urlencode($resetToken) . '">Reset Password</a><br>';
    $message .= "If you didn't initiate the password reset on our platform, please ignore this email.\n\n";
    $message .= "Reset link expires in an hour time,\n";
    $message .= "Best regards,\n";
    $message .= "Your Platform Team";

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

function sendResetToken($email) {
    // Generate the reset token
    $resetToken = generateResetToken($email);

    // Send the password reset link
    sendPasswordResetLink($email, $resetToken);

    // Return a JSON response indicating success
    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];

    // Send the reset token and return a JSON response
    $result = sendResetToken($email);

    if ($result) {
        // Sending the reset token was successful
        echo json_encode(['success' => true]);
    } else {
        // Sending the reset token failed
        echo json_encode(['success' => false, 'error' => 'Failed to send reset token']);
    }
} else {
    // Handle any other cases or provide a default response
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

?>
