<?php
session_name("admin_session");
session_start();
require_once "config.php";

if (!isset($_SESSION['admin_id']) && $_SESSION['dashboard'] !== 'admin_dashboard') {
    // User is not logged in or not authorized for this dashboard
    header('Location: admin_login.php');
    exit();
}
$userId = $_SESSION["admin_unique_id"];

$query = "SELECT title, first_name, last_name FROM admin_registration WHERE unique_identifier = :userId";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':userId', $userId);
$stmt->execute();
$userProfile = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if any of the profile fields are empty or null
$incompleteProfile = false;
foreach ($userProfile as $field) {
    if (empty($field) || is_null($field)) {
        $incompleteProfile = true;
        break;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Other Dashboard</title>
    
</head>
<!-- ... Existing code ... -->
<body>
    <header>
        <div class="header-container">
            <h1>Others Dashboard</h1>
            <img src="Logo_final.png" alt="Logo" class="header-img"> 
            <div class="icon-container">
                <div class="chat-button">
                    <a href="admin_chat.php"><i class="fas fa-comment-alt" style="color:#835237; font-size:40px;"></i></a>
                </div>
                <!-- Notifications Section -->
                <div class="notifications">
                    <!-- Notification Bell Icon -->
                    <div class="notification-icon" id="notificationIcon">
                        <i class="fas fa-bell"></i>
                        <!-- You can add a badge to show the number of unread notifications -->
                        <span class="badge" id="notificationBadge">0</span>
                    </div>

                    <!-- Notification Dropdown (Initially Hidden) -->
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div id="notificationList">
                            <!-- Notifications will be dynamically added here -->
                        </div>
                    </div>
                </div>
                <div class="navbar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars" style="color: black;"></i>
                </div>
                <div class="navbar-toggle" onclick="toggleNavbar()">
                    <i class="fas fa-cog" style="color: black;"></i>
                </div>
            </div>
        </div>
        <nav class="navbar-expanded" id="navbar">
            <ul>
                <li><a href="admin_profile.php" class="navbar-icon" style="color: red;"><i class="fas fa-user" style="color: black"></i>Profile</a></li>
                <li><a href="admin_logout.php" class="navbar-icon" style="color: red;"><i class="fas fa-sign-out-alt" style="color: black"></i> Logout</a></li>
            </ul>
        </nav>
    </header>
    <aside class="sidebar">
        <div class="close-icon" onclick="toggleSidebar()">
            <i class="fas fa-times"></i> <!-- Font Awesome times (close) icon -->
        </div>
        <div class="sidebar-buttons">
            <button id="myBuildingButton" data-target="my-building" class="active">My Building</button>
            <button id="allBuildingsButton" data-target="all-buildings">All Buildings</button>
        </div>
        <div class="sidebar-content" id="my-building" class="active">
            <h2 style="background-color: #000; margin-bottom: 10px; color: #00bcd4; font-weight: 600;">My Buildings</h2>
            <ul class="my-building-list">
                <!-- User's registered buildings will be added here dynamically -->
            </ul>
        </div>
        <div class="sidebar-content" id="all-buildings">
            <h2 style="background-color: #000; margin-bottom: 10px; color: #00bcd4; font-weight: 600;">All Buildings</h2>
            <ul class="all-buildings-list">
                <!-- All registered buildings will be added here dynamically -->
            </ul>
        </div>
    </aside>
    <main>
        <?php
            // Display a message with a link to complete the profile if it's incomplete
            if ($incompleteProfile) {
                echo "<div class='profile-incomplete-message'>Your profile is incomplete. <a href='admin_complete_profile.php'>Complete Profile</a></div>";
            }
        ?>
        <div class="first-compartment">
            <a href="ies.php" class="ies-link">
                <div class="ies-container">
                    <h1 class="ies">Information evaluation page <i class="fas fa-chart-pie" style="margin-left: 10px; color: #050605;"></i></h1>
                </div>
            </a><br>
            <a href="others_register_property.php">
                <div class="ies-container" style="background-color:#454343; margin-bottom: 25px;">
                    <h1 class="ies">Select New Property</h1>
                </div>
            </a><br>
            <a href="tasks.php"class="ies-link">
                <div class="ies-container" style="background-color:#ADA160; margin-bottom:25px;">
                    <h1 class="ies">Tasks<i class="fas fa-question" style="margin-left: 10px; color: #050605;"></i></h1>
                </div>
            </a>
        </div>
        <h1 class="second-compartment-heading">New Listing (Click for more details)</h1>
        <div class="second-compartment">
        </div>
    </main>
    <script>
        function toggleNavbar() {
            const navbar = document.getElementById("navbar");
            navbar.classList.toggle("show");
        }

        function closeNavbar() {
            document.getElementById("navbar-collapsed").style.width = "0";
        }
        // Function to toggle the notification dropdown
        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        // Function to fetch notifications for a specific user
        function fetchNotifications() {
            // Create a new FormData object to send the user ID
            const formData = new FormData();
            formData.append('userId', <?php echo $userId; ?>);

            // Send the AJAX request
            fetch('fetch_notifications.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                // Display notifications in the dropdown
                displayNotifications(data);
                
                // Update the badge count based on unread notifications
                const unreadNotifications = data.filter(notification => notification.read_status === 0);
                const badge = document.getElementById('notificationBadge');
                badge.textContent = unreadNotifications.length;
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        setInterval(fetchNotifications, 5000);
        // Attach click event to the notification icon (assuming you have a userId variable)
        const notificationIcon = document.getElementById('notificationIcon');
        notificationIcon.addEventListener('click', () => {
            toggleNotificationDropdown();
            markAllNotificationsAsRead();
            fetchNotifications(); // Pass the user ID to the fetchNotifications function
        });

        // Close the dropdown when clicking outside of it
        document.addEventListener('click', event => {
            if (!event.target.closest('.notifications')) {
                const dropdown = document.getElementById('notificationDropdown');
                dropdown.style.display = 'none';
            }
        });


        function displayNotifications(notifications) {
            const notificationsContainer = document.getElementById('notificationList');
            
            // Clear previous notifications
            notificationsContainer.innerHTML = '';

            if (notifications.length === 0) {
                // Display a message if there are no notifications with text color set to black
                notificationsContainer.innerHTML = '<p style="color: black;">No notifications.</p>';
            } else {
                // Loop through the notifications and create HTML elements to display them
                notifications.forEach(notification => {
                    const notificationDiv = document.createElement('div');
                    notificationDiv.classList.add('notification-item'); // Add a class for styling

                    // Set the text color to black for notification messages
                    notificationDiv.innerHTML = `
                        <p style="color: black;">${notification.message}</p>
                        <p class="timestamp">${notification.created_at}</p>
                    `;
                    
                    // Add an event listener to mark the notification as read when clicked
                    notificationDiv.addEventListener('click', () => {
                        // Update the read status to 1 (read)
                        markNotificationAsRead(notification.id);
                        
                        // Redirect or perform other actions as needed when a notification is clicked
                    });
                    
                    notificationsContainer.appendChild(notificationDiv);
                });
            }
        }

        function markAllNotificationsAsRead() {
            // Create a new FormData object to send the user ID
            const formData = new FormData();
            formData.append('userId', <?php echo $userId; ?>);

            // Send the AJAX request to mark all notifications as read
            fetch('mark_notification_as_read.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                // Handle the response, e.g., update the UI or perform other actions
                if (data.success) {
                    // All notifications have been marked as read; you can update the UI here if needed
                } else {
                    // Handle errors if necessary
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        // Call the fetch functions to load queries when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            fetchNotifications();
        });

        // Function to fetch and display recent images in a grid layout
        function fetchRecentImages() {
            fetch('fetch_recent_images.php')
                .then(response => response.json())
                .then(data => {
                    const secondCompartment = document.querySelector('.second-compartment');
                    secondCompartment.innerHTML = '';

                    if (data.length > 0) {
                        const gridContainer = document.createElement('div');
                        gridContainer.className = 'image-grid';
                        secondCompartment.appendChild(gridContainer);

                        data.forEach(image => {
                            const imageElement = document.createElement('img');
                            imageElement.src = image.image_path;
                            imageElement.alt = image.apartment_type;

                            // Add a click event to display image details
                            imageElement.addEventListener('click', () => {
                                displayImageDetails(image);
                            });

                            gridContainer.appendChild(imageElement);
                        });
                    } else {
                        secondCompartment.textContent = 'No recent images found.';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function displayImageDetails(image) {
            const secondCompartment = document.querySelector('.second-compartment');
            secondCompartment.innerHTML = `
                <div class="displayed-image-container">
                    <button id="closeButton" class="close-button"><i class="fas fa-times"></i></button>
                    <div class="image-details-container">
                        <div class="image-details">
                            <h2 class="label">Title:</h2>
                            <p style="color: red;">${image.apartment_type}</p>
                            <h2 class="label">Details:</h2>
                            <p style="color: red;">${image.other_details}</p>
                            <h2 class="label">Created at:</h2>
                            <p style="color: red;">${image.created_at}</p>
                            <p class="disclaimer">Acknowledgement: The images used on this platform are for demonstration only and are downloaded from Centaline Property Agency Limited website.</p>
                        </div>
                        <img src="${image.image_path}" alt="${image.apartment_type}" class="displayed-image">
                    </div>
                </div>
            `;

            // Add styles for the disclaimer text
            const disclaimerStyle = `
                .disclaimer {
                    font-style: italic;
                    color: #bdc3c7;
                    font-size: 12px;
                    margin-top: 10px;
                }
            `;

            // Create a style element and append it to the head
            const styleElement = document.createElement('style');
            styleElement.innerHTML = disclaimerStyle;
            document.head.appendChild(styleElement);

            // Add an event listener to close the image details
            const closeButton = document.getElementById('closeButton');
            closeButton.addEventListener('click', () => {
                fetchRecentImages(); // Reload recent images when closing
            });
        }

        // Call the fetchRecentImages function to load recent images when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            fetchRecentImages();
        });

         // Function to toggle the sidebar
         function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar.style.left === '-300px' || sidebar.style.left === '') {
                sidebar.style.left = '0';
            } else {
                sidebar.style.left = '-300px';
            }
        }

        // Function to populate the "My Buildings" section of the sidebar
        function populateMyBuildings() {
            const formData = new FormData();
            formData.append('userId', <?php echo $userId; ?>);

            const url = 'fetch_admin_my_buildings.php';

            fetch(url, {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                // Handle the response data
                const myBuildingList = document.querySelector('.my-building-list');
                myBuildingList.innerHTML = '';

                data.forEach(building => {
                    const listItem = document.createElement('li');
                    listItem.textContent = building.property_name;
                    listItem.addEventListener('click', () => {
                        // Remove the "selected" class from any previously selected building
                        const selectedBuilding = document.querySelector('.my-buildings .selected');
                        if (selectedBuilding) {
                            selectedBuilding.classList.remove('selected');
                        }
                        
                        // Add the "selected" class to the clicked building
                        listItem.classList.add('selected');

                        // Handle the click on the building
                        openBuildingModal(listItem, building.property_id); // Pass building ID and data
                    });

                    myBuildingList.appendChild(listItem);
                });
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Function to populate the "All Buildings" section of the sidebar
        function populateAllBuildings() {
            const formData = new FormData();
            const url = 'fetch_all_buildings.php';

            fetch(url, {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                // Handle the response data
                const allBuildingList = document.querySelector('.all-buildings-list');
                allBuildingList.innerHTML = '';
                data.forEach(building => {
                    const listItem = document.createElement('li');
                    listItem.textContent = building.property_name;
                    listItem.addEventListener('click', () => {
                        // Remove the "selected" class from any previously selected building
                        const selectedBuilding = document.querySelector('.all-buildings .selected');
                        if (selectedBuilding) {
                            selectedBuilding.classList.remove('selected');
                        }
                        
                        // Add the "selected" class to the clicked building
                        listItem.classList.add('selected');

                        // Handle the click on the building
                        openBuildingModal(listItem, building.property_id); // Pass building ID and data
                    });

                    allBuildingList.appendChild(listItem);
                });
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function openBuildingModal(buildingElement, buildingId) {
            // Navigate to another page with building ID as a parameter
            window.location.href = 'feedback_page.php?building_id=' + buildingId;
        }

        // Function to toggle the active content
        function toggleContent(target) {
            const contentElements = document.querySelectorAll('.sidebar-content');
            contentElements.forEach(content => {
                if (content.id === target) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });
        }

        // Event listeners for button clicks
        document.getElementById('myBuildingButton').addEventListener('click', function () {
            toggleContent('my-building');
        });

        document.getElementById('allBuildingsButton').addEventListener('click', function () {
            toggleContent('all-buildings');
        });

        // Function to toggle the active content
        function toggleContent(target) {
            const contentElements = document.querySelectorAll('.sidebar-content');
            const buttonElements = document.querySelectorAll('.sidebar-buttons button');

            contentElements.forEach(content => {
                if (content.id === target) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });

            buttonElements.forEach(button => {
                if (button.getAttribute('data-target') === target) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
        }

        // Event listeners for button clicks
        document.getElementById('myBuildingButton').addEventListener('click', function () {
            toggleContent('my-building');
        });

        document.getElementById('allBuildingsButton').addEventListener('click', function () {
            toggleContent('all-buildings');
        });

        // Set "My Building" as the default on page load
        window.onload = function () {
            toggleContent('my-building');
        };
        // Call the functions to populate the lists when the page loads
        populateMyBuildings();
        populateAllBuildings();
    </script>
</body>
</html>
