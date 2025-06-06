<?php
$host = "localhost"; 
$port = "5432"; 
$dbname = "library_system"; 
$user = "postgres"; 
$password = "librarySystem"; 

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_GET['student_id'])) {
        echo json_encode(["success" => false, "message" => "Missing student_id"]);
        exit;
    }

    $studentId = $_GET['student_id'];
    $query = "SELECT student_id, full_name, program, total_borrowed, total_attendance FROM students WHERE student_id = :student_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':student_id', $studentId);
    $stmt->execute();

    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        echo json_encode(["success" => true, "data" => $student]);
    } else {
        echo json_encode(["success" => false, "message" => "Student not found"]);
    }

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
