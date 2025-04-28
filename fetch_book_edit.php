<?php
header('Content-Type: application/json'); // Ensure JSON response
error_log("Fetch book request received.");

$host = "localhost";
$port = "5432";
$dbname = "library_system";
$user = "postgres";
$password = "librarySystem";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['book_id'])) {
        $book_id = $_GET['book_id'];
        error_log("Fetching book ID: " . $book_id);

        $stmt = $pdo->prepare("SELECT * FROM books WHERE book_id = :book_id");
        $stmt->execute(['book_id' => $book_id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($book) {
            error_log("Book found: " . print_r($book, true));
            echo json_encode($book);
        } else {
            error_log("Book not found for ID: " . $book_id);
            echo json_encode(["error" => "Book not found."]);
        }
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(["error" => "Database error."]);
}
?>