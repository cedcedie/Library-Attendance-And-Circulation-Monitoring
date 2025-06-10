<?php
require_once __DIR__ . '/api/config/database.php';
require_once __DIR__ . '/vendor/autoload.php'; // For FPDF

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="library_report.pdf"');

$pdf = new \FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Library Report', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);

// Fetch stats
$stmt = $pdo->query("SELECT COUNT(*) FROM borrow_requests");
$totalBorrows = (int)$stmt->fetchColumn();
$totalReturns = 0;
$stmt = $pdo->query("SELECT COUNT(DISTINCT student_id) FROM attendance_logs WHERE time_out IS NULL");
$activeStudents = (int)$stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM attendance_logs");
$libraryVisits = (int)$stmt->fetchColumn();

$pdf->Cell(0, 10, "Total Borrows: $totalBorrows", 0, 1);
$pdf->Cell(0, 10, "Total Returns: $totalReturns", 0, 1);
$pdf->Cell(0, 10, "Active Students (Currently Inside): $activeStudents", 0, 1);
$pdf->Cell(0, 10, "Library Visits (All Time): $libraryVisits", 0, 1);

$pdf->Output('I', 'library_report.pdf');
exit; 