<?php
header('Content-Type: application/json');
$settingsFile = __DIR__ . '/settings.json';
$input = file_get_contents('php://input');
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'No input']);
    exit;
}
$data = json_decode($input, true);
if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}
if (file_put_contents($settingsFile, json_encode($data, JSON_PRETTY_PRINT))) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save']);
} 