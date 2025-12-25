<?php
/**
 * Database Configuration
 * Pest Detector IoT System
 */

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Ganti jika berbeda
define('DB_PASS', '');               // Ganti dengan password MySQL Anda
define('DB_NAME', 'pest_detector');

// Create Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check Connection
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

// Set Charset
$conn->set_charset("utf8mb4");

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error Reporting (ubah ke 0 untuk production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS Headers (agar bisa diakses dari berbagai domain)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

/**
 * Helper Function: Clean Input
 */
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

/**
 * Helper Function: JSON Response
 */
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}
?>