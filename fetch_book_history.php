<?php
header('Content-Type: application/json');

// DB config - adjust as needed
$host = 'localhost';
$dbname = 'library_system';
$user = 'postgres';
$password = 'librarySystem';

if (!isset($_GET['book_id']) || empty($_GET['book_id'])) {
    echo json_encode([]);
    exit;
}
$book_id = $_GET['book_id'];

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare("SELECT student_id, book_id, book_title, status, borrow_date, due_date, return_date, created_at FROM borrow_history WHERE book_id = :book_id ORDER BY created_at DESC");
    $stmt->execute(['book_id' => $book_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
} catch (PDOException $e) {
    echo json_encode([]);
}