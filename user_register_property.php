<?php
session_name("user_session");
session_start();
require_once "config.php";

$userId = null;

// Check if unique_identifier is provided in the URL
if (isset($_GET["unique_identifier"])) {
    $userId = $_GET["unique_identifier"];
} else {
    if (!isset($_SESSION["user_unique_id"])) {
        header("Location: user_login.php");
        exit();
    }

    $userId = $_SESSION["user_unique_id"];
}

// Check if user information is filled (tile, first_name, and last_name)
try {
    $stmt = $pdo->prepare("SELECT title, first_name, last_name FROM users WHERE unique_identifier = :user_id");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($user['title']) || empty($user['first_name']) || empty($user['last_name'])) {
        // Show alert and redirect to complete_profile.php
        echo '<script type="text/javascript">
                if (confirm("You have to complete your profile registration before using the property registration function. Click OK to complete your profile.")) {
                    window.location = "complete_profile.php";
                } else {
                    window.location = "user_dashboard.php"; // Redirect to dashboard or another page
                }
            </script>';
        exit();
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $district = $_POST['district'];
    $district_options = $_POST['district_options'];
    $property_name = $_POST['property_name'];
    $address = $_POST['address'];
    $flat = $_POST["flat"];
    $floor = $_POST["floor"];
    $street_name = $_POST['street_name'];
    $property_id = $_POST["property_id"];

    // Connect to the database
    try {
        // Prepare and execute the SQL statement to insert the data
        $stmt = $pdo->prepare("INSERT INTO user_property_registration (property_name, property_id, district, district_options, flat, floor, street_name, user_id, address) VALUES (:property_name, :property_id, :district, :district_options, :flat, :floor, :street_name, :user_id, :address)");
        $stmt->bindParam(':property_name', $property_name);
        $stmt->bindParam(':property_id', $property_id);
        $stmt->bindParam(':district', $district);
        $stmt->bindParam(':district_options', $district_options);
        $stmt->bindParam(':flat', $flat);
        $stmt->bindParam(':floor', $floor);
        $stmt->bindParam(':street_name', $street_name);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':address', $address);

        if ($stmt->execute()) {
            // Data was successfully inserted
            echo '<script type="text/javascript">
                    alert("Property Registration Successful.");
                    window.location = "user_dashboard.php"; // Replace with the actual URL of your homepage
                </script>';
        } else {
            echo "Error: Property registration failed.";
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
            max-width: 500px;
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
        select, input, textarea {
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
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Function to populate the District dropdown with values from the database
            fetch('fetch_districts_1.php')
                .then(response => response.json())
                .then(data => {
                    const districtSelect = document.getElementById("district");
                    data.forEach(district => {
                        const option = document.createElement("option");
                        option.value = district;
                        option.textContent = district;
                        districtSelect.appendChild(option);
                    });
            });

            // Function to populate the District Options dropdown based on selected District
            document.getElementById("district").addEventListener("change", function () {
                const selectedDistrict = this.value;
                const districtOptionsSelect = document.getElementById("district_options");
                districtOptionsSelect.innerHTML = "<option value=''>Select District Options</option>"; // Clear District Options dropdown

                fetch('fetch_district_options_1.php?district=' + selectedDistrict)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(option => {
                            const optionElement = document.createElement("option");
                            optionElement.value = option;
                            optionElement.textContent = option;
                            districtOptionsSelect.appendChild(optionElement);
                        });
                    });
            });

            // Function to populate the Property Names dropdown based on selected District and District Options
            document.getElementById("district_options").addEventListener("change", function () {
                const selectedDistrict = document.getElementById("district").value;
                const selectedDistrictOptions = document.getElementById("district_options").value;
                const propertyNameSelect = document.getElementById("property_name");
                const propertyStreetNo = document.getElementById("street_name");
                const propertyIdInput = document.getElementById("property_id");
                const propertyStreet = document.getElementById("address");

                propertyNameSelect.innerHTML = "<option value=''>Select Property Name</option>"; // Clear Property Name dropdown

                fetch('fetch_property_names.php?district=' + selectedDistrict + '&district_options=' + selectedDistrictOptions)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(property => {
                            const propertyElement = document.createElement("option");
                            propertyElement.value = property.property_name;
                            propertyElement.textContent = property.property_name;

                            // Attach the street_no data as a data attribute
                            propertyElement.setAttribute("data-street-name", property.street_name);
                            propertyElement.setAttribute("data-id", property.property_id)
                            propertyElement.setAttribute("data-address", property.address)

                            propertyNameSelect.appendChild(propertyElement);
                        });
                });
            });

            // Function to update the Street No and Property ID based on the selected Property Name
            document.getElementById("property_name").addEventListener("change", function () {
                const selectedProperty = this.options[this.selectedIndex];
                const selectedStreetNo = selectedProperty.getAttribute("data-street-name");
                const selectedPropertyId =selectedProperty.getAttribute("data-id");
                const selectedpropertyStreet =selectedProperty.getAttribute("data-address");

                const propertyStreetNo = document.getElementById("street_name");
                const propertyIdInput = document.getElementById("property_id");
                const propertyStreet = document.getElementById("address");

                // Set the selected Street No in the "street_no" input
                propertyStreetNo.value = selectedStreetNo;

                // Set the selected Property ID in the "property_id" input
                propertyIdInput.value = selectedPropertyId;

                propertyStreet.value =selectedpropertyStreet;
            });

        });
    </script>
</head>
<body>
    <header>
        <a class="back-link" href="user_dashboard.php">&#8678;</a>
        <img src="Logo_final.png" alt="Logo" class="header-img">
    </header>
    <div class="container">
        <h2>Select New Property</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="district">District:</label>
            <select id="district" name="district">
                <option value="">Select District</option>
            </select>

            <label for="district_options">District Options:</label>
            <select id="district_options" name="district_options">
                <option value="">Select District Options</option>
            </select>

            <label for="property_name">Property name:</label>
            <select id="property_name" name="property_name">
                <option value="">Select Property Name</option>
            </select>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address">

            <label for="flat">Flat:</label>
            <input type="text" id="flat" name="flat">

            <label for="floor">Floor:</label>
            <input type="text" id="floor" name="floor">
            
            <label for="street_name">Street Name:</label>
            <input type="text" id="street_name" name="street_name">
            
            <!-- Add a hidden input field to store the selected property ID -->
            <input type="hidden" id="property_id" name="property_id">
            
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
