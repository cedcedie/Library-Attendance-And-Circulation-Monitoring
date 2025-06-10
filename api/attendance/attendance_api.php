<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $student_id = $_GET['student_id'] ?? null;

        if ($student_id) {
            $stmt = $pdo->prepare("SELECT student_id, full_name, program FROM students WHERE student_id = ?");
            $stmt->execute([$student_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                echo json_encode(['success' => true, 'data' => $student]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Student not found']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Student ID required']);
        }

    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        $student_id = $input['student_id'] ?? null;
        $type = strtolower($input['type'] ?? 'entry');

        if (!$student_id) {
            throw new Exception('Student ID is required');
        }

        if (!in_array($type, ['entry', 'exit'])) {
            throw new Exception("Invalid attendance type. Must be 'entry' or 'exit'");
        }

        $date = date('Y-m-d');
        $time = date('H:i:s');

        // Fetch student info
        $stmt = $pdo->prepare("SELECT student_id, full_name, program FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            throw new Exception('Student not found');
        }

        if ($type === 'entry') {
            // Check if already logged in without logging out
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM attendance_logs WHERE student_id = ? AND date = ? AND time_out IS NULL");
            $checkStmt->execute([$student_id, $date]);
            $count = $checkStmt->fetchColumn();

            if ($count > 0) {
                throw new Exception("You already have an active entry without an exit for today");
            }

            // Insert new log
            $stmt = $pdo->prepare("INSERT INTO attendance_logs (student_id, full_name, program, type, date, time_in)
                                   VALUES (?, ?, ?, 'entry', ?, ?)");
            $stmt->execute([$student['student_id'], $student['full_name'], $student['program'], $date, $time]);

        } else {
            // Handle "exit"
            $idStmt = $pdo->prepare("
                SELECT id FROM attendance_logs 
                WHERE student_id = ? AND date = ? AND time_out IS NULL 
                ORDER BY time_in DESC LIMIT 1
            ");
            $idStmt->execute([$student_id, $date]);
            $logId = $idStmt->fetchColumn();

            if (!$logId) {
                throw new Exception("No active 'entry' record found for exit");
            }

            // Update time_out and set type to 'exit'
            $updateStmt = $pdo->prepare("UPDATE attendance_logs SET time_out = ?, type = 'exit' WHERE id = ?");
            $updateStmt->execute([$time, $logId]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Attendance recorded',
            'student' => $student,
            'type' => $type,
            'date' => $date,
            'time' => $time
        ]);

    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid HTTP method']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
