<?php
require_once 'config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $uploadedBy = trim($_POST['uploaded_by'] ?? 'Anonymous');
    
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid security token. Please try again.');
        header('Location: add.php');
        exit;
    }
    
    // Validate inputs
    if (empty($title)) {
        setFlashMessage('error', 'Title is required.');
        header('Location: add.php');
        exit;
    }
    
    // Handle file upload
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        setFlashMessage('error', 'Please select a file to upload.');
        header('Location: add.php');
        exit;
    }
    
    $file = $_FILES['file'];
    $originalName = $file['name'];
    $tmpPath = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    // Validate file size
    if ($fileSize > MAX_FILE_SIZE) {
        setFlashMessage('error', 'File size exceeds maximum allowed size of ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB.');
        header('Location: add.php');
        exit;
    }
    
    // Get file extension
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    
    // Validate extension
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        setFlashMessage('error', 'Invalid file type. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS));
        header('Location: add.php');
        exit;
    }
    
    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_MIME_TYPES)) {
        setFlashMessage('error', 'Invalid file MIME type.');
        header('Location: add.php');
        exit;
    }
    
    // Generate unique filename
    $uniqueId = uniqid() . '_' . time();
    $safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($originalName));
    $newFilename = $uniqueId . '_' . $safeFilename;
    $destination = UPLOAD_DIR . $newFilename;
    
    // Move uploaded file
    if (!move_uploaded_file($tmpPath, $destination)) {
        setFlashMessage('error', 'Failed to save uploaded file.');
        header('Location: add.php');
        exit;
    }
    
    // Save to database
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO activities (title, description, filename, file_type, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $title, $description, $newFilename, $extension, $fileSize, $uploadedBy);
    
    if ($stmt->execute()) {
        setFlashMessage('success', 'Activity added successfully!');
        header('Location: index.php');
        exit;
    } else {
        // Delete uploaded file if DB insert fails
        @unlink($destination);
        setFlashMessage('error', 'Failed to save activity to database.');
    }
    
    $stmt->close();
    $conn->close();
}

$flash = getFlashMessage();
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Activity - Student Activities</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-logo">MyPage - Add Activity</div>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="add.php" class="nav-link active">Add Activity</a></li>
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
                <h1 class="page-title">Add New Activity</h1>
                <p class="page-subtitle">Upload a file and provide activity details</p>
            </div>

            <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo escape($flash['type']); ?>">
                <?php echo escape($flash['message']); ?>
            </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" enctype="multipart/form-data" id="uploadForm" class="activity-form">
                    <input type="hidden" name="csrf_token" value="<?php echo escape($csrfToken); ?>">
                    
                    <div class="form-group">
                        <label for="title">Title <span class="required">*</span></label>
                        <input type="text" id="title" name="title" required 
                               placeholder="Enter activity title" 
                               value="<?php echo escape($_POST['title'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" 
                                  placeholder="Enter activity description"><?php echo escape($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="uploaded_by">Uploaded By</label>
                        <input type="text" id="uploaded_by" name="uploaded_by" 
                               placeholder="Your name (optional)" 
                               value="<?php echo escape($_POST['uploaded_by'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="file">File <span class="required">*</span></label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="file" name="file" required 
                                   accept=".pdf,.docx,.doc,.jpg,.jpeg,.png">
                            <label for="file" class="file-upload-label">
                                <i class='bx bx-cloud-upload'></i>
                                <span>Choose file or drag and drop</span>
                                <small>Allowed: PDF, DOCX, DOC, JPG, JPEG, PNG (Max 5MB)</small>
                            </label>
                            <div id="filePreview" class="file-preview"></div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-upload'></i> Upload Activity
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

