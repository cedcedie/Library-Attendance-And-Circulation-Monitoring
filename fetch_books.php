<?php
header('Content-Type: application/json');

$host = "localhost"; 
$port = "5432"; 
$dbname = "library_system"; 
$user = "postgres"; 
$password = "librarySystem"; 

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (isset($_GET['book_id']) && !empty($_GET['book_id'])) {
        $book_id = $_GET['book_id'];
        $query = "SELECT * FROM books WHERE book_id = :book_id LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute(['book_id' => $book_id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($book) {
            echo json_encode(['success' => true, 'book' => $book]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Book not found']);
        }
    } else {
        $query = "SELECT * FROM books ORDER BY title ASC";
        $stmt = $conn->query($query);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($books);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
