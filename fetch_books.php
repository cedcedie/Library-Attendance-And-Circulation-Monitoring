<?php
$host = "localhost"; 
$port = "5432"; 
$dbname = "library_system"; 
$user = "postgres"; 
$password = "librarySystem"; 

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

 
    $query = "SELECT book_id, title, author, qr_code FROM books";  
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($books);

} catch (PDOException $e) {
    echo json_encode(["error" => "Failed to fetch data: " . $e->getMessage()]);
}
?>
