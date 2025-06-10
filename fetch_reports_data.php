<?php
header('Content-Type: application/json');
require_once __DIR__ . '/api/config/database.php';

try {
    // Total Borrows: count of borrow_requests with status 'borrowed' or 'approved'
    $stmt = $pdo->query("SELECT COUNT(*) FROM borrow_requests WHERE status IN ('borrowed', 'approved')");
    $totalBorrows = (int)$stmt->fetchColumn();

    // Total Returns: count of borrow_requests with status 'returned'
    $stmt = $pdo->query("SELECT COUNT(*) FROM borrow_requests WHERE status = 'returned'");
    $totalReturns = (int)$stmt->fetchColumn();

    // Active Students: students who have at least 1 attendance log in the last 30 days
    $stmt = $pdo->query("SELECT COUNT(DISTINCT student_id) FROM attendance_logs WHERE date >= CURRENT_DATE - INTERVAL '30 days'");
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

    // Borrows/Returns Monthly: for the last 5 months
    $borrowsMonthly = array_fill(0, 5, 0);
    $returnsMonthly = array_fill(0, 5, 0);
    $months = [];
    for ($i = 4; $i >= 0; $i--) {
        $months[] = date('Y-m', strtotime("-{$i} months"));
    }
    $borrowStmt = $pdo->query("
        SELECT TO_CHAR(date_borrowed, 'YYYY-MM') AS month, COUNT(*) AS borrows
        FROM borrow_requests
        WHERE date_borrowed >= DATE_TRUNC('month', CURRENT_DATE) - INTERVAL '4 months'
        GROUP BY month
    ");
    foreach ($borrowStmt as $row) {
        $idx = array_search($row['month'], $months);
        if ($idx !== false) $borrowsMonthly[$idx] = (int)$row['borrows'];
    }
    $returnStmt = $pdo->query("
        SELECT TO_CHAR(date_returned, 'YYYY-MM') AS month, COUNT(*) AS returns
        FROM borrow_requests
        WHERE date_returned >= DATE_TRUNC('month', CURRENT_DATE) - INTERVAL '4 months'
        AND status = 'returned'
        GROUP BY month
    ");
    foreach ($returnStmt as $row) {
        $idx = array_search($row['month'], $months);
        if ($idx !== false) $returnsMonthly[$idx] = (int)$row['returns'];
    }

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