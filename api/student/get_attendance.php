<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');
$student_id = $_GET['student_id'] ?? '';

$sql = "SELECT * FROM attendance_logs WHERE student_id = $1 ORDER BY date DESC, time_in DESC";
$result = pg_query_params($conn, $sql, [$student_id]);

$logs = pg_fetch_all($result);
echo json_encode(['status' => 'success', 'attendance_logs' => $logs]);
?>
