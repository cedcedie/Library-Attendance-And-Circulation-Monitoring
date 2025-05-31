<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (!file_exists(__DIR__ . '/../config/database.php')) {
    die("Database config not found!");
}
require_once __DIR__ . '/../config/database.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $student_id = $_GET['student_id'] ?? null;

        if ($student_id) {
            $stmt = $pdo->prepare("
                SELECT al.*, s.full_name, s.program 
                FROM attendance_logs al 
                JOIN students s ON al.student_id = s.student_id 
                WHERE al.student_id = ? 
                ORDER BY al.date DESC, al.time_in DESC
            ");
            $stmt->execute([$student_id]);
        } else {
            $stmt = $pdo->prepare("
                SELECT al.*, s.full_name, s.program 
                FROM attendance_logs al 
                JOIN students s ON al.student_id = s.student_id 
                ORDER BY al.date DESC, al.time_in DESC
            ");
            $stmt->execute();
        }

        $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response = [];
        foreach ($attendance as $record) {
            $response[] = [
                'id' => $record['id'],
                'student_id' => $record['student_id'],
                'full_name' => $record['full_name'],
                'program' => $record['program'],
                'type' => $record['type'],
                'date' => $record['date'],
                'time_in' => $record['time_in'],
                'time_out' => $record['time_out']
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => $response
        ]);
    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        $student_id = $input['student_id'] ?? null;
        $type = $input['type'] ?? 'entry';
        $date = date('Y-m-d');
        $time = date('H:i:s');

        if (!$student_id) {
            throw new Exception('Student ID is required');
        }

        // Validate student exists
        $stmt = $pdo->prepare("SELECT student_id, full_name, program FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            throw new Exception('Student not found');
        }

        if ($type === 'entry') {
            $stmt = $pdo->prepare("
                INSERT INTO attendance_logs (student_id, full_name, program, type, date, time_in) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$student['student_id'], $student['full_name'], $student['program'], 'entry', $date, $time]);
        } elseif ($type === 'exit') {
            $stmt = $pdo->prepare("
                UPDATE attendance_logs 
                SET time_out = ? 
                WHERE student_id = ? AND date = ? AND time_out IS NULL
                ORDER BY time_in DESC LIMIT 1
            ");
            $stmt->execute([$time, $student_id, $date]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('No active entry found for today');
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Attendance recorded successfully'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
