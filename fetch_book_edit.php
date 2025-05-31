<?php
header('Content-Type: application/json'); // Ensure JSON response
error_log("Insert book request received.");

$host = "localhost";
$port = "5432";
$dbname = "library_system";
$user = "postgres";
$password = "librarySystem";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input || !isset($input['title']) || !isset($input['author']) || !isset($input['isbn'])) {
            echo json_encode(["error" => "Missing required fields."]);
            exit;
        }

        $title = $input['title'];
        $author = $input['author'];
        $isbn = $input['isbn'];

        $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn) VALUES (:title, :author, :isbn)");
        $stmt->execute([
            'title' => $title,
            'author' => $author,
            'isbn' => $isbn
        ]);

        error_log("Book inserted successfully: $title by $author");
        echo json_encode(["success" => true, "message" => "Book inserted successfully."]);
    } else {
        echo json_encode(["error" => "Invalid request method."]);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(["error" => "Database error."]);
}
?>
