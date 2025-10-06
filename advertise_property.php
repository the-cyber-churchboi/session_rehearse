<?php
require_once 'config.php';

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $apartment_type = $_POST["apartment_type"];
        $image_path = "uploads/" . basename($_FILES["image"]["name"]);
        $other_details = $_POST["other_details"];

        // Move the uploaded image to the server
        move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);

        $stmt = $pdo->prepare("INSERT INTO PropertyAdvertisements (apartment_type, image_path, other_details) VALUES (:apartment_type, :image_path, :other_details)");
        
        $stmt->bindParam(':apartment_type', $apartment_type);
        $stmt->bindParam(':image_path', $image_path);
        $stmt->bindParam(':other_details', $other_details);
        
        if ($stmt->execute()) {
            echo "Property advertisement submitted successfully!";
        } else {
            echo "Error: Unable to execute query.";
        }
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Property Advertisement Form</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f0f0;
            text-align: center;
        }
        .container {
            max-width: 500px;
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
        select, input[type="file"], textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
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
        #image-preview {
            margin-top: 10px;
        }
        #image-preview img {
            max-width: 100%;
            height: auto;
        }
    </style>
    <script>
        function showImagePreview(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('image-preview').innerHTML = '<img src="' + e.target.result + '">';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</head>
<body>
    <header>
        <a class="back-link" href="developer_dashboard.php">&#8678;</a>
        <img src="Logo_final.png" alt="Logo" class="header-img">
    </header>
    <div class="container">
        <h2>Create a New Property Advertisement</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <label for="apartment_type">Type of Apartment:</label>
            <select name="apartment_type">
                <option value="Studio">Studio</option>
                <option value="One-bedroom">One-bedroom</option>
                <option value="Two-bedroom">Two-bedroom</option>
                <option value="Three-bedroom">Three-bedroom</option>
            </select><br><br>

            <label for="image">Image of Apartment:</label>
            <input type="file" name="image" accept="image/*" onchange="showImagePreview(this);"><br>
            <div id="image-preview"></div><br>

            <label for="other_details">Other Details:</label>
            <textarea name="other_details" rows="4" cols="50"></textarea><br><br>

            <input type="submit" value="Submit Advertisement">
        </form>
    </div>
</body>
</html>
