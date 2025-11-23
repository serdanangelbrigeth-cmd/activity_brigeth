<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlashMessage('error', 'Invalid activity ID.');
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();
$activity = null;

// Get activity data
$stmt = $conn->prepare("SELECT * FROM activities WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$activity = $result->fetch_assoc();
$stmt->close();

if (!$activity) {
    setFlashMessage('error', 'Activity not found.');
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $uploadedBy = trim($_POST['uploaded_by'] ?? $activity['uploaded_by']);
    
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid security token. Please try again.');
        header("Location: edit.php?id=$id");
        exit;
    }
    
    // Validate inputs
    if (empty($title)) {
        setFlashMessage('error', 'Title is required.');
        header("Location: edit.php?id=$id");
        exit;
    }
    
    $oldFilename = $activity['filename'];
    $newFilename = $oldFilename;
    $fileSize = $activity['file_size'];
    $fileType = $activity['file_type'];
    
    // Handle file replacement if new file is uploaded
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $originalName = $file['name'];
        $tmpPath = $file['tmp_name'];
        $fileSize = $file['size'];
        
        // Validate file size
        if ($fileSize > MAX_FILE_SIZE) {
            setFlashMessage('error', 'File size exceeds maximum allowed size of ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB.');
            header("Location: edit.php?id=$id");
            exit;
        }
        
        // Get file extension
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        // Validate extension
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            setFlashMessage('error', 'Invalid file type. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS));
            header("Location: edit.php?id=$id");
            exit;
        }
        
        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);
        
        if (!in_array($mimeType, ALLOWED_MIME_TYPES)) {
            setFlashMessage('error', 'Invalid file MIME type.');
            header("Location: edit.php?id=$id");
            exit;
        }
        
        // Generate unique filename
        $uniqueId = uniqid() . '_' . time();
        $safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($originalName));
        $newFilename = $uniqueId . '_' . $safeFilename;
        $destination = UPLOAD_DIR . $newFilename;
        
        // Move uploaded file
        if (move_uploaded_file($tmpPath, $destination)) {
            // Delete old file
            $oldFilePath = UPLOAD_DIR . $oldFilename;
            if (file_exists($oldFilePath)) {
                @unlink($oldFilePath);
            }
            $fileType = $extension;
        } else {
            setFlashMessage('error', 'Failed to save uploaded file.');
            header("Location: edit.php?id=$id");
            exit;
        }
    }
    
    // Update database
    $stmt = $conn->prepare("UPDATE activities SET title = ?, description = ?, filename = ?, file_type = ?, file_size = ?, uploaded_by = ? WHERE id = ?");
    $stmt->bind_param("ssssisi", $title, $description, $newFilename, $fileType, $fileSize, $uploadedBy, $id);
    
    if ($stmt->execute()) {
        setFlashMessage('success', 'Activity updated successfully!');
        header('Location: index.php');
        exit;
    } else {
        setFlashMessage('error', 'Failed to update activity.');
    }
    
    $stmt->close();
}

$conn->close();

$flash = getFlashMessage();
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Activity - Student Activities</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-logo">MyPage - Edit Activity</div>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="add.php" class="nav-link">Add Activity</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php" class="nav-link">Logout</a></li>
                <?php else: ?>
                    <li><a href="ACT2_HTML.SERDAN.php" class="nav-link">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="main-content">
        <div class="content-wrapper">
            <div class="form-page-header">
                <h1 class="page-title">Edit Activity</h1>
                <p class="page-subtitle">Update activity details</p>
            </div>

            <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo escape($flash['type']); ?>">
                <?php echo escape($flash['message']); ?>
            </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" enctype="multipart/form-data" id="editForm" class="activity-form">
                    <input type="hidden" name="csrf_token" value="<?php echo escape($csrfToken); ?>">
                    
                    <div class="form-group">
                        <label for="title">Title <span class="required">*</span></label>
                        <input type="text" id="title" name="title" required 
                               placeholder="Enter activity title" 
                               value="<?php echo escape($activity['title']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" 
                                  placeholder="Enter activity description"><?php echo escape($activity['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="uploaded_by">Uploaded By</label>
                        <input type="text" id="uploaded_by" name="uploaded_by" 
                               placeholder="Your name (optional)" 
                               value="<?php echo escape($activity['uploaded_by'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Current File</label>
                        <div class="current-file-info">
                            <i class='bx bx-file'></i>
                            <span><?php echo escape($activity['filename']); ?></span>
                            <a href="download.php?id=<?php echo escape($id); ?>" class="btn-link">
                                <i class='bx bx-download'></i> Download
                            </a>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="file">Replace File (Optional)</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="file" name="file" 
                                   accept=".pdf,.docx,.doc,.jpg,.jpeg,.png">
                            <label for="file" class="file-upload-label">
                                <i class='bx bx-cloud-upload'></i>
                                <span>Choose new file or drag and drop</span>
                                <small>Leave empty to keep current file. Allowed: PDF, DOCX, DOC, JPG, JPEG, PNG (Max 5MB)</small>
                            </label>
                            <div id="filePreview" class="file-preview"></div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-save'></i> Update Activity
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class='bx bx-x'></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>

