<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get selected course from POST or GET
$selected_course_id = null;
if (isset($_POST['course_id']) && !empty($_POST['course_id'])) {
    $selected_course_id = intval($_POST['course_id']);
} elseif (isset($_GET['course_id']) && !empty($_GET['course_id'])) {
    $selected_course_id = intval($_GET['course_id']);
}

// Get all courses taught by this teacher
$courses_query = "SELECT course_id, course_code, title, enrolled_count FROM courses WHERE instructor_id = ? ORDER BY title";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher_courses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// If a course is selected, get its details and submissions
$course = null;
$stats = ['total_submissions' => 0, 'pending_review' => 0, 'graded' => 0];
$class_average = 0;
$assignments = [];
$submissions = [];

if ($selected_course_id) {
    // Verify teacher owns this course
    $stmt = $conn->prepare("SELECT course_id, title, course_code FROM courses WHERE course_id = ? AND instructor_id = ?");
    $stmt->bind_param("ii", $selected_course_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
    $stmt->close();
    
    if ($course) {
        // Get filter parameters
        $filter_assignment = isset($_GET['assignment']) ? $_GET['assignment'] : 'all';
        $filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
        $search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        // Get statistics
        $stats_query = "
            SELECT 
                COUNT(DISTINCT asub.submission_id) as total_submissions,
                COUNT(DISTINCT CASE WHEN asub.grade IS NULL THEN asub.submission_id END) as pending_review,
                COUNT(DISTINCT CASE WHEN asub.grade IS NOT NULL THEN asub.submission_id END) as graded
            FROM materials m
            LEFT JOIN assignment_submissions asub ON m.material_id = asub.material_id
            WHERE m.course_id = ? AND m.category = 'assignment'
        ";
        $stmt = $conn->prepare($stats_query);
        $stmt->bind_param("i", $selected_course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_assoc();
        $stmt->close();
        
        // Calculate class average
        $avg_query = "
            SELECT AVG(grade) as actual_average
            FROM assignment_submissions asub
            INNER JOIN materials m ON asub.material_id = m.material_id
            WHERE m.course_id = ? AND asub.grade IS NOT NULL
        ";
        $stmt = $conn->prepare($avg_query);
        $stmt->bind_param("i", $selected_course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $avg_result = $result->fetch_assoc();
        $class_average = $avg_result['actual_average'] ?? 0;
        $stmt->close();
        
        // Get all assignments for filter dropdown
        $assignments_query = "SELECT material_id, title FROM materials WHERE course_id = ? AND category = 'assignment' ORDER BY created_at DESC";
        $stmt = $conn->prepare($assignments_query);
        $stmt->bind_param("i", $selected_course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $assignments = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Build query for submissions
        $submissions_query = "
            SELECT 
                asub.submission_id,
                asub.material_id,
                asub.user_id,
                asub.file_path,
                asub.submission_text,
                asub.grade,
                asub.feedback,
                asub.status,
                asub.submitted_at,
                asub.graded_at,
                u.full_name as student_name,
                u.email as student_email,
                m.title as assignment_title,
                m.due_date,
                CASE 
                    WHEN asub.submitted_at > m.due_date THEN 'late'
                    WHEN asub.grade IS NOT NULL THEN 'graded'
                    ELSE 'submitted'
                END as display_status
            FROM assignment_submissions asub
            INNER JOIN users u ON asub.user_id = u.user_id
            INNER JOIN materials m ON asub.material_id = m.material_id
            WHERE m.course_id = ?
        ";
        
        $params = [$selected_course_id];
        $types = "i";
        
        // Apply filters
        if ($filter_assignment !== 'all') {
            $submissions_query .= " AND asub.material_id = ?";
            $params[] = intval($filter_assignment);
            $types .= "i";
        }
        
        if ($filter_status !== 'all') {
            if ($filter_status === 'pending') {
                $submissions_query .= " AND asub.grade IS NULL";
            } elseif ($filter_status === 'graded') {
                $submissions_query .= " AND asub.grade IS NOT NULL";
            } elseif ($filter_status === 'late') {
                $submissions_query .= " AND asub.submitted_at > m.due_date";
            }
        }
        
        if ($search_query !== '') {
            $submissions_query .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
            $search_param = "%{$search_query}%";
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= "ss";
        }
        
        $submissions_query .= " ORDER BY asub.submitted_at DESC";
        
        $stmt = $conn->prepare($submissions_query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $submissions = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

// Helper functions
function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return substr($initials, 0, 2);
}

function getAvatarGradient($index) {
    $gradients = [
        'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
        'linear-gradient(135deg, #f59e0b 0%, #f97316 100%)',
    ];
    return $gradients[$index % count($gradients)];
}

function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return date('M j, Y', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading Center - EduHub</title>
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
            max-width: 1600px;
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
        
        .header-left h1 {
            font-size: 32px;
            margin-bottom: 8px;
        }
        
        .header-left p {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .header-actions {
            display: flex;
            gap: 12px;
        }
        
        .header-btn {
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            font-family: 'Outfit', sans-serif;
        }
        
        .header-btn:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .header-btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        /* Course Selection */
        .course-selector {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .course-selector h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .course-card {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .course-card:hover {
            transform: translateY(-5px);
            border-color: rgba(102, 126, 234, 0.5);
            background: rgba(255, 255, 255, 0.08);
        }

        .course-card.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.15);
        }

        .course-card h3 {
            font-size: 18px;
            margin-bottom: 8px;
        }

        .course-card .course-code {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            margin-bottom: 15px;
        }

        .course-card .course-stats {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 28px;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(102, 126, 234, 0.3);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            background: rgba(102, 126, 234, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 900;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .filter-bar {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 20px 30px;
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-label {
            font-weight: 600;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .filter-select {
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            min-width: 180px;
            font-family: 'Outfit', sans-serif;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .search-input {
            flex: 1;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            color: white;
            font-size: 14px;
            min-width: 250px;
            font-family: 'Outfit', sans-serif;
        }
        
        .search-input:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .submissions-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 30px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 24px;
            font-weight: 700;
        }
        
        .submissions-table {
            width: 100%;
        }
        
        .table-header {
            display: grid;
            grid-template-columns: 2fr 1.5fr 1fr 1fr 1fr 1.5fr;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 14px;
            margin-bottom: 15px;
            font-size: 13px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .table-row {
            display: grid;
            grid-template-columns: 2fr 1.5fr 1fr 1fr 1fr 1.5fr;
            padding: 20px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            margin-bottom: 12px;
            align-items: center;
            transition: all 0.3s;
        }
        
        .table-row:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(102, 126, 234, 0.3);
        }
        
        .student-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .student-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .student-info h4 {
            font-size: 15px;
            margin-bottom: 4px;
        }
        
        .student-info p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .assignment-title {
            font-weight: 600;
            font-size: 14px;
        }
        
        .status-badge {
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
            display: inline-block;
        }
        
        .status-submitted {
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
        }
        
        .status-graded {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
        }
        
        .status-late {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
        }
        
        .grade-display {
            font-size: 20px;
            font-weight: 800;
            color: #10b981;
        }
        
        .grade-display.ungraded {
            color: rgba(255, 255, 255, 0.3);
            font-size: 16px;
            font-weight: 600;
        }
        
        .submission-date {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .actions-cell {
            display: flex;
            gap: 8px;
        }
        
        .action-btn {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: white;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Outfit', sans-serif;
        }
        
        .action-btn:hover {
            background: rgba(102, 126, 234, 0.2);
            border-color: rgba(102, 126, 234, 0.4);
        }
        
        .action-btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .action-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: rgba(26, 26, 36, 0.95);
            backdrop-filter: blur(40px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 40px;
            max-width: 700px;
            width: 90%;
            max-height: 85vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 30px;
        }
        
        .modal-title {
            font-size: 28px;
            font-weight: 800;
        }
        
        .close-btn {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.08);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .close-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            transform: rotate(90deg);
        }
        
        .submission-details {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .detail-value {
            font-weight: 600;
        }
        
        .submission-content {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 25px;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .submission-text {
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 15px;
        }
        
        .grade-input-wrapper {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .grade-input {
            width: 100px;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            color: white;
            font-size: 24px;
            font-weight: 800;
            text-align: center;
            font-family: 'Outfit', sans-serif;
        }
        
        .grade-input:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .grade-slider {
            flex: 1;
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            outline: none;
            -webkit-appearance: none;
        }
        
        .grade-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            cursor: pointer;
        }
        
        .feedback-textarea {
            width: 100%;
            min-height: 150px;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            color: white;
            font-size: 15px;
            font-family: 'Outfit', sans-serif;
            resize: vertical;
        }
        
        .feedback-textarea:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .quick-feedback {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 12px;
        }
        
        .quick-feedback-btn {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: white;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Outfit', sans-serif;
        }
        
        .quick-feedback-btn:hover {
            background: rgba(102, 126, 234, 0.2);
            border-color: rgba(102, 126, 234, 0.4);
        }
        
        .submit-grade-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            color: white;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Outfit', sans-serif;
        }
        
        .submit-grade-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.5);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.6);
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        @media (max-width: 1200px) {
            .table-header,
            .table-row {
                grid-template-columns: 2fr 1fr 1fr 1.5fr;
            }
            
            .table-header div:nth-child(2),
            .table-header div:nth-child(4),
            .table-row > div:nth-child(2),
            .table-row > div:nth-child(4) {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
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
            <div class="header-left">
                <h1>📝 Grading Center</h1>
                <p>Review and grade student submissions</p>
            </div>
            <div class="header-actions">
                <a href="teacher-dashboard.php" class="header-btn">← Back to Dashboard</a>
                <?php if ($course): ?>
                    <button class="header-btn header-btn-primary" onclick="exportGrades()">Export Grades</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Course Selection -->
        <div class="course-selector">
            <h2>📚 Select a Course to Grade</h2>
            <?php if (empty($teacher_courses)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📚</div>
                    <h3>No courses found</h3>
                    <p>You don't have any courses assigned yet.</p>
                </div>
            <?php else: ?>
                <div class="course-grid">
                    <?php foreach ($teacher_courses as $tc): ?>
                        <form method="POST" action="" style="margin: 0;">
                            <input type="hidden" name="course_id" value="<?php echo $tc['course_id']; ?>">
                            <div class="course-card <?php echo ($selected_course_id == $tc['course_id']) ? 'selected' : ''; ?>" 
                                 onclick="this.parentElement.submit()">
                                <h3><?php echo htmlspecialchars($tc['title']); ?></h3>
                                <div class="course-code"><?php echo htmlspecialchars($tc['course_code']); ?></div>
                                <div class="course-stats">
                                    <span>👥 <?php echo $tc['enrolled_count']; ?> Students</span>
                                </div>
                            </div>
                        </form>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($course): ?>
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📥</div>
                    <div class="stat-value"><?php echo $stats['total_submissions']; ?></div>
                    <div class="stat-label">Total Submissions</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-value"><?php echo $stats['pending_review']; ?></div>
                    <div class="stat-label">Pending Review</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value"><?php echo $stats['graded']; ?></div>
                    <div class="stat-label">Graded</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value"><?php echo number_format($class_average, 0); ?>%</div>
                    <div class="stat-label">Class Average</div>
                </div>
            </div>
            
            <!-- Filters -->
            <form method="GET" action="" class="filter-bar">
                <input type="hidden" name="course_id" value="<?php echo $selected_course_id; ?>">
                <span class="filter-label">Filter by:</span>
                <select name="assignment" class="filter-select" onchange="this.form.submit()">
                    <option value="all">All Assignments</option>
                    <?php foreach ($assignments as $assignment): ?>
                        <option value="<?php echo $assignment['material_id']; ?>" 
                            <?php echo (isset($_GET['assignment']) && $_GET['assignment'] == $assignment['material_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($assignment['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="all" <?php echo (!isset($_GET['status']) || $_GET['status'] === 'all') ? 'selected' : ''; ?>>All Status</option>
                    <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="graded" <?php echo (isset($_GET['status']) && $_GET['status'] === 'graded') ? 'selected' : ''; ?>>Graded</option>
                    <option value="late" <?php echo (isset($_GET['status']) && $_GET['status'] === 'late') ? 'selected' : ''; ?>>Late Submission</option>
                </select>
                
                <input type="text" name="search" class="search-input" 
                       placeholder="🔍 Search student name or email..." 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            </form>
            
            <!-- Submissions Table -->
            <div class="submissions-container">
                <div class="section-header">
                    <h2 class="section-title">Student Submissions</h2>
                </div>
                
                <?php if (empty($submissions)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <h3>No submissions found</h3>
                        <p>No student submissions match your current filters.</p>
                    </div>
                <?php else: ?>
                    <div class="submissions-table">
                        <div class="table-header">
                            <div>Student</div>
                            <div>Assignment</div>
                            <div>Status</div>
                            <div>Submitted</div>
                            <div>Grade</div>
                            <div>Actions</div>
                        </div>
                        
                        <?php foreach ($submissions as $index => $submission): ?>
                            <div class="table-row">
                                <div class="student-cell">
                                    <div class="student-avatar" style="background: <?php echo getAvatarGradient($index); ?>">
                                        <?php echo getInitials($submission['student_name']); ?>
                                    </div>
                                    <div class="student-info">
                                        <h4><?php echo htmlspecialchars($submission['student_name']); ?></h4>
                                        <p><?php echo htmlspecialchars($submission['student_email']); ?></p>
                                    </div>
                                </div>
                                <div class="assignment-title"><?php echo htmlspecialchars($submission['assignment_title']); ?></div>
                                <div>
                                    <span class="status-badge status-<?php echo $submission['display_status']; ?>">
                                        <?php echo ucfirst($submission['display_status']); ?>
                                    </span>
                                </div>
                                <div class="submission-date"><?php echo timeAgo($submission['submitted_at']); ?></div>
                                <div>
                                    <?php if ($submission['grade'] !== null): ?>
                                        <span class="grade-display"><?php echo number_format($submission['grade'], 0); ?>/100</span>
                                    <?php else: ?>
                                        <span class="grade-display ungraded">—</span>
                                    <?php endif; ?>
                                </div>
                                <div class="actions-cell">
                                    <button class="action-btn action-btn-primary" 
                                            onclick='openGradingModal(<?php echo json_encode([
                                                "submission_id" => $submission['submission_id'],
                                                "student_name" => $submission['student_name'],
                                                "assignment_title" => $submission['assignment_title'],
                                                "submitted_at" => $submission['submitted_at'],
                                                "display_status" => $submission['display_status'],
                                                "submission_text" => $submission['submission_text'],
                                                "file_path" => $submission['file_path'],
                                                "grade" => $submission['grade'],
                                                "feedback" => $submission['feedback']
                                            ]); ?>)'>
                                        <?php echo $submission['grade'] !== null ? 'Edit Grade' : 'Grade'; ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Grading Modal -->
    <div id="gradingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Grade Submission</h2>
                <button class="close-btn" onclick="closeGradingModal()">×</button>
            </div>
            
            <div class="submission-details">
                <div class="detail-row">
                    <span class="detail-label">Student</span>
                    <span class="detail-value" id="modalStudentName"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Assignment</span>
                    <span class="detail-value" id="modalAssignmentTitle"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Submitted</span>
                    <span class="detail-value" id="modalSubmittedAt"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span id="modalStatusBadge"></span>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Submission Content</label>
                <div class="submission-content">
                    <p class="submission-text" id="modalSubmissionText"></p>
                </div>
                <div id="modalFilePath" style="margin-top: 10px;"></div>
            </div>
            
            <form id="gradeForm">
                <input type="hidden" id="submissionId" name="submission_id">
                
                <div class="form-group">
                    <label class="form-label">Grade (out of 100)</label>
                    <div class="grade-input-wrapper">
                        <input type="number" id="gradeInput" name="grade" class="grade-input" 
                               min="0" max="100" value="0" oninput="updateSlider(this.value)">
                        <input type="range" id="gradeSlider" class="grade-slider" 
                               min="0" max="100" value="0" oninput="updateGradeInput(this.value)">
                        <span style="color: rgba(255,255,255,0.6);">/ 100</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Feedback</label>
                    <textarea id="feedbackTextarea" name="feedback" class="feedback-textarea" 
                              placeholder="Provide detailed feedback for the student..."></textarea>
                    <div class="quick-feedback">
                        <button type="button" class="quick-feedback-btn" onclick="addQuickFeedback('Excellent work! Your implementation was clean and efficient.')">👍 Excellent</button>
                        <button type="button" class="quick-feedback-btn" onclick="addQuickFeedback('Good job! Some areas could use improvement.')">✅ Good</button>
                        <button type="button" class="quick-feedback-btn" onclick="addQuickFeedback('Please review the concepts and resubmit.')">📚 Needs Work</button>
                        <button type="button" class="quick-feedback-btn" onclick="addQuickFeedback('Well structured and documented code.')">💻 Well Coded</button>
                    </div>
                </div>
                
                <button type="submit" class="submit-grade-btn">Submit Grade</button>
            </form>
        </div>
    </div>
    
    <script>
        function openGradingModal(data) {
            document.getElementById('modalStudentName').textContent = data.student_name;
            document.getElementById('modalAssignmentTitle').textContent = data.assignment_title;
            document.getElementById('modalSubmittedAt').textContent = new Date(data.submitted_at).toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            const statusBadge = document.getElementById('modalStatusBadge');
            statusBadge.className = 'status-badge status-' + data.display_status;
            statusBadge.textContent = data.display_status.charAt(0).toUpperCase() + data.display_status.slice(1);
            
            document.getElementById('modalSubmissionText').textContent = data.submission_text || 'No text content provided.';
            
            const filePathDiv = document.getElementById('modalFilePath');
            if (data.file_path) {
                filePathDiv.innerHTML = '<strong>File:</strong> <a href="' + data.file_path + '" target="_blank" style="color: #667eea;">' + data.file_path.split('/').pop() + '</a>';
            } else {
                filePathDiv.innerHTML = '';
            }
            
            document.getElementById('submissionId').value = data.submission_id;
            document.getElementById('gradeInput').value = data.grade || 0;
            document.getElementById('gradeSlider').value = data.grade || 0;
            document.getElementById('feedbackTextarea').value = data.feedback || '';
            
            document.getElementById('gradingModal').classList.add('active');
        }
        
        function closeGradingModal() {
            document.getElementById('gradingModal').classList.remove('active');
        }
        
        function updateSlider(value) {
            document.getElementById('gradeSlider').value = value;
        }
        
        function updateGradeInput(value) {
            document.getElementById('gradeInput').value = value;
        }
        
        function addQuickFeedback(text) {
            const textarea = document.getElementById('feedbackTextarea');
            if (textarea.value) {
                textarea.value += '\n\n' + text;
            } else {
                textarea.value = text;
            }
        }
        
        function exportGrades() {
            <?php if ($selected_course_id): ?>
            window.location.href = 'export-grades.php?course_id=<?php echo $selected_course_id; ?>';
            <?php else: ?>
            alert('Please select a course first');
            <?php endif; ?>
        }
        
        document.getElementById('gradeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('ajax/grade-submission.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Grade submitted successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting the grade.');
            });
        });
        
        // Close modal on outside click
        document.getElementById('gradingModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeGradingModal();
            }
        });
        
        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeGradingModal();
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>