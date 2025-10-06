<?php
// Replace these variables with your actual database credentials
require_once "config.php";

// Function to handle form submission and insert data into the database
function insertContactMessage($name, $email, $subject, $message, $pdo)
{
    $stmt = $pdo->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (:name, :email, :subject, :message)");
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message,
    ]);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Process the form submission
    $name = $_POST["name"];
    $email = $_POST["email"];
    $subject = $_POST["subject"];
    $message = $_POST["message"];

    try {
        // Create a PDO instance
        require_once "config.php";
        // Insert the contact message into the database
        insertContactMessage($name, $email, $subject, $message, $pdo);

        // Redirect to a thank you page or display a success message
        header("Location: thank_you.php");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | EOD Platform</title>
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
            padding: 0;
            position: relative;
            overflow: hidden;
            min-height: 300px;
            display: flex;
            align-items: center;
        }

        .header-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(99, 91, 255, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(0, 212, 170, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(20, 184, 255, 0.1) 0%, transparent 50%);
            z-index: 1;
        }

        .header-content {
            position: relative;
            z-index: 2;
            width: 100%;
            padding: 40px 0;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
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

        .back-button {
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

        .back-button:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(-5px);
        }

        .page-title {
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }

        .page-title h1 {
            font-size: 3rem;
            margin-bottom: 15px;
            background: linear-gradient(90deg, #ffffff, #e0f7fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
        }

        .page-title p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Main Content */
        main {
            padding: 80px 0;
            position: relative;
        }

        .contact-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: start;
        }

        /* Contact Info */
        .contact-info {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 40px;
            height: fit-content;
        }

        .contact-info h2 {
            font-size: 1.8rem;
            margin-bottom: 25px;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .contact-info h2 i {
            color: var(--accent);
        }

        .contact-details {
            margin-bottom: 30px;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 25px;
            padding: 15px;
            border-radius: var(--radius);
            transition: var(--transition);
        }

        .contact-item:hover {
            background: var(--light);
            transform: translateX(5px);
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: linear-gradient(135deg, rgba(99, 91, 255, 0.1), rgba(138, 133, 255, 0.2));
            color: var(--accent);
        }

        .contact-text h3 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: var(--primary);
        }

        .contact-text p {
            color: var(--gray);
            line-height: 1.6;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .social-link {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .social-link:hover {
            background: var(--accent);
            color: white;
            transform: translateY(-5px);
        }

        /* Contact Form */
        .contact-form-container {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .contact-form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--accent), var(--secondary));
        }

        .contact-form-container h2 {
            font-size: 1.8rem;
            margin-bottom: 25px;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .contact-form-container h2 i {
            color: var(--accent);
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary);
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

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

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
            display: inline-flex;
            align-items: center;
            gap: 10px;
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
            right: 5%;
        }

        .shape-2 {
            width: 150px;
            height: 150px;
            bottom: 20%;
            left: 5%;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .contact-layout {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .contact-info {
                order: 2;
            }
        }

        @media (max-width: 768px) {
            .header-top {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .page-title h1 {
                font-size: 2.2rem;
            }
            
            .contact-info, .contact-form-container {
                padding: 30px;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 0 20px;
            }
            
            .page-title h1 {
                font-size: 1.8rem;
            }
            
            .page-title p {
                font-size: 1rem;
            }
            
            .contact-info, .contact-form-container {
                padding: 25px;
            }
            
            .contact-item {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            
            .social-links {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-bg"></div>
        <div class="container">
            <div class="header-content">
                <div class="header-top">
                    <div class="logo-section">
                        <img src="Logo_final.png" alt="EOD Platform">
                        <span class="logo-text">EOD Platform</span>
                    </div>
                    
                    <a href="index.html" class="back-button">
                        <i class="fas fa-arrow-left"></i> Back to Homepage
                    </a>
                </div>
                
                <div class="page-title">
                    <h1>Get In Touch With Us</h1>
                    <p>Have questions or feedback? We'd love to hear from you</p>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        
        <div class="container">
            <div class="contact-layout">
                <div class="contact-info">
                    <h2><i class="fas fa-info-circle"></i> Contact Information</h2>
                    
                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Our Location</h3>
                                <p>123 Innovation Drive<br>Tech City, TC 12345</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Phone Number</h3>
                                <p>+1 (555) 123-4567<br>Mon-Fri from 9am to 6pm</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Email Address</h3>
                                <p>info@eodplatform.com<br>support@eodplatform.com</p>
                            </div>
                        </div>
                    </div>
                    
                    <h2 style="margin-top: 40px;"><i class="fas fa-share-alt"></i> Follow Us</h2>
                    <div class="social-links">
                        <a href="#" class="social-link">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <div class="contact-form-container">
                    <h2><i class="fas fa-paper-plane"></i> Send Us a Message</h2>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email address" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" class="form-control" placeholder="What is this regarding?" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Your Message</label>
                            <textarea id="message" name="message" class="form-control" rows="5" placeholder="Tell us how we can help you..." required></textarea>
                        </div>
                        
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>