<?php
/**
 * API: Get Latest Data
 * Endpoint untuk mengambil data sensor terbaru
 * Method: GET
 */

require_once '../config/database.php';

// Query data terbaru
$sql = "SELECT * FROM monitoring ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    
    // Format response
    $response = [
        'status' => 'success',
        'data' => [
            'id' => (int) $data['id'],
            'motion' => (int) $data['motion'],
            'distance' => (float) $data['distance'],
            'alert_level' => (int) $data['alert_level'],
            'led' => $data['led'],
            'buzzer' => (int) $data['buzzer'],
            'mode' => $data['mode'],
            'timestamp' => $data['timestamp'],
            
            // Tambahan informasi
            'alert_text' => get_alert_text($data['alert_level']),
            'motion_text' => $data['motion'] == 1 ? 'Detected' : 'Clear',
            'time_ago' => time_ago($data['timestamp'])
        ]
    ];
    
    json_response($response);
} else {
    json_response([
        'status' => 'error',
        'message' => 'No data available',
        'data' => null
    ], 404);
}

$conn->close();

/**
 * Helper: Convert alert level to text
 */
function get_alert_text($level) {
    switch ($level) {
        case 0: return 'Aman';
        case 1: return 'Waspada';
        case 2: return 'Bahaya';
        default: return 'Unknown';
    }
}

/**
 * Helper: Time ago format
 */
function time_ago($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return $diff . ' detik lalu';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' menit lalu';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' jam lalu';
    } else {
        return floor($diff / 86400) . ' hari lalu';
    }
}
?>