<?php
header('Content-Type: application/json');

$host = "localhost";
$db = "library_system";
$user = "postgres";
$pass = "librarySystem";

try {
    $conn = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $totalStudents = $conn->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $totalBooks = $conn->query("SELECT COUNT(*) FROM books")->fetchColumn();
    $pendingRequests = $conn->query("SELECT COUNT(*) FROM borrow_requests WHERE status = 'pending'")->fetchColumn();
    $overdueBooks = $conn->query("
        SELECT COUNT(*) FROM borrow_history
        WHERE status = 'overdue' OR (due_date < CURRENT_DATE AND return_date IS NULL)
    ")->fetchColumn();

   $stmt = $conn->prepare("
    SELECT name, book_title, request_type, requested_at
    FROM borrow_requests
    WHERE status = 'pending'
    ORDER BY requested_at DESC
    LIMIT 3
");
$stmt->execute();
$latestRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'totalStudents' => $totalStudents,
        'totalBooks' => $totalBooks,
        'pendingRequests' => $pendingRequests,
        'overdueBooks' => $overdueBooks,
        'latestRequests' => $latestRequests
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
