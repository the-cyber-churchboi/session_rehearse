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
function generateUniqueID() {
    try {
        global $pdo;

        // Generate a unique ID based on timestamp and random number
        $uniqueID = mt_rand(1, 99999999);

        // Check if the ID already exists in the database
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM property_registration WHERE unique_id = :uniqueID");
        $stmt->bindParam(':uniqueID', $uniqueID, PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        // If the ID is not unique, generate a new one
        while ($count > 0) {
            $uniqueID = mt_rand(1, 99999999);
            $stmt->bindParam(':uniqueID', $uniqueID, PDO::PARAM_STR);
            $stmt->execute();
            $count = $stmt->fetchColumn();
        }

        // ID is unique; you can insert it into the table
        return $uniqueID;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

try {
    $stmt = $pdo->prepare("SELECT title, first_name, last_name FROM admin_registration WHERE unique_identifier = :user_id");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($user['title']) || empty($user['first_name']) || empty($user['last_name'])) {
        // Show alert and redirect to complete_profile.php
        echo '<script type="text/javascript">
                if (confirm("You have to complete your profile registration before using the property registration function. Click OK to complete your profile.")) {
                    window.location = "developer_complete_profile.php";
                } else {
                    window.location = "developer_dashboard.php"; // Redirect to dashboard or another page
                }
            </script>';
        exit();
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $district = $_POST["district"];
    $district_options = $_POST["district_options"];
    $apartment_type = $_POST["apartment_type"];
    $image_path = "uploads/" . basename($_FILES["image"]["name"]);
    $other_details = $_POST["other_details"];
    $id = generateUniqueID();

    try {
        $sql = "INSERT INTO property_registration (district, district_options, user_id, unique_id, apartment_type) VALUES (:district, :district_options, :user_id, :unique_id, :apartment_type)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':district', $district);
        $stmt->bindParam(':district_options', $district_options);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':unique_id', $id);
        $stmt->bindParam(':apartment_type', $apartment_type);
        $stmt->execute();

        // Move the uploaded image to the server
        move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);

        $stmt2 = $pdo->prepare("INSERT INTO propertyadvertisements (apartment_type, image_path, other_details, property_id) VALUES (:apartment_type, :image_path, :other_details, :id)");
        
        $stmt2->bindParam(':apartment_type', $apartment_type);
        $stmt2->bindParam(':image_path', $image_path);
        $stmt2->bindParam(':other_details', $other_details);
        $stmt2->bindParam(':id', $id);
        $stmt2->execute();

        echo '<script type="text/javascript">
                    alert("Property Registration Successful.");
                    window.location = "developer_dashboard.php"; // Replace with the actual URL of your homepage
                </script>';
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Register New Property</title>
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
        select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input {
            width: 70%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        textarea {
            width: 95%;
            resize: none;
        }

        input[type="submit"] {
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
        #image-preview {
            margin-top: 10px;
        }
        #image-preview img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <header>
        <a class="back-link" href="developer_dashboard.php">&#8678;</a>
        <img src="Logo_final.png" alt="Logo" class="header-img">
    </header>
    <div class="container">
        <h2>Register New Property</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"  method="post" enctype="multipart/form-data">
            <label>District:</label>
            <select name="district" id="district-select" onchange="populateDistrictOptions()">
                <option>Select your district</option>
                <option value="HONG KONG ISLAND">HONG KONG ISLAND</option>
                <option value="KOWLOON">KOWLOON</option>
                <option value="NEW TERRITORIES">NEW TERRITORIES</option>
                <option value="OUTLYING ISLANDS">OUTLYING ISLANDS</option>
            </select><br>

            <!-- JavaScript to populate district options -->
            <script>
                function populateDistrictOptions() {
                    var districtSelect = document.getElementById("district-select");
                    var selectedDistrict = districtSelect.value;

                    var districtOptions = {
                        "HONG KONG ISLAND": [
                            "Causeway Bay",
                            "Aberdeen",
                            "Central",
                            "Central MidLevel",
                            "Chai Wan",
                            "East Point",
                            "Happy Valley",
                            "Kennedy Town",
                            "North Point",
                            "Pokfulam",
                            "Quarry Bay",
                            "Repulse Bay",
                            "Sai Ying Pun",
                            "Shaukiwan",
                            "Shek Tong Tsui",
                            "Stanley",
                            "Tai Hang",
                            "Tai Koo shing",
                            "The Peak",
                            "Wah Fu Estate",
                            "Wanchai",
                            "Wong Chuk Hang"
                        ],
                        "KOWLOON": [
                            "Cha Kwo Ling",
                            "Cheung Sha Wan",
                            "Choi Hung",
                            "Ho Man Tin",
                            "Hung Hom",
                            "Kowloon Bay",
                            "Kowloon City",
                            "Kowloon Tong",
                            "Kwun Tong",
                            "Lai Chi Kok",
                            "Lai King",
                            "Lam Tin",
                            "Lei Yue Mun",
                            "Lok Fu",
                            "Ma Tau Kok",
                            "Meifoo sun Chuen",
                            "Mongkok",
                            "Ngau Tau Kok",
                            "San Po Kong",
                            "Sau Mau Ping",
                            "Sham Shui Po",
                            "Shek Kip Mei",
                            "So Uk Estate",
                            "Tai Kok Tsui",
                            "To Kwan Wan",
                            "Tsim Sha Tsui",
                            "Tsz Wan Shan", 
                            "Wang Tau Hom", 
                            "Wong Tai Sin", 
                            "Yau Tong", 
                            "Yau Yat Chuen", 
                            "Yau Ma Tei" 
                        ],
                        "NEW TERRITORIES": [
                            "Castle Peak",
                            "Fan Ling",
                            "Hung Shui Kiu",
                            "Kwai Chung",
                            "Ma On Shan", 
                            "Ma Wan",
                            "Sai Kung", 
                            "Sham Tseng",
                            "Shatin", 
                            "Sheung Shui", 
                            "Tai Po", 
                            "Tin Shui Wai", 
                            "Tseung Kwan O", 
                            "Tsing Yi", 
                            "Tsuen Wan", 
                            "Tuen Mun", 
                            "Tung Chung", 
                            "Wo Sang Wai", 
                            "Yuen Long" 
                        ],
                        "OUTLYING ISLANDS": [
                            "Cheung Chau",
                            "Discovery Bay",
                            "Lamma Island",
                            "Outlying Island", 
                            "Peng Chau"                         
                        ]
                    };

                    var options = districtOptions[selectedDistrict];
                    var districtOptionSelect = document.getElementById("district-options");

                    // Clear previous options
                    districtOptionSelect.innerHTML = "";

                    // Populate new options
                    options.forEach(function (option) {
                        var optionElement = document.createElement("option");
                        optionElement.value = option;
                        optionElement.textContent = option;
                        districtOptionSelect.appendChild(optionElement);
                    });

                }

                // Initial population of options on page load
                populateDistrictOptions();
            </script>
            <!-- District Options Dropdown -->
            <label>District Options:</label>
            <select name="district_options" id="district-options"></select><br>
            <label for="apartment_type">Type of Apartment:</label>
            <select name="apartment_type">
                <option value="Studio">Studio</option>
                <option value="One-bedroom">One-bedroom</option>
                <option value="Two-bedroom">Two-bedroom</option>
                <option value="Three-bedroom or more">Three-bedroom or more</option>
            </select><br><br>

            <label for="image">Image of Apartment:</label>
            <input type="file" name="image" accept="image/*" onchange="showImagePreview(this);"><br>
            <div id="image-preview"></div><br>

            <label for="other_details">Other Details:</label>
            <textarea name="other_details" rows="4" cols="50"></textarea><br><br>
            <input type="submit" value="Register Property">
        </form>
    </div>
    <script>
        function showImagePreview(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('image-preview').innerHTML = '<img src="' + e.target.result + '">';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
