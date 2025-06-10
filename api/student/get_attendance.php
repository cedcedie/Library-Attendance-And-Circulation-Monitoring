<?php
ini_set('display_errors', 0);
error_reporting(0);
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
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $student_id = $_GET['student_id'] ?? '';

    if (empty($student_id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid student ID']);
        exit;
    }

    try {
       $stmt = $pdo->prepare("
    SELECT 
        al.id, 
        al.student_id, 
        s.full_name, 
        s.program, 
        al.date, 
        al.time_in, 
        al.time_out,
        CASE 
            WHEN al.time_in IS NOT NULL AND al.time_out IS NULL THEN 'entry'
            WHEN al.time_in IS NOT NULL AND al.time_out IS NOT NULL THEN 'exit'
            ELSE NULL
        END AS type
    FROM attendance_logs al
    JOIN students s ON al.student_id = s.student_id
    WHERE al.student_id = :student_id
    ORDER BY al.date DESC, al.time_in DESC
");
        $stmt->execute(['student_id' => $student_id]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'attendance_logs' => $logs]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Query failed']);
    }

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['student_id'], $data['type']) || !in_array($data['type'], ['entry', 'exit'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }

    $student_id = $data['student_id'];
    $type = $data['type'];
    $now = new DateTime("now", new DateTimeZone('Asia/Manila'));
    $date = $now->format("Y-m-d");
    $time = $now->format("H:i:s");

    try {
        if ($type === 'entry') {
            $stmt = $pdo->prepare("INSERT INTO attendance_logs (student_id, date, time_in) VALUES (:student_id, :date, :time_in)");
            $stmt->execute(['student_id' => $student_id, 'date' => $date, 'time_in' => $time]);
        } else {
            $stmt = $pdo->prepare("UPDATE attendance_logs SET time_out = :time_out WHERE student_id = :student_id AND date = :date AND time_out IS NULL ORDER BY id DESC LIMIT 1");
            $stmt->execute(['student_id' => $student_id, 'date' => $date, 'time_out' => $time]);
        }

        echo json_encode(['success' => true, 'message' => 'Attendance recorded successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
