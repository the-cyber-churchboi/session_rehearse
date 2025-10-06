<?php
session_name("admin_session");
session_start();
require_once "config.php";

if (!isset($_SESSION['admin_id']) && $_SESSION['dashboard'] !== 'manager_dashboard') {
    // User is not logged in or not authorized for this dashboard
    header('Location: admin_login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get data from the AJAX request
    $userId = $_POST["userId"];
    $buildingName = $_POST["buildingName"];
    $inviteSentTimestamp = $_POST["inviteSentTimestamp"];

    try {
        // Check if the user exists
        $userExistsSql = "SELECT COUNT(*) FROM user_invites WHERE user_id = :userId";
        $userExistsStmt = $pdo->prepare($userExistsSql);
        $userExistsStmt->bindParam(':userId', $userId);
        $userExistsStmt->execute();
        $userExists = $userExistsStmt->fetchColumn();

        if ($userExists) {
            // Check if the user already has unanswered invites for the same building
            $checkSql = "SELECT COUNT(*) FROM user_invites WHERE user_id = :userId AND property_name = :buildingName AND invite_answered = 0";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->bindParam(':userId', $userId);
            $checkStmt->bindParam(':buildingName', $buildingName);
            $checkStmt->execute();
            $count = $checkStmt->fetchColumn();

            if ($count > 0) {
                // User has unanswered invites for this building
                echo "User already invited.";
                exit();
            }

            // Check if the user has previously answered an invite for the same building
            $updateSql = "UPDATE user_invites SET invite_answered = 0, invite_answered_timestamp = :inviteSentTimestamp
                         WHERE user_id = :userId AND property_name = :buildingName AND invite_answered = 1";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->bindParam(':userId', $userId);
            $updateStmt->bindParam(':buildingName', $buildingName);
            $updateStmt->bindParam(':inviteSentTimestamp', $inviteSentTimestamp);
            $updated = $updateStmt->execute();

            if ($updated) {
                echo "Invite record updated.";
            } 
        } else {
            // Insert a new invite record if the user has not answered an invite for this building
            $insertSql = "INSERT INTO user_invites (user_id, property_name, invite_sent_timestamp, invite_answered_timestamp)
                          VALUES (:userId, :buildingName, :inviteSentTimestamp, NULL)";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->bindParam(':userId', $userId);
            $insertStmt->bindParam(':buildingName', $buildingName);
            $insertStmt->bindParam(':inviteSentTimestamp', $inviteSentTimestamp);
            $inserted = $insertStmt->execute();

            if ($inserted) {
                echo "Invite sent to user.";
            } else {
                echo "Error inserting invite record.";
            }
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>