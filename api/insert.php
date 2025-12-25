<?php
/**
 * API: Insert Data Sensor
 * Endpoint untuk ESP32 mengirim data sensor
 * Method: POST
 */

require_once '../config/database.php';

// Validasi Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

// Validasi Data
$required_fields = ['motion', 'distance', 'alert_level', 'led', 'buzzer', 'mode'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field])) {
        json_response([
            'status' => 'error', 
            'message' => "Missing field: $field"
        ], 400);
    }
}

// Ambil dan bersihkan data
$motion = (int) clean_input($_POST['motion']);
$distance = (float) clean_input($_POST['distance']);
$alert_level = (int) clean_input($_POST['alert_level']);
$led = clean_input($_POST['led']);
$buzzer = (int) clean_input($_POST['buzzer']);
$mode = clean_input($_POST['mode']);

// Validasi nilai
if ($motion < 0 || $motion > 1) {
    json_response(['status' => 'error', 'message' => 'Invalid motion value'], 400);
}

if ($distance < 0 || $distance > 400) {
    json_response(['status' => 'error', 'message' => 'Invalid distance value'], 400);
}

if ($alert_level < 0 || $alert_level > 2) {
    json_response(['status' => 'error', 'message' => 'Invalid alert_level value'], 400);
}

if (!in_array($mode, ['AUTO', 'MANUAL'])) {
    json_response(['status' => 'error', 'message' => 'Invalid mode value'], 400);
}

// Insert ke database
$sql = "INSERT INTO monitoring (motion, distance, alert_level, led, buzzer, mode) 
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("idisis", $motion, $distance, $alert_level, $led, $buzzer, $mode);

if ($stmt->execute()) {
    // Berhasil
    json_response([
        'status' => 'success',
        'message' => 'Data inserted successfully',
        'id' => $stmt->insert_id,
        'timestamp' => date('Y-m-d H:i:s')
    ], 201);
} else {
    // Gagal
    json_response([
        'status' => 'error',
        'message' => 'Failed to insert data: ' . $conn->error
    ], 500);
}

$stmt->close();
$conn->close();
?>