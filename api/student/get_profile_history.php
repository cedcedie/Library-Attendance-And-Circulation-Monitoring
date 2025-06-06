<?php
require_once(__DIR__ . "/../config/database.php");

header('Content-Type: application/json');
$student_id = $_GET['student_id'] ?? '';

$sql = "SELECT * FROM students WHERE student_id = $1";
$result = pg_query_params($conn, $sql, [$student_id]);

if (pg_num_rows($result) == 1) {
    echo json_encode(['status' => 'success', 'profile' => pg_fetch_assoc($result)]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Student not found']);
}
?>