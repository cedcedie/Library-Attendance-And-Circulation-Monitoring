<?php
require_once __DIR__ . '/../config/database.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $type = $_POST['type'] ?? 'entry';
    $date = date('Y-m-d');
    $time = date('H:i:s');

    try {
        if (empty($student_id)) throw new Exception("Student ID is required.");

        $stmt = $pdo->prepare("SELECT full_name, program FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) throw new Exception("Student not found.");

        if ($type === 'entry') {
            $stmt = $pdo->prepare("INSERT INTO attendance_logs (student_id, full_name, program, type, date, time_in)
                                   VALUES (?, ?, ?, 'entry', ?, ?)");
            $stmt->execute([$student_id, $student['full_name'], $student['program'], $date, $time]);
        } elseif ($type === 'exit') {
            $stmt = $pdo->prepare("UPDATE attendance_logs
                                   SET time_out = ?
                                   WHERE student_id = ? AND date = ? AND time_out IS NULL
                                   ORDER BY time_in DESC LIMIT 1");
            $stmt->execute([$time, $student_id, $date]);

            if ($stmt->rowCount() === 0) throw new Exception("No 'entry' record to update.");
        }

        header("Location: attendance-logs.php?success=1");
        exit;
    } catch (Exception $e) {
        header("Location: attendance-logs.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}