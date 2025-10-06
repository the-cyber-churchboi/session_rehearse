<?php
session_name("admin_session");
session_start();
// Include your database configuration
require_once('config.php');

if (!isset($_SESSION['admin_id']) && $_SESSION['dashboard'] == 'developer_dashboard') {
    // User is not logged in or not authorized for this dashboard
    header('Location: admin_login.php');
    exit();
}

$adminId = $_SESSION["admin_unique_id"];

// Fetch distinct users who have tasks assigned to the admin
$query = "SELECT DISTINCT users.username
          FROM tasks
          JOIN users ON tasks.user_id = users.unique_identifier
          WHERE tasks.consultant_id = :admin_id AND tasks.task_status = 'Not Started'";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':admin_id', $adminId, PDO::PARAM_STR);
$stmt->execute();
$usernames = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch distinct users who have tasks in progress
$queryInProgress = "SELECT DISTINCT users.username
                    FROM tasks
                    JOIN users ON tasks.user_id = users.unique_identifier
                    WHERE tasks.consultant_id = :admin_id AND tasks.task_status = 'In Progress'";
$stmtInProgress = $pdo->prepare($queryInProgress);
$stmtInProgress->bindParam(':admin_id', $adminId, PDO::PARAM_STR);
$stmtInProgress->execute();
$usernamesInProgress = $stmtInProgress->fetchAll(PDO::FETCH_COLUMN);

// Fetch distinct users who have completed tasks
$queryCompleted = "SELECT DISTINCT users.username
                    FROM tasks
                    JOIN users ON tasks.user_id = users.unique_identifier
                    WHERE tasks.consultant_id = :admin_id AND tasks.task_status = 'Completed'";
$stmtCompleted = $pdo->prepare($queryCompleted);
$stmtCompleted->bindParam(':admin_id', $adminId, PDO::PARAM_STR);
$stmtCompleted->execute();
$usernamesCompleted = $stmtCompleted->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        section {
            margin: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        label {
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
        }

        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background-color: #4caf50;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        h2 {
            color: #333;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        ul li {
            margin-bottom: 10px;
        }

        a {
            text-decoration: none;
            color: #007BFF;
        }

        a:hover {
            text-decoration: underline;
        }

        #feedback {
            margin-top: 20px;
            color: green;
        }

        #description {
            margin-top: 20px;
            background-color: #ffeeba; /* Choose your preferred background color */
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }

        #description {
            margin-top: 20px;
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
            <?php
                $backLink = ($_SESSION['dashboard'] === 'admin_dashboard') ? 'admin_dashboard.php' : 'manager_dashboard.php';
                echo "<a class='back-link' href='$backLink'>&#8678; Back</a>";
            ?>
            <img src="Logo_final.png" alt="Logo" class="header-img">
        </div>
        <h2>Tasks</h2>
    </header>

    <section>
        <?php if (!empty($usernames)) { ?>
            <!-- Dropdown to select user -->
            <label for="userSelect">Select User:</label>
            <select name="userSelect" id="userSelect" onchange="getUserTasks()">
                <option value="">Select User</option>
                <?php
                foreach ($usernames as $username) {
                    echo "<option value=\"$username\">$username</option>";
                }
                ?>
            </select>
            

            <!-- Container to display building, title, and description -->
            <div id="taskDetails"></div>
            <div id="feedback"></div>
            <button onclick="startTask()">Start Task</button>
        <?php } else { ?>
            <p>No Tasks available.</p>
        <?php } ?>
    </section>
    <!-- Section to view tasks in progress -->
    <section>
        <h2>Tasks In Progress</h2>
        <?php if (!empty($usernamesInProgress)) { ?>
            <ul>
                <?php foreach ($usernamesInProgress as $usernameInProgress) : ?>
                    <li><a href="view_tasks.php?status=inprogress&user=<?php echo $usernameInProgress; ?>"><?php echo $usernameInProgress; ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php } else { ?>
            <p>No Task in Progress.</p>
        <?php } ?>
    </section>

    <!-- Section to view completed tasks -->
    <section>
        <h2>Completed Tasks</h2>
        <?php if (!empty($usernamesCompleted)) { ?>
            <ul>
                <?php foreach ($usernamesCompleted as $usernameCompleted) : ?>
                    <li><a href="view_tasks.php?status=completed&user=<?php echo $usernameCompleted; ?>"><?php echo $usernameCompleted; ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php } else { ?>
            <p>No Completed Tasks.</p>
        <?php } ?>
    </section>
    <script>
    var userId = <?php echo json_encode($adminId); ?>;
    function getUserTasks() {
        var selectedUser = document.getElementById("userSelect").value;

        if (selectedUser) {
            // Fetch buildings, titles, and descriptions assigned to the admin for the selected user
            fetch('fetch_admin_task_buildings.php', {
                method: 'POST',
                body: new URLSearchParams({ username: selectedUser, userId: userId }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
            })
            .then(response => response.json())
            .then(data => {
                // Update the container with building, title, and description
                var taskDetails = '<label for="buildingSelect">Select Building:</label>' +
                                '<select name="buildingSelect" id="buildingSelect" onchange="getTitleAndDescription()">' +
                                '<option value="">Select Building</option>';
                
                // Populate the select element with buildings
                data.forEach(building => {
                    taskDetails += '<option value="' + building.property_id + '">' + building.building + '</option>';
                });

                taskDetails += '</select>';

                // Append building selection below the user selection
                document.getElementById("taskDetails").innerHTML = taskDetails;
            })
            .catch(error => console.error('Error:', error));
        } else {
            // If "Select User" is chosen, reset all selections and labels
            resetBuildingSelection();
        }
    }

    function getTitleAndDescription() {
        var selectedUser = document.getElementById("userSelect").value;
        var selectedBuilding = document.getElementById("buildingSelect").value;

        if (selectedBuilding) {
            // Fetch titles and descriptions assigned to the admin for the selected user and building
            fetch('fetch_admin_task_titles.php', {
                method: 'POST',
                body: new URLSearchParams({ username: selectedUser, building: selectedBuilding, userId: userId }),
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
                                    '<select name="titleSelect" id="titleSelect" onchange="getDescription()">' +
                                    '<option value="">Select Title</option>';
                    
                    // Populate the select element with titles
                    data.titles.forEach(title => {
                        titleSelect += '<option value="' + title + '">' + title + '</option>';
                    });

                    titleSelect += '</select>';

                    // Append title selection below the building selection
                    document.getElementById("taskDetails").insertAdjacentHTML('beforeend', titleSelect);

                    // Remove description
                    resetDescription();
                } else {
                    // If it exists, update its options
                    existingTitleSelect.innerHTML = '<option value="">Select Title</option>';
                    
                    data.titles.forEach(title => {
                        existingTitleSelect.innerHTML += '<option value="' + title + '">' + title + '</option>';
                    });

                    // Remove description
                    resetDescription();
                }
            })
            .catch(error => console.error('Error:', error));
        } else {
            // If "Select Building" is chosen, reset title selection and description
            resetTitleSelection();
            resetDescription();
        }
    }

    function getDescription() {
        var selectedUser = document.getElementById("userSelect").value;
        var selectedBuilding = document.getElementById("buildingSelect").value;
        var selectedTitle = document.getElementById("titleSelect").value;

        if (selectedTitle) {
            // Fetch description assigned to the admin for the selected user, building, and title
            fetch('fetch_admin_task_descriptions.php', {
                method: 'POST',
                body: new URLSearchParams({ username: selectedUser, building: selectedBuilding, title: selectedTitle, userId: userId }),
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
                    document.getElementById("taskDetails").insertAdjacentHTML('beforeend', descriptionElement);
                } else {
                    // If it exists, update its content
                    existingDescription.innerHTML = data;
                }
            })
            .catch(error => console.error('Error:', error));
        } else {
            // If "Select Title" is chosen, reset description
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

    function startTask() {
        var selectedUser = document.getElementById("userSelect").value;
        var selectedBuilding = document.getElementById("buildingSelect").value;
        var selectedTitle = document.getElementById("titleSelect").value;
        var description = document.getElementById("description").innerText;
        var buildingName = document.getElementById("buildingSelect").options[buildingSelect.selectedIndex].text

        if (selectedTitle) {
            // Make a request to start the task
            fetch('start_task.php', {
                method: 'POST',
                body: new URLSearchParams({
                    username: selectedUser,
                    building: selectedBuilding,
                    title: selectedTitle,
                    userId: userId,
                    description: description,
                    buildingName: buildingName
                }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
            })
            .then(response => response.json())
            .then(data => {
                // Display feedback message
                var feedback = document.getElementById("feedback");
                feedback.innerHTML = data.message;
                
                resetBuildingSelection();
                resetTitleSelection();
                resetDescription();

                window.location.reload();
            })
            .catch(error => console.error('Error:', error));
        } else {
            var feedback = document.getElementById("feedback");
            feedback.innerHTML = '<p style="color: red;">Please select all required fields.</p>';
        }
    }
    </script>
</body>
</html>
