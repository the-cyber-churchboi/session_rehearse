<?php
session_name("user_session");
session_start();
require_once "config.php";

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    // If the user is not logged in, redirect them to the login page
    header("Location: user_login.php");
    exit();
}

$userId = $_SESSION["user_unique_id"];

$query = "SELECT property_id, property_name FROM user_property_registration WHERE user_id = :userId";
$stmt = $pdo->prepare($query);
$stmt->bindParam(":userId", $userId);
$stmt->execute();
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinycolor/1.4.2/tinycolor.min.js"></script>
    <title>User Dashboard</title>
</head>
<body>
    <header>
        <div class="header-container">
            <a class="back-link" href="user_dashboard.php">&#8678;</a>
            <h1>Requirements and Queries</h1>
            <img src="Logo_final.png" alt="Logo" class="header-img">
            <ul>
                <li>
                    <div class="search-filter-queries-section">
                        <label for="queryTitle">Title:</label>
                        <input type="text" id="queryTitle" placeholder="Search by title">

                        <label for="queryDescription">Description:</label>
                        <input type="text" id="queryDescription" placeholder="Search by description">

                        <label for="queryStatus">Status:</label>
                        <select id="queryStatus">
                            <option value="">All</option>
                            <option value="Open">Open</option>
                            <option value="Closed">Closed</option>
                        </select>

                        <button onclick="searchAndFilterQueries()">Apply Filters</button>
                    </div>
                </li>
                <div id="filterModal" class="modal">
                    <div class="modal-content">
                        <div id="queriesContainer" class="filtered-queries"></div>
                    </div>
                </div>
            </ul>
        </div>
    </header>
    <div class="grid-container">
        <div class="grid-item">
            <!-- Apartment Customization Section -->
            <section class="apartment-customization" id="customizationSection">
                <button class="section-header" id="customizationHeader" onclick="toggleSection('customizationContent', 'customizationArrow')">Apartment Customization <span id="customizationArrow" style="display: inline-block; margin-left: 5px;">▼</span></button>
                <div id="customizationContent">
                    <!-- Building Dropdown -->
                    <div class="customization-group">
                        <h3>Select a Building</h3>
                        <div class="building-selection">
                            <label for="buildingSelectOpen">Choose a Building:</label>
                            <select id="buildingSelectOpen">
                                <?php foreach ($properties as $property): ?>
                                    <option value="<?php echo $property['property_id']; ?>"><?php echo $property['property_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <!-- Preset Themes -->
                    <div class="customization-group">
                        <h3>Select a Theme</h3>
                        <div class="theme-selection">
                            <label for="themeSelect">Choose a Theme:</label>
                            <select id="themeSelect">
                                <option value="Modern">Modern</option>
                                <option value="Vintage">Vintage</option>
                                <option value="Minimalistic">Minimalistic</option>
                                <!-- Add more theme options as needed -->
                            </select>
                            <button id="saveTheme">Save Theme</button>
                        </div>
                    </div>

                    <!-- Choose Colors -->
                    <div class="customization-group">
                        <h3>Choose Colors</h3>
                        <div class="color-selection">
                            <div class="color-group">
                                <label for="wallColor">Wall Color:</label>
                                <input type="color" id="wallColor" value="#ffffff">
                            </div>
                            <div class="color-group">
                                <label for="furnitureColor">Furniture Color:</label>
                                <input type="color" id="furnitureColor" value="#000000">
                            </div>
                            <!-- Add more color inputs for other aspects as needed -->
                            <button id="saveColors">Save Colors</button>
                        </div>
                    </div>

                    <!-- Select Interior Finishes -->
                    <div class="customization-group">
                        <h3>Select Interior Finishes</h3>
                        <div class="finish-selection">
                            <div class="finish-group">
                                <label for="wallFinish">Wall Finish:</label>
                                <select id="wallFinish">
                                    <option value="Matte">Matte</option>
                                    <option value="Gloss">Gloss</option>
                                    <option value="Wood Finish">Wood Finish</option>
                                    <!-- Add more finish options as needed -->
                                </select>
                            </div>
                            <div class="finish-group">
                                <label for="floorFinish">Floor Finish:</label>
                                <select id="floorFinish">
                                    <option value="Matte">Matte</option>
                                    <option value="Gloss">Gloss</option>
                                    <option value="Wood Finish">Wood Finish</option>
                                    <!-- Add more finish options as needed -->
                                </select>
                            </div>
                            <!-- Add more finish options for different parts of the apartment as needed -->
                            <button id="saveFinishes">Save Finishes</button>
                        </div>
                    </div>

                    <!-- Select Tiles -->
                    <div class="customization-group">
                        <h3>Select Tiles</h3>
                        <div class="tile-selection">
                            <div class="tile-group">
                                <label for="wallTiles">Wall Tiles:</label>
                                <select id="wallTiles">
                                    <option value="Ceramic">Ceramic</option>
                                    <option value="Porcelain">Porcelain</option>
                                    <option value="Glass">Glass</option>
                                    <option value="Marble">Marble</option>
                                    <!-- Add more wall tile options as needed -->
                                </select>
                            </div>
                            <div class="tile-group">
                                <label for="floorTiles">Floor Tiles:</label>
                                <select id="floorTiles">
                                    <option value="Laminate">Laminate</option>
                                    <option value="Hardwood">Hardwood</option>
                                    <option value="Vinyl">Vinyl</option>
                                    <option value="Stone">Stone</option>
                                    <!-- Add more floor tile options as needed -->
                                </select>
                            </div>
                            <!-- Add more tile selection options as needed -->
                            <button id="saveTiles">Save Tiles</button>
                        </div>
                    </div>

                    <!-- Upload Images -->
                    <div class="customization-group">
                        <h3>Upload Images</h3>
                        <div class="image-upload">
                            <input type="file" id="imageInput" accept="image/*" multiple>
                            <button id="uploadImages">Upload Images</button>
                        </div>
                        <div id="selectedImages"></div>
                    </div>
                    <div id="retrievedImages"></div> 

                    <!-- Additional Requirements -->
                    <div class="customization-group">
                        <h3>Additional Requirements</h3>
                        <div class="additional-requirements">
                            <label for="requirements">Enter your additional requirements:</label>
                            <textarea id="requirements" rows="4" cols="50"></textarea>
                            <button id="saveRequirements">Save Requirements</button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Requirements Section -->
            <section class="requirements-section" id="requirementsSection">
            <button class="section-header" id="requirementsHeader" onclick="toggleSection('requirementsContent', 'requirementsArrow')">Apartment Requirements <span id="requirementsArrow" style="display: inline-block; margin-left: 5px;">▼</span></button><br>
                <div id="requirementsContent">
                
                <!-- Display a message if no requirements exist -->
                <p id="noRequirementsMessage" style="display: none;">No requirements created. Click the button below to create your requirements.</p>

                <!-- Create a button for creating requirements -->
                <button id="createButton" onclick="showCustomization()">Create Requirements</button>
                </div>
            </section>
        </div>
        <div class="grid-item">
            <!-- Open Queries Section -->
            <section class="open-queries-section" id="openQueriesSection">
                <button class="section-header" id="openQueriesHeader" onclick="toggleSection('openQueriesContent', 'openQueriesArrow')">Open Queries <span id="openQueriesArrow" style="display: inline-block; margin-left: 5px;">▼</span></button><br>
                <div id="openQueriesContent">
                    <table class="query-table" id="openQueriesTable">
                        <thead>
                            <tr>
                                <th>Building Name</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Date Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
                <button class="create-query-button" id="createQueryButton" onclick="showCreateQueryForm()">Create Query</button>
                <div id="createQueryForm" style="display: none;">
                    <h3>Create a New Query</h3>
                    <button class="close-button" onclick="closeQueryForm()">
                        <i class="fas fa-times"></i> <!-- Font Awesome close icon -->
                    </button>
                    <form id="queryForm" onsubmit="submitQuery(event)" class="query-form">
                    <!-- Building Dropdown in Create Query Form -->
                        <div class="form-group">
                            <label for="buildingSelectCreate">Select a Building:</label>
                            <select id="buildingSelectCreate" name="buildingSelectCreate">
                                <?php foreach ($properties as $property): ?>
                                    <option value="<?php echo $property['property_id']; ?>"><?php echo $property['property_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="queryTitle">Title:</label>
                            <input type="text" id="queryTitle_1" name="queryTitle" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="queryDescription">Description:</label>
                            <textarea id="queryDescription_1" name="queryDescription" class="form-control" rows="4" cols="50" required></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Submit Query</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
        <div class="grid-item">
            <!-- Closed Queries Section -->
            <section class="closed-queries-section" id="closedQueriesSection">
                <button class="section-header" id="closedQueriesHeader" onclick="toggleSection('closedQueriesContent', 'closedQueriesArrow')">Closed Queries <span id="closedQueriesArrow" style="display: inline-block; margin-left: 5px;">▼</span></button><br>
                <div id="closedQueriesContent">
                    <table class="query-table" id="closedQueriesTable">
                        <thead>
                            <tr>
                                <th>Building Name</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Date Created</th>
                                <th>Date Closed</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        <div class="grid-item">
            <!-- Task Status Section -->
            <section class="task-status-section" id="taskStatusSection">
                <button class="section-header" id="taskStatusHeader" onclick="toggleSection('taskStatusContent', 'taskStatusArrow')">Task Status <span id="taskStatusArrow" style="display: inline-block; margin-left: 5px;">▼</span></button><br>
                <div id="taskStatusContent">
                    <table class="query-table" id="taskTable">
                        <thead>
                            <tr>
                                <th>Building Name</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Assigned Personnel</th>
                                <th>Profession</th>
                                <th>Date Created</th>
                                <th>Date Updated</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeSelect = document.getElementById('themeSelect');
            const saveThemeButton = document.getElementById('saveTheme');
            const buildingSelect = document.getElementById('buildingSelectOpen');

            // Add an event listener to the "Save Theme" button.
            saveThemeButton.addEventListener('click', function() {
                const selectedTheme = themeSelect.value;
                const selectedPropertyId =buildingSelect.value;

                // Create a FormData object and append the data
                const formData = new FormData();
                formData.append('userId', <?php echo $userId; ?>);
                formData.append('theme', selectedTheme);
                formData.append('building', buildingSelect.options[buildingSelect.selectedIndex].text);
                formData.append('propertyId', selectedPropertyId);

                // Send the data to the server using an HTTP request (e.g., AJAX or Fetch).
                fetch('save_theme.php', { // Corrected URL
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Theme saved successfully.');
                        location.reload();
                    } else {
                        alert('Error saving theme.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            const wallColorInput = document.getElementById('wallColor');
            const furnitureColorInput = document.getElementById('furnitureColor');
            const saveColorsButton = document.getElementById('saveColors');
            const buildingSelect = document.getElementById('buildingSelectOpen');

            // Add an event listener to the "Save Colors" button.
            saveColorsButton.addEventListener('click', function() {
                const selectedWallColor = wallColorInput.value;
                const selectedFurnitureColor = furnitureColorInput.value;
                const selectedPropertyId =buildingSelect.value;

                // Create a FormData object and append the data
                const formData = new FormData();
                formData.append('userId', <?php echo $userId; ?>);
                formData.append('wallColor', selectedWallColor);
                formData.append('furnitureColor', selectedFurnitureColor);
                formData.append('building', buildingSelect.options[buildingSelect.selectedIndex].text);
                formData.append('propertyId', selectedPropertyId);

                // Send the data to the server using an HTTP request (e.g., AJAX or Fetch).
                fetch('save_colors.php', { // Corrected URL
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Colors saved successfully.');
                        location.reload();
                    } else {
                        alert('Error saving colors.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const wallFinishSelect = document.getElementById('wallFinish');
            const floorFinishSelect = document.getElementById('floorFinish');
            const saveFinishesButton = document.getElementById('saveFinishes');
            const buildingSelect = document.getElementById('buildingSelectOpen');

            // Add an event listener to the "Save Finishes" button.
            saveFinishesButton.addEventListener('click', function() {
                const selectedWallFinish = wallFinishSelect.value;
                const selectedFloorFinish = floorFinishSelect.value;
                const selectedPropertyId =buildingSelect.value;

                // Create a FormData object and append the data
                const formData = new FormData();
                formData.append('userId', <?php echo $userId; ?>);
                formData.append('wallFinish', selectedWallFinish);
                formData.append('floorFinish', selectedFloorFinish);
                formData.append('building', buildingSelect.options[buildingSelect.selectedIndex].text);
                formData.append('propertyId', selectedPropertyId);

                // Send the data to the server using an HTTP request (e.g., AJAX or Fetch).
                fetch('save_finishes.php', { // Corrected URL
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Finishes saved successfully.');
                        location.reload();
                    } else {
                        alert('Error saving finishes.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            const wallTilesSelect = document.getElementById('wallTiles');
            const floorTilesSelect = document.getElementById('floorTiles');
            const saveTilesButton = document.getElementById('saveTiles');
            const buildingSelect = document.getElementById('buildingSelectOpen');

            // Add an event listener to the "Save Tiles" button.
            saveTilesButton.addEventListener('click', function() {
                const selectedWallTiles = wallTilesSelect.value;
                const selectedFloorTiles = floorTilesSelect.value;
                const selectedPropertyId =buildingSelect.value;

                // Create a FormData object and append the data
                const formData = new FormData();
                formData.append('userId', <?php echo $userId; ?>);
                formData.append('wallTiles', selectedWallTiles);
                formData.append('floorTiles', selectedFloorTiles);
                formData.append('building', buildingSelect.options[buildingSelect.selectedIndex].text);
                formData.append('propertyId', selectedPropertyId);

                // Send the data to the server using an HTTP request (e.g., AJAX or Fetch).
                fetch('save_tiles.php', { // Corrected URL
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Tiles saved successfully.');
                        location.reload();
                    } else {
                        alert('Error saving tiles.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('imageInput');
            const uploadImagesButton = document.getElementById('uploadImages');
            const buildingSelect = document.getElementById('buildingSelectOpen');

            // Add an event listener to the "Upload Images" button.
            uploadImagesButton.addEventListener('click', function() {
                const selectedImages = imageInput.files;
                const selectedPropertyId =buildingSelect.value;

                // Create a FormData object and append the data
                const formData = new FormData();
                formData.append('userId', <?php echo $userId; ?>);
                formData.append('building', buildingSelect.options[buildingSelect.selectedIndex].text);
                formData.append('propertyId', selectedPropertyId);
                for (let i = 0; i < selectedImages.length; i++) {
                    formData.append('images[]', selectedImages[i]);
                }

                // Send the data to the server using an HTTP request (e.g., AJAX or Fetch).
                fetch('upload_images.php', { // Corrected URL
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Images uploaded successfully.');
                        location.reload();
                    } else {
                        alert('Error uploading images.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            const requirementsTextarea = document.getElementById('requirements');
            const saveRequirementsButton = document.getElementById('saveRequirements');
            const buildingSelect = document.getElementById('buildingSelectOpen');

            // Add an event listener to the "Save Requirements" button.
            saveRequirementsButton.addEventListener('click', function() {
                const userRequirements = requirementsTextarea.value;
                const selectedPropertyId =buildingSelect.value;

                // Create a FormData object to send the user requirements to the server
                const formData = new FormData();
                formData.append('requirements', userRequirements);
                formData.append('userId', <?php echo $userId; ?>);
                formData.append('building', buildingSelect.options[buildingSelect.selectedIndex].text);
                formData.append('propertyId', selectedPropertyId);

                // Send the FormData to the server using fetch
                fetch('save_requirements.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Requirements saved successfully.');
                        showRequirements();
                    } else {
                        alert('Error saving requirements.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });

        const customizationSection = document.getElementById('customizationSection');
        const requirementsSection = document.getElementById('requirementsSection');
        const requirementsContent = document.getElementById('requirementsContent');

        // Function to show the Apartment Customization section
        function showCustomization() {
            customizationSection.style.display = 'block';
            requirementsSection.style.display = 'none';
        }

        function toggleSection(sectionId, arrowId) {
            const section = document.getElementById(sectionId);
            const arrow = document.getElementById(arrowId);

            if (section.style.display === 'none' || section.style.display === '') {
                section.style.display = 'block';
                arrow.textContent = '▼'; // Change to a downward-pointing arrow
            } else {
                section.style.display = 'none';
                arrow.textContent = '◄'; // Change to a left-pointing arrow
            }
        }

        function showRequirements() {
            const customizationSection = document.getElementById('customizationSection');
            const noRequirementsMessage = document.getElementById('noRequirementsMessage');
            const createButton = document.getElementById('createButton');
            const requirementHeader = document.getElementById('requirementsHeader');
            const buildingSelect = document.getElementById('buildingSelectOpen');

            // Create a FormData object and append the userId
            const formData = new FormData();
            formData.append('userId', <?php echo $userId; ?>);
            formData.append('property_id', buildingSelect.value);

            fetch('get_requirements.php', {
                method: 'POST', // Use POST method to send FormData
                body: formData, // Pass the FormData object
            })
            .then(response => response.json())
            .then(data => {
                // Check if any customization data exists
                const hasCustomization = data.theme || data.wall_color || data.furniture_color || data.wall_finish || data.floor_finish || data.wall_tile_style || data.floor_tile_style || data.image_urls;
                const retrievedImagesContainer = document.getElementById('retrievedImages');
                if (hasCustomization) {
                    // User has customization data; set the values of HTML elements
                    themeSelect.value = data.theme;
                    wallColor.value = data.wall_color;
                    furnitureColor.value = data.furniture_color;
                    wallFinish.value = data.wall_finish;
                    floorFinish.value = data.floor_finish;
                    wallTiles.value = data.wall_tile_style;
                    floorTiles.value = data.floor_tile_style;
                    requirements.value = data.additional_requirements;

                    const imageUrls = JSON.parse(data.image_urls);
                    retrievedImagesContainer.innerHTML = '';
                    // After displaying the images, add delete buttons for each image
                    imageUrls.forEach((url, index) => {
                        const imgContainer = document.createElement('div');
                        const img = document.createElement('img');
                        img.src = url;
                        img.style.width = '200px';
                        img.style.height = '150px';
                        
                        const deleteButton = document.createElement('button');
                        deleteButton.textContent = 'Delete';
                        deleteButton.addEventListener('click', () => deleteImage(index, data)); 

                        imgContainer.appendChild(img);
                        imgContainer.appendChild(deleteButton);
                        retrievedImagesContainer.appendChild(imgContainer);
                    });

                    // Show the "Edit" button and hide the "Create Requirements" message
                    customizationSection.style.display = 'block';
                    createButton.style.display = 'none';
                    noRequirementsMessage.style.display = 'none';
                    requirementHeader.style.display = 'none';
                } else {
                    // No customization data found; show the "Create Requirements" button and the message
                    customizationSection.style.display = 'none';
                    createButton.style.display = 'block';
                    noRequirementsMessage.style.display = 'block';
                    requirementHeader.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        // Initial page load
        document.addEventListener('DOMContentLoaded', function() {

            const buildingSelect = document.getElementById('buildingSelectOpen');

            // Add an event listener to the building selection dropdown
            buildingSelect.addEventListener('change', function() {
                showRequirements();
            });

            showRequirements();
            fetchOpenQueries();
            fetchClosedQueries();
            fetchTaskStatus();
        });
        

        // Function to handle image selection and display selected images
        document.getElementById('imageInput').addEventListener('change', function (event) {
            const selectedImagesContainer = document.getElementById('selectedImages');
            selectedImagesContainer.innerHTML = ''; // Clear any previously displayed images

            const files = event.target.files;
            for (let i = 0; i < files.length; i++) {
                const image = document.createElement('img');
                image.src = URL.createObjectURL(files[i]);
                image.classList.add('selected-image');
                image.style.width = '200px'; 
                image.style.height = '150px'; 
                selectedImagesContainer.appendChild(image);
            }
        });

        function deleteImage(index, data) {
            // Remove the image from the UI
            const retrievedImagesContainer = document.getElementById('retrievedImages');
            retrievedImagesContainer.removeChild(retrievedImagesContainer.childNodes[index]);

            // Send a request to the server to delete the image from the database
            const imageUrls = JSON.parse(data.image_urls);
            const imageUrlToDelete = imageUrls[index];

            // Create a FormData object and append the data
            const formData = new FormData();
            formData.append('userId', <?php echo $userId; ?>);
            formData.append('imageUrlToDelete', imageUrlToDelete);

            // Send the data to the server using an HTTP request (e.g., AJAX or Fetch).
            fetch('delete_image.php', { // Replace with the actual URL to delete the image
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(deleteData => {
                if (deleteData.success) {
                    alert('Image deleted successfully.');

                    showRequirements();
                } else {
                    alert('Error deleting image.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Function to show the query creation form
        function showCreateQueryForm() {
            const createQueryForm = document.getElementById('createQueryForm');

            // Display the form
            createQueryForm.style.display = 'block';
        }

        // Function to handle creating a new query
        function submitQuery(event) {
            event.preventDefault(); // Prevent the default form submission behavior

            console.log('Submit Query function called');

            const queryTitleInput = document.getElementById('queryTitle_1');
            const queryDescriptionInput = document.getElementById('queryDescription_1');
            const buildingSelect = document.getElementById('buildingSelectCreate');

            const title = queryTitleInput.value.trim();
            const description = queryDescriptionInput.value.trim();
            const selectedPropertyId = buildingSelect.value;
            const selectedPropertyName = buildingSelect.options[buildingSelect.selectedIndex].text;

            if (title && description && selectedPropertyId && selectedPropertyName) {
                // Send the query title and description to the server using an AJAX or Fetch request
                const formData = new FormData();
                formData.append('userId', <?php echo $userId; ?>);
                formData.append('title', title);
                formData.append('description', description);
                formData.append('propertyId', selectedPropertyId);
                formData.append('building', selectedPropertyName);

                fetch('create_query.php', { // Replace with the actual URL to create a query
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Form Data:', formData); // Add this line to your submitQuery function
                    if (data.success) {
                        alert('Query created successfully.');
                        location.reload();
                    } else {
                        alert('Error creating query.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        }


        // Add event listeners to the buttons
        const createQueryButton = document.getElementById('createQueryButton');
        if (createQueryButton) {
            createQueryButton.addEventListener('click', showCreateQueryForm);
        }

        // Function to fetch and display open queries
        function fetchOpenQueries() {
            // Create a FormData object and append the user ID
            const formData = new FormData();
            formData.append('userId', <?php echo $userId; ?>);

            // Make an AJAX request to your server to retrieve open queries
            fetch('fetch_open_queries.php', {
                method: 'POST', // Use POST method to fetch data
                body: formData, // Pass the FormData object
            })
            .then(response => response.json())
            .then(data => {
                // Handle the fetched data and display it in the UI
                const openQueriesTable = document.getElementById('openQueriesTable').getElementsByTagName('tbody')[0];
                const openQueriesContainer = document.getElementById('openQueriesContent');
                openQueriesTable.innerHTML = ''; // Clear previous content

                if (data.length === 0) {
                    openQueriesContainer.innerHTML = 'No open queries.';
                } else {
                    data.forEach(query => {
                        const row = openQueriesTable.insertRow();
                        row.innerHTML = `
                            <td>${query.building}</td>
                            <td>${query.title}</td>
                            <td>${query.description}</td>
                            <td>${query.created_at}</td>
                        `;
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching open queries:', error);
            });
        }

        // Function to fetch and display closed queries
        function fetchClosedQueries() {
            // Create a FormData object and append the user ID
            const formData = new FormData();
            formData.append('userId', <?php echo $userId; ?>);

            // Make an AJAX request to your server to retrieve closed queries
            fetch('fetch_closed_queries.php', {
                method: 'POST', // Use POST method to fetch data
                body: formData, // Pass the FormData object
            })
            .then(response => response.json())
            .then(data => {
                // Handle the fetched data and display it in the UI
                const closedQueriesContainer = document.getElementById('closedQueriesContent');
                const closedQueriesTable = document.getElementById('closedQueriesTable').getElementsByTagName('tbody')[0];
                closedQueriesTable.innerHTML = ''; // Clear previous content

                if (data.length === 0) {
                    closedQueriesContainer.innerHTML = 'No closed queries.';
                } else {
                    data.forEach(query => {
                        const row = closedQueriesTable.insertRow();
                        row.innerHTML = `
                            <td>${query.building}</td>
                            <td>${query.title}</td>
                            <td>${query.description}</td>
                            <td>${query.created_at}</td>
                            <td>${query.closed_at}</td>
                        `;
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching closed queries:', error);
            });
        }

        

        // Function to fetch and display task status
        function fetchTaskStatus() {
            // Send a request to the server to fetch task status for the user's apartment
            const formData = new FormData();
            formData.append('userId', <?php echo $userId; ?>);

            fetch('fetch_task_status.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                console.log('Data received:', data);
                // Display task status information
                const taskStatusContent = document.getElementById('taskStatusContent');
                const taskTable = document.getElementById('taskTable').getElementsByTagName('tbody')[0];
                taskTable.innerHTML = ''; // Clear previous content

                if (data.length === 0) {
                    console.log("No tasks found.");
                    taskStatusContent.innerHTML = 'No tasks found.';
                } else {
                    data.forEach(task => {
                        const row = taskTable.insertRow();
                        row.innerHTML = `
                            <td>${task.building}</td>
                            <td>${task.task_title}</td>
                            <td>${task.task_description}</td>
                            <td>${task.task_status}</td>
                            <td>${task.assigned_person}</td>
                            <td>${task.profession}</td>
                            <td>${task.created_at}</td>
                            <td>${task.updated_at}</td>
                        `;
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Function to close the modal
        function closeModal() {
            const modal = document.getElementById('filterModal');
            modal.style.display = 'none';
        }

        // Function to open the modal when applying filters
        function openModal() {
            const modal = document.getElementById('filterModal');
            modal.style.display = 'block';
        }

        // Close the modal when the page loads
        window.addEventListener('load', function() {
            closeModal();
        });

        function searchAndFilterQueries() {
            const queryTitle = document.getElementById('queryTitle').value;
            const queryDescription = document.getElementById('queryDescription').value;
            const queryStatus = document.getElementById('queryStatus').value;

            const formData = new FormData();
            formData.append('userId', <?php echo $userId; ?>); // Include userId
            formData.append('title', queryTitle);
            formData.append('description', queryDescription);
            formData.append('status', queryStatus);

            // Fetch queries from the server
            fetch('fetch_queries.php', {
                method: 'POST',
                body: formData, // Send the FormData object
            })
            .then(response => response.json())
            .then(data => {
                // Process and display filtered queries
                displayFilteredQueries(data);
                openModal();
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function displayFilteredQueries(queries) {
            const queriesContainer = document.getElementById('queriesContainer');
            queriesContainer.innerHTML = ''; // Clear previous query results

            if (queries.length === 0) {
                queriesContainer.innerHTML = '<p style=color:black;>No matching queries found.</p>';
            } else {
                // Loop through the filtered queries and display them
                queries.forEach(query => {
                    const queryDiv = document.createElement('div');
                    queryDiv.innerHTML = `<h3>${query.title}</h3><p>${query.description}</p><p>Status: ${query.status}</p>`;
                    queriesContainer.appendChild(queryDiv);
                });
            }
        }

        function closeQueryForm() {
            var queryForm = document.getElementById("createQueryForm");
            queryForm.style.display = "none";
        }

       // Function to close the modal when clicking outside the queries container
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('filterModal');
            const modalContent = document.querySelector('.modal-content');

            // Check if the click event occurred outside the modal content
            if (event.target === modal) {
                closeModal(); // Close the modal
            }
        });

        // Prevent clicks inside the modal content from closing the modal
        document.querySelector('.modal-content').addEventListener('click', function(event) {
            event.stopPropagation();
        });
    </script>
</body>
</html>