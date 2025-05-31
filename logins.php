<?php
if (!file_exists(__DIR__ . '/../config/database.php')) {
    die("Database config not found!");
}
require_once __DIR__ . '/../config/database.php'; 

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['username']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$username = $data['username'];
$password = $data['password'];

$query = $pdo->prepare("SELECT * FROM admin_accounts WHERE username = ?");
$query->execute([$username]);
$admin = $query->fetch(PDO::FETCH_ASSOC);

if ($admin && $password === $admin['password_hash']) {

    echo json_encode([
        'success' => true,
        'admin_id' => $admin['id'],
        'username' => $admin['username']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
}

// Close connection
$pdo = null;
