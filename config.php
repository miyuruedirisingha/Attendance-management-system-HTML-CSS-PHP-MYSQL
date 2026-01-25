<?php
// Database configuration - Auto-detect Docker or Local environment
// Default to 'localhost' for non-Docker environments; Compose sets DB_HOST explicitly to 'db'.
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'attendance_system');

// Optional: DB port (defaults to 3306 if not provided)
$DB_PORT = getenv('DB_PORT') ?: 3306;

// First, wait for DNS resolution if host is not immediately resolvable (handles transient name resolution issues)
$max_host_resolve_retries = 10; // up to ~20 seconds
$host_resolved = false;
for ($i = 0; $i < $max_host_resolve_retries; $i++) {
    $resolved_ip = gethostbyname(DB_HOST);
    if ($resolved_ip !== DB_HOST) {
        $host_resolved = true;
        break;
    }
    sleep(2);
}

// Create database connection with retries
$max_retries = 12; // up to ~24 seconds
$retry_count = 0;
$conn = false;

while (!$conn && $retry_count < $max_retries) {
    $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, $DB_PORT);
    if (!$conn) {
        $retry_count++;
        if ($retry_count < $max_retries) {
            sleep(2); // Wait 2 seconds before retry
        }
    }
}

// Check connection
if (!$conn) {
    $dns_status = $host_resolved ? 'resolved' : 'unresolved';
    die("Connection failed after {$max_retries} attempts: " . mysqli_connect_error() . "<br>Host: " . DB_HOST . " (DNS: {$dns_status})");
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
