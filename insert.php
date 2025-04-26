<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = "localhost"; 
$port = "5432"; 
$dbname = "library_system"; 
$user = "postgres"; 
$password = "librarySystem"; 

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $full_name = $_POST['full_name'];
        $student_id = $_POST['student_id'];
        $program = $_POST['program'];
        $contactnumber = $_POST['contactnumber'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // encrypt password
    
        $sql = "INSERT INTO students (full_name, student_id, program, contactnumber, email, username, password)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$full_name, $student_id, $program, $contactnumber, $email, $username, $password]);
    
        // After successful insert, redirect back to students page
        header("Location: students.html");
        exit();
    } else {
        echo "Invalid request.";
    }
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>
