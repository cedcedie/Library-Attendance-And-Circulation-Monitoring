<?php
require_once(__DIR__ . "/../config/database.php");

header('Content-Type: application/json');
$student_id = $_GET['student_id'] ?? '';

$sql = "SELECT * FROM notifications WHERE student_id = $1 ORDER BY created_at DESC";
$result = pg_query_params($conn, $sql, [$student_id]);

$notifications = pg_fetch_all($result);
echo json_encode(['status' => 'success', 'notifications' => $notifications]);
?>
