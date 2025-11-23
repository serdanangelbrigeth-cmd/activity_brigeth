<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['ids']) || !is_array($_POST['ids'])) {
    setFlashMessage('error', 'Invalid request.');
    header('Location: index.php');
    exit;
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Invalid security token. Please try again.');
    header('Location: index.php');
    exit;
}

$ids = array_map('intval', $_POST['ids']);
$ids = array_filter($ids, function($id) { return $id > 0; });

if (empty($ids)) {
    setFlashMessage('error', 'No valid activities selected.');
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();
$deletedCount = 0;
$errors = [];

// Prepare statement for getting filenames
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $conn->prepare("SELECT id, filename FROM activities WHERE id IN ($placeholders)");
$stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
$stmt->execute();
$result = $stmt->get_result();

$filesToDelete = [];
while ($row = $result->fetch_assoc()) {
    $filesToDelete[] = $row;
}
$stmt->close();

// Delete files
foreach ($filesToDelete as $file) {
    $filePath = UPLOAD_DIR . $file['filename'];
    if (file_exists($filePath)) {
        @unlink($filePath);
    }
}

// Delete from database
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $conn->prepare("DELETE FROM activities WHERE id IN ($placeholders)");
$stmt->bind_param(str_repeat('i', count($ids)), ...$ids);

if ($stmt->execute()) {
    $deletedCount = $stmt->affected_rows;
    setFlashMessage('success', "Successfully deleted $deletedCount activity/activities!");
} else {
    setFlashMessage('error', 'Failed to delete some activities.');
}

$stmt->close();
$conn->close();

header('Location: index.php');
exit;
