<?php
session_name("admin_session");
session_start();
require_once "config.php";

if (!isset($_SESSION['admin_id']) && $_SESSION['dashboard'] !== 'admin_dashboard') {
    header('Location: admin_login.php');
    exit();
}

if (!isset($_GET['property_id'])) {
    echo "Error: Property ID not specified in the URL.";
    exit();
}

$property_id = $_GET['property_id'];

$fetchPropertyDataStmt = $pdo->prepare("SELECT district, district_options FROM property_registration WHERE unique_id = :property_id");
$fetchPropertyDataStmt->bindParam(':property_id', $property_id);
$fetchPropertyDataStmt->execute();
$propertyData = $fetchPropertyDataStmt->fetch(PDO::FETCH_ASSOC);

// Fetch image_path from propertyadvertisements table
$fetchImagePathStmt = $pdo->prepare("SELECT image_path FROM propertyadvertisements WHERE property_id = :property_id");
$fetchImagePathStmt->bindParam(':property_id', $property_id);
$fetchImagePathStmt->execute();
$imagePathData = $fetchImagePathStmt->fetch(PDO::FETCH_ASSOC);

// Check if property data is not found
if (!$propertyData || !$imagePathData) {
    echo "Error: Property not found.";
    exit();
} else {
    $district = $propertyData['district'];
    $district_options = $propertyData['district_options'];
    $image_path = $imagePathData['image_path'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $district = $_POST['district'];
    $district_options = $_POST['district_options'];
    $building_name = $_POST['building_name'];
    $address = $_POST['address'];
    $street_name = $_POST['street_name'];
    try {
        $fetchApartmentTypeStmt = $pdo->prepare("SELECT apartment_type FROM property_registration WHERE unique_id = :property_id");
        $fetchApartmentTypeStmt->bindParam(':property_id', $property_id);
        $fetchApartmentTypeStmt->execute();
        $apartmentTypeResult = $fetchApartmentTypeStmt->fetch(PDO::FETCH_ASSOC);

        if (!$apartmentTypeResult) {
            echo "Error: Apartment type not found for the selected property.";
        } else {
            $apartmentType = $apartmentTypeResult['apartment_type'];

            // Update the property_name in the property_registration table
            $updateStmt = $pdo->prepare("UPDATE property_registration SET property_name = :property_name WHERE unique_id = :property_id");
            $updateStmt->bindParam(':property_name', $building_name);
            $updateStmt->bindParam(':property_id', $property_id);

            if ($updateStmt->execute()) {
                // Property update successful, now proceed with the property registration
                $stmt = $pdo->prepare("INSERT INTO manager_property_registration (property_name, property_id, district, district_options, street_name, user_id, address, apartment_type) VALUES (:property_name, :property_id, :district, :district_options, :street_name, :user_id, :address, :apartment_type)");
                $stmt->bindParam(':property_name', $building_name);
                $stmt->bindParam(':property_id', $property_id);
                $stmt->bindParam(':district', $district);
                $stmt->bindParam(':district_options', $district_options);
                $stmt->bindParam(':street_name', $street_name);
                $stmt->bindParam(':user_id', $_SESSION["admin_unique_id"]);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':apartment_type', $apartmentType);

                if ($stmt->execute()) {
                    echo '<script type="text/javascript">
                            alert("Property Registration Successful.");
                            window.location = "manager_dashboard.php"; // Replace with the actual URL of your homepage
                        </script>';
                } else {
                    echo "Error: Property registration failed.";
                }
            } else {
                echo "Error: Property update failed.";
            }
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Property Registration Form</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <meta charset="utf-8">
        <style>
            body {
                font-family: 'Arial', sans-serif;
                background-color: #f0f0f0;
                text-align: center;
            }

            .container {
                max-width: 800px;
                margin: 0 auto;
                background: #fff;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            }

            header {
                display: flex;
                justify-content: flex-start;
                align-items: center;
            }

            .back-link {
                cursor: pointer;
                font-size: 40px;
                color: #3f72af;
                margin-right: 10px;
            }

            h2 {
                color: #3f72af;
            }

            label {
                display: block;
                margin-top: 10px;
                font-weight: bold;
                color: #333;
            }

            select,
            input,
            textarea {
                width: 100%;
                padding: 10px;
                margin-top: 5px;
                margin-bottom: 10px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }

            button[type="submit"] {
                background: #3f72af;
                color: #fff;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
            }

            input[type="submit"]:hover {
                background: #285d8e;
            }

            .header-img {
                max-width: 100px;
                display: block;
                margin: 0 auto;
            }

            .building-image {
                width: 500px;
                height: 300px;
                position: relative;
                overflow: hidden;
                margin-bottom: 20px;
            }

            .building-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                cursor: pointer;
            }

            .details-text {
                cursor: pointer;
                color: #3f72af;
                display: block;
                margin-top: 10px;
            }

            .modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
                justify-content: center;
                align-items: center;
            }

            .modal-content {
                background-color: #fff;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            }

            .close-btn {
                position: absolute;
                top: 10px;
                right: 10px;
                cursor: pointer;
            }

            .grid-container {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
                gap: 20px;
                justify-content: center;
            }

            .grid-item {
                max-width: 100%;
                height: auto;
                cursor: pointer;
            }

            .building-image {
                border: 6px solid red;
                box-sizing: border-box;
                padding: 0;
            }
        </style>
    </head>
    <body>
        <header>
            <a class="back-link" href="manager_dashboard.php">&#8678;</a>
            <img src="Logo_final.png" alt="Logo" class="header-img">
        </header>
        <div class="container">
            <h2>Register New Property</h2>
            <form action="<?php echo 'manager_register_property_1.php?property_id=' . (isset($property_id) ? $property_id : ''); ?>" method="post">
                <label for="district">District:</label>
                <input type="text" name="district" value="<?php echo $district; ?>" readonly required>

                <label for="district_options">District Options:</label>
                <input type="text" name="district_options" value="<?php echo $district_options; ?>" readonly required>
                
                <label>Building Image</label>
                <div class="grid-container"><img src="<?php echo $image_path; ?>" alt="Property Image" class="building-image"></div>

                <div class="building-details">
                    <label for="building_name">Building name:</label>
                    <input name="building_name">
                    <label for="address">Address:</label>
                    <input name="address">
                    <label for="street_name">Street name:</label>
                    <input name="street_name">
                </div>

                <button type="submit">Submit</button>
            </form>
        </div>
    </body>
</html>