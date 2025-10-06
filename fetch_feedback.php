<?php
// Add your database connection code here
session_name("admin_session");
session_start();
if (!isset($_SESSION['admin_id'])) {
    // User is not logged in or not authorized for this dashboard
    header('Location: admin_login.php');
    exit();
}

// Validate property_id
if (!isset($_GET['property_id']) || empty($_GET['property_id'])) {
    // Redirect back to admin_login.php if property_id is not provided
    header('Location: admin_login.php');
    exit();
}

require_once "config.php";

// Initialize $feedbackType with a default value (e.g., 'specific')
$feedbackType = 'specific';
$heading = 'Specific Feedback'; // Default heading

try {
    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Assuming you have a radio button with the name 'feedback_type'
        $feedbackType = $_POST['feedback_type'];

        if ($feedbackType === 'specific') {
            $heading = 'Specific Feedback';
            // Fetch specific feedbacks
            $stmt = $pdo->prepare("
                SELECT f.*, u.username
                FROM feedbacks f
                INNER JOIN users u ON f.user_id = u.unique_identifier
                WHERE f.property_id = :property_id
            ");
        } else {
            $heading = 'Browse New Development Feedback';
            // Fetch data from property_preferences
            $stmt = $pdo->prepare("
                SELECT pp.provisions, pp.kitchen_floor_finish, pp.kitchen_wall_finish, 
                       pp.kitchen_cabinet_color, pp.bathroom_floor_finish, pp.bathroom_wall_finish, 
                       pp.bathroom_cabinet_color, pp.opt_for_lpd
                FROM property_preferences pp
                WHERE pp.property_id = :property_id
            ");
        }

        $stmt->bindParam(':property_id', $_GET['property_id']);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Numbering the values in the provisions column
        foreach ($data as &$row) {
            if (isset($row['provisions'])) {
                $provisions = explode(', ', $row['provisions']);
                $numberedProvisions = array();
                foreach ($provisions as $key => $value) {
                    $numberedProvisions[] = ($key + 1) . '. ' . $value;
                }
                $row['provisions'] = implode('<br>', $numberedProvisions);
            }
        }

    } else {
        // Default behavior, fetch specific feedbacks
        $stmt = $pdo->prepare("
            SELECT f.*, u.username
            FROM feedbacks f
            INNER JOIN users u ON f.user_id = u.unique_identifier
            WHERE f.property_id = :property_id
        ");

        $stmt->bindParam(':property_id', $_GET['property_id']);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    // Handle database connection error
    echo "Error: " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Details</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #007bff;
            color: #fff;
            text-align: center;
            padding: 1rem;
            position: relative;
        }

        .branding {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .back-link {
            color: #fff;
            text-decoration: none;
            margin-right: 20px;
            font-size: 18px;
        }

        .header-img {
            max-width: 80px;
            max-height: 80px;
            margin-right: 20px;
        }

        .container {
            max-width: 800px; /* Set your desired maximum width */
            margin: 0 auto;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: #fff;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        button {
            padding: 10px;
            margin: 5px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .selected-button {
            background-color: red !important;
        }

        .container h2 {
            color: red; /* Set your desired heading color */
            font-size: 24px; /* Set your desired heading font size */
            margin-top: 10px; /* Adjust top margin as needed */
        }
    </style>
</head>
<body>
    <header>
        <div class="branding">
            <?php
                $backLink = ($_SESSION['dashboard'] === 'developer_dashboard') ? 'developer_dashboard.php' : 'manager_dashboard.php';
                echo "<a class='back-link' href='$backLink'>&#8678; Back</a>";
            ?>
            <img src="Logo_final.png" alt="Logo" class="header-img">
        </div>
        <h1>Feedback Details</h1>
    </header>

    <form method="post" style="text-align: center; margin-top: 20px;">
        <!-- Add a conditional class based on the selected feedback_type -->
        <button type="submit" name="feedback_type" value="specific" <?php echo ($feedbackType === 'specific') ? 'class="selected-button"' : ''; ?>>Specific Feedback</button>
        <button type="submit" name="feedback_type" value="new_development" <?php echo ($feedbackType === 'new_development') ? 'class="selected-button"' : ''; ?>>Browse New Development feedback</button>
    </form>

    <div class="container">
        <h2 style="text-align: center;"><?php echo $heading; ?></h2>
        <?php if (empty($data)): ?>
            <p style="text-align: center; margin-top: 20px;">No data available for this property.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <?php
                            // Display table headers based on the selected feedback type
                            $headers = ($feedbackType === 'new_development') ? array_keys($data[0]) : ['Username', 'Email', 'Text'];
                            foreach ($headers as $header) {
                                if ($header !== 'property_id' && $header !== 'id') {
                                    echo "<th>$header</th>";
                                }
                            }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php
                                // Display table data based on the selected feedback type
                                if ($feedbackType === 'new_development') {
                                    foreach ($row as $key => $value) {
                                        if ($key !== 'property_id' && $key !== 'id') {
                                            // Check if the column is one of the color columns
                                            if (in_array($key, ['kitchen_cabinet_color', 'bathroom_cabinet_color'])) {
                                                // Display color as a div with background color
                                                echo "<td><div style='width: 20px; height: 20px; background-color: $value; border: 1px solid #ddd;'></div></td>";
                                            } else {
                                                echo "<td>$value</td>";
                                            }
                                        }
                                    }
                                } else {
                                    echo "<td>{$row['username']}</td>";
                                    echo "<td>{$row['email']}</td>";
                                    echo "<td>{$row['text']}</td>";
                                }
                            ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>