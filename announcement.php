<?php
require_once 'config.php';
session_start();

// Check if user is logged in and is a teacher
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Get course_id from URL if provided
$preselected_course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

$conn = getDBConnection();

// Fetch teacher's courses
$courses_query = "
    SELECT 
        c.course_id,
        c.course_code,
        c.title,
        c.icon,
        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id) as student_count
    FROM courses c
    WHERE c.instructor_id = ?
    ORDER BY c.title
";
$courses_stmt = $conn->prepare($courses_query);
$courses_stmt->bind_param("i", $user_id);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();
$teacher_courses = $courses_result->fetch_all(MYSQLI_ASSOC);
$courses_stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_ids = isset($_POST['course_ids']) ? explode(',', $_POST['course_ids']) : [];
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $priority = $_POST['priority'];
    $send_notification = isset($_POST['send_notification']) ? 1 : 0;
    
    // Validation
    if (empty($course_ids)) {
        $error_message = "Please select at least one course";
    } elseif (empty($title)) {
        $error_message = "Please enter an announcement title";
    } elseif (empty($message)) {
        $error_message = "Please enter the announcement message";
    } else {
        // Insert announcement for each selected course
        $success_count = 0;
        
        foreach ($course_ids as $course_id) {
            $course_id = intval($course_id);
            
            // Verify the course belongs to this teacher
            $verify_query = "SELECT course_id FROM courses WHERE course_id = ? AND instructor_id = ?";
            $verify_stmt = $conn->prepare($verify_query);
            $verify_stmt->bind_param("ii", $course_id, $user_id);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            
            if ($verify_result->num_rows > 0) {
                // Set icon based on priority
                $icon = '📌';
                if ($priority === 'low') $icon = '📚';
                if ($priority === 'high') $icon = '🔴';
                
                $insert_query = "
                    INSERT INTO announcements (course_id, posted_by, title, message, priority, icon, send_notification)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->bind_param("iissssi", $course_id, $user_id, $title, $message, $priority, $icon, $send_notification);
                
                if ($insert_stmt->execute()) {
                    $success_count++;
                }
                $insert_stmt->close();
            }
            $verify_stmt->close();
        }
        
        if ($success_count > 0) {
            $success_message = "Announcement posted successfully to {$success_count} course(s)!";
            // Redirect after 2 seconds
            header("refresh:2;url=tdashboard.php");
        } else {
            $error_message = "Failed to post announcement. Please try again.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Announcement - EduHub</title>
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
        
        .alert {
            padding: 16px 20px;
            border-radius: 16px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
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
            min-height: 150px;
        }
        
        .help-text {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 8px;
        }
        
        .character-count {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 8px;
            text-align: right;
        }
        
        .course-selector {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 15px;
        }
        
        .course-option {
            padding: 18px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .course-option:hover {
            background: rgba(102, 126, 234, 0.1);
            border-color: rgba(102, 126, 234, 0.3);
        }
        
        .course-option.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .course-icon {
            font-size: 32px;
            margin-bottom: 8px;
        }
        
        .course-code {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 8px;
        }
        
        .course-name {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .course-students {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .course-option.selected .course-code,
        .course-option.selected .course-students {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .priority-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px;
        }
        
        .priority-option {
            padding: 18px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .priority-option:hover {
            background: rgba(255, 255, 255, 0.08);
        }
        
        .priority-option.selected {
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
        }
        
        .priority-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .priority-label {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .priority-desc {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .priority-option.selected .priority-desc {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 12px;
        }
        
        .checkbox-item:hover {
            background: rgba(255, 255, 255, 0.08);
        }
        
        .checkbox-item input[type="checkbox"] {
            cursor: pointer;
            width: 20px;
            height: 20px;
        }
        
        .checkbox-label {
            flex: 1;
            cursor: pointer;
        }
        
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
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
            max-width: 400px;
            text-align: right;
            word-break: break-word;
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
        
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
            display: flex;
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
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        @media (max-width: 768px) {
            .course-selector {
                grid-template-columns: 1fr;
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
            <h1 class="page-title">Post Announcement</h1>
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
            
            <?php if (empty($teacher_courses)): ?>
                <div class="empty-state">
                    <div style="font-size: 64px; margin-bottom: 20px;">📚</div>
                    <h3>No Courses Found</h3>
                    <p style="margin-top: 10px;">You need to create a course before posting announcements.</p>
                    <a href="createcourse.php" class="btn btn-primary" style="margin-top: 20px; display: inline-block;">Create Course</a>
                </div>
            <?php else: ?>
            
            <form id="announcementForm" method="POST" action="">
                <div class="info-box">
                    📢 Share important updates with your students. Announcements will be posted to all selected courses.
                </div>
                
                <div class="form-section">
                    <div class="section-title">🎓 Select Courses</div>
                    <div class="form-group">
                        <label class="form-label">Post to Courses <span class="required">*</span></label>
                        <div class="course-selector" id="courseSelector">
                            <?php foreach ($teacher_courses as $course): ?>
                                <div class="course-option <?php echo ($preselected_course_id == $course['course_id']) ? 'selected' : ''; ?>" 
                                     data-course-id="<?php echo $course['course_id']; ?>" 
                                     data-course-name="<?php echo htmlspecialchars($course['title']); ?>" 
                                     data-students="<?php echo $course['student_count']; ?>">
                                    <div class="course-icon"><?php echo htmlspecialchars($course['icon'] ?? '📚'); ?></div>
                                    <div class="course-code"><?php echo htmlspecialchars($course['course_code'] ?? 'N/A'); ?></div>
                                    <div class="course-name"><?php echo htmlspecialchars($course['title']); ?></div>
                                    <div class="course-students"><?php echo $course['student_count']; ?> students</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="help-text">Select one or more courses to post this announcement to</p>
                        <input type="hidden" name="course_ids" id="selectedCourses" value="<?php echo $preselected_course_id > 0 ? $preselected_course_id : ''; ?>">
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">📝 Announcement Content</div>
                    
                    <div class="form-group">
                        <label class="form-label">Title <span class="required">*</span></label>
                        <input type="text" class="form-input" name="title" id="title" placeholder="e.g., Midterm Exam Schedule Update" maxlength="255" required>
                        <div class="character-count"><span id="titleCount">0</span>/255</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Message <span class="required">*</span></label>
                        <textarea class="form-textarea" name="message" id="message" placeholder="Write your announcement message here..." required></textarea>
                        <div class="character-count"><span id="messageCount">0</span> characters</div>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">⚡ Priority & Notifications</div>
                    
                    <div class="form-group">
                        <label class="form-label">Priority Level <span class="required">*</span></label>
                        <div class="priority-options" id="priorityOptions">
                            <div class="priority-option" data-priority="low">
                                <div class="priority-icon">📚</div>
                                <div class="priority-label">Low</div>
                                <div class="priority-desc">General information</div>
                            </div>
                            <div class="priority-option selected" data-priority="medium">
                                <div class="priority-icon">⏰</div>
                                <div class="priority-label">Medium</div>
                                <div class="priority-desc">Important update</div>
                            </div>
                            <div class="priority-option" data-priority="high">
                                <div class="priority-icon">🔴</div>
                                <div class="priority-label">High</div>
                                <div class="priority-desc">Urgent/Critical</div>
                            </div>
                        </div>
                        <input type="hidden" name="priority" id="selectedPriority" value="medium" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="send_notification" value="1">
                            <div class="checkbox-label">📧 Send notification to students (appears in their notifications)</div>
                        </label>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">👁️ Preview</div>
                    <div class="preview-card">
                        <div class="preview-title" id="previewTitle">Announcement Title</div>
                        <div class="preview-item">
                            <span class="preview-label">Posting To:</span>
                            <span class="preview-value" id="previewCourses">No courses selected</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Recipients:</span>
                            <span class="preview-value" id="previewRecipients">0 students</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Priority:</span>
                            <span class="preview-value" id="previewPriority">⏰ Medium</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="tdashboard.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">📢 Post Announcement</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        const courseOptions = document.querySelectorAll('.course-option');
        const priorityOptions = document.querySelectorAll('.priority-option');
        const titleInput = document.getElementById('title');
        const messageInput = document.getElementById('message');
        
        let selectedCourses = [];
        let selectedPriority = 'medium';
        
        // Initialize selected courses from preselected
        <?php if ($preselected_course_id > 0): ?>
        courseOptions.forEach(option => {
            if (option.dataset.courseId == <?php echo $preselected_course_id; ?>) {
                selectedCourses.push({
                    id: option.dataset.courseId,
                    name: option.dataset.courseName,
                    students: parseInt(option.dataset.students)
                });
            }
        });
        <?php endif; ?>
        
        // Course Selection
        courseOptions.forEach(option => {
            option.addEventListener('click', function() {
                this.classList.toggle('selected');
                updateCourseSelection();
            });
        });
        
        function updateCourseSelection() {
            selectedCourses = [];
            let totalStudents = 0;
            
            courseOptions.forEach(option => {
                if (option.classList.contains('selected')) {
                    selectedCourses.push({
                        id: option.dataset.courseId,
                        name: option.dataset.courseName,
                        students: parseInt(option.dataset.students)
                    });
                    totalStudents += parseInt(option.dataset.students);
                }
            });
            
            document.getElementById('selectedCourses').value = selectedCourses.map(c => c.id).join(',');
            updatePreview();
        }
        
        // Priority Selection
        priorityOptions.forEach(option => {
            option.addEventListener('click', function() {
                priorityOptions.forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                selectedPriority = this.dataset.priority;
                document.getElementById('selectedPriority').value = selectedPriority;
                updatePreview();
            });
        });
        
        // Character Count
        titleInput.addEventListener('input', function() {
            document.getElementById('titleCount').textContent = this.value.length;
            updatePreview();
        });
        
        messageInput.addEventListener('input', function() {
            document.getElementById('messageCount').textContent = this.value.length;
        });
        
        // Update Preview
        function updatePreview() {
            const title = titleInput.value || 'Announcement Title';
            const coursesText = selectedCourses.length > 0 
                ? selectedCourses.map(c => c.name).join(', ')
                : 'No courses selected';
            const totalStudents = selectedCourses.reduce((sum, c) => sum + c.students, 0);
            
            const priorityIcons = {
                low: '📚 Low',
                medium: '⏰ Medium',
                high: '🔴 High'
            };
            
            document.getElementById('previewTitle').textContent = title;
            document.getElementById('previewCourses').textContent = coursesText;
            document.getElementById('previewRecipients').textContent = totalStudents + ' students';
            document.getElementById('previewPriority').textContent = priorityIcons[selectedPriority];
        }
        
        // Form Validation
        document.getElementById('announcementForm').addEventListener('submit', function(e) {
            if (selectedCourses.length === 0) {
                e.preventDefault();
                alert('Please select at least one course');
                return false;
            }
        });
        
        // Initialize preview on page load
        window.addEventListener('load', updatePreview);
    </script>
</body>
</html>