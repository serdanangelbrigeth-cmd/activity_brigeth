<?php
require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$file_type = trim($_POST['file_type'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($file_type === '') {
    echo json_encode(['success' => false, 'message' => 'File type required']);
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("INSERT INTO user_files (user_id, file_name, original_name, file_type, file_size, description) VALUES (?, '', '', ?, 0, ?)");
$stmt->bind_param('isss', $user_id, $file_type, $description);

// Note: bind_param types corrected below since above was incorrect - use ssi? We'll prepare properly.
$stmt->close();

// Proper insert
$stmt = $conn->prepare("INSERT INTO user_files (user_id, file_name, original_name, file_type, file_size, description) VALUES (?, ?, ?, ?, ?, ?)");
$empty = '';
$zero = 0;
$stmt->bind_param('isssis', $user_id, $empty, $empty, $file_type, $zero, $description);
$ok = $stmt->execute();
$stmt->close();
$conn->close();

if ($ok) {
    echo json_encode(['success' => true, 'message' => 'Record inserted']);
} else {
    echo json_encode(['success' => false, 'message' => 'DB error']);
}

exit;
