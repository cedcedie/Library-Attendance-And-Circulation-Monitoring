<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
date_default_timezone_set("Asia/Manila");

$host = "localhost"; 
$port = "5432"; 
$dbname = "library_system"; 
$user = "postgres"; 
$password = "librarySystem"; 

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(204);
    exit();
}

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT 
            al.student_id,
            al.full_name,
            al.program,
            al.type,
            al.date,
            al.time_in,
            al.time_out,
            CASE 
                WHEN al.time_out IS NULL THEN 'Inside'
                ELSE 'Exited'
            END AS status
        FROM attendance_logs al
        ORDER BY al.date DESC, al.time_in DESC
    ");
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $logs
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
