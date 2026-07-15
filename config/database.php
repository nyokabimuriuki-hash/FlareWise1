<?php
// -----------------------------------------------------------------------------
// Database connection
// -----------------------------------------------------------------------------
// This is the single database configuration used by all PHP pages and API files.

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'flarewise';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die('Database connection failed.');
}
