<?php
require_once __DIR__ . '/api/config/database.php';
require_once __DIR__ . '/vendor/autoload.php'; // For PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="library_report.xlsx"');

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Library Report');
$sheet->setCellValue('A2', 'Total Borrows');
$sheet->setCellValue('B2', 'Total Returns');
$sheet->setCellValue('C2', 'Active Students (Currently Inside)');
$sheet->setCellValue('D2', 'Library Visits (All Time)');

// Fetch stats
$stmt = $pdo->query("SELECT COUNT(*) FROM borrow_requests");
$totalBorrows = (int)$stmt->fetchColumn();
$totalReturns = 0;
$stmt = $pdo->query("SELECT COUNT(DISTINCT student_id) FROM attendance_logs WHERE time_out IS NULL");
$activeStudents = (int)$stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM attendance_logs");
$libraryVisits = (int)$stmt->fetchColumn();

$sheet->setCellValue('A3', $totalBorrows);
$sheet->setCellValue('B3', $totalReturns);
$sheet->setCellValue('C3', $activeStudents);
$sheet->setCellValue('D3', $libraryVisits);

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit; 