<?php
$host = "localhost"; 
$port = "5432"; 
$dbname = "library_system"; 
$user = "postgres"; 
$password = "librarySystem"; 

// Create the connection using PDO
try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $studentId = $_POST['student_id'];
    
    // Debugging: Check if student_id is correctly passed
    if (empty($studentId)) {
        echo "Error: Student ID is missing!";
        exit();
    }
    
    // Get the path of the QR code image
    $qrImagePath = "images/qrcodes/$studentId.png"; 

    // Prepare the SQL DELETE statement
    $query = "DELETE FROM students WHERE student_id = :student_id";

    // Prepare the statement
    $stmt = $conn->prepare($query);

    // Bind the parameter
    $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);

    try {
        // Start transaction to ensure both actions (database and file deletion) happen together
        $conn->beginTransaction();

        // Execute the delete query
        $stmt->execute();

        // Check if the QR code image file exists
        if (file_exists($qrImagePath)) {
            // Delete the QR code image file
            unlink($qrImagePath);
        }

        // Commit the transaction if everything is successful
        $conn->commit();

        echo "Student and QR code deleted successfully.";
    } catch (PDOException $e) {
        // Rollback if an error occurs
        $conn->rollBack();
        echo "Error deleting student from database: " . $e->getMessage();
    } catch (Exception $e) {
        // Rollback if the file deletion fails
        $conn->rollBack();
        echo "Error deleting QR code image: " . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}
?>
