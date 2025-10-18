<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$material_id = $_GET['material_id'] ?? null;
$course_id = $_GET['course_id'] ?? null;

if (!$material_id || !$course_id) {
    header("Location: tdashboard.php");
    exit();
}

$conn = getDBConnection();

// Verify teacher owns this course
$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = ? AND instructor_id = ?");
$stmt->bind_param("ii", $course_id, $teacher_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header("Location: tdashboard.php");
    exit();
}

// Get material details
$stmt = $conn->prepare("SELECT m.*, 
                       (SELECT GROUP_CONCAT(tag_name) FROM material_tags WHERE material_id = m.material_id) as tags
                       FROM materials m 
                       WHERE m.material_id = ? AND m.course_id = ?");
$stmt->bind_param("ii", $material_id, $course_id);
$stmt->execute();
$material = $stmt->get_result()->fetch_assoc();

if (!$material) {
    header("Location: edit-materials.php?course_id=" . $course_id);
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_material'])) {
    $title = $_POST['title'];
    $short_description = $_POST['short_description'];
    $full_description = $_POST['full_description'];
    $category = $_POST['category'];
    $week_number = $_POST['week_number'] ?? null;
    $difficulty_level = $_POST['difficulty_level'] ?? null;
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $allow_comments = isset($_POST['allow_comments']) ? 1 : 0;
    $allow_download = isset($_POST['allow_download']) ? 1 : 0;
    
    // Update material
    $stmt = $conn->prepare("UPDATE materials SET 
                           title = ?, 
                           short_description = ?,
                           description = ?, 
                           category = ?, 
                           week_number = ?,
                           difficulty_level = ?,
                           is_published = ?,
                           allow_comments = ?,
                           allow_download = ?,
                           updated_at = CURRENT_TIMESTAMP
                           WHERE material_id = ?");
    $stmt->bind_param("ssssisiii", $title, $short_description, $full_description, $category, 
                     $week_number, $difficulty_level, $is_published, $allow_comments, 
                     $allow_download, $material_id);
    $stmt->execute();
    
    // Update tags
    if (isset($_POST['tags'])) {
        // Delete old tags
        $stmt = $conn->prepare("DELETE FROM material_tags WHERE material_id = ?");
        $stmt->bind_param("i", $material_id);
        $stmt->execute();
        
        // Insert new tags
        $tags = array_filter(array_map('trim', explode(',', $_POST['tags'])));
        if (!empty($tags)) {
            $stmt = $conn->prepare("INSERT INTO material_tags (material_id, tag_name) VALUES (?, ?)");
            foreach ($tags as $tag) {
                if (!empty($tag)) {
                    $stmt->bind_param("is", $material_id, $tag);
                    $stmt->execute();
                }
            }
        }
    }
    
    // Handle file upload if provided
    if (isset($_FILES['new_file']) && $_FILES['new_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/materials/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['new_file']['name']);
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['new_file']['tmp_name'], $file_path)) {
            $file_size = $_FILES['new_file']['size'];
            $file_type = $_FILES['new_file']['type'];
            
            // Update file info
            $stmt = $conn->prepare("UPDATE materials SET 
                                   file_path = ?, 
                                   file_type = ?, 
                                   file_size = ? 
                                   WHERE material_id = ?");
            $stmt->bind_param("ssii", $file_path, $file_type, $file_size, $material_id);
            $stmt->execute();
        }
    }
    
    $_SESSION['success_message'] = "Material updated successfully!";
    header("Location: edit-materials.php?course_id=" . $course_id);
    exit();
}

// Parse existing tags
$existing_tags = $material['tags'] ? explode(',', $material['tags']) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Material - EduHub</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: #0f0f1e;
            min-height: 100vh;
            color: white;
        }
        
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }
        
        .bg-gradient {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.3;
            animation: float 20s infinite;
        }
        
        .bg-gradient:nth-child(1) {
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            top: -200px;
            left: -200px;
        }
        
        .bg-gradient:nth-child(2) {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            bottom: -100px;
            right: -100px;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(80px, -80px); }
        }
        
        .container {
            position: relative;
            z-index: 1;
            padding: 30px;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 25px 40px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .back-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 800;
        }
        
        .form-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 40px;
        }
        
        .form-section {
            margin-bottom: 35px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .required {
            color: #ef4444;
        }
        
        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 15px;
            font-family: 'Outfit', sans-serif;
            transition: all 0.3s;
        }
        
        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
            background: rgba(255, 255, 255, 0.08);
        }
        
        .form-input::placeholder,
        .form-textarea::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .form-select option {
            background: #1a1a24;
            color: white;
        }
        
        .help-text {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 8px;
        }
        
        .file-upload-area {
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-upload-area:hover {
            background: rgba(102, 126, 234, 0.1);
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .file-upload-icon {
            font-size: 56px;
            margin-bottom: 15px;
        }
        
        .file-upload-text {
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .file-upload-subtext {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .file-input {
            display: none;
        }
        
        .material-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
        }
        
        .material-type-option {
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .material-type-option:hover {
            background: rgba(102, 126, 234, 0.1);
            border-color: rgba(102, 126, 234, 0.3);
        }
        
        .material-type-option.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .type-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .type-label {
            font-weight: 600;
            font-size: 14px;
        }
        
        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            min-height: 54px;
        }
        
        .tag {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .tag-remove {
            cursor: pointer;
            font-weight: 700;
        }
        
        .tags-input {
            flex: 1;
            min-width: 150px;
            border: none;
            background: transparent;
            color: white;
            outline: none;
            padding: 8px;
            font-family: 'Outfit', sans-serif;
        }
        
        .tags-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 35px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn {
            padding: 14px 32px;
            border: none;
            border-radius: 16px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Outfit', sans-serif;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            flex: 1;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .info-box {
            padding: 16px;
            background: rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 16px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 25px;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .material-types {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="bg-gradient"></div>
        <div class="bg-gradient"></div>
    </div>
    
    <div class="container">
        <div class="header">
            <a href="edit-materials.php?course_id=<?php echo $course_id; ?>" class="back-btn">
                ← Back to Materials
            </a>
            <h1 class="page-title">Edit Material</h1>
        </div>
        
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_material" value="1">
                
                <div class="info-box">
                    📝 Editing: <strong><?php echo htmlspecialchars($material['title']); ?></strong>
                </div>
                
                <!-- Basic Information -->
                <div class="form-section">
                    <div class="section-title">📝 Basic Information</div>
                    <div class="form-group">
                        <label class="form-label">Material Title <span class="required">*</span></label>
                        <input type="text" name="title" class="form-input" 
                               value="<?php echo htmlspecialchars($material['title']); ?>" required>
                    </div>
                </div>
                
                <!-- Material Type -->
                <div class="form-section">
                    <div class="section-title">🏷️ Material Type</div>
                    <div class="material-types">
                        <div class="material-type-option <?php echo $material['category'] === 'lecture_notes' ? 'selected' : ''; ?>" 
                             onclick="selectType('lecture_notes', this)">
                            <div class="type-icon">📄</div>
                            <div class="type-label">Lecture Notes</div>
                        </div>
                        <div class="material-type-option <?php echo $material['category'] === 'video' ? 'selected' : ''; ?>" 
                             onclick="selectType('video', this)">
                            <div class="type-icon">🎥</div>
                            <div class="type-label">Video</div>
                        </div>
                        <div class="material-type-option <?php echo $material['category'] === 'assignment' ? 'selected' : ''; ?>" 
                             onclick="selectType('assignment', this)">
                            <div class="type-icon">📝</div>
                            <div class="type-label">Assignment</div>
                        </div>
                        <div class="material-type-option <?php echo $material['category'] === 'reference' ? 'selected' : ''; ?>" 
                             onclick="selectType('reference', this)">
                            <div class="type-icon">📚</div>
                            <div class="type-label">Reference</div>
                        </div>
                        <div class="material-type-option <?php echo $material['category'] === 'quiz' ? 'selected' : ''; ?>" 
                             onclick="selectType('quiz', this)">
                            <div class="type-icon">🎯</div>
                            <div class="type-label">Quiz</div>
                        </div>
                    </div>
                    <input type="hidden" name="category" id="categoryInput" 
                           value="<?php echo htmlspecialchars($material['category']); ?>">
                </div>
                
                <!-- File Upload -->
                <div class="form-section">
                    <div class="section-title">📤 Update File (Optional)</div>
                    <p class="help-text" style="margin-bottom: 15px;">Leave empty to keep existing file</p>
                    <div class="file-upload-area" onclick="document.getElementById('fileInput').click()">
                        <div class="file-upload-icon">📁</div>
                        <div class="file-upload-text">Click to upload</div>
                        <div class="file-upload-subtext">PDF, DOC, PPT, MP4, ZIP (Max 100MB)</div>
                    </div>
                    <input type="file" name="new_file" id="fileInput" class="file-input">
                    
                    <?php if ($material['file_path']): ?>
                        <p style="margin-top: 15px; color: rgba(255,255,255,0.6);">
                            Current file: <?php echo basename($material['file_path']); ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <!-- Description -->
                <div class="form-section">
                    <div class="section-title">✍️ Description</div>
                    <div class="form-group">
                        <label class="form-label">Short Description</label>
                        <input type="text" name="short_description" class="form-input" 
                               value="<?php echo htmlspecialchars($material['short_description'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Full Description <span class="required">*</span></label>
                        <textarea name="full_description" class="form-textarea" required><?php echo htmlspecialchars($material['description'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <!-- Metadata -->
                <div class="form-section">
                    <div class="section-title">🔖 Metadata</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Course Week</label>
                            <select name="week_number" class="form-select">
                                <option value="">Select Week</option>
                                <?php for ($i = 1; $i <= 16; $i++): ?>
                                    <option value="<?php echo $i; ?>" 
                                            <?php echo $material['week_number'] == $i ? 'selected' : ''; ?>>
                                        Week <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Difficulty Level</label>
                            <select name="difficulty_level" class="form-select">
                                <option value="">Select Level</option>
                                <option value="beginner" <?php echo $material['difficulty_level'] === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                                <option value="intermediate" <?php echo $material['difficulty_level'] === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                <option value="advanced" <?php echo $material['difficulty_level'] === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Tags -->
                <div class="form-section">
                    <div class="section-title">🏷️ Tags</div>
                    <div class="tags-container" id="tagsContainer">
                        <?php foreach ($existing_tags as $tag): ?>
                            <div class="tag">
                                <?php echo htmlspecialchars(trim($tag)); ?>
                                <span class="tag-remove" onclick="removeTag(this)">×</span>
                            </div>
                        <?php endforeach; ?>
                        <input type="text" class="tags-input" id="tagsInput" placeholder="Add tag and press Enter...">
                    </div>
                    <input type="hidden" name="tags" id="tagsHidden" value="<?php echo htmlspecialchars($material['tags'] ?? ''); ?>">
                </div>
                
                <!-- Settings -->
                <div class="form-section">
                    <div class="section-title">⚙️ Settings</div>
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="is_published" 
                                   <?php echo $material['is_published'] ? 'checked' : ''; ?>>
                            <span>Published for students</span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="allow_comments" 
                                   <?php echo $material['allow_comments'] ? 'checked' : ''; ?>>
                            <span>Allow comments</span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="allow_download" 
                                   <?php echo $material['allow_download'] ? 'checked' : ''; ?>>
                            <span>Allow downloads</span>
                        </label>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="form-actions">
                    <a href="edit-materials.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">💾 Update Material</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function selectType(type, element) {
            document.querySelectorAll('.material-type-option').forEach(el => {
                el.classList.remove('selected');
            });
            element.classList.add('selected');
            document.getElementById('categoryInput').value = type;
        }
        
        // Tags handling
        const tagsInput = document.getElementById('tagsInput');
        const tagsHidden = document.getElementById('tagsHidden');
        const tagsContainer = document.getElementById('tagsContainer');
        
        tagsInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && this.value.trim()) {
                e.preventDefault();
                addTag(this.value.trim());
                this.value = '';
            }
        });
        
        function addTag(tagText) {
            const tag = document.createElement('div');
            tag.className = 'tag';
            tag.innerHTML = `
                ${tagText}
                <span class="tag-remove" onclick="removeTag(this)">×</span>
            `;
            tagsContainer.insertBefore(tag, tagsInput);
            updateTagsHidden();
        }
        
        function removeTag(element) {
            element.parentElement.remove();
            updateTagsHidden();
        }
        
        function updateTagsHidden() {
            const tags = [];
            document.querySelectorAll('.tag').forEach(tag => {
                const text = tag.textContent.trim().replace('×', '').trim();
                if (text) tags.push(text);
            });
            tagsHidden.value = tags.join(',');
        }
    </script>
</body>
</html>