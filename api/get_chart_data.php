<?php
/**
 * API: Get Chart Data
 * Endpoint untuk mengambil data grafik real-time
 * Method: GET
 * Parameter: limit (optional, default 20)
 */

require_once '../config/database.php';

// Ambil parameter limit
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
$limit = min($limit, 100); // Maksimal 100 data

// Query data untuk grafik
$sql = "SELECT 
            id,
            motion,
            distance,
            alert_level,
            buzzer,
            DATE_FORMAT(timestamp, '%H:%i:%s') as time_label,
            UNIX_TIMESTAMP(timestamp) as unix_time
        FROM monitoring 
        ORDER BY id DESC 
        LIMIT ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $limit);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => (int) $row['id'],
        'motion' => (int) $row['motion'],
        'distance' => (float) $row['distance'],
        'alert_level' => (int) $row['alert_level'],
        'buzzer' => (int) $row['buzzer'],
        'time' => $row['time_label'],
        'unix_time' => (int) $row['unix_time']
    ];
}

// Reverse array agar urutan chronological (terlama ke terbaru)
$data = array_reverse($data);

// Format untuk Chart.js
$chartData = [
    'labels' => [],
    'distance' => [],
    'alert' => [],
    'motion' => [],
    'buzzer' => []
];

foreach ($data as $item) {
    $chartData['labels'][] = $item['time'];
    $chartData['distance'][] = $item['distance'];
    $chartData['alert'][] = $item['alert_level'];
    $chartData['motion'][] = $item['motion'];
    $chartData['buzzer'][] = $item['buzzer'];
}

json_response([
    'status' => 'success',
    'count' => count($data),
    'data' => $data,
    'chartData' => $chartData
]);

$stmt->close();
$conn->close();
?>