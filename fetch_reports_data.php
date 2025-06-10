<?php
header('Content-Type: application/json');
require_once __DIR__ . '/api/config/database.php';

try {
    // Total Borrows: count of all borrow_requests
    $stmt = $pdo->query("SELECT COUNT(*) FROM borrow_requests");
    $totalBorrows = (int)$stmt->fetchColumn();

    // Total Returns: always 0
    $totalReturns = 0;

    // Active Students: students currently inside (time_out IS NULL)
    $stmt = $pdo->query("SELECT COUNT(DISTINCT student_id) FROM attendance_logs WHERE time_out IS NULL");
    $activeStudents = (int)$stmt->fetchColumn();

    // Library Visits: total attendance logs
    $stmt = $pdo->query("SELECT COUNT(*) FROM attendance_logs");
    $libraryVisits = (int)$stmt->fetchColumn();

    // Attendance Trend: visits per week for the last 4 weeks
    $attendanceTrend = [];
    $trendStmt = $pdo->query("
        SELECT EXTRACT(WEEK FROM date) AS week, COUNT(*) AS visits
        FROM attendance_logs
        WHERE date >= CURRENT_DATE - INTERVAL '28 days'
        GROUP BY week
        ORDER BY week ASC
        LIMIT 4
    ");
    foreach ($trendStmt as $row) {
        $attendanceTrend[] = (int)$row['visits'];
    }

    // Borrows Monthly: for the last 5 months
    $borrowsMonthly = array_fill(0, 5, 0);
    $months = [];
    for ($i = 4; $i >= 0; $i--) {
        $months[] = date('Y-m', strtotime("-{$i} months"));
    }
    $borrowStmt = $pdo->query("
        SELECT TO_CHAR(requested_at, 'YYYY-MM') AS month, COUNT(*) AS borrows
        FROM borrow_requests
        WHERE requested_at >= DATE_TRUNC('month', CURRENT_DATE) - INTERVAL '4 months'
        GROUP BY month
    ");
    foreach ($borrowStmt as $row) {
        $idx = array_search($row['month'], $months);
        if ($idx !== false) $borrowsMonthly[$idx] = (int)$row['borrows'];
    }

    // Returns Monthly: always 0s
    $returnsMonthly = array_fill(0, 5, 0);

    // Top Active Students: top 5 by attendance count
    $topStudents = [];
    $topStmt = $pdo->query("
        SELECT s.full_name, s.program, COUNT(a.id) AS visits
        FROM students s
        JOIN attendance_logs a ON s.student_id = a.student_id
        GROUP BY s.student_id, s.full_name, s.program
        ORDER BY visits DESC
        LIMIT 5
    ");
    foreach ($topStmt as $row) {
        $topStudents[] = [
            'full_name' => $row['full_name'],
            'program' => $row['program'],
            'visits' => (int)$row['visits']
        ];
    }

    echo json_encode([
        'totalBorrows' => $totalBorrows,
        'totalReturns' => $totalReturns,
        'activeStudents' => $activeStudents,
        'libraryVisits' => $libraryVisits,
        'attendanceTrend' => $attendanceTrend,
        'borrowsMonthly' => $borrowsMonthly,
        'returnsMonthly' => $returnsMonthly,
        'topStudents' => $topStudents
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} 