<?php
// Database configuration - Auto-detect Docker or Local environment
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'attendance_system');

// Create database connection
$max_retries = 5;
$retry_count = 0;
$conn = false;

while (!$conn && $retry_count < $max_retries) {
    $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        $retry_count++;
        if ($retry_count < $max_retries) {
            sleep(2); // Wait 2 seconds before retry
        }
    }
}

// Check connection
if (!$conn) {
    die("Connection failed after {$max_retries} attempts: " . mysqli_connect_error() . "<br>Host: " . DB_HOST);
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8");

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

// Function to redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Function to sanitize input
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}
?>
