<?php
require_once __DIR__ . '/../../config/database.php';


// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['student_id'], $data['book_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$student_id = $data['student_id'];
$book_id = $data['book_id'];

try {
    // Check if book exists
    $bookQuery = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
    $bookQuery->execute([$book_id]);
    $book = $bookQuery->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        echo json_encode(['success' => false, 'message' => 'Book not found']);
        exit;
    }

    // Generate request ID
    $request_id = uniqid('BRW');

    // Insert borrow request
    $insert = $conn->prepare("
        INSERT INTO borrow_requests 
        (request_id, student_id, name, book_id, book_title, request_type)
        VALUES (?, ?, (SELECT full_name FROM students WHERE student_id = ?), ?, ?, 'borrow')
    ");
    $success = $insert->execute([
        $request_id,
        $student_id,
        $student_id,
        $book_id,
        $book['title']
    ]);

    if ($success) {
        echo json_encode(['success' => true, 'request_id' => $request_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to insert request']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}