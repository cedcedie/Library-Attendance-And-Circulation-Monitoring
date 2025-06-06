<?php
$host = "localhost";
$port = "5432";
$dbname = "library_system";
$user = "postgres";
$password = "librarySystem";

header('Content-Type: application/json');

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("
        SELECT unique_id, student_id, book_id, book_title, borrow_date, due_date, return_date, status
        FROM borrow_history
        ORDER BY borrow_date DESC
        LIMIT 100
    ");
    $borrow_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($borrow_history);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}