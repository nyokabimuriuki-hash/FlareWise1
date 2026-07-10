<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$idToken = isset($body['idToken']) ? trim($body['idToken']) : '';
if (!$idToken) {
    http_response_code(400);
    echo json_encode(['message' => 'Missing idToken']);
    exit;
}

$projectId = 'flarewise-4722c';
$payload = verifyFirebaseToken($idToken, $projectId);
if (!$payload) {
    http_response_code(401);
    echo json_encode(['message' => 'Invalid Firebase token']);
    exit;
}

$uid = $payload['sub'];
$email = isset($payload['email']) ? $payload['email'] : '';
$fullname = isset($payload['name']) ? $payload['name'] : $email;

include('database.php');

// Add firebase_uid column if it doesn't exist.
$columnCheck = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'firebase_uid'");
if (!$columnCheck || mysqli_num_rows($columnCheck) === 0) {
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN firebase_uid VARCHAR(128) NULL UNIQUE");
}

$escapedUid = mysqli_real_escape_string($conn, $uid);
$escapedEmail = mysqli_real_escape_string($conn, $email);
$escapedName = mysqli_real_escape_string($conn, $fullname);

$result = mysqli_query($conn, "SELECT * FROM users WHERE firebase_uid='$escapedUid' OR email='$escapedEmail' LIMIT 1");
$user = mysqli_fetch_assoc($result);

if ($user) {
    $userId = $user['id'];
    if (empty($user['firebase_uid']) || $user['firebase_uid'] !== $uid) {
        mysqli_query($conn, "UPDATE users SET firebase_uid='$escapedUid' WHERE id='{$user['id']}'");
    }
    if (empty($user['fullname']) || $user['fullname'] === '') {
        mysqli_query($conn, "UPDATE users SET fullname='$escapedName' WHERE id='{$user['id']}'");
    }
} else {
    mysqli_query($conn, "INSERT INTO users (fullname, email, password, firebase_uid) VALUES ('$escapedName', '$escapedEmail', '', '$escapedUid')");
    $userId = mysqli_insert_id($conn);
}

$_SESSION['user_id'] = $userId;
$_SESSION['fullname'] = $fullname;
$_SESSION['email'] = $email;
$_SESSION['firebase_uid'] = $uid;

echo json_encode(['success' => true]);
exit;

function base64UrlDecode($input) {
    $remainder = strlen($input) % 4;
    if ($remainder) {
        $padlen = 4 - $remainder;
        $input .= str_repeat('=', $padlen);
    }
    return base64_decode(strtr($input, '-_', '+/'));
}

function verifyFirebaseToken($idToken, $projectId) {
    $parts = explode('.', $idToken);
    if (count($parts) !== 3) {
        return false;
    }
    list($headerB64, $payloadB64, $signatureB64) = $parts;

    $headerJson = base64UrlDecode($headerB64);
    $payloadJson = base64UrlDecode($payloadB64);
    $signature = base64UrlDecode($signatureB64);

    if (!$headerJson || !$payloadJson || !$signature) {
        return false;
    }

    $header = json_decode($headerJson, true);
    $payload = json_decode($payloadJson, true);
    if (!$header || !$payload) {
        return false;
    }

    if (!isset($header['kid']) || !isset($payload['aud']) || !isset($payload['iss'])) {
        return false;
    }
    if ($payload['aud'] !== $projectId) {
        return false;
    }
    if ($payload['iss'] !== "https://securetoken.google.com/{$projectId}") {
        return false;
    }
    if (!isset($payload['exp']) || $payload['exp'] < time()) {
        return false;
    }

    $certs = fetchFirebaseCerts();
    if (!$certs || !isset($certs[$header['kid']])) {
        return false;
    }

    $cert = $certs[$header['kid']];
    $publicKey = openssl_get_publickey($cert);
    if (!$publicKey) {
        return false;
    }

    $verified = openssl_verify($headerB64 . '.' . $payloadB64, $signature, $publicKey, OPENSSL_ALGO_SHA256);
    openssl_free_key($publicKey);

    return $verified === 1 ? $payload : false;
}

function fetchFirebaseCerts() {
    $url = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';
    $certsJson = file_get_contents($url);
    if (!$certsJson) {
        return false;
    }
    $certs = json_decode($certsJson, true);
    return $certs ?: false;
}
