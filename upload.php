<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mypage.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ACT2_HTML.SERDAN.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$file_type = trim($_POST['file_type'] ?? '');
$description = trim($_POST['description'] ?? '');

if (!isset($_FILES['file_upload']) || $_FILES['file_upload']['error'] !== UPLOAD_ERR_OK) {
    header('Location: mypage.php?error=upload_failed');
    exit;
}

$file = $_FILES['file_upload'];
$originalName = $file['name'];
$tmpPath = $file['tmp_name'];
$size = $file['size'];

// Validate size
if ($size > MAX_FILE_SIZE) {
    header('Location: mypage.php?error=file_too_large');
    exit;
}

$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
if (!in_array($extension, ALLOWED_EXTENSIONS)) {
    header('Location: mypage.php?error=invalid_extension');
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $tmpPath);
finfo_close($finfo);
if (!in_array($mimeType, ALLOWED_MIME_TYPES)) {
    header('Location: mypage.php?error=invalid_type');
    exit;
}

$uniqueId = uniqid() . '_' . time();
$safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($originalName));
$newFilename = $uniqueId . '_' . $safeFilename;
$destination = UPLOAD_DIR . $newFilename;

if (!move_uploaded_file($tmpPath, $destination)) {
    header('Location: mypage.php?error=upload_failed');
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("INSERT INTO user_files (user_id, file_name, original_name, file_type, file_size, description) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param('isssis', $user_id, $newFilename, $originalName, $file_type, $size, $description);
$ok = $stmt->execute();
$stmt->close();
$conn->close();

if ($ok) {
    header('Location: mypage.php?success=upload');
} else {
    header('Location: mypage.php?error=db_error');
}

exit;
