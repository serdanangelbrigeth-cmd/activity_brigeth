<?php
require_once 'config.php';

// Get all activities from database
$conn = getDBConnection();
$activities = [];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT * FROM activities";
if (!empty($search)) {
    $searchTerm = $conn->real_escape_string($search);
    $sql .= " WHERE title LIKE '%$searchTerm%' OR description LIKE '%$searchTerm%'";
}
$sql .= " ORDER BY uploaded_on DESC";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
}
$conn->close();

// Get flash message
$flash = getFlashMessage();
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Activities - MyPage</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <meta name="csrf-token" content="<?php echo escape($csrfToken); ?>">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-logo">MyPage - Student Activities</div>
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

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-wrapper">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <div class="welcome-container">
                    <h1 class="welcome-title">Student Activities Center</h1>
                    <p class="welcome-subtitle">Manage your activities and uploads</p>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo escape($flash['type']); ?>">
                <?php echo escape($flash['message']); ?>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="action-bar">
                <a href="add.php" class="btn btn-primary">
                    <i class='bx bx-plus'></i> Add New Activity
                </a>
                <button type="button" class="btn btn-danger" id="deleteSelectedBtn" style="display: none;">
                    <i class='bx bx-trash'></i> Delete Selected
                </button>
                <form method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search activities..." 
                           value="<?php echo escape($search); ?>" class="search-input">
                    <button type="submit" class="btn btn-secondary">
                        <i class='bx bx-search'></i> Search
                    </button>
                </form>
            </div>

            <!-- Activities Table -->
            <div class="table-container">
                <?php if (empty($activities)): ?>
                <div class="empty-state">
                    <i class='bx bx-inbox'></i>
                    <p>No activities found. <a href="add.php">Add your first activity</a></p>
                </div>
                <?php else: ?>
                <table class="activities-table">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>File Name</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Uploaded On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activities as $activity): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="activity-checkbox" 
                                       value="<?php echo escape($activity['id']); ?>">
                            </td>
                            <td><?php echo escape($activity['id']); ?></td>
                            <td><?php echo escape($activity['title']); ?></td>
                            <td><?php echo escape(truncateText($activity['description'] ?? '', 50)); ?></td>
                            <td>
                                <a href="download.php?id=<?php echo escape($activity['id']); ?>" 
                                   class="file-link">
                                    <i class='bx bx-download'></i>
                                    <?php echo escape($activity['filename']); ?>
                                </a>
                            </td>
                            <td>
                                <span class="file-type-badge">
                                    <?php echo escape(strtoupper($activity['file_type'] ?? 'N/A')); ?>
                                </span>
                            </td>
                            <td><?php echo formatFileSize($activity['file_size'] ?? 0); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($activity['uploaded_on'])); ?></td>
                            <td class="actions-cell">
                                <a href="edit.php?id=<?php echo escape($activity['id']); ?>" 
                                   class="btn-icon btn-edit" title="Edit">
                                    <i class='bx bx-edit'></i>
                                </a>
                                          <a href="delete.php?id=<?php echo escape($activity['id']); ?>" 
                                              class="btn-icon btn-delete" title="Delete" 
                                              onclick="return confirmDelete(this, '<?php echo escape($activity['title']); ?>');">
                                    <i class='bx bx-trash'></i>
                                </a>
                                <a href="download.php?id=<?php echo escape($activity['id']); ?>" 
                                   class="btn-icon btn-download" title="Download">
                                    <i class='bx bx-download'></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>

