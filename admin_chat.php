<?php
session_name("admin_session");
session_start();
require_once "config.php";

if (!isset($_SESSION['admin_id'])) {
    // User is not logged in or not authorized for this dashboard
    header('Location: admin_login.php');
    exit();
}

$userId = $_SESSION["admin_unique_id"];

// If the user is logged in, continue displaying the dashboard
// Update messages with status "sent" to "delivered"
$updateSql = "UPDATE messages SET message_status = 'delivered' WHERE receiver_id = :userId AND message_status = 'sent'";
$updateStmt = $pdo->prepare($updateSql);
$updateStmt->bindParam(":userId", $userId, PDO::PARAM_INT);
$updateStmt->execute();

try {
    require_once "config.php";

    // Fetch building names for the manager from property_registration
    $buildingNamesQuery = "SELECT property_name FROM others_property_registration WHERE user_id = :managerUserId";

    $buildingNamesStmt = $pdo->prepare($buildingNamesQuery);
    $buildingNamesStmt->bindParam(':managerUserId', $userId, PDO::PARAM_INT);
    $buildingNamesStmt->execute();
    $mergedBuildingNames = $buildingNamesStmt->fetchAll(PDO::FETCH_COLUMN);

    $allUserAdmins = [];
    $allUserChats = [];

    foreach ($mergedBuildingNames as $buildingName) {
        // Initialize arrays for each building name
        $allUserAdmins[$buildingName] = [];

        // Fetch admins for property_registration
        $adminsQuery = "SELECT user_id FROM property_registration WHERE property_name = :property_name AND user_id != :managerUniqueId";
        $stmt = $pdo->prepare($adminsQuery);
        $stmt->bindParam(':property_name', $buildingName);
        $stmt->bindParam(':managerUniqueId', $userId);
        $stmt->execute();
        $propertyUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($propertyUsers)) {
            $query = "SELECT id, first_name, last_name, unique_identifier, profession, status FROM admin_registration WHERE unique_identifier IN (" . implode(",", $propertyUsers) . ")";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $propertyAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Merge admins for this building
            $allUserAdmins[$buildingName] = $propertyAdmins;
        }
    }

    // Initialize an empty array to store temporary user data for each building
    $buildingUsers = [];

    // Fetch users with chats for this building
    foreach ($mergedBuildingNames as $buildingName) {
        // Check if users have registered in this building
        $usersQuery = "SELECT user_id FROM user_property_registration WHERE property_name = :property_name";
        $stmt = $pdo->prepare($usersQuery);
        $stmt->bindParam(':property_name', $buildingName);
        $stmt->execute();
        $usersPropertyUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Check if there are users who have messaged the manager
        if (!empty($usersPropertyUsers)) {
            $usersWithChatsQuery = "SELECT DISTINCT users.unique_identifier
                FROM users
                JOIN messages ON (users.unique_identifier = messages.sender_id)
                WHERE users.unique_identifier IN (" . implode(",", $usersPropertyUsers) . ")
                AND (messages.receiver_id = :managerUserId)";

            $usersWithChatsStmt = $pdo->prepare($usersWithChatsQuery);
            $usersWithChatsStmt->bindParam(':managerUserId', $userId, PDO::PARAM_INT);
            $usersWithChatsStmt->execute();
            $usersWithChats = $usersWithChatsStmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($usersWithChats)) {
                // Store users with chats for this building in the temporary array
                $userChatsQuery = "SELECT DISTINCT users.unique_identifier, users.username, status
                    FROM users
                    JOIN messages ON (users.unique_identifier = messages.sender_id)
                    WHERE users.unique_identifier IN (" . implode(",", $usersPropertyUsers) . ")
                    AND (messages.receiver_id = :managerUserId)";

                $userChatsStmt = $pdo->prepare($userChatsQuery);
                $userChatsStmt->bindParam(':managerUserId', $userId, PDO::PARAM_INT);
                $userChatsStmt->execute();
                $userChats = $userChatsStmt->fetchAll(PDO::FETCH_ASSOC);

                // Store users with chats for this building in the temporary array
                $buildingUsers[$buildingName] = $userChats;
            }
        }
    }

    // Update the $allUserChats array with the temporary array of users for each building
    $allUserChats = $buildingUsers;


    // Now, fetch data from others_property_registration
    foreach ($mergedBuildingNames as $buildingName) {
        $othersPropertyAdmins = [];
        $othersPropertyQuery = "SELECT user_id FROM others_property_registration WHERE property_name = :property_name AND user_id != :managerUniqueId";
        $othersPropertyStmt = $pdo->prepare($othersPropertyQuery);
        $othersPropertyStmt->bindParam(':property_name', $buildingName);
        $othersPropertyStmt->bindParam(':managerUniqueId', $userId);
        $othersPropertyStmt->execute();
        $othersPropertyUsers = $othersPropertyStmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($othersPropertyUsers)) {
            $query = "SELECT id, first_name, last_name, unique_identifier, profession, status FROM admin_registration WHERE unique_identifier IN (" . implode(",", $othersPropertyUsers) . ")";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $othersPropertyAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Merge admins for this building from others_property_registration
            $allUserAdmins[$buildingName] = array_merge($allUserAdmins[$buildingName], $othersPropertyAdmins);
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin_chat.css">
    <title>Chat Interface</title>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Chat Interface</h1>
            <a href="admin_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </header>
    <!-- Split Chat interface -->
    <div class="split-interface">
        <!-- Buttons to switch between admin and user lists -->
        <div class="list-switch">
            <button id="admin-list-button">Professionals</button>
            <button id="user-list-button">Users</button>
        </div>

        <!-- Admin list -->
        <div class="admin-list">
            <h2 class="admin-list-header">Admins</h2>
            <?php foreach ($allUserAdmins as $buildingName => $adminsByBuilding) : ?>
                <h3 class="building-name-toggle">
                    <span class="toggle-arrow">▼</span><?php echo $buildingName; ?>
                </h3>
                <ul class="building-admins-list">
                    <?php if (count($adminsByBuilding) > 0) : ?>
                        <?php
                        // Group admins by their professions
                        $adminsByProfession = [];
                        foreach ($adminsByBuilding as $admin) {
                            $profession = $admin['profession'];
                            if (!isset($adminsByProfession[$profession])) {
                                $adminsByProfession[$profession] = [];
                            }
                            $adminsByProfession[$profession][] = $admin;
                        }
                        // Loop through admins by profession
                        foreach ($adminsByProfession as $profession => $admins) : ?>
                            <li style="list-style:none;">
                                <h4 class="profession-toggle">
                                    <span class="toggle-arrow">▼</span><?php echo $profession; ?>
                                </h4>
                                <ul class="admin-list-container" style="list-style: none">
                                    <?php foreach ($admins as $admin) : ?>
                                        <li style="list-style: none;">
                                            <a href="#" class="admin-link" data-admin-id="<?php echo $admin['id']; ?>" data-unique-identifier="<?php echo $admin['unique_identifier']; ?>" onclick="openChatWithAdmin('<?php echo $admin['first_name'] . ' ' . $admin['last_name']; ?>', '<?php echo $admin['unique_identifier']; ?>')">
                                                <?php echo $admin['first_name'] . ' ' . $admin['last_name']; ?>
                                                <?php
                                                $dotColor = ($admin['status'] == 'online') ? 'green' : 'grey';
                                                echo '<span class="dot" style="background-color: ' . $dotColor . ';"></span>';
                                                echo '<div class="badge" id="notreadCount_' . $admin['unique_identifier'] . '" style="color: #ffffff;"></div>';
                                                ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p>No Professionals for this building.</p>
                    <?php endif; ?>
                </ul>
            <?php endforeach; ?>
        </div>

        <!-- User list -->
        <div class="user-list" style="display: none;">
            <h2 class="user-list-header">Users</h2>
            <?php foreach ($allUserChats as $buildingName => $usersByBuilding) : ?>
                <h3 class="building-name-toggle">
                    <span class="toggle-arrow">▼</span><?php echo $buildingName; ?>
                </h3>
                <ul class="user-list-container">
                    <?php foreach ($usersByBuilding as $user) : ?>
                        <li class="user-list-item">
                            <a href="#" class="user-link" data-user-unique-identifier="<?php echo $user['unique_identifier']; ?>" onclick="openChatWithUser('<?php echo $user['username']; ?>', '<?php echo $user['unique_identifier']; ?>')">
                                <?php echo $user['username']; ?>
                                <?php
                                $dotColor = ($user['status'] == 'online') ? 'green' : 'grey';
                                echo '<span class="dot" style="background-color: ' . $dotColor . ';"></span>';
                                echo '<div class="badge" id="unreadCount_' . $user['unique_identifier'] . '" style="color: #ffffff;"></div>';
                                ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        </div>

        <!-- Chat box -->
        <div class="chat-box">
            <div class="chat-window">
                <div class="chat-header">
                    Chat with <span class="chat-partner"></span>
                    <button class="close-button" onclick="closeChat()"><i class="fas fa-times"></i></button>
                </div>
                <div class="chat-messages">
                    <!-- Messages will be displayed here -->
                </div>
                <div class="message-input-container">
                    <form class="message-form" enctype="multipart/form-data">
                        <div class="input-container">
                            <label for="file" class="file-upload-label"><i class="fas fa-share"></i></label>
                            <input type="file" name="file" id="file" accept=".jpg, .jpeg, .png, .gif, .pdf, .doc, .docx, .txt">
                            <p id="selected-file-name" class="selected-file">No file selected</p>
                            <textarea class="message-input" placeholder="Type your message here..."></textarea>
                        </div>
                    </form>
                    <button class="send-button" onclick="sendMessageOrUserMessage(); resetFileInput(); return false;"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Chat interface logic here...
        const splitInterface = document.querySelector('.split-interface');
        const adminLinks = document.querySelectorAll('.admin-link');
        const userLinks = document.querySelectorAll('.user-link');
        const chatPartner = document.querySelector('.chat-partner');
        const messagesContainer = document.querySelector('.chat-messages');
        var currentChatContext = "admin";
        var receiverIdentifier = null;

        // Define a variable to store the interval for chat refresh
        var chatRefreshInterval;

        function toggleChatInterface() {
            splitInterface.classList.toggle('show-chat');
        }

           // JavaScript to handle toggling content and arrow indicators
    const buildingNameToggles = document.querySelectorAll('.building-name-toggle');
    const professionToggles = document.querySelectorAll('.profession-toggle');

    buildingNameToggles.forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const adminList = toggle.nextElementSibling;
            adminList.style.display = adminList.style.display === 'none' ? 'block' : 'none';
            toggle.classList.toggle('active');
        });
    });

    professionToggles.forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const adminList = toggle.nextElementSibling;
            adminList.style.display = adminList.style.display === 'none' ? 'block' : 'none';
            toggle.classList.toggle('active');
        });
    });


        function resetFileInput() {
            const fileInput = document.getElementById("file");
            const selectedFileName = document.getElementById("selected-file-name");

            // Reset the file input
            fileInput.value = null;
            // Update the selected file name to "No file selected"
            selectedFileName.textContent = "No file selected";
        }

        function truncateFileName(name, length) {
            if (name.length <= length) {
                return name;
            }
            const extensionIndex = name.lastIndexOf('.');
            if (extensionIndex === -1) {
                return name.substring(0, length) + '...';
            }
            const extension = name.substring(extensionIndex);
            return name.substring(0, length - 3) + '...' + extension;
        }

        document.getElementById("file").addEventListener("change", function() {
            const fileInput = document.getElementById("file");
            const selectedFileName = document.getElementById("selected-file-name");

            if (fileInput.files.length > 0) {
                selectedFileName.textContent = truncateFileName(fileInput.files[0].name, 8); // Adjust 8 to your desired length
            } else {
                selectedFileName.textContent = "No file selected";
            }
        });

        function openChatWithAdmin(adminFullName, uniqueIdentifier) {
            event.preventDefault(); // Prevent the default behavior of the anchor element

            receiverIdentifier = uniqueIdentifier;
            chatPartner.textContent = adminFullName;
            messagesContainer.innerHTML = '';
            const chatBox = document.querySelector('.chat-box');
            chatBox.style.display = 'block';

            // Set the chat context to admin
            currentChatContext = "admin";

            // Store the unique_identifier in a session variable
            sessionStorage.setItem('selectedAdminUniqueIdentifier', uniqueIdentifier);

            // Clear any existing chat refresh interval
            clearInterval(chatRefreshInterval);

            // Start refreshing the chat for the current admin
            chatRefreshInterval = setInterval(fetchMessages, 100); // Refresh every 5 seconds
            // Remove the badge by clearing its content and hiding it
            const badgeElement = document.getElementById('notreadCount_' + uniqueIdentifier);
            badgeElement.textContent = ''; // Clear the badge content
            badgeElement.style.display = 'none'; // Hide the badge
        }

        function openChatWithUser(userUsername, userUniqueIdentifier) {
            event.preventDefault(); // Prevent the default behavior of the anchor element

            chatPartner.textContent = userUsername;
            messagesContainer.innerHTML = '';
            const chatBox = document.querySelector('.chat-box');
            chatBox.style.display = 'block';

            // Set the chat context to user
            currentChatContext = "user";

            // Store the unique_identifier in a session variable
            sessionStorage.setItem('selectedUserUniqueIdentifier', userUniqueIdentifier);

            // Clear any existing chat refresh interval
            clearInterval(chatRefreshInterval);

            // Start refreshing the chat for the current user
            chatRefreshInterval = setInterval(fetchMessagesForUser, 100); // Refresh every 5 seconds
            // Remove the badge by clearing its content and hiding it
            const badgeElement = document.getElementById('unreadCount_' + uniqueIdentifier);
            badgeElement.textContent = ''; // Clear the badge content
            badgeElement.style.display = 'none'; // Hide the badge
        }

        function closeChat() {
            const chatBox = document.querySelector('.chat-box');
            chatBox.style.display = 'none';
            chatPartner.textContent = '';
            messagesContainer.innerHTML = '';

            // Clear the chat refresh interval when the chat is closed
            clearInterval(chatRefreshInterval);
        }


        function sendUserMessage() {
            const messageInput = document.querySelector('.message-input');
            const fileInput = document.getElementById('file');
            const message = messageInput.value.trim();
            const file = fileInput.files[0];  // Get the selected file#
            messageInput.value = '';
            fileInput.value = "";

            if (message === '' && !file) {
                return; // Don't send empty messages
            }

            // Retrieve the stored unique_identifier from the session
            const userUniqueIdentifier = sessionStorage.getItem('selectedUserUniqueIdentifier');

            if (userUniqueIdentifier) {
                const formData = new FormData();
                formData.append("user_unique_id", userUniqueIdentifier);
                formData.append("admin_unique_id", <?php echo $userId; ?>); // Include the user ID
                formData.append("message", message);

                if (file) {
                    formData.append("file", file);
                }

                fetch("user_admin_send_messages.php", {
                    method: "POST",
                    body: formData,
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
                    return response.json(); // Change to response.text() to get the raw response text
                })
                .then(data => {
                    console.log(data);
                    // Handle the response from the server
                    if (data.success) {
                        // Message sent successfully
                        console.log("Message sent");
                    } else {
                        // Error sending message
                        console.error("Failed to send message:", data.message);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                })
                .finally(() => {
                    // Call fetchMessagesForUser to update the chat box after sending a message
                    fetchMessagesForUser();
                });
            }
        }

        function fetchMessagesForUser() {
            var formData = new FormData();

            userUniqueIdentifier = sessionStorage.getItem('selectedUserUniqueIdentifier');
            formData.append("userUniqueIdentifier", userUniqueIdentifier);
            formData.append("admin_unique_id", <?php echo $userId; ?>);

            fetch("admin_fetch_messages.php", {
                method: "POST",
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then(data => {
                messagesContainer.innerHTML = ""; // Clear the existing messages

                if (data.messages.length === 0) {
                    // Display a message when there are no messages
                    var noMessagesElement = document.createElement("div");
                    noMessagesElement.classList.add("no-messages");
                    noMessagesElement.textContent = "No messages to display";
                    messagesContainer.appendChild(noMessagesElement);
                } else {
                    data.messages.forEach(message => {
                        var messageContent = message.message ? message.message.trim() : "";
                        var fileURL = message.file_path ? message.file_path.trim() : "";
                        if (messageContent !== "") {
                            var messageElement = document.createElement("div");
                            messageElement.classList.add("message");

                            // Check if the message is from the current user or admin
                            if (message.sender_name === "You") {
                                messageElement.classList.add("sender-user");
                            } else {
                                messageElement.classList.add("sender-admin");
                            }

                            // Create a container for sender information
                            var senderInfoContainer = document.createElement("div");
                            senderInfoContainer.classList.add("sender-info-container");

                            // Create and append profession element
                            var professionElement = document.createElement("div");
                            professionElement.classList.add("sender-profession");
                            professionElement.textContent = message.admin_profession ? message.admin_profession.trim() : "";

                            // Create and append sender name element
                            var senderNameElement = document.createElement("div");
                            senderNameElement.classList.add("sender-name");
                            senderNameElement.textContent = message.sender_name ? message.sender_name.trim() : "Unknown";

                            // Append profession and sender name to the sender info container
                            senderInfoContainer.appendChild(professionElement);
                            senderInfoContainer.appendChild(senderNameElement);

                            // Create and append message content element
                            var messageContentElement = document.createElement("div");
                            messageContentElement.classList.add("message-content");
                            messageContentElement.textContent = messageContent;

                            // Create and append message status element
                            var messageStatusElement = document.createElement("div");
                            messageStatusElement.className = "message-status";
                            if (message.status === "sent") {
                                messageStatusElement.classList.add("fas", "fa-check"); // Font Awesome icon for sent
                            } else if (message.status === "delivered") {
                                messageStatusElement.classList.add("fas", "fa-check-double"); // Font Awesome icon for delivered
                            } else if (message.status === "read") {
                                messageStatusElement.classList.add("fas", "fa-check-double", "blue-icon"); // Font Awesome icon for read
                            }

                            // Append sender info container to the message element
                            messageElement.appendChild(senderInfoContainer);

                            // Create a container for message content and status elements
                            var messageContentStatusContainer = document.createElement("div");
                            messageContentStatusContainer.classList.add("message-content-status-container");

                            // Append message content and status to the container
                            messageContentStatusContainer.appendChild(messageContentElement);
                            messageContentStatusContainer.appendChild(messageStatusElement);

                            // Append the container to the message element
                            messageElement.appendChild(messageContentStatusContainer);

                            // Append the message element to the messages container
                            messagesContainer.appendChild(messageElement);
                        }
                        // Inside the fetchMessagesForUser function
                        if (fileURL) {
                            // Create elements to display files
                            var fileContainer = document.createElement("div");
                            fileContainer.classList.add("file-container");

                            // Check if the message is from the current user or admin
                            if (message.sender_name === "You") {
                                fileContainer.classList.add("sender-user-file"); // Position right for "You"
                            } else {
                                fileContainer.classList.add("sender-admin-file"); // Position left for other senders
                            }

                            // Create a container for file content and status elements
                            var fileContentStatusContainer = document.createElement("div");
                            fileContentStatusContainer.classList.add("file-content-status-container");

                            // Check the file type and create an appropriate HTML element
                            var fileElement = document.createElement("a");
                            fileElement.href = fileURL;

                            // Extract the filename for display (first 5 characters + extension)
                            const filename = getDisplayFilename(fileURL);

                            fileElement.download = filename; // Use the extracted filename for download
                            fileElement.classList.add("downloadable-link");

                            // Create and append file status element
                            var fileStatusElement = document.createElement("div");
                            fileStatusElement.className = "file-status";
                            if (message.status === "sent") {
                                fileStatusElement.classList.add("fas", "fa-check"); // Font Awesome icon for sent
                            } else if (message.status === "delivered") {
                                fileStatusElement.classList.add("fas", "fa-check-double"); // Font Awesome icon for delivered
                            } else if (message.status === "read") {
                                fileStatusElement.classList.add("fas", "fa-check-double", "blue-icon"); // Font Awesome icon for read
                            }

                            // Create an icon element to display appropriate icon
                            var fileIcon = document.createElement("i");

                            if (isImage(fileURL)) {
                                // For images, create an image icon
                                fileIcon.classList.add("fas", "fa-image");
                                fileIcon.title = "Image";

                                fileElement = document.createElement("a");
                                fileElement.href = fileURL;
                                fileElement.download = fileURL.split("/").pop(); // Suggest a filename for download
                                fileElement.classList.add("downloadable-link");

                                // Create a download button for images
                                var downloadIcon = document.createElement("i");
                                downloadIcon.classList.add("fas", "fa-download");
                                downloadIcon.title = "Download Image"; // Optional tooltip
                                if (message.sender_name !== "You") {
                                    fileElement.appendChild(downloadIcon);
                                }
                                // Create an image element to display the image
                                var imageElement = document.createElement("img");
                                imageElement.src = fileURL;
                                imageElement.alt = "Received Image";
                                // Append the image and icon elements to the fileElement
                                fileElement.appendChild(imageElement);
                                fileElement.appendChild(fileIcon);

                            } else if (isPDF(fileURL)) {
                                // For PDFs, create a PDF icon
                                fileIcon.classList.add("fas", "fa-file-pdf");
                                fileIcon.title = "PDF Document";
                                // Append the PDF icon to the fileElement
                                // For PDFs, create a direct link to the PDF with a download attribute
                                fileElement = document.createElement("a");
                                fileElement.href = fileURL;
                                fileElement.textContent = "Download PDF";
                                fileElement.download = fileURL.split("/").pop(); // Suggest a filename for download
                                fileElement.classList.add("downloadable-link")
                                // Create a download button for other file types
                                var downloadIcon = document.createElement("i");
                                downloadIcon.classList.add("fas", "fa-download");
                                downloadIcon.title = "Download File"; // Optional tooltip
                                if (message.sender_name !== "You") {
                                    fileElement.appendChild(downloadIcon);
                                }
                                fileElement.appendChild(fileIcon);
                            } else {
                                // For other file types, create a generic file icon
                                fileIcon.classList.add("fas", "fa-file");
                                fileIcon.title = "File";

                                // For other file types, create an anchor tag with a download button
                                fileElement = document.createElement("a");
                                fileElement.href = fileURL;
                                fileElement.download = fileURL.split("/").pop(); // Suggest a filename for download
                                fileElement.classList.add("downloadable-link");

                                // Create a download button for other file types
                                var downloadIcon = document.createElement("i");
                                downloadIcon.classList.add("fas", "fa-download");
                                downloadIcon.title = "Download File"; // Optional tooltip
                                if (message.sender_name !== "You") {
                                    fileElement.appendChild(downloadIcon);
                                }
                                // Append the generic file icon to the fileElement
                                fileElement.appendChild(fileIcon);
                            }

                            // Append the extracted filename for display to the container
                            fileContentStatusContainer.appendChild(document.createTextNode(filename));

                            // Append file content, status, and the icon to the container
                            fileContentStatusContainer.appendChild(fileElement);
                            fileContentStatusContainer.appendChild(fileStatusElement);

                            // Append file content and status container to the file container
                            fileContainer.appendChild(fileContentStatusContainer);

                            // Append the file container to the messages container
                            messagesContainer.appendChild(fileContainer);

                            // Add a click event listener to trigger the download
                            fileElement.addEventListener("click", function (event) {
                                event.preventDefault(); // Prevent the link from opening
                                downloadFile(this.href, this.download);
                            });
                        }
                    });
                }
                })
                .catch(error => {
                console.error("Error:", error);
            });
        }

        // Function to extract the display filename (first 5 characters + extension)
        function getDisplayFilename(fileURL) {
            const filename = fileURL.split('/').pop(); // Extract the filename from the URL
            const extensionIndex = filename.lastIndexOf('.'); // Find the last dot (for file extension)

            if (extensionIndex >= 0) {
                const firstFiveChars = filename.substring(0, Math.min(5, extensionIndex)); // Extract first 5 characters
                const extension = filename.substring(extensionIndex); // Extract the extension
                return firstFiveChars + (firstFiveChars.length < 5 ? '' : '...') + extension;
            } else {
                // If there is no file extension, use the whole filename
                return filename;
            }
        }

        function isImage(url) {
            const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif'];
            const extension = url.substring(url.lastIndexOf('.')).toLowerCase();
            return imageExtensions.includes(extension);
        }

        function isPDF(url) {
            const pdfExtensions = [".pdf"];
            const extension = url.substring(url.lastIndexOf(".")).toLowerCase();
            return pdfExtensions.includes(extension);
        }

        function downloadFile(url, suggestedFilename) {
            var anchor = document.createElement("a");
            anchor.href = url;
            anchor.download = suggestedFilename;
            anchor.style.display = "none";
            document.body.appendChild(anchor);
            anchor.click();
            document.body.removeChild(anchor);
        }
        const professionTitles = document.querySelectorAll('.profession-title');

        professionTitles.forEach(professionTitle => {
            professionTitle.addEventListener('click', function () {
                this.parentElement.classList.toggle('active');
                const professionContent = this.nextElementSibling;
                professionContent.style.display = professionContent.style.display === 'none' ? 'block' : 'none';
            });
        });

        function sendMessage() {     
            const messageInput = document.querySelector('.message-input');
            const fileInput = document.getElementById('file');
            const message = messageInput.value.trim();
            const file = fileInput.files[0];  // Get the selected file#
            messageInput.value = '';
            fileInput.value = "";

            if (message === '' && !file) {
                return; // Don't send empty messages
            }       
            // Retrieve the stored unique_identifier from the session
            const uniqueIdentifier = sessionStorage.getItem('selectedAdminUniqueIdentifier');
           

            if (uniqueIdentifier) {
                var formData = new FormData();
                formData.append("admin_unique_id", uniqueIdentifier);
                formData.append("user_unique_id", <?php echo $userId; ?>); // Include the user ID
                formData.append("message", message);

                if (file) {
                    formData.append("file", file); // Append the selected file
                }

                fetch("admin_admin_send_message.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
                    return response.json();
                })
                .then(data => {
                    console.log(data);
                    // Handle the response from the server
                    if (data.success) {
                        // Message sent successfully
                        console.log("Message sent");
                    } else {
                        // Error sending message
                        console.error("Failed to send message:", data.message);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                })
                .finally(() => {
                    // Call fetchMessagesForUser to update the chat box after sending a message
                    fetchMessages();
                });
            }
        }


            
        function fetchMessages() {
            console.log('Fetching messages...');

            const uniqueIdentifier = sessionStorage.getItem('selectedAdminUniqueIdentifier');
            var formData = new FormData();
            formData.append("admin_unique_id", uniqueIdentifier);
            formData.append("user_unique_id", <?php echo $userId; ?>);

            fetch("admin_admin_fetch_messages.php", {
                method: "POST",
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then(data => {
                console.log('Fetched data:', data);

                messagesContainer.innerHTML = ""; // Clear the existing messages

                if (data.messages.length === 0) {
                    // Display a message when there are no messages
                    var noMessagesElement = document.createElement("div");
                    noMessagesElement.classList.add("no-messages");
                    noMessagesElement.textContent = "No messages to display";
                    messagesContainer.appendChild(noMessagesElement);
                } else {
                    data.messages.forEach(message => {
                        var messageContent = message.message ? message.message.trim() : "";
                        var fileURL = message.file_path ? message.file_path.trim() : "";
                        if (messageContent !== "") {
                            var messageElement = document.createElement("div");
                            messageElement.classList.add("message");

                            // Check if the message is from the current user or admin
                            if (message.sender_name === "You") {
                                messageElement.classList.add("sender-user");
                            } else {
                                messageElement.classList.add("sender-admin");
                            }

                            // Create a container for sender information
                            var senderInfoContainer = document.createElement("div");
                            senderInfoContainer.classList.add("sender-info-container");

                            // Create and append profession element
                            var professionElement = document.createElement("div");
                            professionElement.classList.add("sender-profession");
                            professionElement.textContent = message.admin_profession ? message.admin_profession.trim() : "";

                            // Create and append sender name element
                            var senderNameElement = document.createElement("div");
                            senderNameElement.classList.add("sender-name");
                            senderNameElement.textContent = message.sender_name ? message.sender_name.trim() : "Unknown";

                            // Append profession and sender name to the sender info container
                            senderInfoContainer.appendChild(professionElement);
                            senderInfoContainer.appendChild(senderNameElement);

                            // Create and append message content element
                            var messageContentElement = document.createElement("div");
                            messageContentElement.classList.add("message-content");
                            messageContentElement.textContent = messageContent;

                            // Create and append message status element
                            var messageStatusElement = document.createElement("div");
                            messageStatusElement.className = "message-status";
                            if (message.status === "sent") {
                                messageStatusElement.classList.add("fas", "fa-check"); // Font Awesome icon for sent
                            } else if (message.status === "delivered") {
                                messageStatusElement.classList.add("fas", "fa-check-double"); // Font Awesome icon for delivered
                            } else if (message.status === "read") {
                                messageStatusElement.classList.add("fas", "fa-check-double", "blue-icon"); // Font Awesome icon for read
                            }

                            // Append sender info container to the message element
                            messageElement.appendChild(senderInfoContainer);

                            // Create a container for message content and status elements
                            var messageContentStatusContainer = document.createElement("div");
                            messageContentStatusContainer.classList.add("message-content-status-container");

                            // Append message content and status to the container
                            messageContentStatusContainer.appendChild(messageContentElement);
                            messageContentStatusContainer.appendChild(messageStatusElement);

                            // Append the container to the message element
                            messageElement.appendChild(messageContentStatusContainer);

                            // Append the message element to the messages container
                            messagesContainer.appendChild(messageElement);
                        }
                        // Inside the fetchMessagesForUser function
                        if (fileURL) {
                            // Create elements to display files
                            var fileContainer = document.createElement("div");
                            fileContainer.classList.add("file-container");

                            // Check if the message is from the current user or admin
                            if (message.sender_name === "You") {
                                fileContainer.classList.add("sender-user-file"); // Position right for "You"
                            } else {
                                fileContainer.classList.add("sender-admin-file"); // Position left for other senders
                            }

                            // Create a container for file content and status elements
                            var fileContentStatusContainer = document.createElement("div");
                            fileContentStatusContainer.classList.add("file-content-status-container");

                            // Check the file type and create an appropriate HTML element
                            var fileElement = document.createElement("a");
                            fileElement.href = fileURL;

                            // Extract the filename for display (first 5 characters + extension)
                            const filename = getDisplayFilename(fileURL);

                            fileElement.download = filename; // Use the extracted filename for download
                            fileElement.classList.add("downloadable-link");

                            // Create and append file status element
                            var fileStatusElement = document.createElement("div");
                            fileStatusElement.className = "file-status";
                            if (message.status === "sent") {
                                fileStatusElement.classList.add("fas", "fa-check"); // Font Awesome icon for sent
                            } else if (message.status === "delivered") {
                                fileStatusElement.classList.add("fas", "fa-check-double"); // Font Awesome icon for delivered
                            } else if (message.status === "read") {
                                fileStatusElement.classList.add("fas", "fa-check-double", "blue-icon"); // Font Awesome icon for read
                            }

                            // Create an icon element to display appropriate icon
                            var fileIcon = document.createElement("i");

                            if (isImage(fileURL)) {
                                // For images, create an image icon
                                fileIcon.classList.add("fas", "fa-image");
                                fileIcon.title = "Image";

                                fileElement = document.createElement("a");
                                fileElement.href = fileURL;
                                fileElement.download = fileURL.split("/").pop(); // Suggest a filename for download
                                fileElement.classList.add("downloadable-link");

                                // Create a download button for images
                                var downloadIcon = document.createElement("i");
                                downloadIcon.classList.add("fas", "fa-download");
                                downloadIcon.title = "Download Image"; // Optional tooltip
                                if (message.sender_name !== "You") {
                                    fileElement.appendChild(downloadIcon);
                                }
                                // Create an image element to display the image
                                var imageElement = document.createElement("img");
                                imageElement.src = fileURL;
                                imageElement.alt = "Received Image";
                                // Append the image and icon elements to the fileElement
                                fileElement.appendChild(imageElement);
                                fileElement.appendChild(fileIcon);

                            } else if (isPDF(fileURL)) {
                                // For PDFs, create a PDF icon
                                fileIcon.classList.add("fas", "fa-file-pdf");
                                fileIcon.title = "PDF Document";
                                // Append the PDF icon to the fileElement
                                // For PDFs, create a direct link to the PDF with a download attribute
                                fileElement = document.createElement("a");
                                fileElement.href = fileURL;
                                fileElement.textContent = "Download PDF";
                                fileElement.download = fileURL.split("/").pop(); // Suggest a filename for download
                                fileElement.classList.add("downloadable-link")
                                // Create a download button for other file types
                                var downloadIcon = document.createElement("i");
                                downloadIcon.classList.add("fas", "fa-download");
                                downloadIcon.title = "Download File"; // Optional tooltip
                                if (message.sender_name !== "You") {
                                    fileElement.appendChild(downloadIcon);
                                }
                                fileElement.appendChild(fileIcon);
                            } else {
                                // For other file types, create a generic file icon
                                fileIcon.classList.add("fas", "fa-file");
                                fileIcon.title = "File";

                                // For other file types, create an anchor tag with a download button
                                fileElement = document.createElement("a");
                                fileElement.href = fileURL;
                                fileElement.download = fileURL.split("/").pop(); // Suggest a filename for download
                                fileElement.classList.add("downloadable-link");

                                // Create a download button for other file types
                                var downloadIcon = document.createElement("i");
                                downloadIcon.classList.add("fas", "fa-download");
                                downloadIcon.title = "Download File"; // Optional tooltip
                                if (message.sender_name !== "You") {
                                    fileElement.appendChild(downloadIcon);
                                }
                                // Append the generic file icon to the fileElement
                                fileElement.appendChild(fileIcon);
                            }

                            // Append the extracted filename for display to the container
                            fileContentStatusContainer.appendChild(document.createTextNode(filename));

                            // Append file content, status, and the icon to the container
                            fileContentStatusContainer.appendChild(fileElement);
                            fileContentStatusContainer.appendChild(fileStatusElement);

                            // Append file content and status container to the file container
                            fileContainer.appendChild(fileContentStatusContainer);

                            // Append the file container to the messages container
                            messagesContainer.appendChild(fileContainer);

                            // Add a click event listener to trigger the download
                            fileElement.addEventListener("click", function (event) {
                                event.preventDefault(); // Prevent the link from opening
                                downloadFile(this.href, this.download);
                            });
                        }
                    });
                }
                })
                .catch(error => {
                console.error("Error:", error);
            });
        }
        function updateMessageStatus() {
            // Make an AJAX request to update the message status
            const formData = new FormData();
            formData.append("user_unique_id", <?php echo $userId; ?>);

            fetch("update_admin_message_status.php", {
                method: "POST",
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                // Handle the response as needed
            })
            .catch(error => {
                console.error("Error updating message status:", error);
            });
        }
        setInterval(updateMessageStatus, 100);
        // JavaScript to toggle between admin and user lists
        const adminListButton = document.getElementById('admin-list-button');
        const userListButton = document.getElementById('user-list-button');
        const adminListContainer = document.querySelector('.admin-list');
        const userListContainer = document.querySelector('.user-list');

        // Function to highlight the selected header
        function highlightHeader(selectedButton, otherButton) {
            selectedButton.classList.add('selected-header');
            otherButton.classList.remove('selected-header');
        }

        // Add an event listener to the admin list button
        adminListButton.addEventListener('click', function () {
            adminListContainer.style.display = 'block';
            userListContainer.style.display = 'none';

            // Highlight the admin header
            highlightHeader(adminListButton, userListButton);
        });

        // Add an event listener to the user list button
        userListButton.addEventListener('click', function () {
            adminListContainer.style.display = 'none';
            userListContainer.style.display = 'block';

            // Highlight the user header
            highlightHeader(userListButton, adminListButton);
        });
        // Initially highlight the Admins button
        highlightHeader(adminListButton, userListButton);

        function sendMessageOrUserMessage() {
            const fileInput = document.getElementById('file');
            const messageInput = document.querySelector('.message-input');
            const message = messageInput.value.trim();
            const file = fileInput.files[0];
            if (currentChatContext === "user") {
                sendUserMessage();
            } else {
                sendMessage();
            }
        }
        function fetchUnreadMessageCounts() {
            // Create a new FormData object
            const formData = new FormData();
            
            formData.append('userId', <?php echo $userId; ?>);

            // Send a POST request to your PHP script that fetches unread message counts
            fetch("fetch_unread_message_counts.php", {
                method: "POST",
                body: formData,
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then(data => {
                console.log("Fetch data:", data)
                // Update the badges based on the received data
                for (const senderId in data) {
                    console.log("val", senderId)
                    const badgeElement = document.getElementById(`unreadCount_${senderId}`);
                    if (badgeElement) {
                        badgeElement.innerHTML = data[senderId];

                        badgeElement.classList.add('circle-badge');
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching unread message counts:', error);
            });
        }

        // Call fetchUnreadMessageCounts every 5 seconds (adjust the interval as needed)
        setInterval(fetchUnreadMessageCounts, 300);

        function fetchAdminUnreadMessageCounts() {
            // Create a new FormData object
            const formData = new FormData();
            
            formData.append('userId', <?php echo $userId; ?>);

            // Send a POST request to your PHP script that fetches unread message counts
            fetch("fetch_admin_unread_message_counts.php", {
                method: "POST",
                body: formData,
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then(data => {
                console.log("Fetch data:", data)
                // Update the badges based on the received data
                for (const senderId in data) {
                    console.log("val", senderId)
                    const badgeElement = document.getElementById(`notreadCount_${senderId}`);
                    if (badgeElement) {
                        badgeElement.innerHTML = data[senderId];

                        badgeElement.classList.add('circle-badge');
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching unread message counts:', error);
            });
        }

        // Call fetchUnreadMessageCounts every 5 seconds (adjust the interval as needed)
        setInterval(fetchAdminUnreadMessageCounts, 300);
    </script>
</body>
</html>
