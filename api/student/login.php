<?php
require_once(__DIR__ . "/../config/database.php");

header('Content-Type: application/json');

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!isset($data['username']) || !isset($data['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing username or password']);
    exit;
}

$username = $data['username'];
$password = $data['password'];

if (!function_exists('pg_query_params')) {
    echo json_encode(['status' => 'error', 'message' => 'PostgreSQL extension is not enabled']);
    exit;
}

$sql = "SELECT * FROM students WHERE username = $1";
$result = pg_query_params($conn, $sql, [$username]);

if ($result && pg_num_rows($result) == 1) {
    $student = pg_fetch_assoc($result);
    
    if (password_verify($password, $student['password_hash'])) {
        unset($student['password_hash']); 
        echo json_encode(['status' => 'success', 'student' => $student]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
}
?>
