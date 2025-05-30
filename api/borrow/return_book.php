<?php
require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);
$student_id = $data['student_id'];
$book_id = $data['book_id'];

$request_id = uniqid('RTN');
$insert = $conn->prepare("INSERT INTO borrow_requests (request_id, student_id, name, book_id, book_title, request_type) 
VALUES (?, ?, (SELECT full_name FROM students WHERE student_id = ?), ?, (SELECT title FROM books WHERE book_id = ?), 'return')");
$success = $insert->execute([$request_id, $student_id, $student_id, $book_id, $book_id]);

echo json_encode(['success' => $success]);
