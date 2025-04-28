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
    $bookId = $_POST['book_id'];
    
    if (empty($bookId)) {
        echo "Error: Book ID is missing!";
        exit();
    }
    $qrImagePath = "images/qrcodes/$bookId.png"; 

    $query = "DELETE FROM books WHERE book_id = :book_id";

    $stmt = $conn->prepare($query);

    $stmt->bindParam(':book_id', $bookId, PDO::PARAM_STR);

    try {
        $conn->beginTransaction();
        $stmt->execute();
        if (file_exists($qrImagePath)) {
            unlink($qrImagePath);
        }
        $conn->commit();

        echo "Book and QR code deleted successfully.";
    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error deleting book from database: " . $e->getMessage();
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error deleting QR code image: " . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}
?>
