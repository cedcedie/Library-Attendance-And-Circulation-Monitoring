<?php
require 'vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

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
        $contact_number = $_POST['contact_number'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 

        // QR Code Generation
        $qr_code_path = 'images/qrcodes/' . $student_id . '.png'; 

        $options = new QROptions([
            'version'    => 5,
            'eccLevel'   => QRCode::ECC_L,
            'imageBase64'=> false,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'scale'      => 5,
            'margin'     => 2
        ]);

        $data = "Name: $full_name, ID: $student_id, Program: $program, Contact: $contact_number, Email: $email";
        $qrcode = new QRCode($options);
        $qrcode->render($student_id, $qr_code_path);  

        // Check if the student already exists
        $checkQuery = "SELECT * FROM students WHERE student_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->execute([$student_id]);

        if ($stmt->rowCount() > 0) {
            // If the student exists, update the record
            $updateQuery = "UPDATE students SET full_name = ?, program = ?, contact_number = ?, email = ?, username = ?, password = ?, qr_code = ? WHERE student_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->execute([$full_name, $program, $contact_number, $email, $username, $password, $qr_code_path, $student_id]);

            echo "Student record updated.";
        } else {
            // If the student doesn't exist, insert a new record
            $insertQuery = "INSERT INTO students (full_name, student_id, program, contact_number, email, username, password, qr_code)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->execute([$full_name, $student_id, $program, $contact_number, $email, $username, $password, $qr_code_path]);

            echo "New student added.";
        }

        header("Location: students.html");
        exit();
    } else {
        echo "Invalid request.";
    }
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>
