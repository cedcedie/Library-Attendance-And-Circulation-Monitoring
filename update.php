<?php
$host = "localhost"; 
$port = "5432"; 
$dbname = "library_system"; 
$user = "postgres"; 
$password = "librarySystem"; 

$conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $full_name = $_POST['full_name'];
    $program = $_POST['program'];
    $status = $_POST['status'];

    $sql = "UPDATE students 
            SET full_name = :full_name, program = :program, status = :status
            WHERE student_id = :student_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':program', $program);
    $stmt->bindParam(':status', $status);

    if ($stmt->execute()) {
        echo "Student updated successfully!";
    } else {
        echo "Failed to update student.";
    }
}
?>
