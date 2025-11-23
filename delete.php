<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlashMessage('error', 'Invalid activity ID.');
    header('Location: index.php');
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid security token. Please try again.');
        header('Location: index.php');
        exit;
    }
    
    $conn = getDBConnection();
    
    // Get activity to retrieve filename
    $stmt = $conn->prepare("SELECT filename FROM activities WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $activity = $result->fetch_assoc();
    $stmt->close();
    
    if ($activity) {
        // Delete file
        $filePath = UPLOAD_DIR . $activity['filename'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM activities WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Activity deleted successfully!');
        } else {
            setFlashMessage('error', 'Failed to delete activity from database.');
        }
        
        $stmt->close();
    } else {
        setFlashMessage('error', 'Activity not found.');
    }
    
    $conn->close();
    header('Location: index.php');
    exit;
}

// Get activity for confirmation
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM activities WHERE id = ?");
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

$flash = getFlashMessage();
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Activity - Student Activities</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-logo">MyPage - Delete Activity</div>
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
                <h1 class="page-title">Delete Activity</h1>
                <p class="page-subtitle">Are you sure you want to delete this activity?</p>
            </div>

            <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo escape($flash['type']); ?>">
                <?php echo escape($flash['message']); ?>
            </div>
            <?php endif; ?>

            <div class="form-container">
                <div class="delete-confirmation">
                    <div class="warning-box">
                        <i class='bx bx-error-circle'></i>
                        <h3>Warning: This action cannot be undone!</h3>
                        <p>The following activity and its associated file will be permanently deleted:</p>
                    </div>

                    <div class="activity-details">
                        <div class="detail-row">
                            <strong>Title:</strong>
                            <span><?php echo escape($activity['title']); ?></span>
                        </div>
                        <div class="detail-row">
                            <strong>Description:</strong>
                            <span><?php echo escape($activity['description'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="detail-row">
                            <strong>File:</strong>
                            <span><?php echo escape($activity['filename']); ?></span>
                        </div>
                        <div class="detail-row">
                            <strong>File Size:</strong>
                            <span><?php echo formatFileSize($activity['file_size'] ?? 0); ?></span>
                        </div>
                        <div class="detail-row">
                            <strong>Uploaded On:</strong>
                            <span><?php echo date('M d, Y H:i', strtotime($activity['uploaded_on'])); ?></span>
                        </div>
                    </div>

                    <form method="POST" class="delete-form">
                        <input type="hidden" name="csrf_token" value="<?php echo escape($csrfToken); ?>">
                        <input type="hidden" name="confirm_delete" value="1">
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-danger">
                                <i class='bx bx-trash'></i> Yes, Delete Permanently
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class='bx bx-x'></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>

