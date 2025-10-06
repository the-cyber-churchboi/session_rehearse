<?php
// Assuming you have already established a database connection
require_once "config.php";

function generateUniqueID($min, $max) {
    $uniqueID = rand($min, $max);

    try {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_registration WHERE unique_identifier = :uniqueID");
        $stmt->bindParam(':uniqueID', $uniqueID, PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            // ID is unique; you can insert it into the table
            return $uniqueID;
        } else {
            // ID already exists; generate a new one
            return generateUniqueID($min, $max);
        }
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
function isEmailVerified($email) {
    // Assuming you have a table 'admin' with fields: email and is_verified
    global $pdo;
    $sql = "SELECT is_verified FROM admin WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $result = $stmt->fetchColumn();

    return $result == 1;
}

function isEmailregistered($email) {
    // Assuming you have a table 'admin' with fields: email and is_verified
    global $pdo;
    $sql = "SELECT is_registered FROM admin WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $result = $stmt->fetchColumn();

    return $result == 1;
}

function getHkidOrPassport($email) {
    // Assuming you have a table 'admin' with fields: email and hkid_passport
    global $pdo;
    $sql = "SELECT hkid_passport FROM admin WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $hkid_passport = $stmt->fetchColumn();

    return $hkid_passport;
}
$email = '';
if (isset($_GET["email"])) {
    $email = $_GET["email"];
    // Rest of your code to complete the registration process
    if (!isEmailVerified($email)) {
        die("Email verification is required to complete registration.");
    }

    if (isEmailregistered($email)) {
        echo "Email is registered.";
        header("refresh:1; url=admin_login.php");
        exit();
    }


    // Fetch the HKID or Passport number for the verified admin
    $hkid_passport = getHkidOrPassport($email);

   


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Handle form submission and database insertion here

        // Validate and sanitize the form inputs
        $title = $_POST['title'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $profession = $_POST['profession'];
        $company_name = $_POST['company_name'];
        $floor = $_POST['floor'];
        $building = $_POST['building'];
        $street_number = $_POST['street_number'];
        $street_name = $_POST['street_name'];
        $district = $_POST['district'];
        $years_experience = $_POST['years_experience'];
        $professional_membership = $_POST['professional_membership'];
        $upload_path = '';
        $adminUniqueID = generateUniqueID(1000001, 10000000);

        // Process the uploaded proof of professional membership
        if (isset($_FILES['proof_membership'])) {
            $file_name = $_FILES['proof_membership']['name'];
            $file_tmp = $_FILES['proof_membership']['tmp_name'];
            $file_type = $_FILES['proof_membership']['type'];
            $file_size = $_FILES['proof_membership']['size'];
            $file_error = $_FILES['proof_membership']['error'];

            // Validate the file size and type (e.g., allow only PDF, PNG, and JPEG)
            $allowed_types = ['application/pdf', 'image/png', 'image/jpeg'];
            if ($file_size > 5000000 || !in_array($file_type, $allowed_types) || $file_error !== 0) {
                // Handle file upload error if needed
                die('Error uploading the file. Please make sure it is a valid PDF, PNG, or JPEG file and does not exceed 5MB in size.');
            }

            // Move the uploaded file to the designated folder (e.g., "uploads/")
            $upload_path = __DIR__ . '/uploads/' . basename($file_name);
            if (!move_uploaded_file($file_tmp, $upload_path)) {
                die('Error moving the uploaded file. Please check the destination directory permissions.');
            }

        }

        // Assuming you have tables 'developers' and 'consultants' with appropriate fields to store the data
        $sql1 = "INSERT INTO " . ($profession == 'Building Developer' ? 'developer' : 'consultants') . " (title, first_name, last_name, hkid_passport, profession, company_name, floor, building, street_number, street_name, district, years_experience, professional_membership, proof_membership, email, unique_identifier)
                VALUES (:title, :first_name, :last_name, :hkid_passport, :profession, :company_name, :floor, :building, :street_number, :street_name, :district, :years_experience, :professional_membership, :proof_membership, :email, :unique_identifier)";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([
            'title' => $title,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'hkid_passport' => $hkid_passport,
            'profession' => $profession,
            'company_name' => $company_name,
            'floor' => $floor,
            'building' => $building,
            'street_number' => $street_number,
            'street_name' => $street_name,
            'district' => $district,
            'years_experience' => $years_experience,
            'professional_membership' => $professional_membership,
            'proof_membership' => $upload_path, // Store the file path in the database
            'email' => $email,
            'unique_identifier' => $adminUniqueID 
        ]);


        // Assuming you have a table 'admin_registration' with appropriate fields to store the data
        $sql = "INSERT INTO admin_registration (title, first_name, last_name, hkid_passport, profession, company_name, floor, building, street_number, street_name, district, years_experience, professional_membership, proof_membership, email, unique_identifier)
                VALUES (:title, :first_name, :last_name, :hkid_passport, :profession, :company_name, :floor, :building, :street_number, :street_name, :district, :years_experience, :professional_membership, :proof_membership, :email, :unique_identifier)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'title' => $title,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'hkid_passport' => $hkid_passport,
            'profession' => $profession,
            'company_name' => $company_name,
            'floor' => $floor,
            'building' => $building,
            'street_number' => $street_number,
            'street_name' => $street_name,
            'district' => $district,
            'years_experience' => $years_experience,
            'professional_membership' => $professional_membership,
            'proof_membership' => $upload_path, // Store the file path in the database
            'email' => $email,
            'unique_identifier' => $adminUniqueID 
        ]);
        // Update the is_registered flag to 1 for the registered admin in the 'admin' table
        $sql_update = "UPDATE admin SET is_registered = 1 WHERE email = :email";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute(['email' => $email]);

        header("refresh:1; url=admin_login.php");
        // Show a success message or redirect to a success page
        echo '<p class="success">Registration completed successfully!</p>';
        exit();
    }
} else {
    // For example, you can display an error message or redirect the user back to the previous page
    die("Error: Email not provided. Please check your email and click link to verify and complete registration.");
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Complete</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f2f2f2;
            margin: 50px auto;
            max-width: 600px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #007bff;
        }

        h2 {
            color: #333;
        }

        form {
            margin-top: 20px;
            text-align: left;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        input[type="file"] {
            padding: 6px;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <h1>Welcome to the Collaborative Platform for Building Professionals</h1>
    <h2>Complete Your Registration</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?email=' . urlencode($email); ?>" method="post" enctype="multipart/form-data">
        <!-- Add a hidden input field to pass the email parameter -->
        <label for="title">Title:</label>
        <select name="title" required>
            <option value="">Select Title</option>
            <option value="Mr">Mr</option>
            <option value="Mrs./Ms.">Mrs./Ms.</option>
            <option value="Dr.">Dr.</option>
            <option value="Prof.">Prof.</option>
            <option value="Ir.">Ir.</option>
        </select><br>

        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" required><br>

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" required><br>

        <label for="hkid_passport">HKID or Passport:</label>
        <input type="text" name="hkid_passport" value="<?php echo htmlspecialchars($hkid_passport); ?>" readonly><br>

        <label for="profession">Profession:</label>
        <select name="profession" required>
            <option value="">Select Profession</option>
            <option value="Architect">Architect</option>
            <option value="Engineer">Engineer</option>
            <option value="Building Developer">Building Developer</option>
            <option value="Local Authority">Local Authority</option>
            <option value="Building Manager">Building Manager</option>
            <option value="Others">Others</option>
        </select><br>

        <label for="company_name">Company Name:</label>
        <input type="text" name="company_name" required><br>

        <label for="company_address">Company Address:</label>

        <label for="floor">Floor:</label>
        <input type="text" name="floor"><br>

        <label for="building">Building:</label>
        <input type="text" name="building"><br>

        <label for="street_number">Street number:</label>
        <input type="text" name="street_number" required><br>

        <label for="street_name">Street name:</label>
        <input type="text" name="street_name" required><br>

        <label for="district">District:</label>
        <input type="text" name="district" required><br>

        <label for="years_experience">Years of Professional Working Experience in the AEC Industry:</label>
        <input type="number" name="years_experience" required><br>

        <label for="professional_membership">Professional Membership in Hong Kong SAR:</label>
        <input type="text" name="professional_membership" required><br>

        <label for="proof_membership">Proof of Professional Membership (PDF/PNG/JPEG):</label>
        <input type="file" name="proof_membership" accept=".pdf,.png,.jpeg" required><br>

        <input type="submit" value="Complete Registration">
    </form>
</body>
</html>
