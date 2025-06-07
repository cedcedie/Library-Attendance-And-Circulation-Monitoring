<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

$host = "localhost";
$port = "5432";
$dbname = "library_system";
$user = "postgres";
$password = "librarySystem";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

$student_id = $_GET['student_id'] ?? '';

if (empty($student_id) || !is_numeric($student_id)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid student ID']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT bh.book_id, b.title AS book_title, bh.borrow_date, bh.due_date, bh.return_date,
                (CURRENT_DATE - bh.due_date) AS days_overdue,
                GREATEST(0, (CURRENT_DATE - bh.due_date)) * 5 AS fine_amount
         FROM borrow_history bh
         LEFT JOIN books b ON bh.book_id = b.id
         WHERE bh.student_id = :student_id
           AND (bh.return_date IS NULL OR bh.return_date > bh.due_date)
           AND bh.due_date < CURRENT_DATE
         ORDER BY bh.due_date ASC"
    );
    $stmt->execute(['student_id' => $student_id]);
    $dues = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'dues' => $dues]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Query failed']);
    exit;
}