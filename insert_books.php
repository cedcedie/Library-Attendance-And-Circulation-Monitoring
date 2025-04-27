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
    // Create a PDO connection
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the form is submitted via POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get form data
        $author = $_POST['author'];
        $title = $_POST['title'];
        $book_id = $_POST['book_id'];  // Ensure this matches the form field's name attribute

        // Path for QR code image
        $qr_code = 'images/qrcodes/' . $book_id . '.png';

        // Set up QR Code options
        $options = new QROptions([
            'version'    => 5,
            'eccLevel'   => QRCode::ECC_L,
            'imageBase64'=> false,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'scale'      => 5,
            'margin'     => 2
        ]);

        $qrcode = new QRCode($options);
        // Generate the QR code and save it as an image
        $qrcode->render($book_id, $qr_code);

        // Insert new book into the database
        $insertQuery = "INSERT INTO books (author, title, book_id, qr_code) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->execute([$author, $title, $book_id, $qr_code]);

        // Success message
        echo "✅ New book added successfully.";

        // Redirect after successful insertion
        header("Location: books.html");
        exit();
    } else {
        echo "❌ Invalid request method.";
    }
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
}
?>
