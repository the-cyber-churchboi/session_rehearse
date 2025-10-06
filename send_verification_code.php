<?php
require_once "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


function sendVerificationEmail($email, $verificationCode) {
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

    $subject = 'Email Verification for Admin Registration';
    $message = "Hello,\n\n";
    $message .= "Thank you for registering as an admin on our platform. To verify your email, please click the link below:\n\n";
    $message .= "Verification Link: http://localhost/building_work/verify.php?code=$verificationCode\n\n";
    $message .= "If you didn't register on our platform, please ignore this email.\n\n";
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

?>