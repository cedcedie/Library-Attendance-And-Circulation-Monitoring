<?php
require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);
$student_id = $data['student_id'];
$title = $data['title'];
$message = $data['message'];

$insert = $conn->prepare("INSERT INTO notifications (student_id, title, message) VALUES (?, ?, ?)");
$success = $insert->execute([$student_id, $title, $message]);

echo json_encode(['success' => $success]);
if (!$success) {
    echo json_encode(['success' => false, 'message' => 'Failed to send notification']);
} else {
    echo json_encode(['success' => true, 'message' => 'Notification sent successfully']);
}