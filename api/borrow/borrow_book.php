<?php
header('Content-Type: application/json');

if (!file_exists(__DIR__ . '/../config/database.php')) {
    die(json_encode(['success' => false, 'message' => 'Database config not found!']));
}
require_once __DIR__. '/../config/database.php';
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (!isset($_GET['book_id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing book_id parameter']);
            exit;
        }
        $book_id = $_GET['book_id'];
        $stmt = $pdo->prepare("SELECT book_id, title FROM books WHERE book_id = ?");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($book) {
            echo json_encode(['success' => true, 'data' => $book]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Book not found']);
        }

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['student_id'], $data['book_id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }

        $student_id = $data['student_id'];
        $book_id = $data['book_id'];
        $reason = $data['reason'] ?? '';
        $expiration_date = $data['expiration_date'] ?? null;
        $stmt = $pdo->prepare("SELECT title FROM books WHERE book_id = ?");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$book) {
            echo json_encode(['success' => false, 'message' => 'Book not found']);
            exit;
        }

        // Duplicate borrow check
        $dupCheck = $pdo->prepare("SELECT 1 FROM borrow_requests WHERE student_id = ? AND book_id = ? AND (status IS NULL OR status NOT IN ('returned', 'rejected')) LIMIT 1");
        $dupCheck->execute([$student_id, $book_id]);
        if ($dupCheck->fetch()) {
            echo json_encode(['success' => false, 'message' => 'You already have a pending or active borrow request for this book.']);
            exit;
        }

        $request_id = uniqid('BRW');

        $insert = $pdo->prepare("
            INSERT INTO borrow_requests 
            (request_id, student_id, name, book_id, book_title, reason, expiration_date, request_type)
            VALUES (?, ?, (SELECT full_name FROM students WHERE student_id = ?), ?, ?, ?, ?, 'borrow')
        ");
        $success = $insert->execute([
            $request_id,
            $student_id,
            $student_id,
            $book_id,
            $book['title'],
            $reason,
            $expiration_date
        ]);

        if ($success) {
            // Insert into borrow_history as well
            $historyInsert = $pdo->prepare("INSERT INTO borrow_history (student_id, book_id, book_title, status, borrow_date, due_date, return_date, created_at) VALUES (?, ?, ?, 'pending', NOW(), ?, NULL, NOW())");
            $historyInsert->execute([
                $student_id,
                $book_id,
                $book['title'],
                $expiration_date
            ]);
            echo json_encode(['success' => true, 'request_id' => $request_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to insert request']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Unsupported request method']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
