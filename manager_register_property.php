<?php
session_name("admin_session");
session_start();
require_once "config.php";

if (!isset($_SESSION['admin_id']) && $_SESSION['dashboard'] !== 'admin_dashboard') {
    // User is not logged in or not authorized for this dashboard
    header('Location: admin_login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $district = $_POST['district'];
    $district_options = $_POST['district_options'];
    $building_name = $_POST['building_name'];
    $address = $_POST['address'];
    $street_name = $_POST['street_name'];

    // Get the selected image unique_id
    $uniqueId = $_POST['selected_image'];

    if (empty($uniqueId) || $uniqueId == 0) {
        $error_message = "Select the building image";
    } else {
        try {
            // Fetch apartment_type from property_registration table
            $fetchApartmentTypeStmt = $pdo->prepare("SELECT apartment_type FROM property_registration WHERE unique_id = :property_id");
            $fetchApartmentTypeStmt->bindParam(':property_id', $uniqueId);
            $fetchApartmentTypeStmt->execute();
            $apartmentTypeResult = $fetchApartmentTypeStmt->fetch(PDO::FETCH_ASSOC);

            if (!$apartmentTypeResult) {
                echo "Error: Apartment type not found for the selected property.";
            } else {
                $apartmentType = $apartmentTypeResult['apartment_type'];

                // Update the property_name in the property_registration table
                $updateStmt = $pdo->prepare("UPDATE property_registration SET property_name = :property_name WHERE unique_id = :property_id");
                $updateStmt->bindParam(':property_name', $building_name);
                $updateStmt->bindParam(':property_id', $uniqueId);

                if ($updateStmt->execute()) {
                    // Property update successful, now proceed with the property registration
                    $stmt = $pdo->prepare("INSERT INTO manager_property_registration (property_name, property_id, district, district_options, street_name, user_id, address, apartment_type) VALUES (:property_name, :property_id, :district, :district_options, :street_name, :user_id, :address, :apartment_type)");
                    $stmt->bindParam(':property_name', $building_name);
                    $stmt->bindParam(':property_id', $uniqueId);
                    $stmt->bindParam(':district', $district);
                    $stmt->bindParam(':district_options', $district_options);
                    $stmt->bindParam(':street_name', $street_name);
                    $stmt->bindParam(':user_id', $_SESSION["admin_unique_id"]);
                    $stmt->bindParam(':address', $address);
                    $stmt->bindParam(':apartment_type', $apartmentType);

                    if ($stmt->execute()) {
                        echo '<script type="text/javascript">
                                alert("Property Registration Successful.");
                                window.location = "manager_dashboard.php"; // Replace with the actual URL of your homepage
                            </script>';
                    } else {
                        echo "Error: Property registration failed.";
                    }
                } else {
                    echo "Error: Property update failed.";
                }
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Property Registration Form</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f0f0;
            text-align: center;
        }

        .container {
            max-width: 800px;
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

        select,
        input,
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button[type="submit"] {
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

        .building-image {
            width: 500px;
            height: 300px;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .building-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            cursor: pointer;
        }

        .details-text {
            cursor: pointer;
            color: #3f72af;
            display: block;
            margin-top: 10px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
            justify-content: center;
        }

        .grid-item {
            max-width: 100%;
            height: auto;
            cursor: pointer;
        }

        .label-for-images,
        .building-details {
            display: none;
        }

        /* Add a style for the selected image */
        .selected-image {
            border: 6px solid red;
            box-sizing: border-box;
            /* Ensure the border size is included in the element's total size */
            padding: 0;
            /* Remove any padding that may affect the size */
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let selectedImage = null;

            fetch('fetch_districts.php')
                .then(response => response.json())
                .then(data => {
                    const districtSelect = document.getElementById("district");
                    if (data.length === 0) {
                        document.querySelector('.container').innerHTML = "<p>No building available to register.</p>";
                    } else {
                        data.forEach(district => {
                            const option = document.createElement("option");
                            option.value = district;
                            option.textContent = district;
                            districtSelect.appendChild(option);
                        });
                    }
                });

            // Function to populate the District Options dropdown based on selected District
            document.getElementById("district").addEventListener("change", function () {
                const selectedDistrict = this.value;
                const districtOptionsSelect = document.getElementById("district_options");
                districtOptionsSelect.innerHTML = "<option value=''>Select District Options</option>";

                fetch('fetch_district_options.php?district=' + selectedDistrict)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(option => {
                            const optionElement = document.createElement("option");
                            optionElement.value = option;
                            optionElement.textContent = option;
                            districtOptionsSelect.appendChild(optionElement);
                        });
                    });
            });

            document.getElementById("district_options").addEventListener("change", function () {
                const selectedDistrict = document.getElementById("district").value;
                const selectedDistrictOptions = this.value;

                fetch('fetch_unique_ids.php?district=' + selectedDistrict + '&district_options=' + selectedDistrictOptions)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const buildingImagesContainer = document.getElementById("building_images_container");
                        buildingImagesContainer.innerHTML = "";

                        const gridContainer = document.createElement("div");
                        gridContainer.classList.add("grid-container");

                        data.forEach(uniqueIdObject => {
                            const uniqueId = uniqueIdObject.unique_id;

                            fetch('check_manager_property_registration.php?unique_id=' + uniqueId)
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('Network response was not ok');
                                    }
                                    return response.json();
                                })
                                .then(result => {
                                    if (!result.exists) {
                                        fetch('fetch_building_detail.php?unique_id=' + uniqueId)
                                            .then(response => {
                                                if (!response.ok) {
                                                    throw new Error('Network response was not ok');
                                                }
                                                return response.json();
                                            })
                                            .then(details => {
                                                if (details.length > 0) {
                                                    details.forEach((detail, index) => {
                                                        const gridItem = createImageElement(detail, index);
                                                        gridContainer.appendChild(gridItem);

                                                        // Show the label for images container when there are images
                                                        document.querySelector('.label-for-images').style.display = 'block';
                                                        // Show the building details labels and inputs
                                                        document.querySelector('.building-details').style.display = 'block';
                                                    });
                                                } else {
                                                    console.log("No details found for unique_id:", uniqueId);
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error fetching building details:', error);
                                            });
                                    }
                                })
                                .catch(error => {
                                    console.error('Error checking manager property registration:', error);
                                });
                        });

                        buildingImagesContainer.appendChild(gridContainer);
                    })
                    .catch(error => {
                        console.error('Error fetching unique IDs:', error);
                    });
            });


            function openModal(details) {
                const modal = document.createElement("div");
                modal.classList.add("modal");

                const modalContent = document.createElement("div");
                modalContent.classList.add("modal-content");

                const closeBtn = document.createElement("span");
                closeBtn.innerHTML = "&times;";
                closeBtn.classList.add("close-btn");
                closeBtn.addEventListener("click", function () {
                    modal.style.display = "none";
                });

                const detailsContainer = document.createElement("div");
                detailsContainer.innerHTML = `
                    <p>Apartment Type: ${details.apartment_type}</p>
                    <p>Other Details: ${details.other_details}</p>
                `;

                modalContent.appendChild(closeBtn);
                modalContent.appendChild(detailsContainer);
                modal.appendChild(modalContent);

                document.body.appendChild(modal);
                modal.style.display = "flex";
            }

            function createImageElement(detail, index) {
                const gridItem = document.createElement("div");
                gridItem.classList.add("grid-item");

                const buildingImage = document.createElement("div");
                buildingImage.classList.add("building-image");

                const imgElement = document.createElement("img");
                imgElement.src = detail.image_path;
                imgElement.alt = `Image ${index + 1}`;
                imgElement.classList.add("grid-item");
                imgElement.addEventListener("click", function () {
                    if (selectedImage) {
                        selectedImage.classList.remove("selected-image");
                    }

                    selectedImage = imgElement;
                    imgElement.classList.add("selected-image");

                    // Update the hidden input field with the unique_id of the selected image
                    document.getElementById("selected_image").value = detail.property_id;

                    // Log the unique_id to the console for debugging
                    console.log("Selected unique_id:", detail.property_id);
                });

                buildingImage.appendChild(imgElement);

                const detailsText = document.createElement("p");
                detailsText.innerHTML = "Click for more details";
                detailsText.classList.add("details-text");
                detailsText.addEventListener("click", function () {
                    openModal(detail);
                });

                gridItem.appendChild(buildingImage);
                gridItem.appendChild(detailsText);
                return gridItem;
            }
        });
    </script>
</head>

<body>
    <header>
        <a class="back-link" href="manager_dashboard.php">&#8678;</a>
        <img src="Logo_final.png" alt="Logo" class="header-img">
    </header>
    <div class="container">
        <h2>Register New Property</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="district">District:</label>
            <select id="district" name="district">
                <option value="">Select District</option>
            </select>

            <label for="district_options">District Options:</label>
            <select id="district_options" name="district_options">
                <option value="">Select District Options</option>
            </select>
            <label for="building_images_container" class="label-for-images">Select your building</label>
            <?php if (!empty($error_message)) : ?>
                <p style="color: red;"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <div id="building_images_container" class="grid-container"></div>

            <div class="building-details">
                <label for="building_name">Building name:</label>
                <input name="building_name">
                <label for="address">Address:</label>
                <input name="address">
                <label for="street_name">Street name:</label>
                <input name="street_name">
            </div>

            <!-- Add a hidden input field to store the selected image's unique_id -->
            <input type="hidden" id="selected_image" name="selected_image" value="">

            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>