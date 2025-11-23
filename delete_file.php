<?php
require_once 'config.php';
header('Content-Type: application/json');

// Simple logging to help debug deletion issues
$logDir = __DIR__ . '/logs';
if (!file_exists($logDir)) {
    @mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/delete_file.log';
function _df_log($msg) {
    global $logFile;
    $time = date('Y-m-d H:i:s');
    @file_put_contents($logFile, "[$time] " . $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
}
_df_log("delete_file.php invoked");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    _df_log('Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    _df_log('Unauthenticated request');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$file_id = isset($_POST['file_id']) ? (int)$_POST['file_id'] : 0;
$file_name = $_POST['file_name'] ?? '';
$user_id = (int)$_SESSION['user_id'];

if ($file_id <= 0) {
    _df_log('Invalid file id: ' . var_export($_POST['file_id'] ?? null, true));
    echo json_encode(['success' => false, 'message' => 'Invalid file id']);
    exit;
}

_df_log('User ' . $user_id . ' requested delete for file_id=' . $file_id . ' file_name=' . $file_name);

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, file_name FROM user_files WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $file_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    _df_log('File not found or permission denied for file_id=' . $file_id . ' user=' . $user_id);
    echo json_encode(['success' => false, 'message' => 'File not found or permission denied']);
    $stmt->close();
    $conn->close();
    exit;
}

$row = $res->fetch_assoc();
$dbFileName = $row['file_name'];
$stmt->close();

// Delete file from server if exists and a file name is present
if (!empty($dbFileName)) {
    $file_path = UPLOAD_DIR . $dbFileName;
    _df_log('Resolved file path: ' . $file_path);
    if (file_exists($file_path)) {
        $deleted = @unlink($file_path);
        _df_log('File exists. Unlink result: ' . ($deleted ? 'success' : 'failed'));
    } else {
        _df_log('File does not exist at path: ' . $file_path);
    }
}

$del = $conn->prepare("DELETE FROM user_files WHERE id = ? AND user_id = ?");
$del->bind_param('ii', $file_id, $user_id);
$ok = $del->execute();
$del->close();
$conn->close();

if ($ok) {
    _df_log('Database delete OK for file_id=' . $file_id);
    echo json_encode(['success' => true, 'message' => 'File deleted successfully!']);
} else {
    _df_log('Database delete FAILED for file_id=' . $file_id . ' stmt_error=' . $del->error);
    echo json_encode(['success' => false, 'message' => 'Error deleting file record from database.']);
}

exit;
