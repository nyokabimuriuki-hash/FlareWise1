<?php
// Database configuration
$servername = "localhost"; // Or your server IP
$username = "root";        // Default XAMPP username
$password = "";            // Default XAMPP password
$dbname = "flarewise";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>