<?php
session_name("admin_session");
session_start();
require_once "config.php";

if (!isset($_SESSION['admin_id']) && $_SESSION['dashboard'] !== 'developer_dashboard') {
    // User is not logged in or not authorized for this dashboard
    header('Location: admin_login.php');
    exit();
}
$userId = $_SESSION["admin_unique_id"];

$query = "SELECT title, first_name, last_name FROM admin_registration WHERE unique_identifier = :userId";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':userId', $userId);
$stmt->execute();
$userProfile = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if any of the profile fields are empty or null
$incompleteProfile = false;
foreach ($userProfile as $field) {
    if (empty($field) || is_null($field)) {
        $incompleteProfile = true;
        break;
    }
}


// Check if the user's profile is already complete
if (!$incompleteProfile) {
    // If the profile is already complete, redirect them to the user dashboard
    header("Location: developer_dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve and sanitize the user's input
    $title = filter_var($_POST["title"], FILTER_SANITIZE_STRING);
    $first_name = filter_var($_POST["first_name"], FILTER_SANITIZE_STRING);
    $last_name = filter_var($_POST["last_name"], FILTER_SANITIZE_STRING);

    // Update the user's profile in the database
    $updateQuery = "UPDATE admin_registration SET title = :title, first_name = :first_name, last_name = :last_name WHERE unique_identifier = :userId";
    $stmt = $pdo->prepare($updateQuery);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':userId', $userId);
    
    if ($stmt->execute()) {
        header("Location: developer_dashboard.php");
        exit();
    } else {
        $error_message = "Error: Profile update failed.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complete Your Admin Profile</title>
    <style>
        body {
            background-color: #f2f2f2;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 400px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            margin-top: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            color: #333;
        }

        select, input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-size: 16px;
        }

        select {
            background-color: #f9f9f9;
        }

        .btn-primary {
            background-color: #3ca614;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #2a8000;
        }

        .error-message {
            background-color: #ff3333;
            color: #fff;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 10px;
        }

        .header-img {
            max-width: 100px;
            display: block;
            margin: 0 auto;
        }

        header {
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }
    </style>
</head>
<body>
    <header>
        <img src="Logo_final.png" alt="Logo" class="header-img">
    </header>
    <div class="container">
        <h1>Complete Your Admin Profile</h1>
        <?php
        if (isset($error_message)) {
            echo "<div class='error-message'>$error_message</div>";
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <div class="form-group">
                <label for="title">Title:</label>
                <select name="title" required>
                    <option value="">Select Title</option>
                    <option value="Mr">Mr</option>
                    <option value="Mrs./Ms.">Mrs./Ms.</option>
                    <option value="Dr.">Dr.</option>
                    <option value="Prof.">Prof.</option>
                    <option value="Ir.">Ir.</option>
                </select>
            </div>
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" name="first_name" id="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" name="last_name" id="last_name" required>
            </div>
            <button type="submit" class="btn btn-primary">Complete Profile</button>
        </form>
    </div>
</body>
</html>
