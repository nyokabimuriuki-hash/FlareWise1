<?php
session_start();

// Unset all of the session variables.
session_unset();

// Finally, destroy the session.
session_destroy();

echo json_encode(['status' => 'success', 'message' => 'Session destroyed.']);
?>