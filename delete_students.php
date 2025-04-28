<?php
$host = "localhost"; 
$port = "5432"; 
$dbname = "library_system"; 
$user = "postgres"; 
$password = "librarySystem"; 

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentId = $_POST['student_id'];
    
    if (empty($studentId)) {
        echo "Error: Student ID is missing!";
        exit();
    }
    
    $qrImagePath = "images/qrcodes/$studentId.png"; 

    $query = "DELETE FROM students WHERE student_id = :student_id";

    $stmt = $conn->prepare($query);

    $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);

    try {

        $conn->beginTransaction();
        $stmt->execute();

        if (file_exists($qrImagePath)) {
            unlink($qrImagePath);
        }

        $conn->commit();

        echo "Student and QR code deleted successfully.";
    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error deleting student from database: " . $e->getMessage();
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error deleting QR code image: " . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}
?>
