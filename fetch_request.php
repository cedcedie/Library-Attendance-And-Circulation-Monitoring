<?php
// update_request.php
$host = "localhost";
$port = "5432";
$dbname = "library_system";
$user = "postgres";
$password = "librarySystem";

header('Content-Type: application/json');

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// ✅ Handle GET request to list all borrow requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'list') {
    try {
        $stmt = $pdo->query("SELECT * FROM borrow_requests ORDER BY requested_at DESC");
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($requests);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Fetch error: ' . $e->getMessage()]);
    }
    exit;
}

// ✅ Handle POST request to approve/reject borrow requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $request_id = $_POST['request_id'] ?? '';

    if (empty($action) || empty($request_id)) {
        echo json_encode(['success' => false, 'message' => 'Missing action or request_id']);
        exit;
    }

    if (!in_array($action, ['approve', 'reject'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT * FROM borrow_requests WHERE request_id = :id");
        $stmt->execute(['id' => $request_id]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$request) throw new Exception('Borrow request not found.');

        $student_id = $request['student_id'];
        $book_id = $request['book_id'];
        $book_title = $request['book_title'];
        $now = date('Y-m-d');
        $due = date('Y-m-d', strtotime('+7 days'));

        if ($action === 'approve') {
            $pdo->prepare("UPDATE borrow_requests SET status = 'approved' WHERE request_id = :id")
                ->execute(['id' => $request_id]);

      $pdo->prepare("INSERT INTO notifications (student_id, title, message, status, created_at)
    VALUES (:student_id, :title, :message, :status, :created_at)")
    ->execute([
        'student_id' => $student_id,
        'title' => "Borrowed: $book_title",
        'message' => "Your request to borrow '$book_title' has been approved.",
        'status' => 'unread', // ✅ FIXED
        'created_at' => $now
    ]);

            $pdo->prepare("UPDATE students SET total_borrowed = total_borrowed + 1
                WHERE student_id = :student_id")
                ->execute(['student_id' => $student_id]);

            $pdo->prepare("INSERT INTO borrow_history 
                (student_id, book_id, book_title, borrow_date, due_date, status)
                VALUES (:student_id, :book_id, :book_title, :borrow_date, :due_date, 'borrowed')")
                ->execute([
                    'student_id' => $student_id,
                    'book_id' => $book_id,
                    'book_title' => $book_title,
                    'borrow_date' => $now,
                    'due_date' => $due
                ]);
        } else {
          $pdo->prepare("DELETE FROM borrow_requests WHERE request_id = :id")
    ->execute(['id' => $request_id]);

        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    exit;
}

// ❌ Catch all for other invalid requests
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
?>
