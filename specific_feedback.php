<?php
session_name("user_session");
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Type Selection | EOD Platform</title>
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
            padding: 20px 0;
            position: relative;
            overflow: hidden;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .back-link {
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

        .back-link:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(-5px);
        }

        /* Main Content */
        main {
            padding: 80px 0;
            position: relative;
        }

        .selection-container {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            padding: 50px;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .selection-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--accent), var(--secondary));
        }

        .selection-header {
            margin-bottom: 50px;
        }

        .selection-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(99, 91, 255, 0.1), rgba(138, 133, 255, 0.2));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 40px;
            color: var(--accent);
        }

        .selection-header h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary);
        }

        .selection-header p {
            color: var(--gray);
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* User Options */
        .user-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .user-option {
            background: var(--light);
            border-radius: var(--radius);
            padding: 40px 30px;
            text-align: center;
            transition: var(--transition);
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .user-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--accent), var(--secondary));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.5s ease;
        }

        .user-option:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: var(--accent);
        }

        .user-option:hover::before {
            transform: scaleX(1);
        }

        .option-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            transition: var(--transition);
        }

        .registered-user .option-icon {
            background: linear-gradient(135deg, rgba(0, 201, 167, 0.1), rgba(0, 212, 170, 0.2));
            color: var(--success);
        }

        .non-registered-user .option-icon {
            background: linear-gradient(135deg, rgba(255, 184, 0, 0.1), rgba(255, 203, 60, 0.2));
            color: var(--warning);
        }

        .user-option:hover .option-icon {
            transform: scale(1.1);
        }

        .option-title {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--primary);
        }

        .option-description {
            color: var(--gray);
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .user-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(90deg, var(--accent), var(--secondary));
            color: white;
            padding: 14px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 8px 25px rgba(99, 91, 255, 0.3);
        }

        .user-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(99, 91, 255, 0.4);
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
            right: 10%;
        }

        .shape-2 {
            width: 150px;
            height: 150px;
            bottom: 20%;
            left: 10%;
        }

        /* Info Section */
        .info-section {
            margin-top: 50px;
            padding: 30px;
            background: var(--light);
            border-radius: var(--radius);
            text-align: left;
        }

        .info-section h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-section h3 i {
            color: var(--accent);
        }

        .info-section p {
            color: var(--gray);
            line-height: 1.7;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .selection-container {
                padding: 40px 30px;
            }
            
            .selection-header h1 {
                font-size: 2rem;
            }
            
            .user-options {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 0 20px;
            }
            
            .selection-container {
                padding: 30px 20px;
            }
            
            .selection-header h1 {
                font-size: 1.8rem;
            }
            
            .selection-header p {
                font-size: 1rem;
            }
            
            .user-option {
                padding: 30px 20px;
            }
            
            .option-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="logo-section">
                <img src="Logo_final.png" alt="EOD Platform">
                <span class="logo-text">EOD Platform</span>
            </div>
            
            <a class="back-link" href="index.html">
                <i class="fas fa-arrow-left"></i> Back to Homepage
            </a>
        </div>
    </header>

    <main>
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        
        <div class="container">
            <div class="selection-container">
                <div class="selection-header">
                    <div class="selection-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h1>Choose Your User Type</h1>
                    <p>Select how you'd like to provide specific feedback on our platform</p>
                </div>
                
                <div class="user-options">
                    <div class="user-option registered-user">
                        <div class="option-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h3 class="option-title">Registered User</h3>
                        <p class="option-description">
                            Already have an account? Sign in to access personalized features and provide detailed feedback based on your building experience.
                        </p>
                        <a href="user_specific_feedback.php" class="user-link">
                            <i class="fas fa-sign-in-alt"></i> Continue as Registered User
                        </a>
                    </div>
                    
                    <div class="user-option non-registered-user">
                        <div class="option-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h3 class="option-title">Non-Registered User</h3>
                        <p class="option-description">
                            New to our platform? Create an account to provide feedback and access additional features for building evaluation.
                        </p>
                        <a href="user_signup.php" onclick="<?php $_SESSION['followed_specific_feedback'] = true; ?>" class="user-link">
                            <i class="fas fa-user-plus"></i> Continue as New User
                        </a>
                    </div>
                </div>
                
                <div class="info-section">
                    <h3><i class="fas fa-info-circle"></i> Why Register?</h3>
                    <p>
                        Registered users enjoy additional benefits including the ability to track their feedback history, 
                        receive updates on building developments, and participate in community discussions. Your registration 
                        helps us create better living environments for everyone.
                    </p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>