<?php
require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);
$student_id = $data['student_id'];
$type = strtolower($data['type']) === 'in' ? 'in' : 'out';
$time = date("H:i:s");
$date = date("Y-m-d");

$student = $conn->prepare("SELECT full_name, program FROM students WHERE student_id = ?");
$student->execute([$student_id]);
$row = $student->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit;
}

if ($type === 'in') {
    $insert = $conn->prepare("INSERT INTO attendance_logs (student_id, full_name, program, type, date, time_in) VALUES (?, ?, ?, ?, ?, ?)");
    $success = $insert->execute([$student_id, $row['full_name'], $row['program'], 'in', $date, $time]);
} else {
    $update = $conn->prepare("UPDATE attendance_logs SET time_out = ? WHERE student_id = ? AND date = ? AND type = 'in' AND time_out IS NULL");
    $success = $update->execute([$time, $student_id, $date]);
}

echo json_encode(['success' => $success]);
if (!$success) {
    echo json_encode(['success' => false, 'message' => 'Failed to mark attendance']);
} else {
    echo json_encode(['success' => true, 'message' => 'Attendance marked successfully']);
}