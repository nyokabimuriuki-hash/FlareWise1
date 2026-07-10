<?php
session_start();
require_once 'db_connect.php';

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json);

if (!isset($data->firebase_uid)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Firebase UID is required.']);
    exit;
}

$firebase_uid = $data->firebase_uid;

// Find the user in the database
$stmt = $conn->prepare("SELECT id, fullname, email FROM users WHERE firebase_uid = ?");
$stmt->bind_param("s", $firebase_uid);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    // User found, create the PHP session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_fullname'] = $user['fullname'];
    $_SESSION['user_email'] = $user['email'];

    echo json_encode(['status' => 'success', 'message' => 'Session created successfully.']);
} else {
    // This case might happen if a user exists in Firebase but not in your local DB
    // (e.g., if the DB was cleared).
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'User not found in the local database.']);
}

$stmt->close();
$conn->close();
?>