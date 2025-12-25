<?php
/**
 * API: Update Control Settings
 * Endpoint untuk mengupdate pengaturan sistem dari website
 * Method: POST
 */

require_once '../config/database.php';

// Validasi Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

// Ambil data dari POST atau JSON body
$input = $_POST;
if (empty($input)) {
    $input = json_decode(file_get_contents('php://input'), true);
}

// Default values
$mode = isset($input['mode']) ? clean_input($input['mode']) : 'AUTO';
$buzzer = isset($input['buzzer']) ? clean_input($input['buzzer']) : 'OFF';
$sensitivity = isset($input['sensitivity']) ? (int) $input['sensitivity'] : 50;
$notification = isset($input['notification']) ? (bool) $input['notification'] : true;

// Validasi
if (!in_array($mode, ['AUTO', 'MANUAL'])) {
    json_response(['status' => 'error', 'message' => 'Invalid mode'], 400);
}

if (!in_array($buzzer, ['ON', 'OFF'])) {
    json_response(['status' => 'error', 'message' => 'Invalid buzzer value'], 400);
}

if ($sensitivity < 10 || $sensitivity > 200) {
    json_response(['status' => 'error', 'message' => 'Sensitivity must be between 10-200 cm'], 400);
}

// Update database
$sql = "UPDATE system_settings SET mode=?, buzzer=?, sensitivity=?, notification_enabled=? WHERE id=1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $mode, $buzzer, $sensitivity, $notification);

if ($stmt->execute()) {
    // Update control.json untuk ESP32
    $control_data = [
        'mode' => $mode,
        'buzzer' => $buzzer,
        'sensitivity' => $sensitivity,
        'notification' => $notification,
        'last_update' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents('control.json', json_encode($control_data, JSON_PRETTY_PRINT));
    
    json_response([
        'status' => 'success',
        'message' => 'Control settings updated',
        'data' => $control_data
    ]);
} else {
    json_response([
        'status' => 'error',
        'message' => 'Failed to update settings: ' . $conn->error
    ], 500);
}

$stmt->close();
$conn->close();
?>