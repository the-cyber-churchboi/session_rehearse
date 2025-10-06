<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Get the verification code from the URL
    $verificationCode = $_GET['code'];

    // Check if the verification code exists in the database
    $sql = "SELECT * FROM admin WHERE verification_code = :verification_code";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['verification_code' => $verificationCode]);

    // If the verification code exists, fetch the email and is_verified status
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $email = $row['email'];
        $isVerified = $row['is_verified'];
        $isRegistered = $row['is_registered'];

        // If the email is already verified, redirect to admin_registration.php with the email as a query parameter
        if ($isVerified == 1) {
            if ($isRegistered == 1) {
            header("Location: admin_login.php");
            exit;
        } else {
            header("Location: admin_registration.php?email=" . urlencode($email));
        }
        }

        // If the email is not verified, update the is_verified flag to 1 and redirect to admin_registration.php with the email as a query parameter
        $sql = "UPDATE admin SET is_verified = 1 WHERE verification_code = :verification_code";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['verification_code' => $verificationCode]);

        header("Location: admin_registration.php?email=" . urlencode($email));
        exit;
    } else {
        echo "<p>Invalid verification code.</p>";
    }
}
?>
