<?php
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
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['username']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$username = $data['username'];
$passwordInput = $data['password'];

$query = $pdo->prepare("SELECT * FROM admin_accounts WHERE username = ?");
$query->execute([$username]);
$admin = $query->fetch(PDO::FETCH_ASSOC);

if ($admin && password_verify($passwordInput, $admin['password_hash'])) {
    echo json_encode([
        'success' => true,
        'admin_id' => $admin['id'],
        'username' => $admin['username']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
}
$pdo = null;
