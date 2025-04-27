<?php
// Database connection parameters
$host = "localhost"; 
$port = "5432"; 
$dbname = "library_system"; 
$user = "postgres"; 
$password = "librarySystem"; 

try {
    // Create a connection to the PostgreSQL database
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_id'])) {
        $student_id = $_POST['student_id'];
        $full_name = $_POST['full_name'];
        $program = $_POST['program'];
        $contactnumber = $_POST['contactnumber'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password_raw = $_POST['password'];

        // Hash the password before saving it!
        $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);

        $query = "UPDATE students 
                  SET full_name = :full_name, 
                      program = :program, 
                      contact_number = :contactnumber, 
                      email = :email, 
                      username = :username, 
                      password = :password 
                  WHERE student_id = :student_id";

        $stmt = $pdo->prepare($query);

        $stmt->execute([
            'full_name' => $full_name,
            'program' => $program,
            'contact_number' => $contactnumber,
            'email' => $email,
            'username' => $username,
            'password' => $password_hashed,  // Use hashed password
            'student_id' => $student_id
        ]);

        // Redirect back to students page after successful update
        header("Location: students.html");
        exit;
    } else {
        echo json_encode(["error" => "Invalid request."]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Failed to update data: " . $e->getMessage()]);
}
?>
