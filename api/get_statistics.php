<?php
/**
 * API: Get Statistics
 * Endpoint untuk mengambil statistik sistem
 * Method: GET
 */

require_once '../config/database.php';

// Statistik Hari Ini
$today = date('Y-m-d');
$sql_today = "SELECT 
    COUNT(*) as total_readings,
    SUM(CASE WHEN alert_level = 2 THEN 1 ELSE 0 END) as danger_alerts,
    SUM(CASE WHEN alert_level = 1 THEN 1 ELSE 0 END) as warning_alerts,
    SUM(CASE WHEN motion = 1 THEN 1 ELSE 0 END) as motion_detected,
    AVG(distance) as avg_distance,
    MIN(distance) as min_distance
FROM monitoring 
WHERE DATE(timestamp) = ?";

$stmt = $conn->prepare($sql_today);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
$today_stats = $result->fetch_assoc();

// Statistik 7 Hari Terakhir
$sql_week = "SELECT 
    DATE(timestamp) as date,
    COUNT(*) as readings,
    SUM(CASE WHEN alert_level = 2 THEN 1 ELSE 0 END) as dangers
FROM monitoring 
WHERE timestamp >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY DATE(timestamp)
ORDER BY date ASC";

$result_week = $conn->query($sql_week);
$week_stats = [];
while ($row = $result_week->fetch_assoc()) {
    $week_stats[] = [
        'date' => $row['date'],
        'readings' => (int) $row['readings'],
        'dangers' => (int) $row['dangers']
    ];
}

// Pest Log (10 deteksi terakhir)
$sql_log = "SELECT id, detection_time, distance, alert_level, duration 
            FROM pest_log 
            ORDER BY id DESC 
            LIMIT 10";
$result_log = $conn->query($sql_log);
$pest_log = [];
while ($row = $result_log->fetch_assoc()) {
    $pest_log[] = [
        'id' => (int) $row['id'],
        'time' => $row['detection_time'],
        'distance' => (float) $row['distance'],
        'alert' => (int) $row['alert_level'],
        'duration' => (int) $row['duration']
    ];
}

// System Status
$sql_settings = "SELECT * FROM system_settings WHERE id=1";
$result_settings = $conn->query($sql_settings);
$settings = $result_settings->fetch_assoc();

// Format Response
json_response([
    'status' => 'success',
    'today' => [
        'total_readings' => (int) $today_stats['total_readings'],
        'danger_alerts' => (int) $today_stats['danger_alerts'],
        'warning_alerts' => (int) $today_stats['warning_alerts'],
        'motion_detected' => (int) $today_stats['motion_detected'],
        'avg_distance' => round($today_stats['avg_distance'], 1),
        'min_distance' => round($today_stats['min_distance'], 1)
    ],
    'week' => $week_stats,
    'pest_log' => $pest_log,
    'settings' => [
        'mode' => $settings['mode'],
        'buzzer' => $settings['buzzer'],
        'sensitivity' => (int) $settings['sensitivity'],
        'notification_enabled' => (bool) $settings['notification_enabled']
    ]
]);

$stmt->close();
$conn->close();
?>