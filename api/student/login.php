<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'];
$password = $data['password'];

$sql = "SELECT * FROM students WHERE username = $1";
$result = pg_query_params($conn, $sql, [$username]);

if (pg_num_rows($result) == 1) {
    $student = pg_fetch_assoc($result);
    if (password_verify($password, $student['password_hash'])) {
        echo json_encode(['status' => 'success', 'student' => $student]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
}
?>
