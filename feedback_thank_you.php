<!DOCTYPE html>
<html>
<head>
    <title>Thank You</title>
    <!-- Include your CSS stylesheets and other head elements here -->
    <style>
        /* Additional inline styling for this page */
        body {
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
            text-align: center;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        header {
            background-color: #66A7D8;
            color: #fff;
            padding: 20px;
            border-radius: 8px 8px 0 0;
        }

        h1 {
            font-size: 28px;
            margin: 0;
        }

        main {
            padding: 20px;
        }

        p {
            font-size: 18px;
        }

        a {
            text-decoration: none;
            background-color: #4CAF50;
            color: #fff;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Thank You for Your Feedback!</h1>
        </header>
        <main>
            <p>Thank you for taking the time to provide your valuable feedback. Your input is important to us and helps us improve our services.</p>
        </main>
    </div>
    <script>
        // Automatically redirect to chat.php after 5 seconds
        setTimeout(function () {
            window.location.href = "chat.php";
        }, 2000);
    </script>
</body>
</html>
