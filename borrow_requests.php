<?php
// DB config - change these to your own credentials
$host = 'localhost';
$dbname = 'your_database_name';
$user = 'your_db_user';
$pass = 'your_db_password';

// Connect with PDO (recommended)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    // Set error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// Get search and filter parameters
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : '';  // filter placeholder
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build SQL with placeholder
$sql = "SELECT unique_id, student_id, book_id, book_title, borrow_date, due_date, return_date, status, created_at
        FROM borrow_history
        WHERE 1=1 ";

// Parameters array for PDO
$params = [];

if ($student_id !== '') {
    $sql .= " AND student_id = :student_id ";
    $params[':student_id'] = $student_id;
}

if ($search !== '') {
    $sql .= " AND book_title LIKE :search ";
    $params[':search'] = "%$search%";
}

$sql .= " ORDER BY borrow_date DESC LIMIT 50";  // limit for demo

// Prepare and execute
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$borrowRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Book Borrow History</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="sidebar.css" />
</head>
<body class="bg-gray-50 font-poppins overflow-x-hidden">
    <div class="flex min-h-screen">
        <div id="sidebar-container"></div>
        <main class="main-content flex-1 p-6">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Book Borrow History</h1>
            </header>

            <form method="GET" class="flex justify-between mb-4" action="">
                <div class="flex space-x-4">
                    <input
                        type="text"
                        name="search"
                        value="<?= htmlspecialchars($search) ?>"
                        placeholder="Search by book title"
                        class="p-2 w-64 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <input
                        type="text"
                        name="student_id"
                        value="<?= htmlspecialchars($student_id) ?>"
                        placeholder="Filter by Student ID"
                        class="p-2 w-48 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <button
                        type="submit"
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-all duration-300"
                    >
                        Search
                    </button>
                </div>
                <div>
                    <button
                        type="button"
                        onclick="window.print()"
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-all duration-300"
                    >
                        Print Records
                    </button>
                </div>
            </form>

            <div class="bg-white shadow-lg rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm text-left text-gray-500">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-6 py-4 font-medium">Book Title</th>
                            <th class="px-6 py-4 font-medium">Borrow Date</th>
                            <th class="px-6 py-4 font-medium">Due Date</th>
                            <th class="px-6 py-4 font-medium">Return Date</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($borrowRecords) === 0): ?>
                            <tr>
                                <td colspan="5" class="text-center px-6 py-4 text-gray-600">
                                    No records found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($borrowRecords as $record): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-4"><?= htmlspecialchars($record['book_title']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($record['borrow_date']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($record['due_date']) ?></td>
                                    <td class="px-6 py-4">
                                        <?= $record['return_date'] ? htmlspecialchars($record['return_date']) : '-' ?>
                                    </td>
                                    <td class="px-6 py-4 font-semibold
                                        <?= strtolower($record['status']) === 'returned' ? 'text-green-500' : 'text-red-500' ?>">
                                        <?= htmlspecialchars($record['status']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="flex justify-between items-center px-6 py-4">
                    <div class="text-sm text-gray-600">
                        Showing <?= count($borrowRecords) ?> records
                    </div>
                    <div class="flex space-x-2">
                        <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg cursor-not-allowed" disabled>
                            Previous
                        </button>
                        <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg cursor-not-allowed" disabled>
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="sidebar.js"></script>
</body>
</html>
