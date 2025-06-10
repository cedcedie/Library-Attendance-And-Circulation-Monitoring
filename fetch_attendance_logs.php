<?php
header('Content-Type: application/json');

$host = "localhost"; 
$port = "5432"; 
$dbname = "library_system"; 
$user = "postgres"; 
$password = "librarySystem"; 

try {
    // ✅ Assign the PDO instance to $pdo
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->prepare("
    SELECT 
        a.student_id, 
        s.full_name, 
        s.program, 
        a.type, 
        a.date, 
        a.time_in,
        a.time_out
    FROM attendance_logs a
    JOIN students s ON a.student_id = s.student_id
    ORDER BY a.date DESC, a.time_in DESC
    LIMIT 100
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
?>
