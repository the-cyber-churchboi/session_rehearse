<?php
session_name("user_session");
session_start();
require_once "config.php";

// Check if the required parameters are present
if (!isset($_POST["user_unique_id"]) || !isset($_POST["admin_unique_id"])) {
    header("Content-Type: application/json");
    echo json_encode(["success" => false, "error" => "Invalid request"]);
    exit;
}

// Function to decrypt a message
function decryptMessage($encryptedMessage, $encryptionKey) {
    $method = 'aes-256-cbc';
    $ivSize = openssl_cipher_iv_length($method);
    $ciphertext = base64_decode($encryptedMessage);
    $iv = substr($ciphertext, 0, $ivSize);
    $ciphertext = substr($ciphertext, $ivSize);
    return openssl_decrypt($ciphertext, $method, $encryptionKey, 0, $iv);
}

try {
    
// Retrieve the unique identifier from the POST data
$uniqueIdentifier = $_POST["admin_unique_id"];

// Retrieve the user's ID from the session
$userId = $_POST["user_unique_id"];

// Update messages with status "sent" to "delivered"
$updateSql = "UPDATE admin_messages SET message_status = 'read' WHERE receiver_id = :userId";
$updateStmt = $pdo->prepare($updateSql);
$updateStmt->bindParam(":userId", $userId, PDO::PARAM_INT);
$updateStmt->execute();

$sqlAdmin = "SELECT admin_messages.*, admin_registration.full_name AS sender_name, admin_registration.profession AS admin_profession,admin_messages.file_path
             FROM admin_messages
             INNER JOIN admin_registration ON admin_messages.sender_id = admin_registration.unique_identifier
             WHERE admin_messages.sender_id = :uniqueIdentifier AND admin_messages.receiver_id = :userId
             ORDER BY admin_messages.timestamp ASC";

$stmtStaff = $pdo->prepare($sqlAdmin);
$stmtStaff->bindParam(":uniqueIdentifier", $uniqueIdentifier, PDO::PARAM_INT);
$stmtStaff->bindParam(":userId", $userId, PDO::PARAM_INT);
$stmtStaff->execute();
$staffMessages = [];

$encryptionKey = "061da93cf44eac5f4f00f39c5933dfb0fbee4c08a93785a8b951a23719b7c0bf";

while ($row = $stmtStaff->fetch(PDO::FETCH_ASSOC)) {
    $message = [
        "sender_name" => $row["sender_name"],
        "message" => decryptMessage($row["message"], $encryptionKey),
        "timestamp" => $row["timestamp"],
        "file_path"=> $row["file_path"],
        "admin_profession" => $row["admin_profession"],
    ];
    $staffMessages[] = $message;
}

$sqlUser = "SELECT admin_messages.*, admin_registration.full_name AS sender_name, admin_messages.file_path
            FROM admin_messages
            INNER JOIN admin_registration ON admin_messages.sender_id = admin_registration.unique_identifier
            WHERE admin_messages.sender_id = :userId AND admin_messages.receiver_id = :uniqueIdentifier
            ORDER BY admin_messages.timestamp ASC";

$stmtStudent = $pdo->prepare($sqlUser); // Use $sqlUser here
$stmtStudent->bindParam(":uniqueIdentifier", $uniqueIdentifier, PDO::PARAM_INT);
$stmtStudent->bindParam(":userId", $userId, PDO::PARAM_INT);
$stmtStudent->execute();

$studentMessages = [];

while ($row = $stmtStudent->fetch(PDO::FETCH_ASSOC)) {
    $message = [
        "sender_name" => "You",
        "message" => decryptMessage($row["message"], $encryptionKey),
        "timestamp" => $row["timestamp"],
        "file_path"=> $row["file_path"],
        "status" => $row["message_status"]
    ];
    $studentMessages[] = $message;
}


// Combine staff and student messages into a single array
$messages = array_merge($staffMessages, $studentMessages);

// Sort the messages by timestamp
usort($messages, function ($a, $b) {
    return strtotime($a['timestamp']) - strtotime($b['timestamp']);
});

$response = [
    "success" => true,
    "admin_id" => $userId,
    "user_id" => $uniqueIdentifier,
    "messages" => $messages // Your fetched messages array
];
} catch (PDOException $e) {
$response = [
    "success" => false,
    "error" => "Database error: " . $e->getMessage()
];
}

header("Content-Type: application/json");
echo json_encode($response);

?>
