<?php
require 'vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

header('Content-Type: application/json');

$host = "localhost"; 
$port = "5432"; 
$dbname = "library_system"; 
$user = "postgres"; 
$password = "librarySystem"; 

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $author = $_POST['author'] ?? '';
        $title = $_POST['title'] ?? '';
        $book_id = $_POST['book_id'] ?? '';
        $is_edit = isset($_POST['is_edit']) && $_POST['is_edit'] === 'true';

        if (empty($author) || empty($title) || empty($book_id)) {
            throw new Exception("All fields are required");
        }

        $qr_code_path = 'images/qrcodes/' . $book_id . '.png';
        $options = new QROptions([
            'version'    => 5,
            'eccLevel'   => QRCode::ECC_L,
            'imageBase64'=> false,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'scale'      => 5,
            'margin'     => 2
        ]);

        $qrcode = new QRCode($options);
        $qrcode->render($book_id, $qr_code_path);

        if ($is_edit) {
            $updateQuery = "UPDATE books SET author = ?, title = ?, qr_code = ? WHERE book_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->execute([$author, $title, $qr_code_path, $book_id]);
            $message = "✅ Book updated successfully.";
        } else {
            $insertQuery = "INSERT INTO books (author, title, book_id, qr_code) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->execute([$author, $title, $book_id, $qr_code_path]);
            $message = "✅ New book added successfully.";
        }

        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        throw new Exception("❌ Invalid request method.");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>