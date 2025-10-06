<?php
session_name("admin_session");
session_start();
if (!isset($_SESSION['admin_id'])) {
    // User is not logged in or not authorized for this dashboard
    header('Location: admin_login.php');
    exit();
}
require_once "config.php";

// Query to retrieve the required columns from the users table
$sql = "SELECT scale_of_renovation, apartment_type, amenities, major_requirements, defects FROM evaluation_responses";

// Prepare and execute the SQL query
$stmt = $pdo->prepare($sql);
$stmt->execute();

// Fetch all the rows as an associative array
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize associative arrays to store category counts
$majorRequirementsCount = [];
$apartmentDefectsCount = [];
$apartmentAmenitiesCount = [];

// Calculate the number of users with renovations >= 10%
$renovationsGreaterOrEqual10 = 0;
$renovationsLessThan10 = 0;
$totalUsers = count($data);

if ($totalUsers > 0){

    // Loop through the data and update counts
    foreach ($data as $row) {
        // Update counts for Major Requirements, Apartment Defects, and Apartment Amenities

        // Major Requirements
        $majorRequirements = explode(',', $row['major_requirements']);
        foreach ($majorRequirements as $majorRequirement) {
            $majorRequirement = trim($majorRequirement);
            if (!empty($majorRequirement)) {
                if ($majorRequirement != 'Others') {
                    if (!isset($majorRequirementsCount[$majorRequirement])) {
                        $majorRequirementsCount[$majorRequirement] = 1;
                    } else {
                        $majorRequirementsCount[$majorRequirement]++;
                    }
                }
            }
        }

        // Apartment Defects
        $apartmentDefects = explode(',', $row['defects']);
        foreach ($apartmentDefects as $apartmentDefect) {
            $apartmentDefect = trim($apartmentDefect);
            if (!empty($apartmentDefect)) {
                if ($apartmentDefect != 'Others') {
                    if (!isset($apartmentDefectsCount[$apartmentDefect])) {
                        $apartmentDefectsCount[$apartmentDefect] = 1;
                    } else {
                        $apartmentDefectsCount[$apartmentDefect]++;
                    }
                }
            }
        }

        // Apartment Amenities
        $apartmentAmenities = explode(',', $row['amenities']);
        foreach ($apartmentAmenities as $apartmentAmenity) {
            $apartmentAmenity = trim($apartmentAmenity);
            if (!empty($apartmentAmenity)) {
                if ($apartmentAmenity != 'Others') { // Change $majorRequirement to $apartmentAmenity
                    if (!isset($apartmentAmenitiesCount[$apartmentAmenity])) {
                        $apartmentAmenitiesCount[$apartmentAmenity] = 1;
                    } else {
                        $apartmentAmenitiesCount[$apartmentAmenity]++;
                    }
                }
            }
        }

        // Check for Scale of Renovation >= 10%
        $renovationValue = trim($row['scale_of_renovation']);
        if (
            $renovationValue === '11 - 30 %' ||
            $renovationValue === '31 - 50 %' ||
            $renovationValue === '51 - 70 %' ||
            $renovationValue === 'Above 70 %'
            ) {
                $renovationsGreaterOrEqual10++;
            } else if ($renovationValue === '< 10 %') {
                $renovationsLessThan10++;
            } 
    }
    
    // Calculate the percentages
    $percentageRenovationsGreaterOrEqual10 = ($renovationsGreaterOrEqual10 / $totalUsers) * 100;
    $percentageRenovationsLessThan10 = ($renovationsLessThan10 / $totalUsers) * 100;
} else {
    $percentageRenovationsGreaterOrEqual10 = 0;
    $percentageRenovationsLessThan10 = 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Information Evaluation System</title>
    <!-- Include Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        header {
            background-color: #66A7D8;
            color: #fff;
            text-align: center;
            padding: 20px 0;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h1 {
            font-size: 24px;
        }

        h2 {
            font-size: 18px;
            margin-top: 20px;
        }

        canvas {
            margin-top: 10px;
        }

        .header-img {
            width: 80px;
            height: 70px;
        }

        .back-link {
            cursor: pointer;
            font-size: 40px;
            color: #3f72af;
            margin-right: 10px;
        }

        header {
            background-color: white;
            color: black;
            text-align: center;
            padding: 20px 0;
            border: 2px solid black;
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <header>
        <h1>Information Evaluation System</h1>
        <img src="Logo_final.png" alt="Logo" class="header-img">
        <?php
            // Check the user's dashboard type and provide a back link accordingly
            if ($_SESSION['dashboard'] === 'admin_dashboard') {
                echo '<a class="back-link" href="admin_dashboard.php">&#8678; Back</a>';
            } elseif ($_SESSION['dashboard'] === 'developer_dashboard') {
                echo '<a class="back-link" href="developer_dashboard.php">&#8678; Back</a>';
            } elseif ($_SESSION['dashboard'] === 'manager_dashboard') {
                echo '<a class="back-link" href="manager_dashboard.php">&#8678; Back</a>';
            }
        ?>
    </header>

    <div class="container">
        <!-- Bar chart for major_requirements -->
        <h2>Major Requirements Count</h2>
        <canvas id="majorRequirementsChart" width="400" height="200"></canvas>

        <!-- Bar chart for apartment_defects -->
        <h2>Apartment Defects Count</h2>
        <canvas id="apartmentDefectsChart" width="400" height="200"></canvas>

        <!-- Bar chart for apartment_amenities -->
        <h2>Apartment Amenities Count</h2>
        <canvas id="apartmentAmenitiesChart" width="400" height="200"></canvas>

        <!-- Pie chart for scale_of_renovation -->
        <h2>Scale of Renovation</h2>
        <canvas id="scaleOfRenovationChart" width="400" height="200"></canvas>
    </div>


    <script>
        var majorRequirementsData = <?php echo json_encode(array_values($majorRequirementsCount)); ?>;
        var apartmentDefectsData = <?php echo json_encode(array_values($apartmentDefectsCount)); ?>;
        var apartmentAmenitiesData = <?php echo json_encode(array_values($apartmentAmenitiesCount)); ?>;
        

        // Get the canvas elements
        var majorRequirementsCanvas = document.getElementById("majorRequirementsChart");
        var apartmentDefectsCanvas = document.getElementById("apartmentDefectsChart");
        var apartmentAmenitiesCanvas = document.getElementById("apartmentAmenitiesChart");
        var scaleOfRenovationCanvas = document.getElementById("scaleOfRenovationChart");

        // Create bar charts using Chart.js for major_requirements, apartment_defects, and apartment_amenities
        var majorRequirementsChart = new Chart(majorRequirementsCanvas, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($majorRequirementsCount)); ?>,
                datasets: [{
                    label: 'Major Requirements Count',
                    data: majorRequirementsData,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Count'
                        },
                    }
                },
                animation: {
                    duration: 2000, // Animation duration in milliseconds
                    easing: 'easeInOutQuart' // Easing function for animation
                }
            }
        });

        var apartmentDefectsChart = new Chart(apartmentDefectsCanvas, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($apartmentDefectsCount)); ?>,
                datasets: [{
                    label: 'Apartment Defects Count',
                    data: apartmentDefectsData,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Count'
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });

        var apartmentAmenitiesChart = new Chart(apartmentAmenitiesCanvas, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($apartmentAmenitiesCount)); ?>,
                datasets: [{
                    label: 'Apartment Amenities Count',
                    data: apartmentAmenitiesData,
                    backgroundColor: 'rgba(255, 159, 64, 0.6)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Count'
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });

       // Create a pie chart using Chart.js for scale_of_renovation
        var scaleOfRenovationChart = new Chart(scaleOfRenovationCanvas, {
            type: 'pie',
            data: {
                labels: ['Scale of Renovation >= 10%', 'Scale of Renovation < 10%'],
                datasets: [{
                    data: [
                        <?php echo $percentageRenovationsGreaterOrEqual10; ?>,
                        <?php echo $percentageRenovationsLessThan10; ?>
                    ],
                    backgroundColor: ['rgba(75, 192, 192, 0.6)', 'rgba(255, 99, 132, 0.6)'],
                    borderWidth: 1
                }]
            },
            options: {
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    </script>
</body>
</html>
