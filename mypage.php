<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ACT2_HTML.SERDAN.php');
    exit();
}
// Redirect to index.php if user accesses mypage.php directly after login
header('Location: index.php');
exit();
?>
$conn = getDBConnection();
$user_id = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, file_name, original_name, file_type, file_size, description, upload_date FROM user_files WHERE user_id = ? ORDER BY upload_date DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$files = [];
while ($row = $result->fetch_assoc()) {
    $files[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Student Upload Center - MyPage</title>
    <link rel="stylesheet" href="ACT_CSS.SERDAN.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-logo">MyPage</div>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="mypage.php#filesView" class="nav-link">Student Upload Center</a></li>
                <li><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="upload-center" style="padding-top:100px;">
        <div class="page-header">
            <h1>Student Upload Center</h1>
            <p>Manage your projects, assignments, activities, and images</p>
        </div>

        <div class="action-buttons">
            <button class="action-btn" onclick="openInsertModal()"><i class='bx bx-plus-circle'></i> Insert Record</button>
            <button class="action-btn" onclick="openUploadModal()"><i class='bx bx-cloud-upload'></i> Upload File</button>
            <button class="action-btn" onclick="scrollToView()"><i class='bx bx-list-ul'></i> View Files</button>
        </div>

        <?php if (isset($_GET['success']) && $_GET['success'] == 'upload'): ?>
            <div class="alert alert-success"><i class='bx bx-check-circle'></i> File uploaded successfully!</div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error"><i class='bx bx-error-circle'></i> <?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <!-- Insert Modal -->
        <div id="insertModal" class="modal" style="display:none;">
            <div class="modal-content">
                <div class="modal-header"><h2>Insert New Record</h2><span class="close" onclick="closeModal('insertModal')">&times;</span></div>
                <form id="insertForm" onsubmit="submitInsert(event)">
                    <div class="form-group">
                        <label>File Type</label>
                        <select id="insert_file_type" name="file_type" required>
                            <option value="">Select</option>
                            <option value="activity">Activity</option>
                            <option value="project">Project</option>
                            <option value="assignment">Assignment</option>
                            <option value="image">Image</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="insert_description" name="description"></textarea>
                    </div>
                    <button class="submit-btn" type="submit">Insert Record</button>
                </form>
            </div>
        </div>

        <!-- Upload Modal -->
        <div id="uploadModal" class="modal" style="display:none;">
            <div class="modal-content">
                <div class="modal-header"><h2>Upload File</h2><span class="close" onclick="closeModal('uploadModal')">&times;</span></div>
                <form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>File Type</label>
                        <select id="upload_file_type" name="file_type" required>
                            <option value="">Select</option>
                            <option value="activity">Activity</option>
                            <option value="project">Project</option>
                            <option value="assignment">Assignment</option>
                            <option value="image">Image</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="upload_description" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Select File</label>
                        <input type="file" name="file_upload" required accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip,.rar">
                    </div>
                    <button class="submit-btn" type="submit">Upload File</button>
                </form>
            </div>
        </div>

        <!-- Update Modal -->
        <div id="updateModal" class="modal" style="display:none;">
            <div class="modal-content">
                <div class="modal-header"><h2>Update Record</h2><span class="close" onclick="closeModal('updateModal')">&times;</span></div>
                <form id="updateForm" onsubmit="submitUpdate(event)">
                    <input type="hidden" id="update_file_id" name="file_id">
                    <div class="form-group">
                        <label>File Type</label>
                        <select id="update_file_type" name="file_type" required>
                            <option value="activity">Activity</option>
                            <option value="project">Project</option>
                            <option value="assignment">Assignment</option>
                            <option value="image">Image</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="update_description" name="description"></textarea>
                    </div>
                    <button class="submit-btn" type="submit">Update Record</button>
                </form>
            </div>
        </div>

        <!-- Files Table -->
        <div class="files-container" id="filesView">
            <h2><i class='bx bx-folder-open'></i> My Uploaded Files</h2>
            <?php if (count($files) > 0): ?>
                <table class="files-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>File Name</th>
                            <th>Description</th>
                            <th>Preview</th>
                            <th>Size</th>
                            <th>Upload Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(ucfirst($file['file_type'])); ?></td>
                            <td><?php echo htmlspecialchars($file['original_name'] ?: '—'); ?></td>
                            <td><?php echo htmlspecialchars($file['description'] ?: 'No description'); ?></td>
                            <td>
                                <?php if ($file['file_type'] === 'image' && $file['file_name']): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($file['file_name']); ?>" class="file-preview" alt="preview">
                                <?php else: ?>
                                    <i class='bx bx-file' style="font-size:2rem;color:#ff4d4d"></i>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $file['file_size'] > 0 ? round($file['file_size']/1024,2).' KB' : 'N/A'; ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($file['upload_date'])); ?></td>
                            <td>
                                <div class="action-buttons-cell">
                                    <?php if (!empty($file['file_name'])): ?>
                                        <a href="download.php?id=<?php echo $file['id']; ?>" class="btn-small btn-download"><i class='bx bx-download'></i> Download</a>
                                    <?php endif; ?>
                                    <button class="btn-small btn-edit" onclick="openUpdateModal(<?php echo $file['id']; ?>, '<?php echo htmlspecialchars($file['file_type'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($file['description'] ?? '', ENT_QUOTES); ?>')"><i class='bx bx-edit'></i> Edit</button>
                                    <button class="btn-small btn-delete" onclick="deleteFile(<?php echo $file['id']; ?>, '<?php echo htmlspecialchars($file['file_name'] ?? '', ENT_QUOTES); ?>')"><i class='bx bx-trash'></i> Delete</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class='bx bx-folder-open'></i>
                    <p>No files uploaded yet. Click "Upload File" to get started!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function openInsertModal(){ document.getElementById('insertModal').style.display='block'; }
        function openUploadModal(){ document.getElementById('uploadModal').style.display='block'; }
        function openUpdateModal(id, type, description){
            document.getElementById('update_file_id').value = id;
            document.getElementById('update_file_type').value = type;
            document.getElementById('update_description').value = description;
            document.getElementById('updateModal').style.display='block';
        }
        function closeModal(id){ document.getElementById(id).style.display='none'; }
        window.onclick = function(e){ ['insertModal','uploadModal','updateModal'].forEach(function(id){ var m=document.getElementById(id); if(m && e.target==m) m.style.display='none'; }); }
        function scrollToView(){ document.getElementById('filesView').scrollIntoView({behavior:'smooth'}); }

        // Insert
        function submitInsert(e){
            e.preventDefault();
            var fd = new FormData(document.getElementById('insertForm'));
            fd.append('action','insert');
            fetch('insert_handler.php',{method:'POST',credentials:'same-origin',body:fd})
            .then(r=>r.json()).then(data=>{ if(data.success){ alert(data.message); closeModal('insertModal'); location.reload(); } else alert(data.message); })
            .catch(()=>alert('Error'));
        }

        // Update
        function submitUpdate(e){
            e.preventDefault();
            var fd = new FormData(document.getElementById('updateForm'));
            fd.append('action','update');
            fetch('update_handler.php',{method:'POST',credentials:'same-origin',body:fd})
            .then(r=>r.json()).then(data=>{ if(data.success){ alert(data.message); closeModal('updateModal'); location.reload(); } else alert(data.message); })
            .catch(()=>alert('Error'));
        }

        // Delete
        function deleteFile(fileId, fileName){
            if(!confirm('Are you sure?')) return;
            fetch('delete_file.php',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'file_id='+fileId+'&file_name='+encodeURIComponent(fileName)})
            .then(r=>r.json()).then(data=>{ if(data.success){ alert(data.message); location.reload(); } else alert(data.message); }).catch(()=>alert('Error'));
        }
    </script>

    <script src="ACT3_JAVA.SERDAN.js"></script>
</body>
</html>
