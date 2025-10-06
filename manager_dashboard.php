<?php
session_name("admin_session");
session_start();
require_once "config.php";

if (!isset($_SESSION['admin_id']) && $_SESSION['dashboard'] !== 'manager_dashboard') {
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
    <title>Manager Dashboard</title>
    <!-- Add your CSS styling here or link to an external CSS file -->
    <style>
       /* Reset some default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #B8BBBE;
        }

        /* Header Styles */
        header {
            background-color: white;
            color: black;
            text-align: center;
            padding: 20px 0;
            border: 2px solid black;
        }

        .chat-button {
            cursor: pointer;
            font-size: 24px;
            margin-right: 20px;
            color: #fff;
        }

        .chat-button:hover {
            color: #00bcd4;
        }

        .chart-link {
            text-decoration: none;
            color: #fff;
            display: flex;
            align-items: center;
        }

        .chart-link:hover {
            color: #00bcd4;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-container h1 {
            font-size: 24px;
        }

        .icon-container {
            display: flex;
            align-items: center;
        }

        .icon-container a {
            color: #fff;
            text-decoration: none;
            margin-right: 20px;
            font-size: 20px;
        }

        .logout-link {
            text-decoration: none;
            color: black;
            font-size: 24px;
            margin-right: 20px;
            padding-left: 50px;
        }

        .logout-link:hover {
            color: #ff0000; /* Change the color on hover */
        }

        /* Styling for the notification icon and badge */
        .notification-icon {
            position: relative;
            cursor: pointer;
            margin-right: 10px;
        }

        .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 4px 8px;
        }

        .header-img {
            width: 80px;
            height: 70px;
        }

        /* Style for notification dropdown */
        .notification-dropdown {
            position: absolute;
            top: 80px;
            right: 10px;
            width: 300px;
            max-height: 300px;
            overflow-y: auto;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            display: none;
        }

        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
        }

        .timestamp {
            color: #888;
            font-size: 12px;
            margin-top: 5px;
        }

        /* Styling for individual notifications in the dropdown */
        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }

        .notification-item:last-child {
            border-bottom: none; /* Remove border for the last item */
        }

        /* Styling for notification text and timestamp */
        #notificationList {
            font-weight: bold;
            color: black;
        }

        a {
            text-decoration: none;
        }

        .ies {
            color: white;
            text-align: center;
        }

        .ies-container {
            display: inline-block;
            background-color: #5386B6;
            padding: 10px;
            margin-left: 80px;
            margin-top: 20px;
        }

        .first-compartment {
            border: 2px solid black;
            background-color: #D2D1E3;
        }

        .second-compartment-heading {
            text-align: center;
            text-decoration: underline;
        }

       /* CSS for the image grid layout */
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            justify-items: center;
            align-items: center;
            margin: 10px;
        }

        .image-grid img {
            cursor: pointer;
            max-width: 220px;
            max-height: 220px;
            width: 100%;
            height: 100%;
        }

        .label {
            font-weight: bold;
        }

        /* Add these styles to your existing CSS */
        #feedbacksList {
            display: none;
            position: fixed;
            top: 20%;
            left: 20%;
            width: 60%;
            background: #fff;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            padding: 20px;
            z-index: 9999;
        }

        .close-button {
            position: absolute;
            top: 10px;
            right: 10px;
            background: transparent;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: red;
        }

        .feedback-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .feedback-table th, .feedback-table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }

        .feedback-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .feedback-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .feedback-table tr:hover {
            background-color: #e0e0e0;
        }

        #showFeedbacksButton {
            background-color: #835237; /* Background color */
            color: #fff; /* Text color */
            border: none;
            padding: 10px 20px; /* Adjust padding as needed */
            font-size: 16px; /* Adjust font size as needed */
            cursor: pointer;
        }

        #showFeedbacksButton:hover {
            background-color: #00bcd4; /* Background color on hover */
            color: #fff; /* Text color on hover */
        }

        .navbar-toggle {
            font-size: 24px;
            cursor: pointer;
            margin-left: 20px;
            color: #fff;
        }

        .navbar-expanded ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .navbar-expanded ul li {
            border-bottom: 1px solid #555;
        }

        .navbar-expanded ul li:last-child {
            border-bottom: none;
        }

        .navbar-expanded ul li a {
            display: block;
            padding: 10px 20px;
            text-decoration: none;
            color: #fff;
        }

        .navbar-expanded ul li a i {
            margin-right: 10px;
        }

        .navbar-expanded {
            position: fixed;
            top: 100px;
            right: -300px;
            width: 150px;
            height: calc(160px - 60px);
            background-color: white;
            transition: right 0.3s ease-in-out;
            overflow-y: auto;
            z-index: 1000;
        }
        .navbar-icon {
            color: #fff;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            margin: 0 10px;
        }

        .navbar-expanded.show {
            right: 0;
        }

        /* Hover styles */
        .navbar-expanded ul li:hover {
            background-color: #555;
        }

        .navbar-expanded ul li a:hover {
            color: #fff;
        }

        .navbar-toggle {
            cursor: pointer;
            font-size: 24px;
            color: #fff;
            margin-right: 20px;
        }

        .navbar-toggle:hover {
            color: #00bcd4;
        }

        /* CSS for the sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: -300px; /* Initially hidden off the screen */
            width: 300px;
            height: 100%;
            background-color: #fff;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border-right: 1px solid #ccc;
            z-index: 1000;
            overflow-y: auto;
            transition: left 0.3s ease-in-out;
            padding: 20px;
            width: 200px;
            transition: left 0.3s ease-in-out;
        }

        /* CSS for the sidebar headings */
        .sidebar-heading {
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* CSS for the lists within the sidebar */
        .my-building-list,
        .all-buildings-list {
            list-style: none;
            padding: 0;
        }

        .my-building-list li,
        .all-buildings-list li {
            margin-bottom: 10px;
            cursor: pointer;
        }

        .my-building-list li:hover,
        .all-buildings-list li:hover {
            color: #00bcd4;
        }

        /* CSS for the buttons container */
        .sidebar-buttons {
            display: flex;
            border-top: 1px solid #ccc; /* Add a border above the buttons */
            border-bottom: 1px solid #ccc; /* Add a border below the buttons */
        }

        /* CSS for the buttons */
        .sidebar-buttons button {
            border: none;
            cursor: pointer;
            background: none;
            padding: 10px 20px;
            margin-right: 10px;
            border-bottom: 1px solid transparent; /* Add a transparent border at the bottom */
        }

        /* Style for the active button and hovering color */
        .sidebar-buttons button.active, .sidebar-buttons button:hover {
            color: #00bcd4; /* Change the text color on hover and when active */
            border-bottom: 1px solid #00bcd4; /* Change the border color on hover and when active */
        }

        /* CSS for the content sections */
        .sidebar-content {
            display: none;
            border-top: 1px solid #ccc;
            margin-top: 20px;
        }

        .sidebar-content.active {
            /* margin-top: 20px; */
            display: block;
        }

        /* CSS for the close icon */
        .close-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
        }

        .close-icon i {
            font-size: 24px; /* Adjust the size as needed */
            color: #000; /* Adjust the color as needed */
        }

        /* Hover styles for the close icon */
        .close-icon:hover i {
            color: #ff0000; /* Change the color on hover */
        }

        /* Center the modal */
        .modal {
            display: flex;
            justify-content: center;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1;
            overflow: auto;
        }
        .modal-content {
            background-color: #f4f4f4;
            max-width: 400px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            overflow-y: auto;
            max-height: 80%;
        }
        .modal h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #075e54;
        }
        .modal p {
            font-size: 16px;
            margin-bottom: 20px;
            color: #333;
        }

       /* CSS for the modal */
        .modal {
            display: flex;
            justify-content: center;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .modal-content {
            background-color: #F9EBEA;
            border: 1px solid #E5E7E9;
            border-radius: 4px;
            padding: 10px;
            position: relative; /* Added position relative */
        }

        /* Style for the close icon */
        .close-icon {
            position: absolute;
            top: 1px;
            right: 1px;
            cursor: pointer;
            font-size: 30px;
            color: #34495E;
        }

        .close-icon:hover {
            color: #ff0000; /* Change the color on hover */
        }

        .my-building-list .selected {
            color: #00bcd4;
        }

        .all-buildings-list .selected {
            color: #00bcd4;
        }

        .registration-link {
            color: #00bcd4;
            text-decoration: underline;
            cursor: pointer;
        }

        .registration-link:hover {
            text-decoration: none;
        }

        .registration-icon {
            color: #00bcd4;
            margin-left: 5px;
        }

        .displayed-image-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            padding: 20px;
            max-width: 80%;
            max-height: 80%;
            overflow: auto;
            text-align: center;
            z-index: 999;
        }

        .close-button {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 20px;
            color: #333;
            cursor: pointer;
        }

        .image-details-container {
            margin-top: 20px;
        }

        .image-details {
            text-align: left;
            padding: 10px;
        }

        .label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .displayed-image {
            max-width: 100%;
            max-height: 100%;
        }

        .registration-link {
            color: #00bcd4;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <header>
        <div class="header-container">
            <h1>Manager Dashboard</h1>
            <img src="Logo_final.png" alt="Logo" class="header-img"> 
            <div class="icon-container">
                <div class="chat-button">
                    <a href="manager_chat.php"><i class="fas fa-comment-alt" style="color:#835237; font-size:40px;"></i></a>
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
                <li><a href="manager_profile.php" class="navbar-icon" style="color: red;"><i class="fas fa-user" style="color: black"></i>Profile</a></li>
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
                echo "<div class='profile-incomplete-message'>Your profile is incomplete. <a href='manager_complete_profile.php'>Complete Profile</a></div>";
            }
        ?>
        <div class="first-compartment">
            <a href="ies.php" class="ies-link">
                <div class="ies-container">
                    <h1 class="ies">Information evaluation page <i class="fas fa-chart-pie" style="margin-left: 10px; color: #050605;"></i></h1>
                </div>
            </a><br>
            <a href="manager_register_property.php">
                <div class="ies-container" style="background-color:#454343;">
                    <h1 class="ies">Register new property</h1>
                </div>
            </a><br>
            <a href="others_register_property.php">
                <div class="ies-container" style="background-color:green; margin-bottom: 25px;">
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


        async function displayImageDetails(image) {
            // Check if the property is registered
            const isRegistered = await checkPropertyRegistration(image.property_id);

            const secondCompartment = document.querySelector('.second-compartment');
            secondCompartment.innerHTML = `
                <div class="displayed-image-container">
                    <button id="closeButton" class="close-button"><i class="fas fa-times"></i></button>
                    <img src="${image.image_path}" alt="${image.apartment_type}" class="displayed-image">
                    <div class="image-details-container">
                        <div class="image-details">
                            <h2 class="label">Title:</h2>
                            <p style="color: red;">${image.apartment_type}</p>
                            <h2 class="label">Details:</h2>
                            <p style="color: red;">${image.other_details}</p>
                            <h2 class="label">Created at:</h2>
                            <p style="color: red;">${image.created_at}</p>
                            <h2 class="label">Registration:</h2>
                            ${
                                isRegistered
                                    ? '<p style="color: green;">Registered</p>'
                                    : `<p style="color: red;">Not Registered</p><br><a href="manager_register_property_1.php?property_id=${image.property_id}" class="registration-link">Click to Register <i class="fas fa-registered registration-icon"></i></a>`
                            }
                        </div>
                        <p class="disclaimer">Acknowledgement: The images used on this platform are for demonstration only and are downloaded from Centaline Property Agency Limited website.</p>
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



        // Function to check if the property is registered
        async function checkPropertyRegistration(propertyId) {
            const url = 'check_property_registration.php'; // Replace with the actual URL of your server-side script

            // Create a new FormData object to send the property ID
            const formData = new FormData();
            formData.append('propertyId', propertyId);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const data = await response.json();
                // Assuming the server sends back a JSON response with a "registered" property
                return data.registered === 1; // Return true only if property is registered
            } catch (error) {
                console.error('Error checking property registration:', error);
                return false; // Return false in case of an error
            }
        }

        // Call the fetchRecentImages function to load recent images when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            fetchRecentImages();
        });

        function toggleNavbar() {
            const navbar = document.getElementById("navbar");
            navbar.classList.toggle("show");
        }

        function closeNavbar() {
            document.getElementById("navbar-collapsed").style.width = "0";
        }

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
            window.location.href = 'developer_feedback_page.php?building_id=' + buildingId;
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
