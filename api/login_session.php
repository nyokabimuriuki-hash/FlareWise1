<?php
// =============================================================================
// Firebase-to-PHP session bridge
// Creates or links the local user record, then starts the PHP session.
// =============================================================================

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$data = json_decode(file_get_contents('php://input'));
$firebaseUid = trim($data->firebase_uid ?? '');
$email = trim($data->email ?? '');
$fullname = trim($data->fullname ?? '');

if ($firebaseUid === '' || $email === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Firebase user details are required.']);
    exit;
}

// First, look for an account already linked to this Firebase user.
$stmt = $conn->prepare('SELECT id, fullname, email, role FROM users WHERE firebase_uid = ? LIMIT 1');
$stmt->bind_param('s', $firebaseUid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    // Link a legacy local account with the same email, if one exists.
    $stmt = $conn->prepare('SELECT id, fullname, email, role FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user) {
        $stmt = $conn->prepare('UPDATE users SET firebase_uid = ? WHERE id = ?');
        $stmt->bind_param('si', $firebaseUid, $user['id']);
        $stmt->execute();
        $stmt->close();
    } else {
        $displayName = $fullname !== '' ? $fullname : $email;
        $stmt = $conn->prepare('INSERT INTO users (firebase_uid, fullname, email) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $firebaseUid, $displayName, $email);

        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create your local account.']);
            exit;
        }

        $user = ['id' => $stmt->insert_id, 'fullname' => $displayName, 'email' => $email];
        $stmt->close();
    }
}

$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['user_fullname'] = $user['fullname'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'] ?? 'user';

echo json_encode(['status' => 'success', 'message' => 'Session created successfully.']);
