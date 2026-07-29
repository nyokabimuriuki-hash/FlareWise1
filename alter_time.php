<?php
require_once __DIR__ . '/config/database.php';
$sql = "ALTER TABLE medications MODIFY reminder_time DATETIME";
if ($conn->query($sql) === TRUE) {
    echo "Table medications altered successfully\n";
} else {
    echo "Error altering table: " . $conn->error . "\n";
}
$conn->close();
?>
