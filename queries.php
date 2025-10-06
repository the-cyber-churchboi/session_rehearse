<?php
session_name("admin_session");
session_start();
// Include your database configuration
require_once('config.php');

if (!isset($_SESSION['admin_id']) && $_SESSION['dashboard'] !== 'developer_dashboard') {
    // User is not logged in or not authorized for this dashboard
    header('Location: admin_login.php');
    exit();
}
$userId = $_SESSION["admin_unique_id"];
// Fetch distinct usernames who have submitted queries
$query = "SELECT DISTINCT users.username
          FROM queries
          JOIN users ON queries.user_id = users.unique_identifier
          WHERE queries.assigned_to is NULL";
$result = $pdo->query($query);
$usernames = $result->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Queries</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        header {
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            text-align: left;
            width: 100%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header a {
            color: #fff;
            text-decoration: none;
            font-size: 20px;
            margin-right: 20px;
        }

        header .header-img {
            max-width: 50px;
            max-height: 50px;
            margin-right: 10px;
        }

        h2 {
            margin: 0;
        }

        main {
            margin-top: 70px; /* Adjusted to accommodate the header */
        }

        label {
            margin-bottom: 8px;
            font-weight: bold;
            color: #343a40;
        }

        select {
            padding: 10px;
            margin-bottom: 15px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ced4da;
            box-sizing: border-box;
            color: #495057;
            background-color: #fff; /* Added background color */
        }

        select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        #queryDetails,
        #adminDetails,
        #feedback {
            margin-top: 20px;
            text-align: center;
        }

        #description {
            margin-top: 15px;
            padding: 15px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            color: #495057;
        }

        p {
            color: #6c757d;
        }

        .no-queries {
            color: #dc3545; /* Red color for emphasis */
            font-size: 18px;
            margin-top: 20px;
        }

        button {
            padding: 10px 15px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }
        header {
            background-color: #007bff;
            color: #fff;
            text-align: center;
            padding: 1rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .branding {
            display: flex;
            align-items: center;
        }

        .back-link {
            color: #fff;
            text-decoration: none;
            margin-right: 20px;
        }

        .header-img {
            max-width: 80px;
            max-height: 80px;
            margin-right: 20px;
        }

    </style>
</head>
<body>
    <header class="header-content">
        <div class="branding">
            <a class='back-link' href='developer_dashboard.php'>&#8678; Back</a>
            <img src="Logo_final.png" alt="Logo" class="header-img">
        </div>
        <h2>Queries</h2>
    </header>
    
    <?php if (!empty($usernames)) { ?>
        <!-- Dropdown to select user -->
        <label for="userSelect">Select User:</label>
        <select name="userSelect" id="userSelect" onchange="getUserData()">
            <option value="">Select User</option>
            <?php
            foreach ($usernames as $username) {
                echo "<option value=\"$username\">$username</option>";
            }
            ?>
        </select>

        <!-- Container to display building, title, description, and admin selection -->
        <div id="queryDetails"></div>
        <label for="professionSelect">Select Profession:</label>
        <select name="professionSelect" id="professionSelect" onchange="getAdmins()">
            <option value="">Select Profession</option>
            <option value="Architect">Architect</option>
            <option value="Property Manager">Property Manager</option>
            <option value="Others">Others</option>
        </select>
        <div id="adminDetails"></div>
        <div id="feedback"></div>
        <button onclick="assignTask()">Assign</button>
    <?php } else { ?>
        <p class="no-queries">No Queries available.</p>
    <?php } ?>

    <script>
    function getUserData() {
        var selectedUser = document.getElementById("userSelect").value;

        if (selectedUser) {
            // Fetch distinct buildings submitted by the selected user
            fetch('fetch_buildings.php', {
                method: 'POST',
                body: new URLSearchParams({ username: selectedUser }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
            })
            .then(response => response.json())
            .then(data => {
                // Update the container with building selection
                var buildingSelect = '<label for="buildingSelect">Select Building:</label>' +
                                    '<select name="buildingSelect" id="buildingSelect" onchange="getQueryData()">' +
                                    '<option value="">Select Building</option>';
                
                // Populate the select element with buildings
                data.forEach(building => {
                    buildingSelect += '<option value="' + building.property_id + '">' + building.building + '</option>';
                });

                buildingSelect += '</select>';

                // Replace the existing building selection dropdown
                document.getElementById("queryDetails").innerHTML = buildingSelect;

                // Remove title selection and description
                resetTitleSelection();
            })
            .catch(error => console.error('Error:', error));
        } else {
            // If "Select User" is chosen, reset all selections and labels
            resetBuildingSelection();
            resetTitleSelection();
        }
    }

    function getQueryData() {
        var selectedUser = document.getElementById("userSelect").value;
        var selectedBuilding = document.getElementById("buildingSelect").value;

        if (selectedBuilding) {
            // Fetch query titles for the selected user and building
            fetch('fetch_titles.php', {
                method: 'POST',
                body: new URLSearchParams({ username: selectedUser, building: selectedBuilding }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
            })
            .then(response => response.json())
            .then(data => {
                // Check if the title selection dropdown already exists
                var existingTitleSelect = document.getElementById("titleSelect");
                if (!existingTitleSelect) {
                    // If it doesn't exist, create a new one
                    var titleSelect = '<label for="titleSelect">Select Query Title:</label>' +
                                    '<select name="titleSelect" id="titleSelect" onchange="getQueryDescription()">' +
                                    '<option value="">Select Title</option>';
                    
                    // Populate the select element with titles
                    data.forEach(title => {
                        titleSelect += '<option value="' + title + '">' + title + '</option>';
                    });

                    titleSelect += '</select>';

                    // Append title selection below the building selection
                    document.getElementById("queryDetails").insertAdjacentHTML('beforeend', titleSelect);

                    // Remove description
                    resetDescription();
                } else {
                    // If it exists, update its options
                    existingTitleSelect.innerHTML = '<option value="">Select Title</option>';
                    
                    data.forEach(title => {
                        existingTitleSelect.innerHTML += '<option value="' + title + '">' + title + '</option>';
                    });

                    // Remove description
                    resetDescription();
                }
            })
            .catch(error => console.error('Error:', error));
        } else {
            // If "Select Building" is chosen, reset title selection, description, and admin selection
            resetTitleSelection();
            resetDescription();
        }
    }

    function getQueryDescription() {
        var selectedUser = document.getElementById("userSelect").value;
        var selectedBuilding = document.getElementById("buildingSelect").value;
        var selectedTitle = document.getElementById("titleSelect").value;

        if (selectedTitle) {
            // Fetch query description for the selected user, building, and title
            fetch('fetch_description.php', {
                method: 'POST',
                body: new URLSearchParams({ username: selectedUser, building: selectedBuilding, title: selectedTitle }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
            })
            .then(response => response.json())
            .then(data => {
                // Check if the description element already exists
                var existingDescription = document.getElementById("description");
                if (!existingDescription) {
                    // If it doesn't exist, create a new one
                    var descriptionElement = '<div id="description">' + data + '</div>';
                    
                    // Append description below the title selection
                    document.getElementById("queryDetails").insertAdjacentHTML('beforeend', descriptionElement);
                } else {
                    // If it exists, update its content
                    existingDescription.innerHTML = data;
                }
            })
            .catch(error => console.error('Error:', error));
        } else {
            // If "Select Title" is chosen, reset description and admin selection
            resetDescription();
        }
    }

    // Helper function to reset description
    function resetDescription() {
        var description = document.getElementById("description");
        if (description) {
            description.remove();
        }
    }

    // Helper function to reset building selection, title selection, and description
    function resetBuildingSelection() {
        var buildingSelectLabel = document.querySelector('label[for="buildingSelect"]');
        if (buildingSelectLabel) {
            buildingSelectLabel.remove();
        }

        var buildingSelect = document.getElementById("buildingSelect");
        if (buildingSelect) {
            buildingSelect.remove();
        }

        resetTitleSelection();
    }

    // Helper function to reset title selection and description
    function resetTitleSelection() {
        var titleSelectLabel = document.querySelector('label[for="titleSelect"]');
        if (titleSelectLabel) {
            titleSelectLabel.remove();
        }

        var titleSelect = document.getElementById("titleSelect");
        if (titleSelect) {
            titleSelect.remove();
        }

        var description = document.getElementById("description");
        if (description) {
            description.remove();
        }
    }

    function getAdmins() {
        var selectedProfession = document.getElementById("professionSelect").value;

        if (selectedProfession) {
            // Fetch admins for the selected profession
            fetch('fetch_admins.php', {
                method: 'POST',
                body: new URLSearchParams({ profession: selectedProfession }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
            })
            .then(response => response.json())
            .then(data => {
                // Check if there are admins to display
                if (data.length > 0) {
                    // Update the container with admin selection
                    var adminSelect = '<label for="adminSelect">Select Admin:</label>' +
                                    '<select name="adminSelect" id="adminSelect">' +
                                    '<option value="">Select Admin</option>';
                    
                    // Populate the select element with admins
                    data.forEach(admin => {
                        adminSelect += '<option value="' + admin.unique_identifier + '">' + admin.full_name + '</option>';
                    });

                    adminSelect += '</select>';

                    // Update the existing container with admin selection
                    var queryDetails = document.getElementById("adminDetails");
                    queryDetails.innerHTML = adminSelect;
                } else {
                    var adminDetails = document.getElementById("adminDetails");
                    resetAdminSelection();
                    adminDetails.innerHTML = '<p>No admins registered for this profession.</p>';
                }
            })
            .catch(error => console.error('Error:', error));
        } else {
            resetAdminSelection();
        }
    }

    function resetAdminSelection() {
        var adminDetails = document.getElementById("adminDetails");
        if (adminDetails) {
            adminDetails.innerHTML = '';
        }
    }

    function assignTask(adminIdentifier) {
        var selectedUser = document.getElementById("userSelect").value;
        var selectedBuilding = document.getElementById("buildingSelect").value;
        var selectedTitle = document.getElementById("titleSelect").value;
        var selectedDescription = document.getElementById("description").innerText;
        var selectedAdmin = document.getElementById("adminSelect").value;
        var buildingName = document.getElementById("buildingSelect").options[buildingSelect.selectedIndex].text

        // Check if all required fields are selected
        if (selectedUser && selectedBuilding && selectedTitle && selectedDescription && selectedAdmin) {
            // Prepare data for sending to the server
            var formData = new FormData();
            formData.append('user', selectedUser);
            formData.append('building', buildingName);
            formData.append('property_id', selectedBuilding);
            formData.append('title', selectedTitle);
            formData.append('description', selectedDescription);
            formData.append('admin', selectedAdmin);
            formData.append('admin_identifier', adminIdentifier);  // Use admin_identifier directly

            // Send data to the server using fetch or another method
            fetch('assign_tasks.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                // Display feedback message
                var feedback = document.getElementById("feedback");
                feedback.innerHTML = data.message;

                // Reset selections and containers
                resetBuildingSelection();
                resetTitleSelection();
                resetDescription();
                resetAdminSelection();

                window.location.reload();
                
            })
            .catch(error => console.error('Error:', error));
        } else {
            // Display an error message if any required field is not selected
            var feedback = document.getElementById("feedback");
            feedback.innerHTML = '<p style="color: red;">Please select all required fields.</p>';
        }
    }
    </script>
</body>
</html>
