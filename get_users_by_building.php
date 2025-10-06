<?php
require_once('config.php'); // Include your database configuration

if (isset($_POST['building']) && isset($_POST['userId'])) {
    $building = $_POST['building'];

    try {
        // Query to get user IDs and usernames in the selected building from user_property_registration
        $sql = "SELECT unique_identifier, username FROM users 
                WHERE unique_identifier IN (SELECT user_id FROM user_property_registration WHERE property_name = :building)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':building', $building);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Generate a table of users with checkboxes and hidden "Invite" buttons
        $html = "<div class='table-container'>";
        $html .= "<table class='user-table'>";
        $html .= "<tr>";
        $html .= "<th>Select</th>"; // Added a "Select" header
        $html .= "<th>Users</th>";
        $html .= "</tr>";

        foreach ($users as $user) {
            $userId = $user['unique_identifier'];
            $userName = $user['username'];

            $html .= "<tr>";
            $html .= "<td><input type='checkbox' class='userCheckbox' data-user='$userId' value='$userName'></td>";
            $html .= "<td>$userName</td>";
            $html .= "</tr>";
        }

        $html .= "</table>";
        $html .= "</div>";
        echo $html;

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
