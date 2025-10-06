<?php
require_once "config.php";

// Fetch images and details from the database
$stmt = $pdo->prepare("SELECT a.*, p.property_name FROM propertyadvertisements a
                      LEFT JOIN manager_property_registration p ON a.property_id = p.property_id");
$stmt->execute();
$advertisements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Property Advertisements</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #ff6b6b, #556270);
            color: #fff;
        }

        header {
            background: linear-gradient(to right, #434343, #000000);
            color: #fff;
            padding: 0.8em;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header .logo {
            max-width: 80px;
            max-height: 32px;
        }

        header a {
            color: #fff;
            text-decoration: none;
            margin: 0 15px;
            font-size: 16px;
        }

        h1 {
            text-align: center;
            margin-top: 30px;
            color: #fff;
        }

        .grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin: 20px;
        }

        .advertisement {
            background: linear-gradient(to right, #f06, #9f6);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            margin: 20px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease-in-out;
            border-radius: 10px;
            overflow: hidden;
            position: relative; 
        }

        .advertisement:hover {
            transform: scale(1.05);
        }

        .advertisement p {
            margin: 10px 0;
            font-size: 14px;
        }

        .advertisement img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.3s ease-in-out;
        }

        .advertisement img:hover {
            transform: scale(1.1);
        }

        .tip {
            font-style: italic;
            color: #bdc3c7;
            font-size: 12px;
        }

        .details {
            margin-top: 10px;
            font-size: 14px;
        }

        .feedback-button {
            background: linear-gradient(to right, #4CAF50, #45a049);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s ease-in-out;
            margin-bottom: 20px;
        }

        .feedback-button:hover {
            background: linear-gradient(to right, #45a049, #4CAF50);
        }

        .full-image {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
        }

        .full-image img {
            max-width: 80%;
            max-height: 80%;
            border-radius: 8px;
        }

        .full-details {
            text-align: left;
            color: #fff;
            margin-top: 20px;
            font-size: 14px;
        }

        .close-button {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 30px;
            color: #fff;
            cursor: pointer;
        }

        .disclaimer {
            position: absolute;
            bottom: 10px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 12px;
            color: black;
            font-style: italic;
        }
    </style>
</head>
<body>
    <header>
        <img src="Logo_final.png" alt="Logo" class="logo">
        <div>
            <a href="index.html">Home</a>
            <a href="user_login.php">Login</a>
            <a href="user_signup">Signup</a>
        </div>
    </header>
    <h1>Property Advertisements</h1>

    <?php foreach ($advertisements as $key => $advertisement) : ?>
    <?php
        // Check if property_id exists in manager_property_registration
        if ($advertisement['property_name']) {
    ?>
        <div class="advertisement">
            <p><?php echo $advertisement['property_name']; ?></p>
            <!-- Include the disclaimer text here -->
            <p class="disclaimer">Acknowledgement: The images used on this platform are for demonstration only and are downloaded from Centaline Property Agency Limited website.</p>
            <img src="<?php echo $advertisement['image_path']; ?>" alt="Advertisement Image">
            <p class="tip">Click for more information</p>
            <div class="details">
                <p>Apartment Type: <?php echo $advertisement['apartment_type']; ?></p>
                <p>Other Details: <?php echo $advertisement['other_details']; ?></p>
                <p>Created At: <?php echo $advertisement['created_at']; ?></p>
            </div>
            <button class="feedback-button" data-property-id="<?php echo $advertisement['property_id']; ?>" onclick="openFeedbackForm('feedbackform.php', <?php echo $advertisement['property_id']; ?>)">Give Feedback</button>
        </div>
        <?php
            }
        ?>
    <?php endforeach; ?>

    <!-- Full-screen image display -->
    <div class="full-image" id="fullImage" style="display: none;">
        <span class="close-button" id="closeButton">&times;</span>
        <img id="fullImageView">
        <div class="full-details" id="fullDetails">
            <p>Apartment Type: <span id="apartmentType"></span></p>
            <p>Other Details: <span id="otherDetails"></span></p>
            <p>Created At: <span id="createdAt"></span></p>
        </div>
    </div>

    <script>
        const advertisements = document.querySelectorAll(".advertisement");

        function openFeedbackForm(url, propertyId) {
            // Concatenate property_id to the feedback form URL
            const feedbackUrl = `${url}?property_id=${propertyId}`;
            window.open(feedbackUrl, '_blank'); // Opens the feedback form in a new window
        }
        const fullImage = document.getElementById("fullImage");
        const fullImageView = document.getElementById("fullImageView");
        const closeButton = document.getElementById("closeButton");
        const fullDetails = document.getElementById("fullDetails");
        const apartmentType = document.getElementById("apartmentType");
        const otherDetails = document.getElementById("otherDetails");
        const createdAt = document.getElementById("createdAt");

        advertisements.forEach((advertisement, index) => {
            advertisement.addEventListener("click", (event) => {
                // Check if the clicked element is not the feedback button
                if (!event.target.classList.contains("feedback-button")) {
                    const imageSrc = advertisement.querySelector("img").src;
                    const apartmentTypeText = advertisement.querySelector(".details p:nth-child(1)").textContent.split(': ')[1];
                    const otherDetailsText = advertisement.querySelector(".details p:nth-child(2)").textContent.split(': ')[1];
                    const createdAtText = advertisement.querySelector(".details p:nth-child(3)").textContent.split(': ')[1];

                    fullImageView.src = imageSrc;
                    apartmentType.textContent = apartmentTypeText;
                    otherDetails.textContent = otherDetailsText;
                    createdAt.textContent = createdAtText;

                    fullImage.style.display = "flex";
                    document.body.style.overflow = "hidden";
                }
            });
        });

        closeButton.addEventListener("click", () => {
            fullImage.style.display = "none";
            document.body.style.overflow = "auto";
            apartmentType.textContent = "";
            otherDetails.textContent = "";
            createdAt.textContent = "";
        });
    </script>
</body>
</html>