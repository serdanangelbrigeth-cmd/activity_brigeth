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
$file_id = isset($_POST['file_id']) ? (int)$_POST['file_id'] : 0;
$file_type = trim($_POST['file_type'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($file_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid file id']);
    exit;
}

$conn = getDBConnection();

// Verify ownership
$stmt = $conn->prepare("SELECT id FROM user_files WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $file_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'File not found or permission denied']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

$stmt = $conn->prepare("UPDATE user_files SET file_type = ?, description = ? WHERE id = ?");
$stmt->bind_param('ssi', $file_type, $description, $file_id);
$ok = $stmt->execute();
$stmt->close();
$conn->close();

if ($ok) {
    echo json_encode(['success' => true, 'message' => 'Record updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'DB error']);
}

exit;
