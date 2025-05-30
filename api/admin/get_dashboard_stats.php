<?php
require_once __DIR__ . '/../../config/database.php';

$students = $conn->query("SELECT COUNT(*) FROM students")->fetchColumn();
$books = $conn->query("SELECT COUNT(*) FROM books")->fetchColumn();
$borrow_pending = $conn->query("SELECT COUNT(*) FROM borrow_requests WHERE status = 'pending'")->fetchColumn();
$attendance_today = $conn->prepare("SELECT COUNT(*) FROM attendance_logs WHERE date = CURRENT_DATE");
$attendance_today->execute();
$attendance_count = $attendance_today->fetchColumn();

echo json_encode([
    'students' => $students,
    'books' => $books,
    'pending_requests' => $borrow_pending,
    'today_attendance' => $attendance_count
]);