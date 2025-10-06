<?php
// Add your database connection code here
session_name("admin_session");
session_start();
if (!isset($_SESSION['admin_id'])) {
    // User is not logged in or not authorized for this dashboard
    header('Location: admin_login.php');
    exit();
}
require_once "config.php";

// Retrieve building_id from the URL parameter
$buildingId = $_GET['building_id'];

// Fetch property_name based on building_id
$stmtBuilding = $pdo->prepare("SELECT property_name FROM manager_property_registration WHERE property_id = ?");
$stmtBuilding->execute([$buildingId]);
$buildingData = $stmtBuilding->fetch(PDO::FETCH_ASSOC);

if (!$buildingData) {
    // Handle the case where building data is not found
    echo "Building data not found";
    exit;
}

$propertyName = $buildingData['property_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Building Feedback for <?php echo $propertyName; ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
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

        .action-icons {
            display: flex;
        }

        .action-icons span {
            cursor: pointer;
            margin-left: 20px;
            color: #fff;
        }

        h1 {
            margin-top: 10px;
        }

        .container {
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
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

        .list-container ul {
            padding: 0;
            margin: 0;
        }

        .list-container li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="branding">
                <?php
                $backLink = ($_SESSION['dashboard'] === 'developer_dashboard') ? 'developer_dashboard.php' : 'manager_dashboard.php';
                echo "<a class='back-link' href='$backLink'>&#8678; Back</a>";
                ?>
                <img src="Logo_final.png" alt="Logo" class="header-img">
            </div>
            <div class="action-icons">
                <span class="delete-icon" data-building-id="<?php echo $buildingId; ?>">&#128465; Delete Building</span>
                <span class="edit-icon" data-building-id="<?php echo $buildingId; ?>">&#9998; Edit Building</span>
            </div>
        </div>

        <h1>Building Feedback for <?php echo $propertyName; ?></h1>
    </header>
    <div class="container">
        <!-- Fetch feedback data with username based on the property_name using JOIN -->
        <?php
            $stmtFeedback = $pdo->prepare("SELECT b.*, u.username FROM building_evaluations b
                                        JOIN users u ON b.user_id = u.unique_identifier
                                        WHERE b.property_id = ?");
            $stmtFeedback->execute([$buildingId]);

            // Check if there is feedback data
            if ($stmtFeedback->rowCount() === 0) {
                // Display a message when there is no feedback data
                echo "<div class='container'>";
                echo "<h2>No Specific Feedback yet for this Building</h2>";
                echo "</div>";
            } else {
                // Add a table to display feedback data
        ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Scale of Renovation</th>
                            <th>Apartment Type</th>
                            <th>Apartment Amenities</th>
                            <th>Major Requirements</th>
                            <th>Apartment Defects</th>
                            <th>Most Sustainable Feature</th>
                            <th>Area for Review</th>
                            <th>Future Improvement Feature</th>
                            <th>Feedback</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            while ($row = $stmtFeedback->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                // Add other table cells based on your database columns
                                echo "<td>" . ($row['username'] ? $row['username'] : "Null") . "</td>";
                                echo "<td>" . ($row['scale_of_renovation'] ? $row['scale_of_renovation'] : "Null") . "</td>";
                                echo "<td>" . ($row['apartment_type'] ? $row['apartment_type'] : "Null") . "</td>";
                                echo "<td class='list-container'>";
                                if ($row['apartment_amenities']) {
                                    echo "<ul>";
                                    $amenitiesList = explode(',', $row['apartment_amenities']);
                                    foreach ($amenitiesList as $amenity) {
                                        echo "<li>" . $amenity . "</li>";
                                    }
                                    echo "</ul>";
                                } else {
                                    echo "Null";
                                }
                                echo "</td>";
                                echo "<td class='list-container'>";
                                if ($row['major_requirements']) {
                                    echo "<ul>";
                                    $requirementsList = explode(',', $row['major_requirements']);
                                    foreach ($requirementsList as $requirement) {
                                        echo "<li>" . $requirement . "</li>";
                                    }
                                    echo "</ul>";
                                } else {
                                    echo "Null";
                                }
                                echo "</td>";
                                echo "<td class='list-container'>";
                                if ($row['apartment_defects']) {
                                    echo "<ul>";
                                    $defectsList = explode(',', $row['apartment_defects']);
                                    foreach ($defectsList as $defect) {
                                        echo "<li>" . $defect . "</li>";
                                    }
                                    echo "</ul>";
                                } else {
                                    echo "Null";
                                }
                                echo "</td>";
                                echo "<td>" . ($row['most_sustainable_feature'] ? $row['most_sustainable_feature'] : "Null") . "</td>";
                                echo "<td>" . ($row['area_for_review'] ? $row['area_for_review'] : "Null") . "</td>";
                                echo "<td>" . ($row['future_improvement_feature'] ? $row['future_improvement_feature'] : "Null") . "</td>";
                                echo "<td>" . ($row['feedback'] ? $row['feedback'] : "Null") . "</td>";
                                echo "<td>" . ($row['created_at'] ? $row['created_at'] : "Null") . "</td>";
                                echo "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
        <?php
            }
        ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Add click event listeners to delete and edit icons
            const deleteIcon = document.querySelector('.delete-icon');
            const editIcon = document.querySelector('.edit-icon');

            deleteIcon.addEventListener('click', function () {
                // Handle delete action using AJAX
                const buildingId = this.getAttribute('data-building-id');
                
                // Confirm deletion
                if (confirm("Are you sure you want to delete this building?")) {
                    // Use Fetch API to send a DELETE request
                    fetch('delete_building.php', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ buildingId: buildingId }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Redirect back to the session dashboard.php
                            const dashboardType = '<?php echo $_SESSION['dashboard']; ?>';
                            const dashboardPage = (dashboardType === 'developer_dashboard') ? 'developer_dashboard.php' : 'manager_dashboard.php';
                            window.location.href = dashboardPage;
                        } else {
                            // Handle the case where deletion failed (optional)
                            console.log('Deletion failed:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                }
            });

            editIcon.addEventListener('click', function () {
                // Handle edit action, you can redirect to an edit page with building ID
                const buildingId = this.getAttribute('data-building-id');
                window.location.href = 'edit_building.php?building_id=' + buildingId;
            });
        });
    </script>
</body>
</html>
