<?php
require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'];
$password = $data['password'];

$query = $conn->prepare("SELECT * FROM admin_accounts WHERE username = ?");
$query->execute([$username]);
$admin = $query->fetch(PDO::FETCH_ASSOC);

if ($admin && password_verify($password, $admin['password_hash'])) {
    echo json_encode(['success' => true, 'admin_id' => $admin['id'], 'username' => $admin['username']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
}
// Close the database connection
$conn = null;