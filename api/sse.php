<?php
/**
 * API: Server-Sent Events (SSE)
 * Endpoint untuk real-time data streaming
 * Method: GET
 */

// Set headers untuk SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');

// Disable output buffering
if (ob_get_level()) ob_end_clean();

require_once '../config/database.php';

// Fungsi untuk mengirim event
function send_sse_data($data) {
    echo "data: " . json_encode($data) . "\n\n";
    if (ob_get_level()) ob_flush();
    flush();
}

// Kirim ping setiap 30 detik untuk keep-alive
$last_ping = time();

// ID data terakhir yang dikirim (untuk deteksi perubahan)
$last_id = 0;

// Loop infinite untuk streaming
while (true) {
    // Cek koneksi masih hidup
    if (connection_aborted()) {
        break;
    }
    
    // Query data terbaru
    $sql = "SELECT * FROM monitoring ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $current_id = (int) $data['id'];
        
        // Kirim data hanya jika ada perubahan
        if ($current_id > $last_id) {
            $last_id = $current_id;
            
            $response = [
                'type' => 'data',
                'id' => $current_id,
                'motion' => (int) $data['motion'],
                'distance' => (float) $data['distance'],
                'alert_level' => (int) $data['alert_level'],
                'led' => $data['led'],
                'buzzer' => (int) $data['buzzer'],
                'mode' => $data['mode'],
                'timestamp' => $data['timestamp'],
                'alert_text' => get_alert_text($data['alert_level'])
            ];
            
            send_sse_data($response);
        }
    }
    
    // Kirim ping untuk keep-alive setiap 30 detik
    if (time() - $last_ping > 30) {
        send_sse_data([
            'type' => 'ping',
            'time' => date('Y-m-d H:i:s')
        ]);
        $last_ping = time();
    }
    
    // Sleep 1 detik sebelum check lagi
    sleep(1);
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
?>