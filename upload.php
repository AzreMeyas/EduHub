<?php
require_once 'config.php';
session_start();

// Check if user is logged in and is a teacher
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'teacher') {
    header("Location: sdashboard.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

$conn = getDBConnection();

// Fetch teacher's courses for dropdown
$courses_query = "
    SELECT course_id, course_code, title, icon, enrolled_count
    FROM courses
    WHERE instructor_id = ?
    ORDER BY title
";
$courses_stmt = $conn->prepare($courses_query);
$courses_stmt->bind_param("i", $teacher_id);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();
$teacher_courses = $courses_result->fetch_all(MYSQLI_ASSOC);
$courses_stmt->close();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Sanitize and validate input
        $course_id = intval($_POST['course_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $short_description = trim($_POST['short_description'] ?? '');
        $full_description = trim($_POST['full_description'] ?? '');
        $category = $_POST['category'] ?? 'lecture_notes';
        $week_number = !empty($_POST['week_number']) ? intval($_POST['week_number']) : NULL;
        $difficulty = $_POST['difficulty'] ?? NULL;
        $tags = !empty($_POST['tags']) ? explode(',', $_POST['tags']) : [];
        $is_published = isset($_POST['is_published']) ? 1 : 0;
        $allow_comments = isset($_POST['allow_comments']) ? 1 : 0;
        $allow_download = isset($_POST['allow_download']) ? 1 : 0;
        
        // Validation
        if (empty($title) || empty($short_description) || empty($full_description)) {
            throw new Exception("Please fill in all required fields");
        }
        
        if ($course_id === 0) {
            throw new Exception("Please select a course");
        }
        
        // Verify course belongs to teacher
        $verify_stmt = $conn->prepare("SELECT course_id FROM courses WHERE course_id = ? AND instructor_id = ?");
        $verify_stmt->bind_param("ii", $course_id, $teacher_id);
        $verify_stmt->execute();
        if ($verify_stmt->get_result()->num_rows === 0) {
            throw new Exception("Invalid course selected");
        }
        $verify_stmt->close();
        
        // Handle file upload
        $file_path = NULL;
        $file_type = NULL;
        $file_size = NULL;
        
        if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/materials/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_name = $_FILES['material_file']['name'];
            $file_tmp = $_FILES['material_file']['tmp_name'];
            $file_size = $_FILES['material_file']['size'];
            $file_type = $_FILES['material_file']['type'];
            
            // Validate file size (100MB max)
            if ($file_size > 100 * 1024 * 1024) {
                throw new Exception("File size must be less than 100MB");
            }
            
            // Generate unique filename
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $unique_filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file_tmp, $file_path)) {
                throw new Exception("Failed to upload file");
            }
        }
        
        // Get appropriate icon based on category
        $icon_map = [
            'lecture_notes' => '📄',
            'video' => '🎥',
            'assignment' => '📝',
            'reference' => '📚',
            'quiz' => '🎯',
            'other' => '📁'
        ];
        $icon = $icon_map[$category] ?? '📄';
        
        // Insert material
        $insert_query = "
            INSERT INTO materials (
                course_id, uploaded_by, title, description, short_description,
                category, file_path, file_type, file_size, icon,
                week_number, difficulty_level, is_published, 
                allow_comments, allow_download, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param(
            "iissssssisssiii",
            $course_id, $teacher_id, $title, $full_description, $short_description,
            $category, $file_path, $file_type, $file_size, $icon,
            $week_number, $difficulty, $is_published,
            $allow_comments, $allow_download
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to upload material: " . $stmt->error);
        }
        
        $material_id = $stmt->insert_id;
        $stmt->close();
        
        // Insert tags
        if (!empty($tags)) {
            $tag_stmt = $conn->prepare("INSERT INTO material_tags (material_id, tag_name) VALUES (?, ?)");
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (!empty($tag)) {
                    $tag_stmt->bind_param("is", $material_id, $tag);
                    $tag_stmt->execute();
                }
            }
            $tag_stmt->close();
        }
        
        // Update course materials count
        $update_count = $conn->prepare("
            UPDATE courses 
            SET materials_count = (SELECT COUNT(*) FROM materials WHERE course_id = ?)
            WHERE course_id = ?
        ");
        $update_count->bind_param("ii", $course_id, $course_id);
        $update_count->execute();
        $update_count->close();
        
        // Commit transaction
        $conn->commit();
        
        $success_message = "Material uploaded successfully!";
        header("refresh:2;url=courseview.php?id=$course_id");
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        
        // Delete uploaded file if exists
        if (isset($file_path) && file_exists($file_path)) {
            unlink($file_path);
        }
        
        $error_message = $e->getMessage();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Material - EduHub</title>
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

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 16px;
            margin-bottom: 25px;
            font-size: 14px;
            animation: slideDown 0.3s ease;
        }
        
        .alert-error {
            background: rgba(245, 87, 108, 0.15);
            border: 1px solid rgba(245, 87, 108, 0.3);
            color: #ff6b6b;
        }
        
        .alert-success {
            background: rgba(56, 239, 125, 0.15);
            border: 1px solid rgba(56, 239, 125, 0.3);
            color: #38ef7d;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        .form-select option {
            background: #1a1a2e;
            color: white;
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .help-text {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 8px;
        }
        
        /* File Upload */
        .file-upload-area {
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .file-upload-area:hover {
            background: rgba(102, 126, 234, 0.1);
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .file-upload-area.dragover {
            background: rgba(102, 126, 234, 0.2);
            border-color: rgba(102, 126, 234, 0.8);
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
        
        /* File List */
        .file-list {
            margin-top: 20px;
        }
        
        .file-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            margin-bottom: 12px;
        }
        
        .file-item-icon {
            font-size: 32px;
            flex-shrink: 0;
        }
        
        .file-item-info {
            flex: 1;
        }
        
        .file-item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .file-item-size {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .file-remove-btn {
            padding: 8px 12px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            color: #ef4444;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .file-remove-btn:hover {
            background: rgba(239, 68, 68, 0.25);
        }
        
        /* Material Type Selection */
        .material-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
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
        
        /* Tags Input */
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
        
        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        
        /* Preview */
        .preview-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .preview-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .preview-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .preview-item:last-child {
            border-bottom: none;
        }
        
        .preview-label {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }
        
        .preview-value {
            font-weight: 600;
            color: white;
        }
        
        /* Action Buttons */
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
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            flex: 1;
        }
        
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
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

            .container {
                padding: 15px;
            }

            .form-container {
                padding: 25px 20px;
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
            <a href="tdashboard.php" class="back-btn">
                ← Back to Dashboard
            </a>
            <h1 class="page-title">Upload Material</h1>
        </div>
        
        <div class="form-container">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    ⚠️ <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    ✅ <?php echo htmlspecialchars($success_message); ?> Redirecting...
                </div>
            <?php endif; ?>

            <form id="uploadForm" method="POST" action="" enctype="multipart/form-data">
                <!-- Hidden fields -->
                <input type="hidden" name="category" id="category" value="lecture_notes">
                <input type="hidden" name="tags" id="tagsInput">

                <!-- Course Selection -->
                <div class="form-section">
                    <div class="section-title">🎓 Select Course</div>
                    <?php if (empty($teacher_courses)): ?>
                        <div class="info-box">
                            You haven't created any courses yet. <a href="createcourse.php" style="color: #667eea; font-weight: 700;">Create your first course</a>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label class="form-label">Course <span class="required">*</span></label>
                            <select class="form-select" name="course_id" id="selectedCourse" required style="font-size: 16px; padding: 16px 20px;">
                                <option value="">Choose a course to upload material to</option>
                                <?php foreach ($teacher_courses as $course): ?>
                                    <option value="<?php echo $course['course_id']; ?>" 
                                            data-name="<?php echo htmlspecialchars($course['title']); ?>"
                                            data-code="<?php echo htmlspecialchars($course['course_code']); ?>"
                                            data-students="<?php echo $course['enrolled_count']; ?>">
                                        <?php echo htmlspecialchars($course['icon'] ?? '📚'); ?> 
                                        <?php echo htmlspecialchars($course['title']); ?> 
                                        (<?php echo htmlspecialchars($course['course_code']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="help-text">Select the course for which you're uploading this material</p>
                        </div>
                        
                        <!-- Course Info Display -->
                        <div class="preview-card" id="courseInfoCard" style="display: none;">
                            <div class="preview-item">
                                <span class="preview-label">Course Name:</span>
                                <span class="preview-value" id="selectedCourseName">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">Course Code:</span>
                                <span class="preview-value" id="selectedCourseCode">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">Enrolled Students:</span>
                                <span class="preview-value" id="selectedCourseStudents">-</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($teacher_courses)): ?>
                <!-- Info Box -->
                <div class="info-box">
                    📌 Upload course materials to help students learn. Supported formats: PDF, DOC, PPT, MP4, ZIP (Max 100MB)
                </div>
                
                <!-- Material Title -->
                <div class="form-section">
                    <div class="section-title">📝 Basic Information</div>
                    <div class="form-group">
                        <label class="form-label">Material Title <span class="required">*</span></label>
                        <input type="text" class="form-input" name="title" id="materialTitle" placeholder="e.g., Introduction to Algorithms" required>
                        <p class="help-text">Give your material a clear, descriptive title</p>
                    </div>
                </div>
                
                <!-- Material Type -->
                <div class="form-section">
                    <div class="section-title">🏷️ Material Type</div>
                    <div class="material-types">
                        <div class="material-type-option selected" data-type="lecture_notes">
                            <div class="type-icon">📄</div>
                            <div class="type-label">Lecture Notes</div>
                        </div>
                        <div class="material-type-option" data-type="video">
                            <div class="type-icon">🎥</div>
                            <div class="type-label">Video</div>
                        </div>
                        <div class="material-type-option" data-type="assignment">
                            <div class="type-icon">📝</div>
                            <div class="type-label">Assignment</div>
                        </div>
                        <div class="material-type-option" data-type="reference">
                            <div class="type-icon">📚</div>
                            <div class="type-label">Reference</div>
                        </div>
                    </div>
                </div>
                
                <!-- File Upload -->
                <div class="form-section">
                    <div class="section-title">📤 Upload File</div>
                    <div class="file-upload-area" id="fileUploadArea">
                        <div class="file-upload-icon">📁</div>
                        <div class="file-upload-text">Click to upload or drag and drop</div>
                        <div class="file-upload-subtext">PDF, DOC, PPT, MP4, ZIP (Max 100MB)</div>
                        <input type="file" class="file-input" name="material_file" id="fileInput" accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.zip,.rar,.jpg,.jpeg,.png">
                    </div>
                    <div class="file-list" id="fileList"></div>
                </div>
                
                <!-- Description -->
                <div class="form-section">
                    <div class="section-title">✍️ Description</div>
                    <div class="form-group">
                        <label class="form-label">Short Description <span class="required">*</span></label>
                        <input type="text" class="form-input" name="short_description" id="shortDescription" placeholder="Brief overview (one line)" required>
                        <p class="help-text">A short summary that will appear in course listings</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Full Description <span class="required">*</span></label>
                        <textarea class="form-textarea" name="full_description" id="fullDescription" placeholder="Detailed description, learning objectives, and key topics..." required></textarea>
                        <p class="help-text">Include what students will learn, prerequisites, and any important notes</p>
                    </div>
                </div>
                
                <!-- Metadata -->
                <div class="form-section">
                    <div class="section-title">🔖 Metadata</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Course Week</label>
                            <select class="form-select" name="week_number" id="courseWeek">
                                <option value="">Select Week</option>
                                <?php for ($i = 1; $i <= 16; $i++): ?>
                                    <option value="<?php echo $i; ?>">Week <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Difficulty Level</label>
                            <select class="form-select" name="difficulty" id="difficulty">
                                <option value="">Select Level</option>
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Tags -->
                <div class="form-section">
                    <div class="section-title">🏷️ Tags</div>
                    <label class="form-label">Add Tags</label>
                    <div class="tags-container" id="tagsContainer">
                        <input type="text" class="tags-input" id="tagsInputField" placeholder="Type tag and press Enter...">
                    </div>
                    <p class="help-text">Add relevant tags to help students find this material (e.g., algorithms, sorting, important)</p>
                </div>
                
                <!-- Visibility & Settings -->
                <div class="form-section">
                    <div class="section-title">👁️ Visibility & Settings</div>
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="is_published" id="isPublished" checked>
                            <span>Publish immediately for students</span>
                        </label>
                        <p class="help-text">Uncheck to save as draft</p>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="allow_comments" id="allowComments" checked>
                            <span>Allow student comments and discussions</span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="allow_download" id="allowDownload" checked>
                            <span>Allow students to download</span>
                        </label>
                    </div>
                </div>
                
                <!-- Preview -->
                <div class="form-section">
                    <div class="section-title">👁️ Preview</div>
                    <div class="preview-card">
                        <div class="preview-title" id="previewTitle">Material Title</div>
                        <div class="preview-item">
                            <span class="preview-label">Type:</span>
                            <span class="preview-value" id="previewType">Lecture Notes</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Week:</span>
                            <span class="preview-value" id="previewWeek">Not selected</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Difficulty:</span>
                            <span class="preview-value" id="previewDifficulty">Not selected</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">File:</span>
                            <span class="preview-value" id="previewFiles">No file selected</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Status:</span>
                            <span class="preview-value" id="previewStatus">Published</span>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="form-actions">
                    <a href="tdashboard.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">🚀 Upload Material</button>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <script>
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');
        const tagsContainer = document.getElementById('tagsContainer');
        const tagsInputField = document.getElementById('tagsInputField');
        const materialTypeOptions = document.querySelectorAll('.material-type-option');
        const uploadForm = document.getElementById('uploadForm');
        const courseSelect = document.getElementById('selectedCourse');
        const courseInfoCard = document.getElementById('courseInfoCard');
        
        let uploadedFile = null;
        let tags = [];
        
        // Course Selection Handler
        if (courseSelect) {
            courseSelect.addEventListener('change', function() {
                if (this.value) {
                    const selectedOption = this.options[this.selectedIndex];
                    document.getElementById('selectedCourseName').textContent = selectedOption.dataset.name;
                    document.getElementById('selectedCourseCode').textContent = selectedOption.dataset.code;
                    document.getElementById('selectedCourseStudents').textContent = selectedOption.dataset.students + ' students';
                    courseInfoCard.style.display = 'block';
                } else {
                    courseInfoCard.style.display = 'none';
                }
            });
        }
        
        // File Upload - Click
        fileUploadArea.addEventListener('click', () => fileInput.click());
        
        // File Upload - Drag and Drop
        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });
        
        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('dragover');
        });
        
        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                handleFile(e.dataTransfer.files[0]);
            }
        });
        
        // File Input Change
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFile(e.target.files[0]);
            }
        });
        
        // Handle File
        function handleFile(file) {
            uploadedFile = file;
            addFileToList(file);
            updatePreview();
        }
        
        // Add File to List
        function addFileToList(file) {
            const fileIcon = getFileIcon(file.type, file.name);
            const fileSize = formatFileSize(file.size);
            
            fileList.innerHTML = `
                <div class="file-item">
                    <div class="file-item-icon">${fileIcon}</div>
                    <div class="file-item-info">
                        <div class="file-item-name">${file.name}</div>
                        <div class="file-item-size">${fileSize}</div>
                    </div>
                    <div class="file-item-actions">
                        <button type="button" class="file-remove-btn" onclick="removeFile()">Remove</button>
                    </div>
                </div>
            `;
        }
        
        // Remove File
        function removeFile() {
            uploadedFile = null;
            fileInput.value = '';
            fileList.innerHTML = '';
            updatePreview();
        }
        
        // Get File Icon
        function getFileIcon(fileType, fileName) {
            const ext = fileName.split('.').pop().toLowerCase();
            if (fileType.includes('pdf') || ext === 'pdf') return '📕';
            if (fileType.includes('word') || fileType.includes('document') || ext === 'doc' || ext === 'docx') return '📄';
            if (fileType.includes('presentation') || ext === 'ppt' || ext === 'pptx') return '📊';
            if (fileType.includes('video') || fileType.includes('mp4') || ext === 'mp4') return '🎬';
            if (fileType.includes('zip') || fileType.includes('rar') || ext === 'zip' || ext === 'rar') return '📦';
            if (fileType.includes('image') || ext === 'jpg' || ext === 'jpeg' || ext === 'png') return '🖼️';
            return '📁';
        }
        
        // Format File Size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
        
        // Material Type Selection
        materialTypeOptions.forEach(option => {
            option.addEventListener('click', function() {
                materialTypeOptions.forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                const type = this.dataset.type;
                document.getElementById('category').value = type;
                updatePreview();
            });
        });
        
        // Tags Input
        tagsInputField.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && tagsInputField.value.trim()) {
                e.preventDefault();
                addTag(tagsInputField.value.trim());
                tagsInputField.value = '';
                updatePreview();
            }
        });
        
        function addTag(tagText) {
            if (!tags.includes(tagText)) {
                tags.push(tagText);
                const tag = document.createElement('div');
                tag.className = 'tag';
                tag.innerHTML = `
                    ${tagText}
                    <span class="tag-remove" onclick="removeTag('${tagText.replace(/'/g, "\\'")}')">×</span>
                `;
                tagsContainer.insertBefore(tag, tagsInputField);
                updateTagsInput();
            }
        }
        
        function removeTag(tagText) {
            tags = tags.filter(t => t !== tagText);
            renderTags();
            updatePreview();
        }
        
        function renderTags() {
            const existingTags = tagsContainer.querySelectorAll('.tag');
            existingTags.forEach(tag => tag.remove());
            tags.forEach(tag => addTag(tag));
        }

        function updateTagsInput() {
            document.getElementById('tagsInput').value = tags.join(',');
        }
        
        // Update Preview
        function updatePreview() {
            const title = document.getElementById('materialTitle').value || 'Material Title';
            const typeValue = document.getElementById('category').value;
            const typeLabels = {
                'lecture_notes': 'Lecture Notes',
                'video': 'Video',
                'assignment': 'Assignment',
                'reference': 'Reference'
            };
            const typeLabel = typeLabels[typeValue] || 'Lecture Notes';
            const week = document.getElementById('courseWeek').value || 'Not selected';
            const difficulty = document.getElementById('difficulty').value || 'Not selected';
            const isPublished = document.getElementById('isPublished').checked;
            
            document.getElementById('previewTitle').textContent = title;
            document.getElementById('previewType').textContent = typeLabel;
            document.getElementById('previewWeek').textContent = week ? 'Week ' + week : 'Not selected';
            document.getElementById('previewDifficulty').textContent = difficulty.charAt(0).toUpperCase() + difficulty.slice(1);
            document.getElementById('previewFiles').textContent = uploadedFile ? uploadedFile.name : 'No file selected';
            document.getElementById('previewStatus').textContent = isPublished ? 'Published' : 'Draft';
            document.getElementById('previewStatus').style.color = isPublished ? '#10b981' : '#f97316';
            
            updateTagsInput();
        }
        
        // Real-time Preview Updates
        if (document.getElementById('materialTitle')) {
            document.getElementById('materialTitle').addEventListener('input', updatePreview);
            document.getElementById('courseWeek').addEventListener('change', updatePreview);
            document.getElementById('difficulty').addEventListener('change', updatePreview);
            document.getElementById('isPublished').addEventListener('change', updatePreview);
        }
        
        // Form Submission
        uploadForm.addEventListener('submit', function(e) {
            // Validation
            if (!document.getElementById('selectedCourse') || !document.getElementById('selectedCourse').value) {
                e.preventDefault();
                alert('Please select a course');
                return;
            }
            
            if (!uploadedFile) {
                e.preventDefault();
                alert('Please upload a file');
                return;
            }

            // Check file size (100MB)
            if (uploadedFile.size > 100 * 1024 * 1024) {
                e.preventDefault();
                alert('File size must be less than 100MB');
                return;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Uploading...';
        });
        
        // Initialize preview on page load
        window.addEventListener('load', updatePreview);
    </script>
</body>
</html>