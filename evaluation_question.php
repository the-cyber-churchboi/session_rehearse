<?php
 session_name("user_session");
 session_start();
 require_once "config.php"; 
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Evaluation Questions | EOD Platform</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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

            .evaluation-container {
                background: white;
                border-radius: var(--radius);
                box-shadow: var(--shadow-lg);
                padding: 50px;
                width: 100%;
                max-width: 800px;
                margin: 0 auto;
                position: relative;
                overflow: hidden;
            }

            .evaluation-container::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 5px;
                background: linear-gradient(90deg, var(--accent), var(--secondary));
            }

            .evaluation-header {
                text-align: center;
                margin-bottom: 40px;
            }

            .evaluation-icon {
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

            .evaluation-header h1 {
                font-size: 2.2rem;
                margin-bottom: 10px;
                color: var(--primary);
            }

            .evaluation-header p {
                color: var(--gray);
                font-size: 1.1rem;
            }

            /* Form Styles */
            .form-grid {
                display: flex;
                flex-direction: column;
                gap: 30px;
            }

            .form-section {
                background: var(--light);
                border-radius: var(--radius);
                padding: 25px;
                transition: var(--transition);
            }

            .form-section:hover {
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            }

            .form-section h2 {
                font-size: 1.4rem;
                margin-bottom: 20px;
                color: var(--primary);
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .form-section h2 i {
                color: var(--accent);
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

            /* Custom Dropdown Styles */
            .custom-dropdown-container {
                margin-top: 10px;
            }

            .custom-dropdown {
                position: relative;
                width: 100%;
            }

            .custom-dropdown-select {
                background: white;
                border: 2px solid #e8edf5;
                border-radius: var(--radius);
                padding: 15px 20px;
                cursor: pointer;
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-weight: 500;
                transition: var(--transition);
            }

            .custom-dropdown-select:hover {
                border-color: var(--accent);
            }

            .custom-dropdown-select.expanded {
                border-color: var(--accent);
                box-shadow: 0 0 0 3px rgba(99, 91, 255, 0.1);
            }

            .arrow-right {
                transition: var(--transition);
            }

            .custom-dropdown-select.expanded .arrow-right {
                transform: rotate(90deg);
            }

            .custom-dropdown-content {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: white;
                border: 2px solid #e8edf5;
                border-top: none;
                border-radius: 0 0 var(--radius) var(--radius);
                max-height: 300px;
                overflow-y: auto;
                z-index: 100;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            }

            .custom-dropdown-content label {
                display: flex;
                align-items: center;
                padding: 12px 20px;
                cursor: pointer;
                transition: var(--transition);
                border-bottom: 1px solid #f0f4f8;
            }

            .custom-dropdown-content label:hover {
                background: var(--light);
            }

            .custom-dropdown-content label:last-child {
                border-bottom: none;
            }

            .custom-dropdown-content input[type="checkbox"] {
                margin-right: 12px;
                accent-color: var(--accent);
            }

            .close-button-container {
                padding: 15px 20px;
                border-top: 1px solid #f0f4f8;
                background: var(--light);
            }

            .close-button-1 {
                background: var(--accent);
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 8px;
                cursor: pointer;
                font-weight: 500;
                text-align: center;
                transition: var(--transition);
                width: 100%;
            }

            .close-button-1:hover {
                background: var(--accent-light);
                transform: translateY(-2px);
            }

            /* Textarea Styles */
            textarea.form-control {
                resize: vertical;
                min-height: 120px;
                font-family: 'Inter', sans-serif;
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

            /* Other Input Styles */
            .other-input {
                margin-top: 15px;
                padding: 15px;
                background: #f8fafc;
                border-radius: 8px;
                border-left: 3px solid var(--accent);
            }

            .other-input label {
                display: block;
                margin-bottom: 8px;
                font-weight: 500;
                color: var(--primary);
            }

            .other-input input {
                width: 100%;
                padding: 10px 15px;
                border: 1px solid #e8edf5;
                border-radius: 8px;
                font-family: 'Inter', sans-serif;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .header-container {
                    flex-direction: column;
                    gap: 15px;
                    text-align: center;
                }
                
                .evaluation-container {
                    padding: 40px 30px;
                }
                
                .evaluation-header h1 {
                    font-size: 1.8rem;
                }
                
                .form-section {
                    padding: 20px;
                }
            }

            @media (max-width: 576px) {
                .container {
                    padding: 0 20px;
                }
                
                .evaluation-container {
                    padding: 30px 20px;
                }
                
                .evaluation-header h1 {
                    font-size: 1.6rem;
                }
                
                .evaluation-header p {
                    font-size: 1rem;
                }
                
                .form-section h2 {
                    font-size: 1.2rem;
                }
                
                .custom-dropdown-content {
                    max-height: 250px;
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
                
                <?php
                    if (isset($_SESSION["user_id"])) {
                        echo '<a class="back-link" href="user_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>';
                    } else {
                        echo '<a class="back-link" href="index.html"><i class="fas fa-arrow-left"></i> Back to Homepage</a>';
                    }
                ?>
            </div>
        </header>

        <main>
            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
            
            <div class="container">
                <div class="evaluation-container">
                    <div class="evaluation-header">
                        <div class="evaluation-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h1>Your Evaluation</h1>
                        <p>Please answer the following questions related to high-rise residential buildings in Hong Kong</p>
                    </div>
                    
                    <form id="evaluation-form" action="evaluation.php" method="POST" class="form-grid">
                        <!-- Scale of renovation question -->
                        <div class="form-section">
                            <h2><i class="fas fa-ruler-combined"></i> Scale of renovation or modification:</h2>
                            <select name="scale_of_renovation" class="form-control">
                                <option value="N/A">N/A</option>
                                <option value="< 10 %">&lt; 10 %</option>
                                <option value="11 - 30 %">11 - 30 %</option>
                                <option value="31 - 50 %">31 - 50 %</option>
                                <option value="51 - 70 %">51 - 70 %</option>
                                <option value="Above 70 %">Above 70 %</option>
                            </select>
                        </div>

                        <!-- Apartment Type Dropdown -->
                        <div class="form-section">
                            <h2><i class="fas fa-home"></i> Apartment type:</h2>
                            <select name="apartment_type" class="form-control">
                                <option value="Studio">Studio</option>
                                <option value="One Bedroom">One Bedroom</option>
                                <option value="Two Bedroom">Two Bedroom</option>
                                <option value="Three Bedroom or above">Three Bedroom or above</option>
                            </select>
                        </div>

                        <!-- Amenities questions -->
                        <div class="form-section">
                            <h2><i class="fas fa-concierge-bell"></i> Which of your apartment's amenities are OVER-supplied?</h2>
                            <div class="custom-dropdown-container">
                                <div class="custom-dropdown">
                                    <div class="custom-dropdown-select" onclick="toggleDropdown('amenities')">
                                        <span class="arrow-right"><i class="fas fa-chevron-right"></i></span> Select Amenities
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
                                        <div id="other_amenities" class="other-input" style="display: none;">
                                            <label>Specify other amenities:</label>
                                            <input type="text" name="apartment_amenities_other">
                                        </div>
                                        <div class="close-button-container">
                                            <div class="close-button-1" onclick="closeDropdown('amenities')">Close</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Major Requirements question -->
                        <div class="form-section">
                            <h2><i class="fas fa-tools"></i> Major Requirements in your apartment</h2>
                            <div class="custom-dropdown-container">
                                <div class="custom-dropdown">
                                    <div class="custom-dropdown-select" onclick="toggleDropdown('major_requirements')" onchange="handleNASelection('major_requirements_content');">
                                        <span class="arrow-right"><i class="fas fa-chevron-right"></i></span>Select Major Requirements
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
                                        <div id="other_major_requirements" class="other-input" style="display: none;">
                                            <label>Specify other major requirements:</label>
                                            <input type="text" name="major_requirements_other">
                                        </div>
                                        <div class="close-button-container">
                                            <div class="close-button-1" onclick="closeDropdown('major_requirements')">Close</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Defects question -->
                        <div class="form-section">
                            <h2><i class="fas fa-exclamation-triangle"></i> Defects noticed in your apartment after occupation</h2>
                            <div class="custom-dropdown-container">
                                <div class="custom-dropdown">
                                    <div class="custom-dropdown-select" onclick="toggleDropdown('defects')">
                                        <span class="arrow-right"><i class="fas fa-chevron-right"></i></span>Select defects
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
                                        <div id="other_defects" class="other-input" style="display: none;">
                                            <label>Specify other defects:</label>
                                            <input type="text" name="apartment_defects_other">
                                        </div>
                                        <div class="close-button-container">
                                            <div class="close-button-1" onclick="closeDropdown('defects')">Close</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Other feedback -->
                        <div class="form-section">
                            <h2><i class="fas fa-comment-dots"></i> Other feedback:</h2>
                            <textarea id="feedback" name="feedback" class="form-control" rows="4" placeholder="Please share any additional feedback or comments..."></textarea>
                        </div>

                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i> Submit Evaluation
                        </button>
                    </form>
                </div>
            </div>
        </main>

        <script>  
            // Function to close the dropdown content
            function closeDropdown(dropdownId) {
                const dropdownContent = document.getElementById(dropdownId + "_content");
                dropdownContent.style.display = "none";
                const dropdownSelect = dropdownContent.parentElement.querySelector(".custom-dropdown-select");
                dropdownSelect.classList.remove("expanded");
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
                    if (naOption) naOption.checked = false;
                } else {
                    otherOptions.style.display = "none";
                }
            }

            function handleNASelection(dropdownId) {
                const dropdown = document.getElementById(dropdownId);
                const naOption = dropdown.querySelector('input[value="N/A"]');
                const otherOptions = dropdown.querySelectorAll('input:not([value="N/A"])');

                // Check if the "N/A" option is selected
                if (naOption && naOption.checked) {
                    // If "N/A" is selected, deselect all other options (excluding "Other")
                    otherOptions.forEach((option) => {
                        if (option.value !== "Others") {
                            option.checked = false;
                        }
                    });
                } else {
                    // If any other option (excluding "Other") is selected, deselect "N/A"
                    if (naOption) naOption.checked = false;
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