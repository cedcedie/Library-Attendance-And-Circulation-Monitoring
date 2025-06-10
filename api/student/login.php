<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1); 
error_reporting(E_ALL);

header('Content-Type: application/json');

$host = "localhost";
$port = "5432";
$dbname = "library_system";
$user = "postgres";
$password = "librarySystem";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500); 
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['username']) || !isset($input['password'])) {
    http_response_code(400); 
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing username or password'
    ]);
    exit;
}

$username = $input['username'];
$password = $input['password'];
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        echo json_encode([
    'status' => 'success',
    'message' => 'Login successful',
    'student_id' => $user['student_id'], 
    'name' => $user['name'],
    'username' => $user['username']
]);
    } else {
        http_response_code(401); 
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid username or password'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database query error'
    ]);
}
