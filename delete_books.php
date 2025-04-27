<?php
// Database connection details
$host = "localhost"; 
$port = "5432"; 
$dbname = "library_system"; 
$user = "postgres"; 
$password = "librarySystem"; 

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if book_id is provided via POST
if (isset($_POST['book_id']) && !empty($_POST['book_id'])) {
    $bookId = $_POST['book_id'];

    // Prepare the DELETE query
    $sql = "DELETE FROM books WHERE book_id = :book_id";
    
    // Prepare the statement
    $stmt = $pdo->prepare($sql);
    
    // Bind the parameter
    $stmt->bindParam(':book_id', $bookId, PDO::PARAM_STR);
    
    // Execute the query and check if the deletion was successful
    if ($stmt->execute()) {
        echo "Book with ID $bookId deleted successfully.";
    } else {
        echo "Error: Could not delete the book.";
    }
} else {
    echo "Error: Book ID not provided.";
}
?>
