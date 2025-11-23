<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlashMessage('error', 'Invalid activity ID.');
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT filename, file_type FROM activities WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$activity = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$activity) {
    setFlashMessage('error', 'Activity not found.');
    header('Location: index.php');
    exit;
}

$filePath = UPLOAD_DIR . $activity['filename'];

if (!file_exists($filePath)) {
    setFlashMessage('error', 'File not found on server.');
    header('Location: index.php');
    exit;
}

// Get original filename (remove unique prefix)
$originalFilename = $activity['filename'];
if (preg_match('/^[a-f0-9]+_\d+_(.+)$/i', $originalFilename, $matches)) {
    $originalFilename = $matches[1];
}

// Set headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($originalFilename) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Output file
readfile($filePath);
exit;
