<?php 
// Include the config.php file to establish the database connection
require_once 'config.php';

function sanitizeInput($input)
{
    return htmlspecialchars(stripslashes(trim($input)));
}

function isUsernameExist($username) {
    // Assuming you have a table 'admin' with fields: email and is_verified
    global $pdo;
    $sql = "SELECT username FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $result = $stmt->fetchColumn();

    return $result == 1;
}
// Function to check if the email is unique
function isEmailUnique($pdo, $email)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    return $count === 0;
}

function isEmailUnique1($pdo, $email)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin WHERE email = :email");
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    return $count === 0;
}

$username = "";
// Check if the form is submitted
if (isset($_GET["username"])) {
    $username = $_GET["username"];

    if (isUsernameExist($username)) {
        header("refresh:1; url=user_login.php");
        echo "User does not exist.";
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $errors = array();

        $date_of_birth = $_POST['date_of_birth'];
        $phone_number = sanitizeInput($_POST['phone_number']);
        $email = $_POST["email"];

        // Apartment Address
        $room = sanitizeInput($_POST['room']);
        $flat = sanitizeInput($_POST['flat']);
        $block = sanitizeInput($_POST['block']);
        $floor = sanitizeInput($_POST['floor']);
        $building = sanitizeInput($_POST['building']);
        $street_number = sanitizeInput($_POST['street_number']);
        $street_name = sanitizeInput($_POST['street_name']);
        $district = sanitizeInput($_POST['district']);
        $district_options = $_POST['district_options'] ?? ''; // Use the null coalescing operator to provide a default value
        $tenancy_status = $_POST['tenancy_status'] ?? ''; // Use the null coalescing operator to provide a default value

        // Scale of renovation or modification (Modified to keep the "<" sign)
        $scale_of_renovation = $_POST['scale_of_renovation'];

        // Apartment Type
        $apartment_type = sanitizeInput($_POST['apartment_type']);

        // Major requirements in apartment
        $major_requirements = isset($_POST['major_requirements']) ? $_POST['major_requirements'] : array();
        if (in_array('Others', $major_requirements)) {
            // If "Others" is selected, get the specified other major requirements
            $major_requirements_other = sanitizeInput($_POST['major_requirements_other']);
            $major_requirements[] = $major_requirements_other;
        }

        // Defects Noticed in Your Apartment
        $apartment_defects = isset($_POST['apartment_defects']) ? $_POST['apartment_defects'] : array();
        if (in_array('Others', $apartment_defects)) {
            // If "Others" is selected, get the specified other defects
            $apartment_defects_other = sanitizeInput($_POST['apartment_defects_other']);
            $apartment_defects[] = $apartment_defects_other;
        }

        // Check if the email is unique
        if (!isEmailUnique($pdo, $email) || !isEmailUnique1($pdo, $email)) {
            $errors["email"] = "Email is already taken. Please choose a different email.";
        }

        // If there are any errors, display them
        if (empty($errors)) {
            // No errors, proceed with registration
            $major_requirements_str = implode(', ', $major_requirements);
            $apartment_defects_str = implode(', ', $apartment_defects);

            // Prepare and execute the SQL query to insert data into the database using prepared statements
            try {
                $stmt = $pdo->prepare("UPDATE users SET date_of_birth = :date_of_birth, phone_number = :phone_number, room = :room, flat = :flat, block =  :block, floor = :floor, building = :building, street_number = :street_number, street_name = :street_name, district = :district, district_options = :district_options, tenancy_status = :tenancy_status, scale_of_renovation = :scale_of_renovation, apartment_type = :apartment_type, major_requirements = :major_requirements, apartment_defects = :apartment_defects, email = :email WHERE username = :username ");

                $stmt->bindParam(':date_of_birth', $date_of_birth);
                $stmt->bindParam(':phone_number', $phone_number);
                $stmt->bindParam(':room', $room);
                $stmt->bindParam(':flat', $flat);
                $stmt->bindParam(':block', $block);
                $stmt->bindParam(':floor', $floor);
                $stmt->bindParam(':building', $building);
                $stmt->bindParam(':street_number', $street_number);
                $stmt->bindParam(':street_name', $street_name);
                $stmt->bindParam(':district', $district);
                $stmt->bindParam(':district_options', $district_options);
                $stmt->bindParam(':tenancy_status', $tenancy_status);
                $stmt->bindParam(':scale_of_renovation', $scale_of_renovation);
                $stmt->bindParam(':apartment_type', $apartment_type);
                $stmt->bindParam(':major_requirements', $major_requirements_str);
                $stmt->bindParam(':apartment_defects', $apartment_defects_str);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':username', $username);

                if ($stmt->execute()) {
                    // Registration successful
                    header("refresh:3;url=user_login.php");
                    echo"<h1>Registration Successful</h1>";
                    echo"<p>Congratulations! Your registration was successful.</p>";
                    exit; // Terminate the script after redirecting
                } else {
                    // Registration failed
                    echo "Error: Registration failed. Please try again.";
                }

            } catch (PDOException $e) {
                die("Error: " . $e->getMessage());
            }
        } 
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <style>
        * {
            padding: 0;
            margin: 0;
        }
        .custom-dropdown {
            position: relative;
            display: inline-block;
        }

        .custom-dropdown-select {
            width: 300px;
            padding: 10px;
            cursor: pointer;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .custom-dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 300px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            padding: 12px;
            z-index: 1;
        }

        .custom-dropdown-content label {
            display: block;
            margin-bottom: 8px;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            text-align: center;
            padding: 20px;
            margin: 0;
        }

        h1 {
            color: #009688;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 10px;
        }

        input[type="text"],
        input[type="password"],
        input[type="date"],
        input[type="tel"],
        input[type="email"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: url('arrow-down.png') no-repeat right center;
            background-size: 20px;
            padding-right: 30px;
        }

        .custom-dropdown {
            position: relative;
        }

        .custom-dropdown-select {
            display: inline-block;
            padding: 10px;
            cursor: pointer;
            background-color: #009688;
            color: #fff;
            border-radius: 3px;
            user-select: none;
        }

        .custom-dropdown-content {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background-color: #fff;
            border: 1px solid #ccc;
            border-top: none;
            border-radius: 0 0 3px 3px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1;
        }

        .custom-dropdown-content label {
            display: block;
            padding: 10px;
            cursor: pointer;
        }

        .custom-dropdown-content label:hover {
            background-color: #f0f0f0;
        }

        input[type="file"] {
            margin-top: 10px;
        }

        button[type="button"],
        input[type="submit"] {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #009688;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        button[type="button"]:hover,
        input[type="submit"]:hover {
            background-color: #00796b;
        }

        span.error {
            color: red;
            display: block;
            text-align: left;
            margin-top: 5px;
        }

        img {
            height: 50px;
            width: 60px;
        }

        header{
            background-color: #009688;
            padding: 0;
            margin: 0;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .close-button-container {
            position: absolute;
            bottom: 10px;
            right: 10px;
        }

        .close-button {
            cursor: pointer;
            padding: 5px 10px;
            background-color: #f44336;
            color: white;
            font-size: 14px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .close-button:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>
    <header>
        <img src="Logo_final.png" alt="Logo"> 
    </header>
    <h1>Complete Your Registration</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?username=' . urlencode($username); ?>" method="post" enctype="multipart/form-data">
    <label>Date of Birth:</label>
        <input type="date" name="date_of_birth" required><br>

        <label>Phone Number:</label>
        <input type="tel" name="phone_number" required pattern="(\+?[0-9]{1,3}\s?)?[0-9]{8}" placeholder="E.g., 94636396, +85294636396, or 85294636396"><br>

        <label>Email:</label>
        <input type="email" name="email" required><br>
        <?php if (!empty($errors['email'])) : ?>
            <span style="color: red;"><?php echo $errors['email']; ?></span><br>
        <?php endif; ?>


        <!-- Apartment Address -->
        <h2>Apartment Address</h2>
        <label>Room:</label>
        <input type="text" name="room"><br>

        <label>Flat:</label>
        <input type="text" name="flat"><br>

        <label>Block:</label>
        <input type="text" name="block"><br>

        <label>Floor:</label>
        <input type="text" name="floor"><br>

        <label>Building:</label>
        <input type="text" name="building"><br>

        <label>Street Number:</label>
        <input type="text" name="street_number"><br>

        <label>Street Name:</label>
        <input type="text" name="street_name"><br>

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
                        "Aberdeen",
                        "Causeway Bay",
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

        <h2>Tenancy status</h2>
        <select name="tenancy_status">
            <option>Tenant</option>
            <option>Homeowner</option>
        </select><br>

        <h2>Scale of renovation or modification:</h2>
        <select name="scale_of_renovation">
            <option value="N/A">N/A</option>
            <option value="< 10 %">&lt; 10 %</option>
            <option value="11 - 30 %">11 - 30 %</option>
            <option value="31 - 50 %">31 - 50 %</option>
            <option value="51 - 70 %">51 - 70 %</option>
            <option value="Above 70 %">Above 70 %</option>
        </select><br>

        <!-- Apartment Type Dropdown -->
        <h2>Apartment Type:</h2>
        <select name="apartment_type">
            <option value="Studio">Studio</option>
            <option value="One Bedroom">One Bedroom</option>
            <option value="Two Bedroom">Two Bedroom</option>
            <option value="Three Bedroom">Three Bedroom</option>
        </select><br>

        <h2>Major requirements in apartment</h2>
        <div class="custom-dropdown">
            <div class="custom-dropdown-select" onclick="toggleDropdown('major_requirements')" onchange="handleNASelection('major_requirements_content');">Select Major Requirements</div>
            <div class="custom-dropdown-content" id="major_requirements_content">
                <label><input type="checkbox" name="major_requirements[]" value="Balcony lighting">Balcony lighting</label>
                <label><input type="checkbox" name="major_requirements[]" value="Built-in cooker with exhaust hood overhead">Built-in cooker with exhaust hood overhead</label>
                <label><input type="checkbox" name="major_requirements[]" value="Built-in dishwasher">Built-in dishwasher</label>
                <label><input type="checkbox" name="major_requirements[]" value="Built-in dryer">Built-in dryer</label>
                <label><input type="checkbox" name="major_requirements[]" value="Built-in kitchen cabinet">Built-in kitchen cabinet</label>
                <label><input type="checkbox" name="major_requirements[]" value="Built-in microwave">Built-in microwave</label>
                <label><input type="checkbox" name="major_requirements[]" value="Built-in oven">Built-in oven</label>
                <label><input type="checkbox" name="major_requirements[]" value="Built-in wardrobe in every bedroom">Built-in wardrobe in every bedroom</label>
                <label><input type="checkbox" name="major_requirements[]" value="Built-in washing machine">Built-in washing machine</label>
                <label><input type="checkbox" name="major_requirements[]" value="Ceiling lighting for all rooms">Ceiling lighting for all rooms</label>
                <label><input type="checkbox" name="major_requirements[]" value="Cloth drying rack">Cloth drying rack</label>
                <label><input type="checkbox" name="major_requirements[]" value="Connecting indoor space to exterior such as balcony">Connecting indoor space to exterior such as balcony</label>
                <label><input type="checkbox" name="major_requirements[]" value="Developers provide several options of internal finishings and fittings">Developers provide several options of internal finishings and fittings</label>
                <label><input type="checkbox" name="major_requirements[]" value="Doorbell">Doorbell</label>
                <label><input type="checkbox" name="major_requirements[]" value="External shading devices e.g., horizontal window fins to reduce sun glare">External shading devices e.g., horizontal window fins to reduce sun glare</label>
                <label><input type="checkbox" name="major_requirements[]" value="Grey water system that recycles wastewater from sinks for toilet flushing">Grey water system that recycles wastewater from sinks for toilet flushing</label>
                <label><input type="checkbox" name="major_requirements[]" value="Hot water supply">Hot water supply</label>
                <label><input type="checkbox" name="major_requirements[]" value="Large panorama window to enhance good ventilation but allow more heat gain">Large panorama window to enhance good ventilation but allow more heat gain</label>
                <label><input type="checkbox" name="major_requirements[]" value="Metal entrance door in addition to wooden door">Metal entrance door in addition to wooden door</label>
                <label><input type="checkbox" name="major_requirements[]" value="Mirror cabinet">Mirror cabinet</label>
                <label><input type="checkbox" name="major_requirements[]" value="Natural daylight">Natural daylight</label>
                <label><input type="checkbox" name="major_requirements[]" value="Natural ventilation">Natural ventilation</label>
                <label><input type="checkbox" name="major_requirements[]" value="Open kitchen using electric cooking instead of an enclosed kitchen">Open kitchen using electric cooking instead of an enclosed kitchen</label>
                <label><input type="checkbox" name="major_requirements[]" value="Passive daylighting system that can introduce daylight into the interior of the apartment">Passive daylighting system that can introduce daylight into the interior of the apartment</label>
                <label><input type="checkbox" name="major_requirements[]" value="Roof garden for recreational purposes">Roof garden for recreational purposes</label>
                <label><input type="checkbox" name="major_requirements[]" value="Shower instead of bathtub">Shower instead of bathtub</label>
                <label><input type="checkbox" name="major_requirements[]" value="Smaller window to reduce heat gain">Smaller window to reduce heat gain</label>
                <label><input type="checkbox" name="major_requirements[]" value="Smart Home provision such as artificial lighting and air-conditioning that can be controlled remotely">Smart Home provision such as artificial lighting and air-conditioning that can be controlled remotely</label>
                <label><input type="checkbox" name="major_requirements[]" value="Sound insulation to reduce external noise">Sound insulation to reduce external noise</label>
                <label><input type="checkbox" name="major_requirements[]" value="Storage space/room">Storage space/room</label>
                <label><input type="checkbox" name="major_requirements[]" value="The flexibility of internal partitioning">The flexibility of internal partitioning</label>
                <label><input type="checkbox" name="major_requirements[]" value="Thermal insulation to an external wall to minimize heat gain and loss">Thermal insulation to an external wall to minimize heat gain and loss</label>
                <label><input type="checkbox" name="major_requirements[]" value="Under-the-sink cabinet">Under-the-sink cabinet</label>
                <label><input type="checkbox" name="major_requirements[]" value="Use of energy-saving lamps e.g., compact fluorescent lamps (CFLs) and light-emitting diodes (LEDs)">Use of energy-saving lamps e.g., compact fluorescent lamps (CFLs) and light-emitting diodes (LEDs)</label>
                <label><input type="checkbox" name="major_requirements[]" value="Ventilation fan">Ventilation fan</label>
                <label><input type="checkbox" name="major_requirements[]" value="Ventilation fan with heater">Ventilation fan with heater</label>
                <label><input type="checkbox" name="major_requirements[]" value="Vertical green wall">Vertical green wall</label>
                <label><input type="checkbox" name="major_requirements[]" value="Wall-hang heater">Wall-hang heater</label>
                <label>
                    <input type="checkbox" name="major_requirements[]" value="Others" onclick="handleOtherOption('major_requirements', this)">
                    Others
                </label>
                <div id="other_major_requirements" style="display: none;">
                    <label>Specify other major requirements:</label>
                    <input type="text" name="major_requirements_other">
                </div>
                <div class="close-button-container">
                    <div class="close-button" onclick="closeDropdown('major_requirements')">Close</div>
                </div>
            </div>
        </div>

        <h2>Defects Noticed in Your Apartment</h2>
        <div class="custom-dropdown">
            <div class="custom-dropdown-select" onclick="toggleDropdown('defects')">Select Defects</div>
            <div class="custom-dropdown-content" id="defects_content">
                <label><input type="checkbox" name="apartment_defects[]" value="Absence of fire detector and emergency alert in design">Absence of fire detector and emergency alert in design</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Design and technology not user friendly to end-users and maintenance personnel">Design and technology not user friendly to end-users and maintenance personnel</label>
                <label><input type="checkbox" name="apartment_defects[]" value="External pipes inconvenient for maintenance e.g., lack of maintenance platform">External pipes inconvenient for maintenance e.g., lack of maintenance platform</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Foul odor transmission due to inappropriate positioning of kitchen and toilets">Foul odor transmission due to inappropriate positioning of kitchen and toilets</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Ignorance of materials performance">Ignorance of materials performance</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Improper design of the ventilation system">Improper design of the ventilation system</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Inadequate sound insulation to reduce external noise">Inadequate sound insulation to reduce external noise</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Inadequate space, size, and location of ductwork">Inadequate space, size, and location of ductwork</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Inadequate vertical circulation design">Inadequate vertical circulation design</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Inadequate working drawings and specification">Inadequate working drawings and specification</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Inappropriate design, selection, and specification of rebar (reinforcing bar) and pipe">Inappropriate design, selection, and specification of rebar (reinforcing bar) and pipe</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Incomplete or incorrect working drawings and details">Incomplete or incorrect working drawings and details</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Insufficient flow rate and inadequate pressure of water supply system">Insufficient flow rate and inadequate pressure of water supply system</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Insufficient number and misdistribution of electric sockets">Insufficient number and misdistribution of electric sockets</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Lack of parallel or cross ventilation in bedrooms">Lack of parallel or cross ventilation in bedrooms</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Loose electrical connections">Loose electrical connections</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Poor curtain wall design leading to difficult access for maintenance">Poor curtain wall design leading to difficult access for maintenance</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Poor internal partition design and detailing">Poor internal partition design and detailing</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Poor power supply system design">Poor power supply system design</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Specifying lifts with low passenger capacity (e.g., at peak hours)">Specifying lifts with low passenger capacity (e.g., at peak hours)</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Structural design is not flexible for future renovation (e.g., alteration in layout plan)">Structural design is not flexible for future renovation (e.g., alteration in layout plan)</label>
                <label><input type="checkbox" name="apartment_defects[]" value="Water seepage, delaminated tiles, discolored tiles, and efflorescence">Water seepage, delaminated tiles, discolored tiles, and efflorescence</label>
                <label>
                    <input type="checkbox" name="apartment_defects[]" value="Others" onclick="handleOtherOption('defects', this)">
                    Others
                </label>
                <div id="other_defects" style="display: none;">
                    <label>Specify other defects:</label>
                    <input type="text" name="apartment_defects_other">
                </div>
                <div class="close-button-container">
                    <div class="close-button" onclick="closeDropdown('defects')">Close</div>
                </div>
            </div>
        </div>
        <script>
            // Function to close the dropdown content
            function closeDropdown(dropdownId) {
                const dropdownContent = document.getElementById(`${dropdownId}_content`);
                dropdownContent.style.display = 'none';
            }

            function toggleDropdown(type) {
                var dropdownContent = document.getElementById(type + "_content");
                if (dropdownContent.style.display === "block") {
                    dropdownContent.style.display = "none";
                } else {
                    dropdownContent.style.display = "block";
                }
            }

            function handleOtherOption(type, checkbox) {
                const otherOptions = document.getElementById("other_" + type);
                if (checkbox.checked) {
                    otherOptions.style.display = "block";
                    // If the "Other" option is selected, deselect "N/A"
                    const naOption = document.querySelector(`#${type}_content input[value="N/A"]`);
                    naOption.checked = false;
                } else {
                    otherOptions.style.display = "none";
                }
            }

            function handleNASelection(dropdownId) {
                const dropdown = document.getElementById(dropdownId);
                const naOption = dropdown.querySelector('input[value="N/A"]');
                const otherOptions = dropdown.querySelectorAll('input:not([value="N/A"])');

                // Check if the "N/A" option is selected
                if (naOption.checked) {
                    // If "N/A" is selected, deselect all other options (excluding "Other")
                    otherOptions.forEach((option) => (option.checked = false));
                } else {
                    // If any other option (excluding "Other") is selected, deselect "N/A"
                    naOption.checked = false;
                }
            }
        </script>
        <input type="submit" value="Submit">
    </form>
</body>
</html>
