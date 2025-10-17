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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Sanitize and validate input
        $title = trim($_POST['title'] ?? '');
        $course_code = trim($_POST['course_code'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $icon = $_POST['icon'] ?? '💻';
        $color = $_POST['color'] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        $short_description = trim($_POST['short_description'] ?? '');
        $full_description = trim($_POST['full_description'] ?? '');
        $duration = intval($_POST['duration'] ?? 0);
        $credits = intval($_POST['credits'] ?? 3);
        $difficulty = $_POST['difficulty'] ?? 'Beginner';
        $max_students = !empty($_POST['max_students']) ? intval($_POST['max_students']) : NULL;
        
        // Pricing fields
        $is_free = isset($_POST['is_free']) && $_POST['is_free'] === '1';
        $price = !$is_free ? floatval($_POST['price'] ?? 0) : 0.00;
        $currency = $_POST['currency'] ?? 'USD';
        $discount_price = !$is_free && !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : NULL;
        
        // Calculate discount percentage if discount price is set
        $discount_percentage = NULL;
        if ($discount_price && $price > 0 && $discount_price < $price) {
            $discount_percentage = round((($price - $discount_price) / $price) * 100);
        }
        
        $tags = json_decode($_POST['tags'] ?? '[]', true);
        $schedule = json_decode($_POST['schedule'] ?? '[]', true);
        
        // Validation
        if (empty($title) || empty($course_code) || empty($department)) {
            throw new Exception("Please fill in all required fields");
        }
        
        if ($duration < 1) {
            throw new Exception("Duration must be at least 1 week");
        }
        
        if (!$is_free && $price <= 0) {
            throw new Exception("Price must be greater than 0 for paid courses");
        }
        
        if ($discount_price && $discount_price >= $price) {
            throw new Exception("Discount price must be less than the original price");
        }
        
        // Check if course code already exists
        $check_stmt = $conn->prepare("SELECT course_id FROM courses WHERE course_code = ? AND instructor_id = ?");
        $check_stmt->bind_param("si", $course_code, $teacher_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            throw new Exception("Course code already exists. Please use a different code.");
        }
        $check_stmt->close();
        
        // Insert course
        $insert_query = "
            INSERT INTO courses (
                course_code, title, department, description, short_description,
                icon, color, instructor_id, duration_weeks, credits,
                difficulty_level, max_students, is_free, price, currency,
                discount_price, discount_percentage, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', NOW())
        ";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param(
            "sssssssiisiiidsdi",
            $course_code, $title, $department, $full_description, $short_description,
            $icon, $color, $teacher_id, $duration, $credits,
            $difficulty, $max_students, $is_free, $price, $currency,
            $discount_price, $discount_percentage
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create course: " . $stmt->error);
        }
        
        $course_id = $stmt->insert_id;
        $stmt->close();
        
        // Insert tags
        if (!empty($tags) && is_array($tags)) {
            $tag_stmt = $conn->prepare("INSERT INTO course_tags (course_id, tag_name) VALUES (?, ?)");
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (!empty($tag)) {
                    $tag_stmt->bind_param("is", $course_id, $tag);
                    $tag_stmt->execute();
                }
            }
            $tag_stmt->close();
        }
        
        // Insert schedule
        if (!empty($schedule) && is_array($schedule)) {
            $schedule_stmt = $conn->prepare("
                INSERT INTO course_schedule (course_id, day_of_week, start_time, end_time, room_location)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($schedule as $session) {
                if (!empty($session['day']) && !empty($session['start']) && !empty($session['end'])) {
                    $room = !empty($session['room']) ? $session['room'] : NULL;
                    $schedule_stmt->bind_param(
                        "issss",
                        $course_id,
                        $session['day'],
                        $session['start'],
                        $session['end'],
                        $room
                    );
                    $schedule_stmt->execute();
                }
            }
            $schedule_stmt->close();
        }
        
        // Commit transaction
        $conn->commit();
        
        $success_message = "Course created successfully!";
        
        // Redirect to course view or dashboard
        header("refresh:2;url=tdashboard.php");
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $error_message = $e->getMessage();
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course - EduHub</title>
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
            max-width: 1200px;
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
            font-size: 32px;
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
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }
        
        .progress-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(255, 255, 255, 0.1);
            z-index: 0;
        }
        
        .progress-line {
            position: absolute;
            top: 20px;
            left: 0;
            height: 2px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.5s;
            z-index: 1;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
            z-index: 2;
        }
        
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        
        .step.active .step-circle {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4);
        }
        
        .step.completed .step-circle {
            background: rgba(16, 185, 129, 0.2);
            border-color: #10b981;
        }
        
        .step-label {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            text-align: center;
        }
        
        .step.active .step-label {
            color: white;
            font-weight: 600;
        }
        
        .form-section {
            display: none;
        }
        
        .form-section.active {
            display: block;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .section-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .section-subtitle {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 30px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-label .required {
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
        
        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-input::placeholder,
        .form-textarea::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .form-select option {
            background: #1a1a2e;
            color: white;
        }
        
        .thumbnail-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 15px;
        }
        
        .thumbnail-option {
            aspect-ratio: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .thumbnail-option:hover {
            transform: scale(1.05);
        }
        
        .thumbnail-option.selected {
            border-color: #667eea;
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
        }
        
        .tags-input-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            min-height: 54px;
        }
        
        .tag-item {
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
        
        .schedule-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 15px;
        }
        
        .schedule-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .remove-schedule {
            padding: 8px 16px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            color: #ef4444;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            font-size: 13px;
        }
        
        .remove-schedule:hover {
            background: rgba(239, 68, 68, 0.25);
        }
        
        .add-schedule-btn {
            width: 100%;
            padding: 14px;
            background: rgba(102, 126, 234, 0.15);
            border: 2px dashed rgba(102, 126, 234, 0.3);
            border-radius: 16px;
            color: #667eea;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .add-schedule-btn:hover {
            background: rgba(102, 126, 234, 0.25);
        }
        
        .preview-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
        }
        
        .preview-header {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .preview-thumbnail {
            width: 100px;
            height: 100px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            flex-shrink: 0;
        }
        
        .preview-info h3 {
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .preview-meta {
            display: flex;
            gap: 20px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .preview-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .preview-section h4 {
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .preview-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
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
        
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:disabled {
            opacity: 0.6;
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
        
        .help-text {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 8px;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .progress-steps {
                flex-wrap: wrap;
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
            <h1 class="page-title">Create New Course</h1>
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

            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="progress-line" id="progressLine"></div>
                <div class="step active" data-step="1">
                    <div class="step-circle">1</div>
                    <div class="step-label">Basic Info</div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-circle">2</div>
                    <div class="step-label">Details</div>
                </div>
                <div class="step" data-step="3">
                    <div class="step-circle">3</div>
                    <div class="step-label">Schedule</div>
                </div>
                <div class="step" data-step="4">
                    <div class="step-circle">4</div>
                    <div class="step-label">Preview</div>
                </div>
            </div>
            
            <form id="createCourseForm" method="POST" action="">
                <!-- Hidden fields for icon data -->
                <input type="hidden" name="icon" id="iconInput" value="💻">
                <input type="hidden" name="color" id="colorInput" value="linear-gradient(135deg, #667eea 0%, #764ba2 100%)">
                <input type="hidden" name="tags" id="tagsInput">
                <input type="hidden" name="schedule" id="scheduleInput">

                <!-- Step 1: Basic Information -->
                <div class="form-section active" data-section="1">
                    <h2 class="section-title">Basic Information</h2>
                    <p class="section-subtitle">Let's start with the fundamentals of your course</p>
                    
                    <div class="form-group">
                        <label class="form-label">Course Title <span class="required">*</span></label>
                        <input type="text" class="form-input" name="title" id="courseTitle" placeholder="e.g., Advanced Computer Science" required>
                        <p class="help-text">Choose a clear, descriptive title for your course</p>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Course Code <span class="required">*</span></label>
                            <input type="text" class="form-input" name="course_code" id="courseCode" placeholder="e.g., CS-401" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Department <span class="required">*</span></label>
                            <select class="form-select" name="department" id="department" required>
                                <option value="">Select Department</option>
                                <option>Computer Science</option>
                                <option>Engineering</option>
                                <option>Mathematics</option>
                                <option>Physics</option>
                                <option>Business</option>
                                <option>Arts & Humanities</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Course Icon/Emoji</label>
                        <div class="thumbnail-selector">
                            <div class="thumbnail-option selected" data-icon="💻" data-color="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">💻</div>
                            <div class="thumbnail-option" data-icon="🚀" data-color="linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">🚀</div>
                            <div class="thumbnail-option" data-icon="📊" data-color="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">📊</div>
                            <div class="thumbnail-option" data-icon="🧪" data-color="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">🧪</div>
                            <div class="thumbnail-option" data-icon="📐" data-color="linear-gradient(135deg, #fa709a 0%, #fee140 100%)" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">📐</div>
                            <div class="thumbnail-option" data-icon="🎨" data-color="linear-gradient(135deg, #30cfd0 0%, #330867 100%)" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">🎨</div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 2: Course Details -->
                <div class="form-section" data-section="2">
                    <h2 class="section-title">Course Details</h2>
                    <p class="section-subtitle">Provide more information about your course</p>
                    
                    <div class="form-group">
                        <label class="form-label">Short Description <span class="required">*</span></label>
                        <textarea class="form-textarea" name="short_description" id="shortDescription" placeholder="Brief overview of the course (2-3 sentences)" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Full Description <span class="required">*</span></label>
                        <textarea class="form-textarea" name="full_description" id="fullDescription" style="min-height: 180px;" placeholder="Detailed course description, learning objectives, and prerequisites" required></textarea>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Duration (Weeks) <span class="required">*</span></label>
                            <input type="number" class="form-input" name="duration" id="duration" placeholder="e.g., 12" min="1" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Credits</label>
                            <input type="number" class="form-input" name="credits" id="credits" placeholder="e.g., 3" min="1" value="3">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Difficulty Level <span class="required">*</span></label>
                            <select class="form-select" name="difficulty" id="difficulty" required>
                                <option value="">Select Level</option>
                                <option>Beginner</option>
                                <option>Intermediate</option>
                                <option>Advanced</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Max Students</label>
                            <input type="number" class="form-input" name="max_students" id="maxStudents" placeholder="Leave empty for unlimited" min="1">
                        </div>
                    </div>

                    <!-- Pricing Section -->
                    <div class="form-group full-width" style="margin-top: 30px; padding-top: 30px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                        <h3 style="font-size: 18px; margin-bottom: 20px;">💰 Course Pricing</h3>
                        
                        <div class="form-group">
                            <label class="form-label">Course Type <span class="required">*</span></label>
                            <div style="display: flex; gap: 15px;">
                                <label style="flex: 1; padding: 20px; background: rgba(255, 255, 255, 0.05); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 16px; cursor: pointer; transition: all 0.3s;" class="pricing-option" data-type="free">
                                    <input type="radio" name="is_free" value="1" checked style="display: none;">
                                    <div style="font-size: 24px; margin-bottom: 8px;">🎁</div>
                                    <div style="font-weight: 700; margin-bottom: 4px;">Free Course</div>
                                    <div style="font-size: 13px; color: rgba(255, 255, 255, 0.6);">Open to all students</div>
                                </label>
                                
                                <label style="flex: 1; padding: 20px; background: rgba(255, 255, 255, 0.05); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 16px; cursor: pointer; transition: all 0.3s;" class="pricing-option" data-type="paid">
                                    <input type="radio" name="is_free" value="0" style="display: none;">
                                    <div style="font-size: 24px; margin-bottom: 8px;">💳</div>
                                    <div style="font-weight: 700; margin-bottom: 4px;">Paid Course</div>
                                    <div style="font-size: 13px; color: rgba(255, 255, 255, 0.6);">Requires payment</div>
                                </label>
                            </div>
                        </div>
                        
                        <div id="pricingFields" style="display: none; margin-top: 20px;">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Price <span class="required">*</span></label>
                                    <div style="position: relative;">
                                        <select class="form-select" name="currency" id="currency" style="position: absolute; width: 80px; height: 100%; border-right: 1px solid rgba(255, 255, 255, 0.1);">
                                            <option value="USD">USD</option>
                                            <option value="EUR">EUR</option>
                                            <option value="GBP">GBP</option>
                                            <option value="BDT">BDT</option>
                                        </select>
                                        <input type="number" class="form-input" name="price" id="price" placeholder="49.99" step="0.01" min="0" style="padding-left: 95px;">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Discount Price (Optional)</label>
                                    <input type="number" class="form-input" name="discount_price" id="discountPrice" placeholder="39.99" step="0.01" min="0">
                                    <p class="help-text">Leave empty for no discount</p>
                                </div>
                            </div>
                            
                            <div id="discountPreview" style="display: none; padding: 15px; background: rgba(102, 126, 234, 0.1); border: 1px solid rgba(102, 126, 234, 0.3); border-radius: 12px; margin-top: 15px;">
                                <div style="font-size: 13px; color: rgba(255, 255, 255, 0.7); margin-bottom: 5px;">Students will see:</div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span id="originalPricePreview" style="text-decoration: line-through; color: rgba(255, 255, 255, 0.4); font-size: 18px;"></span>
                                    <span id="discountedPricePreview" style="font-size: 24px; font-weight: 800; color: #667eea;"></span>
                                    <span id="discountBadgePreview" style="padding: 4px 10px; background: rgba(239, 68, 68, 0.2); color: #ef4444; border-radius: 8px; font-size: 12px; font-weight: 700;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tags</label>
                        <div class="tags-input-container" id="tagsContainer">
                            <input type="text" class="tags-input" id="tagsInputField" placeholder="Type and press Enter to add tags...">
                        </div>
                        <p class="help-text">Add relevant tags to help students find your course</p>
                    </div>
                </div>
                
                <!-- Step 3: Schedule -->
                <div class="form-section" data-section="3">
                    <h2 class="section-title">Course Schedule</h2>
                    <p class="section-subtitle">Define when and where your course will meet</p>
                    
                    <div id="scheduleContainer">
                        <div class="schedule-item">
                            <div class="schedule-header">
                                <h4 style="font-size: 16px; font-weight: 600;">Class Session 1</h4>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Day</label>
                                    <select class="form-select schedule-day">
                                        <option>Monday</option>
                                        <option>Tuesday</option>
                                        <option>Wednesday</option>
                                        <option>Thursday</option>
                                        <option>Friday</option>
                                        <option>Saturday</option>
                                        <option>Sunday</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Room/Location</label>
                                    <input type="text" class="form-input schedule-room" placeholder="e.g., Room 301">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Start Time</label>
                                    <input type="time" class="form-input schedule-start">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">End Time</label>
                                    <input type="time" class="form-input schedule-end">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="add-schedule-btn" onclick="addSchedule()">
                        + Add Another Session
                    </button>
                </div>
                
                <!-- Step 4: Preview & Submit -->
                <div class="form-section" data-section="4">
                    <h2 class="section-title">Preview Your Course</h2>
                    <p class="section-subtitle">Review all information before creating</p>
                    
                    <div class="preview-card">
                        <div class="preview-header">
                            <div class="preview-thumbnail" id="previewIcon">💻</div>
                            <div class="preview-info">
                                <h3 id="previewTitle">Course Title</h3>
                                <div class="preview-meta">
                                    <span id="previewCode">CS-000</span>
                                    <span id="previewDept">Department</span>
                                    <span id="previewDuration">0 weeks</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="preview-section">
                            <h4>Description</h4>
                            <p id="previewDescription" style="color: rgba(255,255,255,0.7); line-height: 1.6;"></p>
                        </div>
                        
                        <div class="preview-section">
                            <h4>Course Details</h4>
                            <div class="form-grid">
                                <div>
                                    <p style="color: rgba(255,255,255,0.6); font-size: 13px; margin-bottom: 5px;">Difficulty</p>
                                    <p id="previewDifficulty" style="font-weight: 600;">-</p>
                                </div>
                                <div>
                                    <p style="color: rgba(255,255,255,0.6); font-size: 13px; margin-bottom: 5px;">Max Students</p>
                                    <p id="previewMaxStudents" style="font-weight: 600;">-</p>
                                </div>
                                <div>
                                    <p style="color: rgba(255,255,255,0.6); font-size: 13px; margin-bottom: 5px;">Credits</p>
                                    <p id="previewCredits" style="font-weight: 600;">-</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="preview-section" id="previewTagsSection" style="display: none;">
                            <h4>Tags</h4>
                            <div class="preview-tags" id="previewTags"></div>
                        </div>
                        
                        <div class="preview-section" id="previewScheduleSection" style="display: none;">
                            <h4>Schedule</h4>
                            <div id="previewSchedule"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation Buttons -->
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="prevBtn" onclick="prevStep()" style="display: none;">
                        ← Previous
                    </button>
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextStep()">
                        Next →
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">
                        🎉 Create Course
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let currentStep = 1;
        const totalSteps = 4;
        let tags = [];
        let schedules = 1;
        let selectedIcon = '💻';
        let selectedIconGradient = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        
        // Thumbnail selection
        document.querySelectorAll('.thumbnail-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.thumbnail-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                selectedIcon = this.dataset.icon;
                selectedIconGradient = this.dataset.color;
                document.getElementById('iconInput').value = selectedIcon;
                document.getElementById('colorInput').value = selectedIconGradient;
            });
        });
        
        // Tags functionality
        const tagsInputField = document.getElementById('tagsInputField');
        const tagsContainer = document.getElementById('tagsContainer');
        
        tagsInputField.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && this.value.trim()) {
                e.preventDefault();
                addTag(this.value.trim());
                this.value = '';
            }
        });
        
        function addTag(tagText) {
            if (!tags.includes(tagText)) {
                tags.push(tagText);
                const tagElement = document.createElement('div');
                tagElement.className = 'tag-item';
                tagElement.innerHTML = `
                    ${tagText}
                    <span class="tag-remove" onclick="removeTag('${tagText.replace(/'/g, "\\'")}')">×</span>
                `;
                tagsContainer.insertBefore(tagElement, tagsInputField);
                updateTagsInput();
            }
        }
        
        function removeTag(tagText) {
            tags = tags.filter(t => t !== tagText);
            updateTagsDisplay();
            updateTagsInput();
        }
        
        function updateTagsDisplay() {
            const existingTags = tagsContainer.querySelectorAll('.tag-item');
            existingTags.forEach(tag => tag.remove());
            tags.forEach(tag => {
                const tagElement = document.createElement('div');
                tagElement.className = 'tag-item';
                tagElement.innerHTML = `
                    ${tag}
                    <span class="tag-remove" onclick="removeTag('${tag.replace(/'/g, "\\'")}')">×</span>
                `;
                tagsContainer.insertBefore(tagElement, tagsInputField);
            });
        }

        function updateTagsInput() {
            document.getElementById('tagsInput').value = JSON.stringify(tags);
        }
        
        // Schedule management
        function addSchedule() {
            schedules++;
            const scheduleHTML = `
                <div class="schedule-item">
                    <div class="schedule-header">
                        <h4 style="font-size: 16px; font-weight: 600;">Class Session ${schedules}</h4>
                        <button type="button" class="remove-schedule" onclick="this.closest('.schedule-item').remove()">
                            Remove
                        </button>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Day</label>
                            <select class="form-select schedule-day">
                                <option>Monday</option>
                                <option>Tuesday</option>
                                <option>Wednesday</option>
                                <option>Thursday</option>
                                <option>Friday</option>
                                <option>Saturday</option>
                                <option>Sunday</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Room/Location</label>
                            <input type="text" class="form-input schedule-room" placeholder="e.g., Room 301">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Start Time</label>
                            <input type="time" class="form-input schedule-start">
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Time</label>
                            <input type="time" class="form-input schedule-end">
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('scheduleContainer').insertAdjacentHTML('beforeend', scheduleHTML);
        }
        
        // Step navigation
        function updateProgressBar() {
            const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
            document.getElementById('progressLine').style.width = progress + '%';
            
            document.querySelectorAll('.step').forEach((step, index) => {
                const stepNum = index + 1;
                step.classList.remove('active', 'completed');
                
                if (stepNum < currentStep) {
                    step.classList.add('completed');
                    step.querySelector('.step-circle').innerHTML = '✓';
                } else if (stepNum === currentStep) {
                    step.classList.add('active');
                    step.querySelector('.step-circle').innerHTML = stepNum;
                } else {
                    step.querySelector('.step-circle').innerHTML = stepNum;
                }
            });
        }
        
        function showStep(step) {
            document.querySelectorAll('.form-section').forEach(section => {
                section.classList.remove('active');
            });
            
            document.querySelector(`[data-section="${step}"]`).classList.add('active');
            
            // Update buttons
            document.getElementById('prevBtn').style.display = step === 1 ? 'none' : 'block';
            document.getElementById('nextBtn').style.display = step === totalSteps ? 'none' : 'block';
            document.getElementById('submitBtn').style.display = step === totalSteps ? 'block' : 'none';
            
            // Update preview if on last step
            if (step === totalSteps) {
                updatePreview();
            }
            
            updateProgressBar();
        }
        
        function nextStep() {
            if (validateStep(currentStep)) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                }
            }
        }
        
        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        }
        
        function validateStep(step) {
            const section = document.querySelector(`[data-section="${step}"]`);
            const requiredFields = section.querySelectorAll('[required]');
            
            for (let field of requiredFields) {
                if (!field.value.trim()) {
                    field.focus();
                    alert('Please fill in all required fields');
                    return false;
                }
            }
            
            return true;
        }
        
        function updatePreview() {
            // Basic info
            const icon = document.getElementById('iconInput').value;
            document.getElementById('previewIcon').innerHTML = icon;
            document.getElementById('previewIcon').style.background = selectedIconGradient;
            
            document.getElementById('previewTitle').textContent = 
                document.getElementById('courseTitle').value || 'Course Title';
            
            document.getElementById('previewCode').textContent = 
                document.getElementById('courseCode').value || 'CS-000';
            
            document.getElementById('previewDept').textContent = 
                document.getElementById('department').value || 'Department';
            
            const duration = document.getElementById('duration').value;
            document.getElementById('previewDuration').textContent = 
                duration ? `${duration} weeks` : '0 weeks';
            
            // Description
            const shortDesc = document.getElementById('shortDescription').value;
            const fullDesc = document.getElementById('fullDescription').value;
            document.getElementById('previewDescription').textContent = 
                shortDesc || fullDesc || 'No description provided';
            
            // Details
            document.getElementById('previewDifficulty').textContent = 
                document.getElementById('difficulty').value || '-';
            
            const maxStudents = document.getElementById('maxStudents').value;
            document.getElementById('previewMaxStudents').textContent = 
                maxStudents || 'Unlimited';
            
            const credits = document.getElementById('credits').value;
            document.getElementById('previewCredits').textContent = 
                credits || '-';
            
            // Tags
            if (tags.length > 0) {
                document.getElementById('previewTagsSection').style.display = 'block';
                const previewTagsContainer = document.getElementById('previewTags');
                previewTagsContainer.innerHTML = '';
                tags.forEach(tag => {
                    const tagElement = document.createElement('span');
                    tagElement.className = 'tag-item';
                    tagElement.textContent = tag;
                    previewTagsContainer.appendChild(tagElement);
                });
            } else {
                document.getElementById('previewTagsSection').style.display = 'none';
            }
            
            // Schedule
            const scheduleItems = document.querySelectorAll('.schedule-item');
            const scheduleData = [];
            
            if (scheduleItems.length > 0) {
                document.getElementById('previewScheduleSection').style.display = 'block';
                const previewSchedule = document.getElementById('previewSchedule');
                previewSchedule.innerHTML = '';
                
                scheduleItems.forEach((item, index) => {
                    const day = item.querySelector('.schedule-day').value;
                    const room = item.querySelector('.schedule-room').value;
                    const start = item.querySelector('.schedule-start').value;
                    const end = item.querySelector('.schedule-end').value;
                    
                    if (day && start && end) {
                        // Store schedule data
                        scheduleData.push({
                            day: day,
                            room: room,
                            start: start,
                            end: end
                        });
                        
                        const scheduleDiv = document.createElement('div');
                        scheduleDiv.style.cssText = 'background: rgba(255,255,255,0.05); padding: 12px; border-radius: 12px; margin-bottom: 8px;';
                        scheduleDiv.innerHTML = `
                            <div style="font-weight: 600; margin-bottom: 4px;">${day} ${start} - ${end}</div>
                            <div style="font-size: 13px; color: rgba(255,255,255,0.6);">${room || 'Location TBD'}</div>
                        `;
                        previewSchedule.appendChild(scheduleDiv);
                    }
                });
            }
            
            // Update hidden schedule input
            document.getElementById('scheduleInput').value = JSON.stringify(scheduleData);
        }
        
        // Form submission
        document.getElementById('createCourseForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating Course...';
        });
        
        // Initialize
        showStep(currentStep);

        // Pricing type selection
        let isFree = true;

        document.querySelectorAll('.pricing-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.pricing-option').forEach(o => {
                    o.style.borderColor = 'rgba(255, 255, 255, 0.1)';
                    o.style.background = 'rgba(255, 255, 255, 0.05)';
                });
                
                this.style.borderColor = 'rgba(102, 126, 234, 0.5)';
                this.style.background = 'rgba(102, 126, 234, 0.1)';
                this.querySelector('input[type="radio"]').checked = true;
                
                const type = this.dataset.type;
                isFree = type === 'free';
                
                document.getElementById('pricingFields').style.display = isFree ? 'none' : 'block';
                
                // Update required attributes
                if (!isFree) {
                    document.getElementById('price').setAttribute('required', 'required');
                } else {
                    document.getElementById('price').removeAttribute('required');
                }
            });
        });

        // Price and discount calculation
        const priceInput = document.getElementById('price');
        const discountInput = document.getElementById('discountPrice');
        const currencySelect = document.getElementById('currency');

        function updateDiscountPreview() {
            const price = parseFloat(priceInput.value) || 0;
            const discountPrice = parseFloat(discountInput.value) || 0;
            const currency = currencySelect.value;
            const discountPreview = document.getElementById('discountPreview');
            
            if (price > 0 && discountPrice > 0 && discountPrice < price) {
                const discountPercent = Math.round(((price - discountPrice) / price) * 100);
                
                document.getElementById('originalPricePreview').textContent = `${currency} ${price.toFixed(2)}`;
                document.getElementById('discountedPricePreview').textContent = `${currency} ${discountPrice.toFixed(2)}`;
                document.getElementById('discountBadgePreview').textContent = `-${discountPercent}%`;
                
                discountPreview.style.display = 'block';
            } else {
                discountPreview.style.display = 'none';
            }
        }

        priceInput?.addEventListener('input', updateDiscountPreview);
        discountInput?.addEventListener('input', updateDiscountPreview);
        currencySelect?.addEventListener('change', updateDiscountPreview);

        // Update preview function to include pricing
        const originalUpdatePreview = updatePreview;
        updatePreview = function() {
            originalUpdatePreview();
            
            // Add pricing to preview
            const pricingSection = document.getElementById('previewScheduleSection');
            if (pricingSection) {
                const pricingPreview = document.createElement('div');
                pricingPreview.className = 'preview-section';
                pricingPreview.innerHTML = '<h4>Pricing</h4>';
                
                if (isFree) {
                    pricingPreview.innerHTML += '<p style="color: #10b981; font-weight: 700; font-size: 18px;">🎁 Free Course</p>';
                } else {
                    const price = parseFloat(document.getElementById('price').value) || 0;
                    const discountPrice = parseFloat(document.getElementById('discountPrice').value) || 0;
                    const currency = document.getElementById('currency').value;
                    
                    if (discountPrice > 0 && discountPrice < price) {
                        const discountPercent = Math.round(((price - discountPrice) / price) * 100);
                        pricingPreview.innerHTML += `
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="text-decoration: line-through; color: rgba(255, 255, 255, 0.4);">${currency} ${price.toFixed(2)}</span>
                                <span style="font-size: 24px; font-weight: 800; color: #667eea;">${currency} ${discountPrice.toFixed(2)}</span>
                                <span style="padding: 4px 10px; background: rgba(239, 68, 68, 0.2); color: #ef4444; border-radius: 8px; font-size: 12px; font-weight: 700;">-${discountPercent}%</span>
                            </div>
                        `;
                    } else {
                        pricingPreview.innerHTML += `<p style="font-size: 24px; font-weight: 800; color: #667eea;">${currency} ${price.toFixed(2)}</p>`;
                    }
                }
                
                pricingSection.parentNode.insertBefore(pricingPreview, pricingSection);
            }
        };
    </script>
</body>
</html>