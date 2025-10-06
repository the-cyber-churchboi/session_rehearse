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

$userId = $_SESSION["user_unique_id"];

// If the user is logged in, continue displaying the dashboard
$updateSql = "UPDATE messages SET message_status = 'delivered' WHERE receiver_id = :userId AND message_status = 'sent'";
$updateStmt = $pdo->prepare($updateSql);
$updateStmt->bindParam(":userId", $userId, PDO::PARAM_INT);
$updateStmt->execute();

// ...

try {
    require_once "config.php";
    
    // Fetch the user's property names
    $userPropertyNames = [];
    $query = "SELECT property_name FROM user_property_registration WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $userPropertyNames = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Check if there are user property names
    if (!empty($userPropertyNames)) {
        // Initialize an empty array to store all admins for the user
        $allUserAdmins = [];

        // Loop through each property name
        foreach ($userPropertyNames as $userProperty) {
            // Fetch admins from property_registration matching the user's property name
            $propertyAdmins = [];
            $query = "SELECT user_id FROM property_registration WHERE property_name = :property_name";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':property_name', $userProperty);
            $stmt->execute();
            $propertyUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Fetch admin details from admin_registration for matching users
            if (!empty($propertyUsers)) {
                $query = "SELECT id, first_name, last_name, unique_identifier, profession, status FROM admin_registration WHERE unique_identifier IN (".implode(",", $propertyUsers).")";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $propertyAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Fetch admins from others_property_registration matching the user's property name
            $othersPropertyAdmins = [];
            $query = "SELECT user_id FROM others_property_registration WHERE property_name = :property_name";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':property_name', $userProperty);
            $stmt->execute();
            $othersPropertyUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Fetch admin details from admin_registration for matching users
            if (!empty($othersPropertyUsers)) {
                $query = "SELECT id, first_name, last_name, unique_identifier, profession,  status FROM admin_registration WHERE unique_identifier IN (".implode(",", $othersPropertyUsers).")";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $othersPropertyAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Merge the two arrays of admins
            $mergedAdmins = array_merge($propertyAdmins, $othersPropertyAdmins);

            // Store these admins in the main array
            $allUserAdmins[$userProperty] = $mergedAdmins;
        }
    } else {
        // Handle the case where there are no user property names
        echo "No Professionals to chat with...";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

function getAverageRating($adminId, $pdo)
{
    $query = "SELECT AVG(rating) AS average_rating FROM feedback_ratings WHERE admin_id = :adminId";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':adminId', $adminId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['average_rating'] ?? 0;
}

// Function to generate star ratings with partial fill
function generateStarRating($averageRating)
{
    $fullStars = floor($averageRating); // Get the number of full stars
    $decimalPart = $averageRating - $fullStars; // Get the decimal part

    $starHtml = '';

    // Full stars
    for ($i = 0; $i < $fullStars; $i++) {
        $starHtml .= '<i class="fas fa-star" style="color: gold;"></i>';
    }

    // Partially filled star
    if ($decimalPart > 0) {
        $starHtml .= '<i class="fas fa-star-half-alt" style="color: gold;"></i>';
    }

    // Empty stars
    $emptyStars = 5 - $fullStars - ($decimalPart > 0 ? 1 : 0);
    for ($i = 0; $i < $emptyStars; $i++) {
        $starHtml .= '<i class="far fa-star" style="color: gold;"></i>';
    }

    return $starHtml;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Interface | EOD Platform</title>
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

        /* Premium Header */
        header {
            background: rgba(10, 14, 23, 0.7);
            backdrop-filter: blur(30px);
            border-bottom: 1px solid var(--glass-border);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
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
            filter: brightness(0) invert(1);
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

        .back-link {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            padding: 12px 20px;
            border-radius: 50px;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            transition: var(--transition);
        }

        .back-link:hover {
            background: rgba(123, 79, 255, 0.1);
            border-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        /* Main Chat Interface */
        main {
            padding: 40px 0;
        }

        .chat-interface {
            display: flex;
            gap: 30px;
            height: calc(100vh - 180px);
            max-height: 800px;
        }

        /* Professionals Panel */
        .professionals-panel {
            width: 380px;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius);
            backdrop-filter: blur(20px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .panel-header {
            padding: 25px;
            border-bottom: 1px solid var(--glass-border);
        }

        .panel-header h2 {
            font-size: 1.5rem;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .panel-header h2 i {
            color: var(--accent);
        }

        .search-container {
            margin-top: 20px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 14px 45px 14px 20px;
            background: var(--dark);
            border: 1px solid var(--glass-border);
            border-radius: 50px;
            color: var(--text);
            font-size: 14px;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        .professionals-list {
            flex: 1;
            overflow-y: auto;
            padding: 0 20px 20px;
        }

        .property-container {
            margin-bottom: 15px;
        }

        .property-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 0;
            cursor: pointer;
            transition: var(--transition);
            border-bottom: 1px solid var(--glass-border);
        }

        .property-header:hover {
            color: var(--secondary);
        }

        .toggle-arrow {
            transition: var(--transition);
            font-size: 12px;
        }

        .property-header.expanded .toggle-arrow {
            transform: rotate(90deg);
        }

        .property-content {
            margin-left: 15px;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .profession-title {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 0;
            cursor: pointer;
            font-size: 1rem;
            color: var(--text-secondary);
            transition: var(--transition);
        }

        .profession-title:hover {
            color: var(--text);
        }

        .profession-title i {
            font-size: 14px;
            color: var(--accent);
        }

        .admins {
            margin-left: 15px;
        }

        .admin-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 8px;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            border: 1px solid transparent;
        }

        .admin-item:hover {
            background: rgba(123, 79, 255, 0.05);
            border-color: var(--glass-border);
            transform: translateX(5px);
        }

        .admin-item.active {
            background: rgba(123, 79, 255, 0.1);
            border-color: var(--accent);
        }

        .admin-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--darker);
            font-size: 18px;
            position: relative;
        }

        .status-dot {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid var(--light);
        }

        .status-dot.online {
            background: var(--success);
        }

        .status-dot.offline {
            background: var(--text-secondary);
        }

        .admin-info {
            flex: 1;
        }

        .admin-name {
            font-weight: 600;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-profession {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .admin-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
        }

        .admin-rating i {
            font-size: 12px;
            color: gold;
        }

        .unread-badge {
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
        }

        /* Chat Panel */
        .chat-panel {
            flex: 1;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius);
            backdrop-filter: blur(20px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .chat-header {
            padding: 25px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(10, 14, 23, 0.5);
        }

        .chat-partner {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .partner-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--darker);
            font-size: 18px;
        }

        .partner-info h3 {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .partner-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .partner-status .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .partner-status .online {
            background: var(--success);
        }

        .partner-status .offline {
            background: var(--text-secondary);
        }

        .chat-actions {
            display: flex;
            gap: 15px;
        }

        .chat-action-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            color: var(--text);
            cursor: pointer;
            transition: var(--transition);
        }

        .chat-action-btn:hover {
            background: rgba(123, 79, 255, 0.1);
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        .chat-messages {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .no-chat-selected {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-secondary);
            text-align: center;
        }

        .no-chat-selected i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--accent);
            opacity: 0.5;
        }

        .no-chat-selected h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .message {
            display: flex;
            max-width: 70%;
            animation: messageAppear 0.3s ease;
        }

        @keyframes messageAppear {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.sent {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message.received {
            align-self: flex-start;
        }

        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--darker);
            font-size: 14px;
            flex-shrink: 0;
        }

        .message-content {
            margin: 0 15px;
            padding: 15px 20px;
            border-radius: 20px;
            position: relative;
            max-width: 100%;
        }

        .message.sent .message-content {
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            color: var(--darker);
            border-bottom-right-radius: 5px;
        }

        .message.received .message-content {
            background: var(--light);
            border: 1px solid var(--glass-border);
            border-bottom-left-radius: 5px;
        }

        .message-text {
            word-wrap: break-word;
            line-height: 1.5;
        }

        .message-time {
            font-size: 0.75rem;
            margin-top: 5px;
            opacity: 0.7;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .message-status {
            font-size: 0.75rem;
        }

        .message.sent .message-status .sent {
            color: rgba(10, 14, 23, 0.7);
        }

        .message.sent .message-status .delivered {
            color: rgba(10, 14, 23, 0.7);
        }

        .message.sent .message-status .read {
            color: var(--accent);
        }

        .file-message {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: 12px;
            background: var(--light);
            border: 1px solid var(--glass-border);
            margin-top: 10px;
            transition: var(--transition);
            cursor: pointer;
        }

        .file-message:hover {
            background: rgba(123, 79, 255, 0.05);
            border-color: var(--accent);
        }

        .file-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--glass);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: var(--accent);
        }

        .file-info {
            flex: 1;
        }

        .file-name {
            font-weight: 500;
            margin-bottom: 3px;
        }

        .file-size {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .file-download {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text);
            transition: var(--transition);
        }

        .file-download:hover {
            background: var(--accent);
            color: var(--darker);
        }

        .image-message {
            border-radius: 15px;
            overflow: hidden;
            margin-top: 10px;
            max-width: 300px;
            border: 1px solid var(--glass-border);
        }

        .image-message img {
            width: 100%;
            height: auto;
            display: block;
        }

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
        }

        .image-message:hover .image-overlay {
            opacity: 1;
        }

        /* Message Input */
        .message-input-container {
            padding: 25px;
            border-top: 1px solid var(--glass-border);
            background: rgba(10, 14, 23, 0.5);
        }

        .input-container {
            display: flex;
            align-items: flex-end;
            gap: 15px;
            margin-bottom: 15px;
        }

        .file-upload-container {
            position: relative;
        }

        .file-upload-label {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            color: var(--text);
        }

        .file-upload-label:hover {
            background: rgba(123, 79, 255, 0.1);
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        .file-input {
            display: none;
        }

        .selected-file {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 5px;
            text-align: center;
        }

        .message-input-wrapper {
            flex: 1;
            position: relative;
        }

        .message-input {
            width: 100%;
            min-height: 50px;
            max-height: 120px;
            padding: 15px 60px 15px 20px;
            background: var(--dark);
            border: 1px solid var(--glass-border);
            border-radius: 25px;
            color: var(--text);
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            resize: none;
            transition: var(--transition);
        }

        .message-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .send-button {
            position: absolute;
            right: 10px;
            bottom: 10px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            border: none;
            color: var(--darker);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .send-button:hover {
            transform: translateY(-2px) scale(1.1);
            box-shadow: 0 5px 15px rgba(123, 79, 255, 0.4);
        }

        .send-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .action-btn {
            padding: 10px 20px;
            border-radius: 50px;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            color: var(--text);
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .action-btn:hover {
            background: rgba(255, 82, 82, 0.1);
            border-color: var(--danger);
            color: var(--danger);
        }

        .action-btn.primary {
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            color: var(--darker);
        }

        .action-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(123, 79, 255, 0.4);
        }

        /* Responsive Styles */
        @media (max-width: 1200px) {
            .chat-interface {
                height: calc(100vh - 150px);
            }
            
            .professionals-panel {
                width: 320px;
            }
        }

        @media (max-width: 992px) {
            .chat-interface {
                flex-direction: column;
                height: auto;
            }
            
            .professionals-panel {
                width: 100%;
                max-height: 300px;
            }
            
            .chat-panel {
                min-height: 500px;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 20px;
            }
            
            .message {
                max-width: 85%;
            }
            
            .chat-header {
                padding: 20px;
            }
            
            .chat-messages {
                padding: 20px;
            }
            
            .message-input-container {
                padding: 20px;
            }
        }

        @media (max-width: 576px) {
            .header-container {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .back-link {
                align-self: flex-start;
            }
            
            .message {
                max-width: 90%;
            }
            
            .partner-avatar {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
            
            .partner-info h3 {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Advanced Animated Background -->
    <div class="bg-animation">
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
            
            <a href="user_dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>
    </header>

    <!-- Main Chat Interface -->
    <main>
        <div class="container">
            <div class="chat-interface">
                <!-- Professionals Panel -->
                <div class="professionals-panel">
                    <div class="panel-header">
                        <h2>
                            <i class="fas fa-users"></i>
                            Professionals
                        </h2>
                        <div class="search-container">
                            <input type="text" class="search-input" placeholder="Search professionals...">
                            <i class="fas fa-search search-icon"></i>
                        </div>
                    </div>
                    <div class="professionals-list">
                        <?php
                        // Loop through the user's property names
                        foreach ($userPropertyNames as $property) {
                            // Check if there are admins for this property
                            $propertyAdmins = $allUserAdmins[$property];

                            if (!empty($propertyAdmins)) {
                                echo '<div class="property-container">';
                                echo '<div class="property-header" data-building-toggle>';
                                echo '<span class="toggle-arrow">▶</span>';
                                echo '<i class="fas fa-building"></i>';
                                echo '<span>' . $property . '</span>';
                                echo '</div>';
                                echo '<div class="property-content" style="display: none;">';
                                
                                // Group admins by profession for this property
                                $adminsByProfession = [];
                                foreach ($propertyAdmins as $admin) {
                                    $profession = $admin['profession'];
                                    if (!isset($adminsByProfession[$profession])) {
                                        $adminsByProfession[$profession] = [];
                                    }
                                    $adminsByProfession[$profession][] = $admin;
                                }

                                // List admins under each profession
                                foreach ($adminsByProfession as $profession => $admins) {
                                    echo '<div class="profession">';
                                    echo '<div class="profession-title" data-profession-toggle>';
                                    echo '<i class="fas fa-chevron-right"></i>';
                                    echo '<span>' . $profession . '</span>';
                                    echo '</div>';
                                    echo '<div class="admins" style="display: none;">';

                                    foreach ($admins as $admin) {
                                        $averageRating = getAverageRating($admin['unique_identifier'], $pdo);
                                        $dotColor = ($admin['status'] == 'online') ? 'online' : 'offline';
                                        $initials = substr($admin['first_name'], 0, 1) . substr($admin['last_name'], 0, 1);
                                        
                                        echo '<div class="admin-item" data-admin-id="' . $admin['id'] . '" data-unique-identifier="' . $admin['unique_identifier'] . '">';
                                        echo '<div class="admin-avatar">';
                                        echo $initials;
                                        echo '<div class="status-dot ' . $dotColor . '"></div>';
                                        echo '</div>';
                                        echo '<div class="admin-info">';
                                        echo '<div class="admin-name">';
                                        echo $admin['first_name'] . ' ' . $admin['last_name'];
                                        echo '</div>';
                                        echo '<div class="admin-profession">' . $admin['profession'] . '</div>';
                                        echo '<div class="admin-rating">';
                                        echo generateStarRating($averageRating);
                                        echo '</div>';
                                        echo '</div>';
                                        echo '<div class="unread-badge" id="unreadCount_' . $admin['unique_identifier'] . '" style="display: none;"></div>';
                                        echo '</div>';
                                    }

                                    echo '</div>';
                                    echo '</div>';
                                }

                                echo '</div>';
                                echo '</div>';
                            }
                        }

                        if (empty($allUserAdmins)) {
                            echo '<div class="no-chat-selected" style="padding: 40px 20px;">';
                            echo '<i class="fas fa-users"></i>';
                            echo '<h3>No Professionals Available</h3>';
                            echo '<p>There are no professionals to chat with at the moment.</p>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Chat Panel -->
                <div class="chat-panel">
                    <div class="chat-header">
                        <div class="chat-partner">
                            <div class="partner-avatar" id="partnerAvatar">?</div>
                            <div class="partner-info">
                                <h3 id="partnerName">Select a Professional</h3>
                                <div class="partner-status">
                                    <span class="dot" id="partnerStatusDot"></span>
                                    <span id="partnerStatusText">Select a professional to start chatting</span>
                                </div>
                            </div>
                        </div>
                        <div class="chat-actions">
                            <button class="chat-action-btn" title="Voice Call">
                                <i class="fas fa-phone"></i>
                            </button>
                            <button class="chat-action-btn" title="Video Call">
                                <i class="fas fa-video"></i>
                            </button>
                            <button class="chat-action-btn" title="More Options">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chat-messages" id="chatMessages">
                        <div class="no-chat-selected">
                            <i class="fas fa-comments"></i>
                            <h3>No Chat Selected</h3>
                            <p>Select a professional from the list to start a conversation</p>
                        </div>
                    </div>
                    <div class="message-input-container" style="display: none;" id="messageInputContainer">
                        <div class="input-container">
                            <div class="file-upload-container">
                                <label for="file" class="file-upload-label">
                                    <i class="fas fa-paperclip"></i>
                                </label>
                                <input type="file" name="file" id="file" class="file-input" accept=".jpg, .jpeg, .png, .gif, .pdf, .doc, .docx, .txt">
                                <p id="selected-file-name" class="selected-file">No file selected</p>
                            </div>
                            <div class="message-input-wrapper">
                                <textarea class="message-input" id="messageInput" placeholder="Type your message here..." rows="1"></textarea>
                                <button class="send-button" id="sendButton">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                        <div class="action-buttons">
                            <button class="action-btn" id="endChatButton">
                                <i class="fas fa-phone-slash"></i>
                                End Chat
                            </button>
                            <button class="action-btn primary" id="requestMeetingButton">
                                <i class="fas fa-calendar-plus"></i>
                                Request Meeting
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Chat interface logic
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize variables
            let currentAdminId = null;
            let currentAdminUniqueId = null;
            let chatRefreshInterval = null;
            
            // DOM Elements
            const propertyHeaders = document.querySelectorAll('[data-building-toggle]');
            const professionTitles = document.querySelectorAll('[data-profession-toggle]');
            const adminItems = document.querySelectorAll('.admin-item');
            const chatMessages = document.getElementById('chatMessages');
            const messageInputContainer = document.getElementById('messageInputContainer');
            const messageInput = document.getElementById('messageInput');
            const sendButton = document.getElementById('sendButton');
            const fileInput = document.getElementById('file');
            const selectedFileName = document.getElementById('selected-file-name');
            const endChatButton = document.getElementById('endChatButton');
            const partnerName = document.getElementById('partnerName');
            const partnerAvatar = document.getElementById('partnerAvatar');
            const partnerStatusDot = document.getElementById('partnerStatusDot');
            const partnerStatusText = document.getElementById('partnerStatusText');
            const searchInput = document.querySelector('.search-input');
            
            // Toggle property sections
            propertyHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    this.classList.toggle('expanded');
                    const propertyContent = this.nextElementSibling;
                    
                    if (propertyContent.style.display === 'block') {
                        propertyContent.style.display = 'none';
                    } else {
                        propertyContent.style.display = 'block';
                    }
                });
            });
            
            // Toggle profession sections
            professionTitles.forEach(title => {
                title.addEventListener('click', function() {
                    const arrow = this.querySelector('i');
                    const admins = this.nextElementSibling;
                    
                    arrow.classList.toggle('fa-chevron-right');
                    arrow.classList.toggle('fa-chevron-down');
                    
                    if (admins.style.display === 'block') {
                        admins.style.display = 'none';
                    } else {
                        admins.style.display = 'block';
                    }
                });
            });
            
            // Select admin to chat with
            adminItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Remove active class from all items
                    adminItems.forEach(i => i.classList.remove('active'));
                    
                    // Add active class to clicked item
                    this.classList.add('active');
                    
                    // Get admin details
                    const adminId = this.getAttribute('data-admin-id');
                    const uniqueId = this.getAttribute('data-unique-identifier');
                    const adminName = this.querySelector('.admin-name').textContent;
                    const adminProfession = this.querySelector('.admin-profession').textContent;
                    const statusDot = this.querySelector('.status-dot');
                    const isOnline = statusDot.classList.contains('online');
                    
                    // Update chat header
                    partnerName.textContent = adminName;
                    partnerAvatar.textContent = adminName.split(' ').map(n => n[0]).join('');
                    partnerStatusDot.className = 'dot ' + (isOnline ? 'online' : 'offline');
                    partnerStatusText.textContent = isOnline ? 'Online' : 'Offline';
                    
                    // Store current admin info
                    currentAdminId = adminId;
                    currentAdminUniqueId = uniqueId;
                    
                    // Show message input
                    messageInputContainer.style.display = 'block';
                    
                    // Update chat messages area
                    chatMessages.innerHTML = '';
                    chatMessages.appendChild(createNoMessagesElement());
                    
                    // Clear any existing interval
                    if (chatRefreshInterval) {
                        clearInterval(chatRefreshInterval);
                    }
                    
                    // Start fetching messages
                    chatRefreshInterval = setInterval(fetchMessages, 2000);
                    
                    // Clear unread badge
                    const badge = document.getElementById('unreadCount_' + uniqueId);
                    if (badge) {
                        badge.style.display = 'none';
                        badge.textContent = '';
                    }
                });
            });
            
            // Send message
            sendButton.addEventListener('click', sendMessage);
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
            
            // Auto-resize textarea
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
            
            // File input change
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    selectedFileName.textContent = truncateFileName(this.files[0].name, 20);
                } else {
                    selectedFileName.textContent = 'No file selected';
                }
            });
            
            // End chat
            endChatButton.addEventListener('click', endChat);
            
            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                adminItems.forEach(item => {
                    const adminName = item.querySelector('.admin-name').textContent.toLowerCase();
                    const adminProfession = item.querySelector('.admin-profession').textContent.toLowerCase();
                    
                    if (adminName.includes(searchTerm) || adminProfession.includes(searchTerm)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
            
            // Fetch unread message counts
            setInterval(fetchUnreadMessageCounts, 5000);
            fetchUnreadMessageCounts();
            
            // Functions
            function createNoMessagesElement() {
                const noMessages = document.createElement('div');
                noMessages.className = 'no-chat-selected';
                noMessages.innerHTML = `
                    <i class="fas fa-comment-slash"></i>
                    <h3>No Messages Yet</h3>
                    <p>Start the conversation by sending a message</p>
                `;
                return noMessages;
            }
            
            function sendMessage() {
                const message = messageInput.value.trim();
                const file = fileInput.files[0];
                
                if (message === '' && !file) {
                    return;
                }
                
                if (!currentAdminUniqueId) {
                    alert('Please select a professional to chat with');
                    return;
                }
                
                const formData = new FormData();
                formData.append("admin_unique_id", currentAdminUniqueId);
                formData.append("user_unique_id", <?php echo $userId; ?>);
                formData.append("message", message);
                
                if (file) {
                    formData.append("file", file);
                }
                
                // Simulate sending message (replace with actual API call)
                simulateMessageSend(formData);
                
                // Clear inputs
                messageInput.value = '';
                messageInput.style.height = 'auto';
                fileInput.value = '';
                selectedFileName.textContent = 'No file selected';
            }
            
            function simulateMessageSend(formData) {
                // In a real implementation, this would be an API call
                console.log('Sending message:', formData);
                
                // Simulate successful send
                const message = formData.get('message');
                const file = formData.get('file');
                
                if (message || file) {
                    // Add message to UI immediately for better UX
                    addMessageToUI({
                        sender_name: "You",
                        message: message,
                        file_path: file ? URL.createObjectURL(file) : null,
                        timestamp: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
                        status: "sent"
                    });
                }
                
                // In a real implementation, you would handle the response and update status
                setTimeout(() => {
                    updateLastMessageStatus("delivered");
                }, 1000);
                
                setTimeout(() => {
                    updateLastMessageStatus("read");
                }, 3000);
            }
            
            function addMessageToUI(messageData) {
                const messageElement = document.createElement('div');
                messageElement.className = `message ${messageData.sender_name === "You" ? "sent" : "received"}`;
                
                let messageContent = '';
                
                if (messageData.sender_name !== "You") {
                    messageContent += `
                        <div class="message-avatar">${getInitials(messageData.sender_name)}</div>
                    `;
                }
                
                messageContent += `
                    <div class="message-content">
                        <div class="message-text">${messageData.message || ''}</div>
                `;
                
                if (messageData.file_path) {
                    if (isImage(messageData.file_path)) {
                        messageContent += `
                            <div class="image-message">
                                <img src="${messageData.file_path}" alt="Shared image">
                                <div class="image-overlay">
                                    <div class="file-download">
                                        <i class="fas fa-download"></i>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        const fileName = getDisplayFilename(messageData.file_path);
                        messageContent += `
                            <div class="file-message">
                                <div class="file-icon">
                                    <i class="fas fa-file"></i>
                                </div>
                                <div class="file-info">
                                    <div class="file-name">${fileName}</div>
                                    <div class="file-size">${getFileSize(0)}</div>
                                </div>
                                <div class="file-download">
                                    <i class="fas fa-download"></i>
                                </div>
                            </div>
                        `;
                    }
                }
                
                messageContent += `
                        <div class="message-time">
                            ${messageData.timestamp}
                            ${messageData.sender_name === "You" ? 
                                `<span class="message-status">
                                    <i class="fas fa-check${messageData.status === "delivered" ? "-double" : ""}${messageData.status === "read" ? " blue-icon" : ""}"></i>
                                </span>` : ''}
                        </div>
                    </div>
                `;
                
                if (messageData.sender_name === "You") {
                    messageContent += `
                        <div class="message-avatar">${getInitials("You")}</div>
                    `;
                }
                
                messageElement.innerHTML = messageContent;
                
                // Remove "no messages" element if it exists
                const noMessages = chatMessages.querySelector('.no-chat-selected');
                if (noMessages) {
                    noMessages.remove();
                }
                
                chatMessages.appendChild(messageElement);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
            
            function updateLastMessageStatus(status) {
                const lastMessage = chatMessages.querySelector('.message.sent:last-child');
                if (lastMessage) {
                    const statusElement = lastMessage.querySelector('.message-status i');
                    if (statusElement) {
                        statusElement.className = `fas fa-check${status === "delivered" ? "-double" : ""}${status === "read" ? " blue-icon" : ""}`;
                    }
                }
            }
            
            function fetchMessages() {
                if (!currentAdminUniqueId) return;
                
                // In a real implementation, this would fetch messages from the server
                console.log('Fetching messages for admin:', currentAdminUniqueId);
                
                // Simulate receiving a message after some time
                if (Math.random() > 0.7) {
                    setTimeout(() => {
                        addMessageToUI({
                            sender_name: "Professional",
                            message: "Thanks for your message. How can I assist you today?",
                            timestamp: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
                            status: "delivered"
                        });
                    }, 2000);
                }
            }
            
            function fetchUnreadMessageCounts() {
                // In a real implementation, this would fetch unread counts from the server
                console.log('Fetching unread message counts');
                
                // Simulate some unread messages
                adminItems.forEach((item, index) => {
                    if (index < 2 && Math.random() > 0.5) {
                        const uniqueId = item.getAttribute('data-unique-identifier');
                        const badge = document.getElementById('unreadCount_' + uniqueId);
                        if (badge) {
                            badge.textContent = Math.floor(Math.random() * 5) + 1;
                            badge.style.display = 'flex';
                        }
                    }
                });
            }
            
            function endChat() {
                if (!currentAdminUniqueId) {
                    alert('No active chat to end');
                    return;
                }
                
                if (confirm('Are you sure you want to end this chat?')) {
                    // In a real implementation, this would send a request to the server
                    console.log('Ending chat with admin:', currentAdminUniqueId);
                    
                    // Reset UI
                    chatMessages.innerHTML = '';
                    chatMessages.appendChild(createNoMessagesElement());
                    messageInputContainer.style.display = 'none';
                    partnerName.textContent = 'Select a Professional';
                    partnerAvatar.textContent = '?';
                    partnerStatusDot.className = 'dot';
                    partnerStatusText.textContent = 'Select a professional to start chatting';
                    
                    // Clear interval
                    if (chatRefreshInterval) {
                        clearInterval(chatRefreshInterval);
                        chatRefreshInterval = null;
                    }
                    
                    // Remove active class
                    adminItems.forEach(item => item.classList.remove('active'));
                    
                    // Reset current admin
                    currentAdminId = null;
                    currentAdminUniqueId = null;
                    
                    alert('Chat ended successfully');
                }
            }
            
            function getInitials(name) {
                return name.split(' ').map(n => n[0]).join('').toUpperCase();
            }
            
            function truncateFileName(name, length) {
                if (name.length <= length) return name;
                
                const extensionIndex = name.lastIndexOf('.');
                if (extensionIndex === -1) {
                    return name.substring(0, length) + '...';
                }
                
                const extension = name.substring(extensionIndex);
                const nameWithoutExt = name.substring(0, extensionIndex);
                
                if (nameWithoutExt.length <= length - 3) {
                    return name;
                }
                
                return nameWithoutExt.substring(0, length - 3) + '...' + extension;
            }
            
            function getDisplayFilename(url) {
                // Extract filename from URL or blob URL
                let filename = url.split('/').pop() || 'file';
                return truncateFileName(filename, 20);
            }
            
            function isImage(url) {
                return /\.(jpg|jpeg|png|gif)$/i.test(url);
            }
            
            function getFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        });
    </script>
</body>
</html>