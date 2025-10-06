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
// Include your database configuration
require_once('config.php');

// Get parameters from the URL
$status = isset($_GET['status']) ? $_GET['status'] : '';
$user = isset($_GET['user']) ? $_GET['user'] : '';

// Validate parameters
if (!in_array($status, ['inprogress', 'completed']) || empty($user)) {
    // Invalid parameters, redirect or show an error message
    header('Location: index.html'); // Redirect to the main page or handle the error
    exit();
}

// Fetch tasks based on status and user
$query = "SELECT * FROM tasks WHERE user_id = (SELECT unique_identifier FROM users WHERE username = :username) AND task_status = :status AND consultant_id = :admin";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':username', $user, PDO::PARAM_STR);
$stmt->bindValue(':status', ($status === 'inprogress' ? 'In Progress' : 'Completed'), PDO::PARAM_STR);
$stmt->bindValue(':admin', $adminId, PDO::PARAM_STR);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Tasks</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f8f8;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        h2 {
            color: #3498db;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            background-color: #fff;
            border-radius: 5px;
            margin: 10px 0;
            padding: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        button.complete-button {
            background-color: #2ecc71;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        button.complete-button:hover {
            background-color: #27ae60;
        }
    </style>
</head>
<body>

<h2>Tasks <?php echo ucfirst($status); ?> for <?php echo $user; ?></h2>
<?php if (!empty($tasks)) { ?>
<ul>
    <?php foreach ($tasks as $task) : ?>
        <li>
            <?php echo $task['building'] . ' - ' . $task['task_title'] . ' (' . $task['task_status'] . ')'; ?>
            
            <?php if ($status === 'inprogress' && $task['task_status'] === 'In Progress') : ?>
                <!-- Display "Complete" button for tasks in progress -->
                <button class="complete-button" onclick="completeTask(<?php echo $task['task_id']; ?>)">Complete</button>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
<?php } else { ?>
    <p>No Tasks in Progress.</p>
<?php } ?>
<script>
function completeTask(taskId) {
    // Make a request to complete the task
    fetch('complete_task.php', {
        method: 'POST',
        body: new URLSearchParams({ taskId: taskId }),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        window.location.reload();
    })
    .catch(error => console.error('Error:', error));
}
</script>

</body>
</html>
