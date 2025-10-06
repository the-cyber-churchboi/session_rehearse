<?php
session_name("user_session");
session_start();

require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: user_login_1.php");
    exit();
}

if (isset($_SESSION["success_message"])) {
    echo "<script>alert('" . $_SESSION["success_message"] . "');</script>";
    unset($_SESSION["success_message"]);
}

$user_unique_id = $_SESSION["user_unique_id"];
$stmt = $pdo->prepare("SELECT property_name FROM manager_property_registration");
$stmt->execute();
$buildings = $stmt->fetchAll(PDO::FETCH_COLUMN);

function sanitizeInput($input)
{
    return htmlspecialchars(stripslashes(trim($input)));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user_unique_id"];
    $building = $_POST["building"];
    $scale_of_renovation = $_POST["scale_of_renovation"];
    $apartment_type = $_POST["apartment_type"];

    // Amenities in Your Apartment
    $apartment_amenities = isset($_POST['apartment_amenities']) ? $_POST['apartment_amenities'] : array();
    $apartment_amenities_other = isset($_POST['apartment_amenities_other']) ? sanitizeInput($_POST['apartment_amenities_other']) : '';

    // If "Others" is selected, get the specified other amenities
    if (in_array('Others', $apartment_amenities) && !empty($apartment_amenities_other)) {
        $apartment_amenities = array_filter($apartment_amenities, function ($value) {
            return $value !== 'Others';
        });
        $apartment_amenities[] = "Others: $apartment_amenities_other";
    }

    // Major requirements in apartment
    $major_requirements = isset($_POST['major_requirements']) ? $_POST['major_requirements'] : array();
    $major_requirements_other = isset($_POST['major_requirements_other']) ? sanitizeInput($_POST['major_requirements_other']) : '';

    // If "Others" is selected, get the specified other major requirements
    if (in_array('Others', $major_requirements) && !empty($major_requirements_other)) {
        $major_requirements = array_filter($major_requirements, function ($value) {
            return $value !== 'Others';
        });
        $major_requirements[] = "Others: $major_requirements_other";
    }

    // Defects Noticed in Your Apartment
    $apartment_defects = isset($_POST['apartment_defects']) ? $_POST['apartment_defects'] : array();
    $apartment_defects_other = isset($_POST['apartment_defects_other']) ? sanitizeInput($_POST['apartment_defects_other']) : '';

    // If "Others" is selected, get the specified other defects
    if (in_array('Others', $apartment_defects) && !empty($apartment_defects_other)) {
        $apartment_defects = array_filter($apartment_defects, function ($value) {
            return $value !== 'Others';
        });
        $apartment_defects[] = "Others: $apartment_defects_other";
    }

    // Convert arrays to strings
    $apartment_amenities_str = implode(', ', $apartment_amenities);
    $major_requirements_str = implode(', ', $major_requirements);
    $apartment_defects_str = implode(', ', $apartment_defects);


    $feedback1 = $_POST["feedback1"];
    $feedback2 = $_POST["feedback2"];
    $feedback3 = $_POST["feedback3"];
    $feedback = $_POST["feedback"];

    // Check if the building name already exists for the user_id
    $stmt = $pdo->prepare("SELECT * FROM building_evaluations WHERE user_id = :user_id AND building = :building");
    $stmt->execute(['user_id' => $user_id, 'building' => $building]);
    $existingRecord = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingRecord) {
        $stmt = $pdo->prepare("UPDATE building_evaluations SET 
            scale_of_renovation = :scale_of_renovation,
            apartment_type = :apartment_type,
            apartment_amenities = :apartment_amenities,
            major_requirements = :major_requirements,
            apartment_defects = :apartment_defects,
            most_sustainable_feature = :feedback1,
            area_for_review = :feedback2,
            future_improvement_feature = :feedback3,
            feedback = :feedback
            WHERE user_id = :user_id AND building = :building");

        // Execute the update query
        $stmt->execute([
            'user_id' => $user_id,
            'building' => $building,
            'scale_of_renovation' => $scale_of_renovation,
            'apartment_type' => $apartment_type,
            'apartment_amenities' => $apartment_amenities_str,
            'major_requirements' => $major_requirements_str,
            'apartment_defects' => $apartment_defects_str,
            'feedback1' => $feedback1,
            'feedback2' => $feedback2,
            'feedback3' => $feedback3,
            'feedback' => $feedback,
        ]);
        echo '<script>alert("Answers updated succesfully");</script>';
        echo '<script>window.location.href = "index.html";</script>';
        exit();
    } else {
        $stmt = $pdo->prepare("INSERT INTO building_evaluations (user_id, building, scale_of_renovation, apartment_type, apartment_amenities, major_requirements, apartment_defects, most_sustainable_feature, area_for_review, future_improvement_feature, feedback) VALUES (:user_id, :building, :scale_of_renovation, :apartment_type, :apartment_amenities, :major_requirements, :apartment_defects, :feedback1, :feedback2, :feedback3, :feedback)");
        $stmt->execute([
            'user_id' => $user_id,
            'building' => $building,
            'scale_of_renovation' => $scale_of_renovation,
            'apartment_type' => $apartment_type,
            'apartment_amenities' => $apartment_amenities_str,
            'major_requirements' => $major_requirements_str,
            'apartment_defects' => $apartment_defects_str,
            'feedback1' => $feedback1,
            'feedback2' => $feedback2,
            'feedback3' => $feedback3,
            'feedback' => $feedback,
        ]);
        echo '<script>alert("Answers submitted succesfully");</script>';
        echo '<script>window.location.href = "index.html";</script>';
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Evaluation Questions</title>
        <meta charset="utf-8">
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                background-color: white;
            }

            header {
                background-color: white;
                color: black;
                text-align: center;
                padding: 5px 0;
                border: 2px solid black;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            header a {
                color: #fff;
                text-decoration: none;
            }

            .container {
                max-width: 800px;
                margin: 8px auto;
                padding: 20px;
                background-color: #fff;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                border-radius: 5px;
            }

            .my-h1 {
                font-size: 24px;
                background-color: black;
                color: #fff;
                padding: 10px;
            }

            @keyframes rainbowBlink {
                0% { color: red; }
                14% { color: orange; }
                28% { color: yellow; }
                42% { color: green; }
                57% { color: blue; }
                71% { color: indigo; }
                85% { color: violet; }
                100% { color: red; }
            }

            .my-h1-1 {
                font-size: 50px;
                animation: rainbowBlink 4s linear infinite;
            }

            form {
                margin-top: 20px;
            }

            h2 {
                font-size: 18px;
                background-color: #7ed957;
                color: #fff;
                padding: 10px;
            }

            select, input[type="text"] {
                width: 80%;
                padding: 10px;
                margin: 5px 5px;
                border: 2px solid black;
                border-radius: 5px;
            }

            .custom-dropdown {
                position: relative;
                display: inline-block;
                margin-right: 10px;
            }

            .custom-dropdown-select {
                background-color: #007BFF;
                color: #fff;
                padding: 10px;
                cursor: pointer;
            }

            .custom-dropdown-content {
                display: none;
                position: absolute;
                background-color: #fff;
                min-width: 200px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                border: 1px solid #ccc;
                z-index: 1;
            }

            .custom-dropdown-content label {
                display: block;
                padding: 10px;
                cursor: pointer;
            }

            .close-button-container {
                text-align: right;
                margin-top: 10px;
            }

            .close-button-1 {
                background-color: #ccc;
                color: #fff;
                padding: 10px;
                cursor: pointer;
            }

            .close-button-1:hover {
                background-color: #007BFF;
            }

            button {
                background-color: #007BFF;
                color: #fff;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }

            button:hover {
                background-color: #0056b3;
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

            .custom-dropdown-container {
                position: relative;
            }

            .custom-dropdown-container .custom-dropdown-content {
                width: 100%; /* Set the width to 100% */
                max-width: 100%;
                left: 0;
                right: 0;
            }

            .arrow-down:before,
            .arrow-right:before {
                content: "▼"; /* Down arrow ▼ */
                font-size: 14px;
            }

            .arrow-right:before {
                content: "►";
            }

            .custom-dropdown-select.expanded .arrow-down:before,
            .custom-dropdown-select.expanded .arrow-right:before {
                content: "▼";
                display: inline-block;
                vertical-align: middle;
            }

            .custom-dropdown-select:not(.expanded) .arrow-right:before {
                content: "►";
                display: inline-block;
                vertical-align: middle;
            }

            textarea#feedback {
                width: 95%;
                padding: 10px;
                margin: 5px 0;
                border: 2px solid black;
                border-radius: 5px;
                resize: none; 
            }
        </style>
    </head>
    <body>
        <header>
            <a class="back-link" href="user_dashboard.php">&#8678; Back</a>
            <img src="Logo_final.png" alt="Logo" class="header-img">
            <h1 class="my-h1-1">Specific Feedback</h1>
        </header>
        <div class="container">
            <div id="evaluation-modal" class="modal">
                <div class="modal-content">
                    <h1 class="my-h1">Evaluation Questions</h1>
                    <p>Please answer the following questions related to the selected apartment</p>
                    <form id="evaluation-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="form-grid">
                        <h2>Select a Building:</h2>
                        <select name="building" id="building">
                            <?php
                                foreach ($buildings as $building) {
                                    echo "<option value=\"$building\">$building</option>";
                                }
                            ?>
                        </select>
                        <!-- Scale of renovation question -->
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
                        <h2>Apartment type:</h2>
                        <select name="apartment_type">
                            <option value="Studio">Studio</option>
                            <option value="One Bedroom">One Bedroom</option>
                            <option value="Two Bedroom">Two Bedroom</option>
                            <option value="Three Bedroom or above">Three Bedroom or above</option>
                        </select><br>

                        <!-- Amenities questions -->
                        <h2>Which of your apartment's amenities are OVER-supplied?</h2>
                        <div class="custom-dropdown-container">
                            <div class="custom-dropdown">
                                <div class="custom-dropdown-select" onclick="toggleDropdown('amenities')">
                                    <span class="arrow-right"></span> Select Amenities
                                </div>
                                <div class="custom-dropdown-content" id="amenities_content">
                                    <label><input type="checkbox" name="apartment_amenities[]" value="N/A" onchange="handleNASelection('amenities_content');">N/A</label>
                                    <label><input type="checkbox" name="apartment_amenities[]" value="Air-conditioner" onchange="handleNASelection('amenities_content');">Air-conditioner</label>
                                    <label><input type="checkbox" name="apartment_amenities[]" value="Bathroom cabinet" onchange="handleNASelection('amenities_content');">Bathroom cabinet</label>
                                    <label><input type="checkbox" name="apartment_amenities[]" value="Cloth drying facility e.g., drying rack" onchange="handleNASelection('amenities_content');">Cloth drying facility e.g., drying rack</label>
                                    <label><input type="checkbox" name="apartment_amenities[]" value="Fully furnished kitchen" onchange="handleNASelection('amenities_content');">Fully furnished kitchen</label>
                                    <label><input type="checkbox" name="apartment_amenities[]" value="Fully furnished washing room" onchange="handleNASelection('amenities_content');">Fully furnished washing room</label>
                                    <label><input type="checkbox" name="apartment_amenities[]" value="Internal partitions" onchange="handleNASelection('amenities_content');">Internal partitions</label>
                                    <label><input type="checkbox" name="apartment_amenities[]" value="Wall painting/interior decoration" onchange="handleNASelection('amenities_content');">Wall painting/interior decoration</label>
                                    <label><input type="checkbox" name="apartment_amenities[]" value="Wardrobes" onchange="handleNASelection('amenities_content');">Wardrobes</label>
                                    <label><input type="checkbox" name="apartment_amenities[]" value="Others" onclick="handleOtherOption('amenities', this)">Others</label>
                                    <div id="other_amenities" style="display: none;">
                                        <label>Specify other amenities:</label>
                                        <input type="text" name="apartment_amenities_other">
                                    </div>
                                    <div class="close-button-container">
                                        <div class="close-button-1" onclick="closeDropdown('amenities')">Close</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Major Requirements question -->
                        <h2>Major Requirements in your apartment</h2>
                        <div class="custom-dropdown-container">
                            <div class="custom-dropdown">
                                <div class="custom-dropdown-select" onclick="toggleDropdown('major_requirements')" onchange="handleNASelection('major_requirements_content');">
                                    <span class="arrow-right"></span>Select Major Requirements
                                </div>
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
                                    <label><input type="checkbox" name="major_requirements[]" value="Others" onclick="handleOtherOption('major_requirements', this)">Others</label>
                                    <div id="other_major_requirements" style="display: none;">
                                        <label>Specify other major requirements:</label>
                                        <input type="text" name="major_requirements_other">
                                    </div>
                                    <div class="close-button-container">
                                        <div class="close-button-1" onclick="closeDropdown('major_requirements')">Close</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Defects question -->
                        <h2>Defects noticed in your apartment after occupation</h2>
                        <div class="custom-dropdown-container">
                            <div class="custom-dropdown">
                                <div class="custom-dropdown-select" onclick="toggleDropdown('defects')">
                                    <span class="arrow-right"></span>Select defects
                                </div>
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
                                    <label><input type="checkbox" name="apartment_defects[]" value="Others" onclick="handleOtherOption('defects', this)">Others</label>
                                    <div id="other_defects" style="display: none;">
                                        <label>Specify other defects:</label>
                                        <input type="text" name="apartment_defects_other">
                                    </div>
                                    <div class="close-button-container">
                                        <div class="close-button-1" onclick="closeDropdown('defects')">Close</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h2>What feature in your apartment is most sustainable?</h2>
                            <textarea id="feedback" name="feedback1" rows="4" cols="50"></textarea>
                        </div>
                        <div>
                            <h2>Which area would you love a significant review of your apartment design to make it more sustainable?</h2>
                            <textarea id="feedback" name="feedback2" rows="4" cols="50"></textarea>
                        </div>
                        <div>
                            <h2>What feature would you love to see improvements in future lean premise design?</h2>
                            <textarea id="feedback" name="feedback3" rows="4" cols="50"></textarea>
                        </div>
                        <div>
                            <h2>Other feedback:</h2>
                            <textarea id="feedback" name="feedback" rows="4" cols="50"></textarea>
                        </div>
                        <button id="submit-evaluation" style="margin-top: 10px;">Submit</button>
                    </form>
                </div>
            </div>
        </div>
        <script>  
            // Function to close the dropdown content
            function closeDropdown(dropdownId) {
                const dropdownContent = document.getElementById(dropdownId + "_content");
                dropdownContent.style.display = "none";
            }

            function toggleDropdown(type) {
                // Toggle the selected dropdown and arrow icon
                const dropdownContent = document.getElementById(type + "_content");
                const dropdownSelect = dropdownContent.parentElement.querySelector(".custom-dropdown-select");
                if (dropdownContent.style.display === "block") {
                    dropdownContent.style.display = "none";
                    dropdownSelect.classList.remove("expanded");
                } else {
                    dropdownContent.style.display = "block";
                    dropdownSelect.classList.add("expanded");
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

            document.body.addEventListener("click", function (event) {
                const allDropdowns = document.querySelectorAll(".custom-dropdown-content");
                const dropdowns = document.querySelectorAll(".custom-dropdown");

                for (let i = 0; i < dropdowns.length; i++) {
                    if (event.target !== dropdowns[i] && !dropdowns[i].contains(event.target)) {
                        const dropdownContent = dropdowns[i].querySelector(".custom-dropdown-content");
                        const dropdownSelect = dropdowns[i].querySelector(".custom-dropdown-select");
                        dropdownContent.style.display = "none";
                        dropdownSelect.classList.remove("expanded");
                    }
                }
            });
        </script>
    </body>
</html>