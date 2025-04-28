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

    if (isset($_GET['student_id'])) {
        $student_id = $_GET['student_id'];
        $query = "SELECT student_id, full_name, program, contact_number, email, username FROM students WHERE student_id = :student_id";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
        $stmt->execute();

        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');

        if ($student) {
            echo json_encode($student);
        } else {
            echo json_encode(["error" => "Student not found."]);
        }
    } else {
        echo json_encode(["error" => "No student_id provided."]);
    }

} catch (PDOException $e) {
    echo json_encode(["error" => "Failed to fetch data: " . $e->getMessage()]);
}
?>
