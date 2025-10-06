<?php
session_name("user_session");
session_start();
require_once "config.php";

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    // If the user is not logged in, redirect them to the login page
    header("Location: user_login.php");
    exit();
}

// Check if it's time to show the evaluation modal
$userId = $_SESSION["id"]; // Adjust this based on your session variable


$query = "SELECT title, first_name, last_name FROM users WHERE id = :userId";
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

$userId = $_SESSION["user_unique_id"];

// Check for unanswered invites in the user_invites table
$unansweredInvites = false;
try {
    $query = "SELECT COUNT(*) FROM user_invites WHERE user_id = :userId AND invite_answered = 0";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $unansweredInvitesCount = $stmt->fetchColumn();

    // If there are unanswered invites, set the $unansweredInvites variable to true
    if ($unansweredInvitesCount > 0) {
        $unansweredInvites = true;
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | EOD Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0a0e17;
            --secondary: #00f5d4;
            --accent: #7b4fff;
            --accent-glow: rgba(123, 79, 255, 0.4);
            --accent-secondary: #ff6b9d;
            --dark: #0f1420;
            --darker: #0a0e17;
            --light: #1a1f2e;
            --lighter: #242a3a;
            --text: #ffffff;
            --text-secondary: #a0a8c4;
            --success: #00e676;
            --warning: #ffb74d;
            --danger: #ff5252;
            --transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            --shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            --radius: 20px;
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.7;
            color: var(--text);
            background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 50%, #151a28 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            line-height: 1.2;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 30px;
        }

        /* Advanced Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .bg-circle {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, var(--accent-glow) 0%, transparent 70%);
            animation: float 20s infinite linear;
            filter: blur(40px);
        }

        .bg-circle:nth-child(1) {
            width: 800px;
            height: 800px;
            top: -400px;
            left: -200px;
            animation-delay: 0s;
            background: radial-gradient(circle, rgba(123, 79, 255, 0.3) 0%, transparent 70%);
        }

        .bg-circle:nth-child(2) {
            width: 600px;
            height: 600px;
            top: 50%;
            right: -300px;
            animation-delay: -5s;
            animation-duration: 25s;
            background: radial-gradient(circle, rgba(0, 245, 212, 0.2) 0%, transparent 70%);
        }

        .bg-circle:nth-child(3) {
            width: 400px;
            height: 400px;
            bottom: -200px;
            left: 30%;
            animation-delay: -10s;
            animation-duration: 30s;
            background: radial-gradient(circle, rgba(255, 107, 157, 0.2) 0%, transparent 70%);
        }

        .grid-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            opacity: 0.3;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg) scale(1); }
            33% { transform: translate(30px, -50px) rotate(120deg) scale(1.1); }
            66% { transform: translate(-20px, 20px) rotate(240deg) scale(0.9); }
            100% { transform: translate(0, 0) rotate(360deg) scale(1); }
        }

        /* Premium Header Styles */
        header {
            background: rgba(10, 14, 23, 0.7);
            backdrop-filter: blur(30px);
            border-bottom: 1px solid var(--glass-border);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 20px 0;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo img {
            height: 50px;
            width: auto;
        }

        .logo-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(90deg, var(--text), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            transition: var(--transition);
            padding: 14px 22px;
            border-radius: 50px;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            position: relative;
            overflow: hidden;
            text-decoration: none;
            color: white;
        }

        .header-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: var(--transition);
        }

        .header-item:hover::before {
            left: 100%;
        }

        .header-item:hover {
            background: rgba(123, 79, 255, 0.1);
            border-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .icon-wrapper {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            position: relative;
        }

        .icon-wrapper i {
            font-size: 18px;
            color: var(--darker);
            z-index: 2;
        }

        .notification-container {
            position: relative;
        }

        .notification-icon {
            position: relative;
            cursor: pointer;
            padding: 14px;
            border-radius: 50%;
            transition: var(--transition);
            background: var(--glass);
            border: 1px solid var(--glass-border);
        }

        .notification-icon:hover {
            background: rgba(123, 79, 255, 0.1);
            border-color: var(--accent);
            transform: translateY(-2px) rotate(10deg);
        }

        .badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: linear-gradient(90deg, var(--accent), var(--accent-secondary));
            color: var(--darker);
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 0 10px var(--accent-glow);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 400px;
            background: var(--light);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            z-index: 10;
            display: none;
            max-height: 450px;
            overflow-y: auto;
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(20px);
            transform: translateY(10px);
            opacity: 0;
            transition: var(--transition);
        }

        .notification-dropdown.show {
            display: block;
            transform: translateY(0);
            opacity: 1;
        }

        .notification-header {
            padding: 20px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-item {
            padding: 18px 20px;
            border-bottom: 1px solid var(--glass-border);
            transition: var(--transition);
            cursor: pointer;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }

        .notification-item:hover {
            background: rgba(123, 79, 255, 0.05);
        }

        .notification-icon-small {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            flex-shrink: 0;
        }

        .notification-icon-small i {
            font-size: 16px;
            color: var(--darker);
        }

        .notification-content {
            flex: 1;
        }

        .notification-item p {
            margin-bottom: 5px;
            color: var(--text);
        }

        .timestamp {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .navbar-toggle {
            cursor: pointer;
            padding: 14px;
            border-radius: 50%;
            transition: var(--transition);
            background: var(--glass);
            border: 1px solid var(--glass-border);
        }

        .navbar-toggle:hover {
            background: rgba(123, 79, 255, 0.1);
            border-color: var(--accent);
            transform: translateY(-2px) rotate(90deg);
        }

        .navbar-expanded {
            position: absolute;
            top: 100%;
            right: 30px;
            background: var(--light);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            z-index: 10;
            display: none;
            width: 240px;
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(20px);
            transform: translateY(10px);
            opacity: 0;
            transition: var(--transition);
        }

        .navbar-expanded.show {
            display: block;
            transform: translateY(0);
            opacity: 1;
        }

        .navbar-expanded ul {
            list-style: none;
        }

        .navbar-expanded li {
            border-bottom: 1px solid var(--glass-border);
        }

        .navbar-expanded li:last-child {
            border-bottom: none;
        }

        .navbar-expanded a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 18px 20px;
            transition: var(--transition);
            color: var(--text);
        }

        .navbar-expanded a:hover {
            background: rgba(123, 79, 255, 0.1);
            color: var(--secondary);
        }

        .nav-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--glass);
        }

        /* Main Content */
        main {
            padding: 50px 0;
        }

        .profile-incomplete-message {
            background: linear-gradient(90deg, var(--warning), #ff9800);
            color: var(--darker);
            padding: 20px 30px;
            border-radius: var(--radius);
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 20px rgba(255, 183, 77, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .profile-incomplete-message::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: translateX(-100%);
        }

        .profile-incomplete-message:hover::before {
            transform: translateX(100%);
            transition: transform 0.6s ease;
        }

        .profile-incomplete-message a {
            color: var(--darker);
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            transition: var(--transition);
        }

        .profile-incomplete-message a:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(5px);
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 50px;
        }

        .dashboard-title h1 {
            font-size: 3rem;
            background: linear-gradient(90deg, var(--text), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }

        .dashboard-title h1::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--secondary));
            border-radius: 2px;
        }

        .dashboard-title p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            max-width: 500px;
        }

        .stats-container {
            display: flex;
            gap: 20px;
        }

        .stat-item {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius);
            padding: 20px 30px;
            text-align: center;
            backdrop-filter: blur(10px);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(123, 79, 255, 0.05), rgba(0, 245, 212, 0.05));
            opacity: 0;
            transition: var(--transition);
        }

        .stat-item:hover::before {
            opacity: 1;
        }

        .stat-item:hover {
            transform: translateY(-5px);
            border-color: var(--accent);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(90deg, var(--secondary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .dashboard-card {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius);
            padding: 40px 35px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            text-decoration: none;
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(123, 79, 255, 0.05), rgba(0, 245, 212, 0.05));
            opacity: 0;
            transition: var(--transition);
        }

        .dashboard-card:hover::before {
            opacity: 1;
        }

        .dashboard-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            border-color: rgba(123, 79, 255, 0.3);
        }

        .card-icon {
            width: 90px;
            height: 90px;
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            font-size: 36px;
            position: relative;
            z-index: 2;
            transition: var(--transition);
        }

        .dashboard-card:hover .card-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .card-1 .card-icon {
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            color: var(--darker);
            box-shadow: 0 10px 30px rgba(123, 79, 255, 0.4);
        }

        .card-2 .card-icon {
            background: linear-gradient(135deg, #5c6bc0, #7986cb);
            color: var(--darker);
            box-shadow: 0 10px 30px rgba(92, 107, 192, 0.4);
        }

        .card-3 .card-icon {
            background: linear-gradient(135deg, var(--accent-secondary), #ff8aab);
            color: var(--darker);
            box-shadow: 0 10px 30px rgba(255, 107, 157, 0.4);
        }

        .dashboard-card h2 {
            font-size: 1.7rem;
            margin-bottom: 15px;
            color: var(--text);
            position: relative;
            z-index: 2;
        }

        .dashboard-card p {
            color: var(--text-secondary);
            margin-bottom: 25px;
            position: relative;
            z-index: 2;
            line-height: 1.6;
        }

        .card-link {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: var(--secondary);
            font-weight: 600;
            transition: var(--transition);
            position: relative;
            z-index: 2;
            padding: 12px 0;
        }

        .card-link:hover {
            gap: 18px;
            color: var(--text);
        }

        .card-link i {
            transition: var(--transition);
        }

        .card-link:hover i {
            transform: translateX(5px);
        }

        /* Gallery Section */
        .gallery-section {
            margin-top: 60px;
        }

        .section-heading {
            font-size: 2.4rem;
            color: var(--text);
            margin-bottom: 40px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--glass-border);
            position: relative;
        }

        .section-heading::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 120px;
            height: 4px;
            background: linear-gradient(90deg, var(--accent), var(--secondary));
            border-radius: 2px;
        }

        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .image-item {
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            border: 1px solid var(--glass-border);
        }

        .image-item:hover {
            transform: translateY(-10px) scale(1.03);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            border-color: var(--accent);
        }

        .image-item img {
            width: 100%;
            height: 240px;
            object-fit: cover;
            display: block;
            transition: var(--transition);
        }

        .image-item:hover img {
            transform: scale(1.1);
        }

        .image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(10, 14, 23, 0.95));
            color: white;
            padding: 25px;
            transform: translateY(100%);
            transition: var(--transition);
        }

        .image-item:hover .image-overlay {
            transform: translateY(0);
        }

        .image-overlay h3 {
            font-size: 1.3rem;
            margin-bottom: 8px;
        }

        .image-overlay p {
            font-size: 0.95rem;
            opacity: 0.8;
            line-height: 1.5;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 14, 23, 0.95);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            backdrop-filter: blur(10px);
        }

        .modal-content {
            background: var(--light);
            border-radius: var(--radius);
            width: 100%;
            max-width: 1100px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            border: 1px solid var(--glass-border);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
            transform: scale(0.9);
            opacity: 0;
            transition: all 0.4s ease;
        }

        .modal.show .modal-content {
            transform: scale(1);
            opacity: 1;
        }

        .close-button {
            position: absolute;
            top: 25px;
            right: 25px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--glass-border);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            transition: var(--transition);
            color: var(--text);
        }

        .close-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        .image-details-container {
            display: flex;
            flex-direction: column;
        }

        @media (min-width: 992px) {
            .image-details-container {
                flex-direction: row;
            }
        }

        .displayed-image {
            width: 100%;
            max-width: 600px;
            height: auto;
            object-fit: cover;
        }

        .image-details {
            flex: 1;
            padding: 40px;
        }

        .image-texts {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--glass-border);
        }

        .label {
            font-size: 1rem;
            color: var(--text-secondary);
            margin-bottom: 8px;
            font-weight: 500;
        }

        .disclaimer {
            font-style: italic;
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 25px;
            padding: 20px;
            background: var(--glass);
            border-radius: var(--radius);
            border: 1px solid var(--glass-border);
            line-height: 1.6;
        }

        .feedback-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(90deg, var(--accent), var(--secondary));
            color: var(--darker);
            padding: 20px 25px;
            border-radius: var(--radius);
            margin-top: 30px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
        }

        .feedback-toggle:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(123, 79, 255, 0.3);
        }

        .arrow-container {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .arrow {
            width: 16px;
            height: 2px;
            background: var(--darker);
            transform: rotate(45deg);
        }

        .arrow:last-child {
            transform: rotate(-45deg);
        }

        .feedback-form-container {
            margin-top: 30px;
            padding: 30px;
            background: var(--glass);
            border-radius: var(--radius);
            border: 1px solid var(--glass-border);
            transition: var(--transition);
        }

        .feedback-form-heading {
            font-size: 1.6rem;
            margin-bottom: 25px;
            color: var(--text);
            position: relative;
            display: inline-block;
        }

        .feedback-form-heading::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 50%;
            height: 2px;
            background: linear-gradient(90deg, var(--accent), var(--secondary));
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--text);
        }

        input, textarea {
            width: 100%;
            padding: 18px 20px;
            background: var(--dark);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius);
            font-size: 16px;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
            color: var(--text);
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        textarea {
            min-height: 140px;
            resize: vertical;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 18px 35px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            font-size: 15px;
            gap: 12px;
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--accent), var(--secondary));
            color: var(--darker);
            box-shadow: 0 10px 25px rgba(123, 79, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(123, 79, 255, 0.4);
        }

        .hidden {
            display: none;
        }

        /* Responsive Styles */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            }
        }

        @media (max-width: 992px) {
            .header-right {
                gap: 10px;
            }
            
            .header-item span {
                display: none;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 25px;
            }
            
            .stats-container {
                width: 100%;
                justify-content: space-between;
            }
            
            .dashboard-title h1 {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .image-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .notification-dropdown {
                width: 320px;
                right: -50px;
            }
            
            .dashboard-title h1 {
                font-size: 2.2rem;
            }
            
            .stats-container {
                flex-wrap: wrap;
            }
            
            .stat-item {
                flex: 1;
                min-width: 120px;
            }
        }

        @media (max-width: 576px) {
            .header-container {
                flex-wrap: wrap;
            }
            
            .logo {
                margin-bottom: 15px;
            }
            
            .header-right {
                width: 100%;
                justify-content: space-between;
            }
            
            .notification-dropdown {
                width: 280px;
                right: -80px;
            }
            
            .image-details {
                padding: 25px;
            }
            
            .container {
                padding: 0 20px;
            }
            
            .dashboard-title h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Advanced Animated Background -->
    <div class="bg-animation">
        <div class="bg-circle"></div>
        <div class="bg-circle"></div>
        <div class="bg-circle"></div>
        <div class="grid-pattern"></div>
    </div>

    <!-- Premium Header -->
    <header>
        <div class="container header-container">
            <div class="logo">
                <img src="Logo_final.png" alt="EOD Platform">
                <span class="logo-text">EOD Platform</span>
            </div>
            
            <div class="header-right">
                <a class="header-item" href="chat.php">
                    <div class="icon-wrapper">
                        <i class="fas fa-comments"></i>
                    </div>
                    <span>Messages</span>
                </a>

                <a class="header-item" href="evaluation_question.php">
                    <div class="icon-wrapper">
                        <i class="fas fa-star"></i>
                    </div>
                    <span>Evaluation</span>
                </a>
                
                <!-- Notifications Section -->
                <div class="notification-container">
                    <div class="notification-icon" id="notificationIcon">
                        <i class="fas fa-bell"></i>
                        <span class="badge" id="notificationBadge">3</span>
                    </div>

                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-header">
                            <h3>Notifications</h3>
                            <span class="timestamp">3 unread</span>
                        </div>
                        <div id="notificationList">
                            <!-- Notifications will be dynamically added here -->
                        </div>
                    </div>
                </div>
                
                <div class="navbar-toggle" onclick="toggleNavbar()">
                    <i class="fas fa-cog"></i>
                </div>
            </div>
        </div>
        
        <nav class="navbar-expanded" id="navbar">
            <ul>
                <li>
                    <a href="user_profile.php">
                        <div class="nav-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <span>Profile</span>
                    </a>
                </li>
                <li>
                    <a href="user_logout.php">
                        <div class="nav-icon">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <?php
                // Display a message with a link to complete the profile if it's incomplete
                if ($incompleteProfile) {
                    echo "<div class='profile-incomplete-message'>
                            <div><i class='fas fa-exclamation-circle'></i> Your profile is incomplete. Complete it to access all features.</div>
                            <a href='complete_profile.php'>Complete Profile <i class='fas fa-arrow-right'></i></a>
                          </div>";
                }
            ?>
            
            <div class="dashboard-header">
                <div class="dashboard-title">
                    <h1>Welcome Back</h1>
                    <p>Manage your properties, provide feedback, and engage with developers</p>
                </div>
                <div class="stats-container">
                    <div class="stat-item">
                        <div class="stat-value">12</div>
                        <div class="stat-label">Properties</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">5</div>
                        <div class="stat-label">Feedbacks</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">3</div>
                        <div class="stat-label">Messages</div>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <a href="requirement_queries.php" class="dashboard-card card-1">
                    <div class="card-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <h2>Requirements & Queries</h2>
                    <p>Submit your requirements and get answers to your questions about properties and developments from our expert team.</p>
                    <div class="card-link">
                        <span>Get Started</span>
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
                
                <a href="user_register_property.php" class="dashboard-card card-2">
                    <div class="card-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h2>Select New Property</h2>
                    <p>Browse and select from available properties to provide feedback and get involved in the development process.</p>
                    <div class="card-link">
                        <span>Browse Properties</span>
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
                
                <?php if ($unansweredInvites): ?>
                <a href="feedback_answer.php" class="dashboard-card card-3">
                    <div class="card-icon">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <h2>Unanswered Feedback</h2>
                    <p>You have pending feedback requests that need your attention. Provide your valuable input to shape better buildings.</p>
                    <div class="card-link">
                        <span>Provide Feedback</span>
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
                <?php endif; ?>
            </div>
            
            <div class="gallery-section">
                <h2 class="section-heading">Featured Properties</h2>
                <div class="image-grid" id="imageGrid">
                    <!-- Images will be dynamically added here -->
                </div>
            </div>
        </div>
    </main>

    <!-- Image Details Modal -->
    <div class="modal" id="imageModal">
        <div class="modal-content">
            <button class="close-button" id="closeModal">
                <i class="fas fa-times"></i>
            </button>
            <div class="image-details-container" id="imageDetailsContainer">
                <!-- Image details will be dynamically added here -->
            </div>
        </div>
    </div>

    <script>
        // Function to toggle the notification dropdown
        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('show');
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

        // Fetch notifications every 5 seconds
        setInterval(fetchNotifications, 5000);

        // Attach click event to the notification icon
        const notificationIcon = document.getElementById('notificationIcon');
        notificationIcon.addEventListener('click', () => {
            toggleNotificationDropdown();
            markAllNotificationsAsRead();
            fetchNotifications();
        });

        // Close the dropdown when clicking outside of it
        document.addEventListener('click', event => {
            if (!event.target.closest('.notification-container')) {
                const dropdown = document.getElementById('notificationDropdown');
                dropdown.classList.remove('show');
            }
        });

        function displayNotifications(notifications) {
            const notificationsContainer = document.getElementById('notificationList');
            
            // Clear previous notifications
            notificationsContainer.innerHTML = '';

            if (notifications.length === 0) {
                // Display a message if there are no notifications
                notificationsContainer.innerHTML = '<div class="notification-item"><div class="notification-icon-small"><i class="fas fa-bell-slash"></i></div><div class="notification-content"><p>No notifications.</p></div></div>';
            } else {
                // Loop through the notifications and create HTML elements to display them
                notifications.forEach(notification => {
                    const notificationDiv = document.createElement('div');
                    notificationDiv.classList.add('notification-item');
                    
                    notificationDiv.innerHTML = `
                        <div class="notification-icon-small">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="notification-content">
                            <p>${notification.message}</p>
                            <p class="timestamp">${notification.created_at}</p>
                        </div>
                    `;
                    
                    // Add an event listener to mark the notification as read when clicked
                    notificationDiv.addEventListener('click', () => {
                        markNotificationAsRead(notification.id);
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
                if (data.success) {
                    // All notifications have been marked as read
                    const badge = document.getElementById('notificationBadge');
                    badge.textContent = '0';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Function to fetch and display recent images in a grid layout
        function fetchRecentImages() {
            fetch('fetch_recent_images.php')
                .then(response => response.json())
                .then(data => {
                    const imageGrid = document.getElementById('imageGrid');
                    imageGrid.innerHTML = '';

                    if (data.length > 0) {
                        data.forEach(image => {
                            const imageItem = document.createElement('div');
                            imageItem.className = 'image-item';
                            
                            imageItem.innerHTML = `
                                <img src="${image.image_path}" alt="${image.apartment_type}">
                                <div class="image-overlay">
                                    <h3>${image.apartment_type}</h3>
                                    <p>${image.other_details ? image.other_details.substring(0, 80) + '...' : 'No details available'}</p>
                                </div>
                            `;
                            
                            // Add a click event to display image details
                            imageItem.addEventListener('click', () => {
                                displayImageDetails(image);
                            });

                            imageGrid.appendChild(imageItem);
                        });
                    } else {
                        imageGrid.innerHTML = '<p style="color: var(--text-secondary); text-align: center; grid-column: 1 / -1; padding: 40px;">No property images available at the moment.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function displayImageDetails(image) {
            const modal = document.getElementById('imageModal');
            const container = document.getElementById('imageDetailsContainer');
            
            container.innerHTML = `
                <img src="${image.image_path}" alt="${image.apartment_type}" class="displayed-image">
                <div class="image-details">
                    <h2 class="feedback-form-heading">Property Details</h2>
                    <div class="image-texts">
                        <h3 class="label">Property Type:</h3>
                        <p>${image.apartment_type}</p>
                    </div>
                    <div class="image-texts">
                        <h3 class="label">Description:</h3>
                        <p>${image.other_details || 'No description available'}</p>
                    </div>
                    <div class="image-texts">
                        <h3 class="label">Listed On:</h3>
                        <p>${image.created_at}</p>
                    </div>
                    <p class="disclaimer">Acknowledgement: The images used on this platform are for demonstration only and are downloaded from Centaline Property Agency Limited website.</p>
                    
                    <div id="feedbackToggle" class="feedback-toggle">
                        <p>Provide Feedback</p>
                        <div class="arrow-container">
                            <div class="arrow"></div>
                            <div class="arrow"></div>
                        </div>
                    </div>
                    
                    <div id="feedbackForm" class="feedback-form-container hidden">
                        <h2 class="feedback-form-heading">Property Feedback</h2>
                        <div class="form-group">
                            <label for="gmail">Your Email:</label>
                            <input type="email" id="gmail" name="gmail" placeholder="Enter your email address">
                        </div>
                        <div class="form-group">
                            <label for="text">Your Feedback:</label>
                            <textarea id="text" name="text" placeholder="Share your thoughts about this property..."></textarea>
                        </div>
                        <button type="button" id="submitButton" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            <span>Submit Feedback</span>
                        </button>
                    </div>
                </div>
            `;

            // Show the modal
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
            
            // Add an event listener to close the modal
            const closeButton = document.getElementById('closeModal');
            closeButton.addEventListener('click', () => {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 400);
            });

            // Add an event listener to submit feedback
            const submitButton = document.getElementById('submitButton');
            submitButton.addEventListener('click', () => {
                const gmailValue = document.getElementById('gmail').value;
                const textValue = document.getElementById('text').value;

                // Get the property_id and user_id from the image object
                const propertyId = image.property_id;
                const userId = <?php echo $userId ?>;

                // Call the handleSubmitForm function with all values
                handleSubmitForm(propertyId, userId, gmailValue, textValue);
            });

            // Add an event listener to toggle the feedback form visibility
            const feedbackToggle = document.getElementById('feedbackToggle');
            const feedbackFormContainer = document.getElementById('feedbackForm');

            feedbackToggle.addEventListener('click', () => {
                feedbackFormContainer.classList.toggle('hidden');
                // Update the text based on the current state
                const feedbackToggleText = feedbackFormContainer.classList.contains('hidden') ? 'Provide Feedback' : 'Close Feedback Form';
                feedbackToggle.querySelector('p').textContent = feedbackToggleText;
            });
        }

        // Function to handle form submission
        function handleSubmitForm(propertyId, userId, gmailValue, textValue) {
            fetch('save_feedback.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ property_id: propertyId, user_id: userId, gmail: gmailValue, text: textValue }),
            })
            .then(response => response.json())
            .then(data => {
                console.log('Server response:', data);
                if (data.success) {
                    // Show success message
                    alert('Feedback submitted successfully!');
                    // Close the modal
                    const modal = document.getElementById('imageModal');
                    modal.classList.remove('show');
                    setTimeout(() => {
                        modal.style.display = 'none';
                    }, 400);
                } else {
                    alert('Error submitting feedback. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting feedback. Please try again.');
            });
        }

        function toggleNavbar() {
            const navbar = document.getElementById("navbar");
            navbar.classList.toggle("show");
        }

        // Close navbar when clicking outside
        document.addEventListener('click', function(event) {
            const navbar = document.getElementById("navbar");
            const navbarToggle = document.querySelector('.navbar-toggle');
            
            if (!navbar.contains(event.target) && !navbarToggle.contains(event.target)) {
                navbar.classList.remove("show");
            }
        });

        // Call the fetch functions to load data when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            fetchNotifications();
            fetchRecentImages();
            
            // Add some sample notifications for demo
            setTimeout(() => {
                const notificationsContainer = document.getElementById('notificationList');
                if (notificationsContainer.children.length === 0) {
                    notificationsContainer.innerHTML = `
                        <div class="notification-item">
                            <div class="notification-icon-small">
                                <i class="fas fa-home"></i>
                            </div>
                            <div class="notification-content">
                                <p>New luxury apartment listing added in your area</p>
                                <p class="timestamp">2 hours ago</p>
                            </div>
                        </div>
                        <div class="notification-item">
                            <div class="notification-icon-small">
                                <i class="fas fa-comment"></i>
                            </div>
                            <div class="notification-content">
                                <p>Your feedback has been reviewed by the developer</p>
                                <p class="timestamp">1 day ago</p>
                            </div>
                        </div>
                        <div class="notification-item">
                            <div class="notification-icon-small">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="notification-content">
                                <p>You have an unanswered feedback request</p>
                                <p class="timestamp">3 days ago</p>
                            </div>
                        </div>
                    `;
                }
            }, 1000);
            
            // Add some sample property images for demo
            setTimeout(() => {
                const imageGrid = document.getElementById('imageGrid');
                if (imageGrid.children.length === 0) {
                    imageGrid.innerHTML = `
                        <div class="image-item">
                            <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" alt="Modern Apartment">
                            <div class="image-overlay">
                                <h3>Luxury Skyline Residence</h3>
                                <p>Spacious 3-bedroom apartment with panoramic city views and premium amenities</p>
                            </div>
                        </div>
                        <div class="image-item">
                            <img src="https://images.unsplash.com/photo-1513584684374-8bab748fbf90?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" alt="Contemporary Home">
                            <div class="image-overlay">
                                <h3>Urban Loft Design</h3>
                                <p>Modern open-concept living with industrial elements and smart home features</p>
                            </div>
                        </div>
                        <div class="image-item">
                            <img src="https://images.unsplash.com/photo-1493809842364-78817add7ffb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" alt="Luxury Interior">
                            <div class="image-overlay">
                                <h3>Executive Penthouse</h3>
                                <p>Premium top-floor residence with private terrace and luxury finishes throughout</p>
                            </div>
                        </div>
                    `;
                    
                    // Add click events to the demo images
                    document.querySelectorAll('.image-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const image = {
                                image_path: this.querySelector('img').src,
                                apartment_type: this.querySelector('h3').textContent,
                                other_details: this.querySelector('p').textContent,
                                created_at: '2023-11-15',
                                property_id: 123
                            };
                            displayImageDetails(image);
                        });
                    });
                }
            }, 1500);
        });
    </script>
</body>
</html>