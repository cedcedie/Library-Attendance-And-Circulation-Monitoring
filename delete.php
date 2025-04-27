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

    $sql = "DELETE FROM students WHERE student_id = :student_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':student_id', $student_id);

    if ($stmt->execute()) {
        echo "Student deleted successfully!";
    } else {
        echo "Failed to delete student.";
    }
}
?>
