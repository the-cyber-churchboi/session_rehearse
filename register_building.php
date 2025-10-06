<?php
session_name("user_session");
session_start();
require_once "config.php";

$userId = null;

if (isset($_GET["unique_identifier"])) {
    $userId = $_GET["unique_identifier"];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                echo '<div class="success-message">Property registration successful!</div>';
            } else {
                echo '<div class="error-message">Error: Property registration failed.</div>';
            }
        } catch (PDOException $e) {
            echo '<div class="error-message">Error: ' . $e->getMessage() . '</div>';
        }
        if (isset($_SESSION['followed_specific_feedback']) && $_SESSION['followed_specific_feedback'] === true) {
            header("Location: user_specific_feedback.php");
            exit();
        } 
    }
} else {
    header("Location: user_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Registration | EOD Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary: #0a2540;
            --secondary: #00d4aa;
            --accent: #635bff;
            --accent-light: #8a85ff;
            --light: #f6f9fc;
            --dark: #0a2540;
            --success: #00c9a7;
            --warning: #ffb800;
            --info: #14b8ff;
            --gray: #7c8ca1;
            --transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 25px 50px rgba(0, 0, 0, 0.15);
            --radius: 16px;
            --glass: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.18);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background: linear-gradient(135deg, #f6f9fc 0%, #f0f4f8 100%);
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            line-height: 1.2;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
        }

        /* Header Styles */
        header {
            background: linear-gradient(135deg, var(--primary) 0%, #1a365d 100%);
            color: white;
            padding: 20px 0;
            position: relative;
            overflow: hidden;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-section img {
            height: 50px;
            width: auto;
        }

        .logo-text {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 500;
            transition: var(--transition);
            border: 1px solid var(--glass-border);
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(-5px);
        }

        /* Main Content */
        main {
            padding: 60px 0;
            position: relative;
        }

        .registration-container {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            padding: 50px;
            width: 100%;
            max-width: 700px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        .registration-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--accent), var(--secondary));
        }

        .registration-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .registration-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(99, 91, 255, 0.1), rgba(138, 133, 255, 0.2));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            color: var(--accent);
        }

        .registration-header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .registration-header p {
            color: var(--gray);
            font-size: 1.1rem;
        }

        /* Form Styles */
        .registration-form {
            display: grid;
            gap: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group label i {
            color: var(--accent);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e8edf5;
            border-radius: var(--radius);
            font-size: 1rem;
            transition: var(--transition);
            background: white;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99, 91, 255, 0.1);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'><path fill='%23666' d='M2 0L0 2h4zm0 5L0 3h4z'/></svg>");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 12px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Submit Button */
        .submit-btn {
            background: linear-gradient(90deg, var(--accent), var(--secondary));
            color: white;
            border: none;
            padding: 16px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            display: block;
            width: 100%;
            margin-top: 20px;
            box-shadow: 0 8px 25px rgba(99, 91, 255, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(99, 91, 255, 0.4);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        /* Success/Error Messages */
        .success-message {
            background: rgba(0, 201, 167, 0.1);
            border: 1px solid rgba(0, 201, 167, 0.3);
            color: var(--success);
            padding: 15px 20px;
            border-radius: var(--radius);
            margin-bottom: 25px;
            text-align: center;
            font-weight: 500;
        }

        .error-message {
            background: rgba(255, 87, 87, 0.1);
            border: 1px solid rgba(255, 87, 87, 0.3);
            color: #d32f2f;
            padding: 15px 20px;
            border-radius: var(--radius);
            margin-bottom: 25px;
            text-align: center;
            font-weight: 500;
        }

        /* Loading States */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid var(--accent);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Floating Elements */
        .floating-shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            opacity: 0.05;
            z-index: -1;
        }

        .shape-1 {
            width: 200px;
            height: 200px;
            top: 10%;
            right: 10%;
        }

        .shape-2 {
            width: 150px;
            height: 150px;
            bottom: 20%;
            left: 10%;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .registration-container {
                padding: 40px 30px;
            }
            
            .registration-header h1 {
                font-size: 1.8rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 0 20px;
            }
            
            .registration-container {
                padding: 30px 20px;
            }
            
            .registration-header h1 {
                font-size: 1.6rem;
            }
            
            .registration-header p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="logo-section">
                <img src="Logo_final.png" alt="EOD Platform">
                <span class="logo-text">EOD Platform</span>
            </div>
            
            <a class="back-link" href="user_dashboard.php">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </header>

    <main>
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        
        <div class="container">
            <div class="registration-container">
                <div class="registration-header">
                    <div class="registration-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h1>Register New Property</h1>
                    <p>Add your property details to get started with the EOD Platform</p>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="success-message"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form class="registration-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?unique_identifier=' . urlencode($userId); ?>" method="post">
                    <div class="form-group">
                        <label for="district"><i class="fas fa-map-marker-alt"></i> District</label>
                        <select id="district" name="district" class="form-control" required>
                            <option value="">Select District</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="district_options"><i class="fas fa-map-marked-alt"></i> District Options</label>
                        <select id="district_options" name="district_options" class="form-control" required>
                            <option value="">Select District Options</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="property_name"><i class="fas fa-home"></i> Property Name</label>
                        <select id="property_name" name="property_name" class="form-control" required>
                            <option value="">Select Property Name</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="address"><i class="fas fa-address-card"></i> Address</label>
                        <input type="text" id="address" name="address" class="form-control" placeholder="Enter full address" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="flat"><i class="fas fa-door-open"></i> Flat Number</label>
                            <input type="text" id="flat" name="flat" class="form-control" placeholder="e.g., A1, 15B" required>
                        </div>

                        <div class="form-group">
                            <label for="floor"><i class="fas fa-layer-group"></i> Floor</label>
                            <input type="text" id="floor" name="floor" class="form-control" placeholder="e.g., 5, 12" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="street_name"><i class="fas fa-road"></i> Street Name</label>
                        <input type="text" id="street_name" name="street_name" class="form-control" placeholder="Enter street name" required>
                    </div>
                    
                    <!-- Hidden property ID field -->
                    <input type="hidden" id="property_id" name="property_id">
                    
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-building"></i> Register Property
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Function to populate the District dropdown with values from the database
            fetch('fetch_districts.php')
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

                if (selectedDistrict) {
                    districtOptionsSelect.classList.add('loading');
                    fetch('fetch_district_options.php?district=' + selectedDistrict)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(option => {
                                const optionElement = document.createElement("option");
                                optionElement.value = option;
                                optionElement.textContent = option;
                                districtOptionsSelect.appendChild(optionElement);
                            });
                            districtOptionsSelect.classList.remove('loading');
                        });
                }
            });

            // Function to populate the Property Names dropdown based on selected District and District Options
            document.getElementById("district_options").addEventListener("change", function () {
                const selectedDistrict = document.getElementById("district").value;
                const selectedDistrictOptions = document.getElementById("district_options").value;
                const propertyNameSelect = document.getElementById("property_name");
                const propertyStreetNo = document.getElementById("street_name");
                const propertyIdInput = document.getElementById("property_id");

                propertyNameSelect.innerHTML = "<option value=''>Select Property Name</option>"; // Clear Property Name dropdown

                if (selectedDistrict && selectedDistrictOptions) {
                    propertyNameSelect.classList.add('loading');
                    fetch('fetch_property_names.php?district=' + selectedDistrict + '&district_options=' + selectedDistrictOptions)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(property => {
                                const propertyElement = document.createElement("option");
                                propertyElement.value = property.property_name;
                                propertyElement.textContent = property.property_name;

                                // Attach the street_no data as a data attribute
                                propertyElement.setAttribute("data-street-name", property.street_name);
                                propertyElement.setAttribute("data-id", property.id)

                                propertyNameSelect.appendChild(propertyElement);
                            });
                            propertyNameSelect.classList.remove('loading');
                    });
                }
            });

            // Function to update the Street No and Property ID based on the selected Property Name
            document.getElementById("property_name").addEventListener("change", function () {
                const selectedProperty = this.options[this.selectedIndex];
                const selectedStreetNo = selectedProperty.getAttribute("data-street-name");
                const selectedPropertyId = selectedProperty.getAttribute("data-id")

                const propertyStreetNo = document.getElementById("street_name");
                const propertyIdInput = document.getElementById("property_id");

                // Set the selected Street No in the "street_no" input
                propertyStreetNo.value = selectedStreetNo || '';

                // Set the selected Property ID in the "property_id" input
                propertyIdInput.value = selectedPropertyId || '';
            });

        });
    </script>
</body>
</html>