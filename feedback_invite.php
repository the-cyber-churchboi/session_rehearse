<?php
session_name("admin_session");
session_start();
require_once "config.php";

if (!isset($_SESSION['admin_id']) && $_SESSION['dashboard'] !== 'manager_dashboard') {
    // User is not logged in or not authorized for this dashboard
    header('Location: admin_login.php');
    exit();
}
$userId = $_SESSION["admin_unique_id"];

try {
    // Query to get the building names where the admin is registered
    $sql = "SELECT DISTINCT property_name FROM others_property_registration WHERE user_id = :userId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $buildingNames = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Invite User for Feedback</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f0f0;
            text-align: center;
        }

        header {
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        .header-img {
            max-width: 100px;
            display: block;
            margin: 0 auto;
            margin-bottom: 40px;
        }

        .back-link {
            cursor: pointer;
            font-size: 40px;
            color: #3f72af;
            margin-right: 10px;
        }

        .table-container {
            text-align: center; /* Center-align the contents horizontally */
        }

        .user-table {
            margin: 0 auto; /* Center-align the table within its container */
            border-collapse: collapse;
            width: 30%;
        }

        .user-table th, .user-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .user-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .user-table th {
            background-color: #4CAF50;
            color: white;
        }

        .userCheckbox {
            margin: 0;
            padding: 0;
        }

        .inviteButton {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 5px 10px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 12px;
            transition: background-color 0.3s; /* Add a smooth transition effect */
            cursor: pointer; /* Change cursor to a hand pointer on hover */
        }

        /* Hover effect: Change background color on hover */
        .inviteButton:hover {
            background-color: #45a049; /* New background color on hover */
        }

        #buildingSelector {
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* Style for the container of the building selector */
        .building-selector-container {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header>
        <a class="back-link" href="manager_dashboard.php">&#8678;</a>
        <img src="Logo_final.png" alt="Logo" class="header-img">
    </header>
    <div class="building-selector-container">
        <label for="buildingSelector">Select a Building:</label>
        <select id="buildingSelector">
            <option value="">Select a Building</option>
            <?php
            foreach ($buildingNames as $building) {
                echo "<option value='$building'>$building</option>";
            }
            ?>
        </select>
    </div>

    <div id="userList">
        <!-- User list will be displayed here via AJAX -->
    </div>

    <button id="inviteButton" class="inviteButton" style="display: none">Invite Selected Users</button>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const buildingSelector = document.getElementById("buildingSelector");
            const userList = document.getElementById("userList");
            const inviteButton = document.getElementById("inviteButton");

            buildingSelector.addEventListener("change", function() {
                const selectedBuilding = buildingSelector.value;

                if (selectedBuilding) {
                    const userId = <?php echo $userId; ?>;
                    const formData = new FormData();
                    formData.append("building", selectedBuilding);
                    formData.append("userId", userId);

                    fetch("get_users_by_building.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        userList.innerHTML = data;
                        const userCheckboxes = document.querySelectorAll('.userCheckbox');
                        if (userCheckboxes.length > 0) {
                            userList.innerHTML = data;
                            userList.style.display = "block"; // Show the user list
                            inviteButton.style.display = "inline-block"; // Show the button
                        } else {
                            // Hide the user list
                            inviteButton.style.display = "none"; // Hide the button
                            userList.innerHTML = "<h3>No users registered for this building....</h3>";
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                    });
                } else {
                    userList.style.display = "none"; // Hide the user list
                    inviteButton.style.display = "none"; // Hide the button
                }
            });

            inviteButton.addEventListener('click', function() {
                console.log("Invite button clicked");
                const userCheckboxes = document.querySelectorAll('.userCheckbox');
                const selectedUsers = Array.from(userCheckboxes)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => checkbox.getAttribute("data-user")); // Get the data-user attribute

                if (selectedUsers.length > 0) {
                    // Get the selected building name
                    const buildingName = buildingSelector.value;

                    // Iterate through selected users and insert invite records
                    selectedUsers.forEach(user => {
                        // Insert a new invite record for each selected user
                        const formData = new FormData();
                        formData.append("buildingName", buildingName);
                        formData.append("userId", user); // Use the selected user's unique_identifier
                        formData.append("inviteSentTimestamp", new Date().toISOString());

                        // Send an AJAX request to insert the invite record
                        fetch("insert_invite.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            alert(data);
                        })
                        .catch(error => {
                            console.error("Error:", error);
                        });
                    });
                } else {
                    alert("No users selected for invitation.");
                }
            });
        });
    </script>
</body>
</html>
