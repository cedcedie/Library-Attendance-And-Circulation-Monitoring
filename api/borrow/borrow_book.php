<?php
require '../config.php';

$data = json_decode(file_get_contents("php://input"), true);
$student_id = $data['student_id'];
$book_id = $data['book_id'];

$bookQuery = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
$bookQuery->execute([$book_id]);
$book = $bookQuery->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    echo json_encode(['success' => false, 'message' => 'Book not found']);
    exit;
}

$request_id = uniqid('BRW');
$insert = $conn->prepare("INSERT INTO borrow_requests (request_id, student_id, name, book_id, book_title, request_type) VALUES (?, ?, (SELECT full_name FROM students WHERE student_id = ?), ?, ?, 'borrow')");
$success = $insert->execute([$request_id, $student_id, $student_id, $book_id, $book['title']]);

echo json_encode(['success' => $success]);
