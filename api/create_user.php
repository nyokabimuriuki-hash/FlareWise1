<?php
session_start();

// Include your database connection file
// Make sure to create this file with your database credentials
require_once __DIR__ . '/../config/database.php';

// Get the raw POST data
$json = file_get_contents('php://input');
// Decode the JSON data
$data = json_decode($json);

// Basic validation
if (!isset($data->firebase_uid) || !isset($data->email) || !isset($data->fullname)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit;
}

$firebase_uid = $data->firebase_uid;
$email = $data->email;
$fullname = $data->fullname;

// Prepare and execute the SQL statement to insert the new user
$stmt = $conn->prepare("INSERT INTO users (firebase_uid, fullname, email) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $firebase_uid, $fullname, $email);

if ($stmt->execute()) {
    // On successful insertion, get the new user's ID
    $user_id = $stmt->insert_id;

    // Create a PHP session for the user
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_fullname'] = $fullname;
    $_SESSION['user_email'] = $email;

    // Send a success response
    echo json_encode(['status' => 'success', 'message' => 'User created and session started.']);
} else {
    // Handle potential errors, like a duplicate email
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
