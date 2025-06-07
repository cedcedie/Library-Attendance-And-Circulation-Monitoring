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
        "SELECT bh.*, b.title AS book_title
         FROM borrow_history bh
         LEFT JOIN books b ON bh.book_id = b.id
         WHERE bh.student_id = :student_id
         ORDER BY bh.created_at DESC"
    );
    $stmt->execute(['student_id' => $student_id]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'borrow_history' => $history]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Query failed']);
    exit;
}