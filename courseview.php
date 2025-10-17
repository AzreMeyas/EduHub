<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($course_id === 0) {
    header("Location: " . ($user_role === 'teacher' ? 'tdashboard.php' : 'sdashboard.php'));
    exit();
}

$conn = getDBConnection();

// Fetch course details
$course_query = "
    SELECT 
        c.*,
        u.full_name as instructor_name,
        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id) as actual_enrolled_count
    FROM courses c
    LEFT JOIN users u ON c.instructor_id = u.user_id
    WHERE c.course_id = ?
";
$course_stmt = $conn->prepare($course_query);
$course_stmt->bind_param("i", $course_id);
$course_stmt->execute();
$course_result = $course_stmt->get_result();

if ($course_result->num_rows === 0) {
    die("Course not found");
}

$course = $course_result->fetch_assoc();
$course_stmt->close();

$is_instructor = ($user_role === 'teacher' && $course['instructor_id'] == $user_id);

$is_enrolled = false;
$enrollment_progress = 0;
if ($user_role === 'student') {
    $enroll_check = $conn->prepare("SELECT progress_percentage FROM enrollments WHERE user_id = ? AND course_id = ?");
    $enroll_check->bind_param("ii", $user_id, $course_id);
    $enroll_check->execute();
    $enroll_result = $enroll_check->get_result();
    if ($enroll_result->num_rows > 0) {
        $is_enrolled = true;
        $enrollment_data = $enroll_result->fetch_assoc();
        $enrollment_progress = $enrollment_data['progress_percentage'];
    }
    $enroll_check->close();
}

// Fetch materials
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'latest';

$materials_query = "
    SELECT 
        m.*,
        u.full_name as uploader_name,
        (SELECT COUNT(*) FROM material_comments WHERE material_id = m.material_id) as comment_count
    FROM materials m
    LEFT JOIN users u ON m.uploaded_by = u.user_id
    WHERE m.course_id = ?
";

$params = [$course_id];
$types = "i";

if (!empty($category_filter)) {
    $materials_query .= " AND m.category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

if (!empty($search_query)) {
    $materials_query .= " AND (m.title LIKE ? OR m.description LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

switch ($sort_by) {
    case 'oldest':
        $materials_query .= " ORDER BY m.created_at ASC";
        break;
    case 'popular':
        $materials_query .= " ORDER BY m.views_count DESC";
        break;
    case 'rated':
        $materials_query .= " ORDER BY m.rating_average DESC";
        break;
    default:
        $materials_query .= " ORDER BY m.created_at DESC";
}

$materials_stmt = $conn->prepare($materials_query);
$materials_stmt->bind_param($types, ...$params);
$materials_stmt->execute();
$materials_result = $materials_stmt->get_result();
$materials = $materials_result->fetch_all(MYSQLI_ASSOC);
$materials_stmt->close();

// Fetch announcements
$announcements_query = "
    SELECT a.*, u.full_name as posted_by_name
    FROM announcements a
    LEFT JOIN users u ON a.posted_by = u.user_id
    WHERE a.course_id = ?
    ORDER BY a.created_at DESC
    LIMIT 5
";
$announcements_stmt = $conn->prepare($announcements_query);
$announcements_stmt->bind_param("i", $course_id);
$announcements_stmt->execute();
$announcements_result = $announcements_stmt->get_result();
$announcements = $announcements_result->fetch_all(MYSQLI_ASSOC);
$announcements_stmt->close();

// Fetch discussions
$discussions_query = "
    SELECT 
        cd.*,
        u.full_name as user_name,
        (SELECT COUNT(*) FROM course_discussions WHERE parent_discussion_id = cd.discussion_id) as reply_count
    FROM course_discussions cd
    LEFT JOIN users u ON cd.user_id = u.user_id
    WHERE cd.course_id = ? AND cd.parent_discussion_id IS NULL
    ORDER BY cd.created_at DESC
";
$discussions_stmt = $conn->prepare($discussions_query);
$discussions_stmt->bind_param("i", $course_id);
$discussions_stmt->execute();
$discussions_result = $discussions_stmt->get_result();
$discussions = $discussions_result->fetch_all(MYSQLI_ASSOC);
$discussions_stmt->close();

// Fetch grades
if ($is_instructor) {
    $grades_query = "
        SELECT 
            sg.*,
            u.full_name as student_name,
            u.email as student_email
        FROM student_grades sg
        LEFT JOIN users u ON sg.user_id = u.user_id
        WHERE sg.course_id = ?
        ORDER BY u.full_name, sg.graded_at DESC
    ";
    $grades_stmt = $conn->prepare($grades_query);
    $grades_stmt->bind_param("i", $course_id);
} else {
    $grades_query = "
        SELECT 
            sg.*,
            u.full_name as graded_by_name
        FROM student_grades sg
        LEFT JOIN users u ON sg.graded_by = u.user_id
        WHERE sg.course_id = ? AND sg.user_id = ?
        ORDER BY sg.graded_at DESC
    ";
    $grades_stmt = $conn->prepare($grades_query);
    $grades_stmt->bind_param("ii", $course_id, $user_id);
}
$grades_stmt->execute();
$grades_result = $grades_stmt->get_result();
$grades = $grades_result->fetch_all(MYSQLI_ASSOC);
$grades_stmt->close();

$student_avg_grade = 0;
if (!$is_instructor && !empty($grades)) {
    $total = 0;
    foreach ($grades as $grade) {
        $percentage = ($grade['grade'] / $grade['max_grade']) * 100;
        $total += $percentage;
    }
    $student_avg_grade = $total / count($grades);
}

// Fetch students
$students_query = "
    SELECT 
        e.*,
        u.full_name,
        u.email,
        u.last_login
    FROM enrollments e
    LEFT JOIN users u ON e.user_id = u.user_id
    WHERE e.course_id = ?
    ORDER BY u.full_name
";
$students_stmt = $conn->prepare($students_query);
$students_stmt->bind_param("i", $course_id);
$students_stmt->execute();
$students_result = $students_stmt->get_result();
$students = $students_result->fetch_all(MYSQLI_ASSOC);
$students_stmt->close();

$conn->close();

function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return "just now";
    if ($diff < 3600) return floor($diff / 60) . " minutes ago";
    if ($diff < 86400) return floor($diff / 3600) . " hours ago";
    if ($diff < 604800) return floor($diff / 86400) . " days ago";
    if ($diff < 2592000) return floor($diff / 604800) . " weeks ago";
    return date('M j, Y', $timestamp);
}

function getCategoryDisplay($category) {
    $categories = [
        'lecture_notes' => ['icon' => '📄', 'label' => 'Lecture Notes'],
        'video' => ['icon' => '🎥', 'label' => 'Video Lecture'],
        'assignment' => ['icon' => '📝', 'label' => 'Assignment'],
        'reference' => ['icon' => '📚', 'label' => 'Reference'],
        'quiz' => ['icon' => '🎯', 'label' => 'Quiz'],
        'other' => ['icon' => '📁', 'label' => 'Other']
    ];
    return $categories[$category] ?? $categories['other'];
}

$instructor_initials = '';
if (!empty($course['instructor_name'])) {
    $name_parts = explode(' ', $course['instructor_name']);
    $instructor_initials = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
}

function getInitials($name) {
    $name_parts = explode(' ', $name);
    $initials = strtoupper(substr($name_parts[0], 0, 1));
    if (isset($name_parts[1])) {
        $initials .= strtoupper(substr($name_parts[1], 0, 1));
    }
    return $initials;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - EduHub</title>
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
            overflow: hidden;
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
            top: 50%;
            right: -100px;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(100px, -100px) scale(1.1); }
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
        
        .header-actions {
            display: flex;
            gap: 15px;
        }
        
        .header-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
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
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .course-header-card {
            background: <?php echo htmlspecialchars($course['color'] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'); ?>;
            border-radius: 35px;
            padding: 40px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .course-header-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .course-header-content {
            position: relative;
            z-index: 1;
        }
        
        .course-badge {
            display: inline-block;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .course-title {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 15px;
        }
        
        .course-meta {
            display: flex;
            gap: 30px;
            font-size: 15px;
            opacity: 0.9;
            flex-wrap: wrap;
        }
        
        .course-stats {
            display: flex;
            gap: 40px;
            margin-top: 30px;
        }
        
        .course-stat {
            text-align: center;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 13px;
            opacity: 0.9;
        }
        
        .main-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 8px;
            margin-bottom: 25px;
        }
        
        .tab {
            flex: 1;
            padding: 14px 24px;
            border-radius: 18px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.6);
            border: none;
            background: transparent;
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
        }
        
        .tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .filter-bar {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .filter-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .search-input,
        .filter-select {
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 15px;
            font-family: 'Outfit', sans-serif;
        }

        .search-input {
            flex: 1;
            min-width: 250px;
        }
        
        .search-input:focus,
        .filter-select:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(102, 126, 234, 0.5);
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .filter-select option {
            background: #1a1a2e;
            color: white;
        }
        
        .materials-grid {
            display: grid;
            gap: 20px;
        }
        
        .material-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 30px;
            transition: all 0.4s;
            cursor: pointer;
        }
        
        .material-card:hover {
            transform: translateY(-5px);
            border-color: rgba(102, 126, 234, 0.4);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.3);
        }
        
        .material-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }
        
        .material-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            flex-shrink: 0;
        }
        
        .material-info {
            flex: 1;
            margin-left: 20px;
        }
        
        .material-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .material-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            flex-wrap: wrap;
        }
        
        .material-description {
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .material-tags {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .tag {
            padding: 6px 14px;
            background: rgba(102, 126, 234, 0.15);
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            color: #667eea;
        }
        
        .material-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .material-stats {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            flex-wrap: wrap;
        }
        
        .material-actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .action-btn:hover {
            background: rgba(102, 126, 234, 0.2);
            border-color: rgba(102, 126, 234, 0.4);
        }
        
        .action-btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .discussion-form {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .discussion-textarea {
            width: 100%;
            min-height: 100px;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 15px;
            font-family: 'Outfit', sans-serif;
            resize: vertical;
            margin-bottom: 15px;
        }
        
        .discussion-textarea:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
            background: rgba(255, 255, 255, 0.08);
        }
        
        .discussion-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .discussion-header {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .discussion-avatar {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .discussion-info {
            flex: 1;
        }
        
        .discussion-author {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .discussion-time {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .discussion-text {
            font-size: 15px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 15px;
        }
        
        .discussion-actions {
            display: flex;
            gap: 20px;
            font-size: 14px;
        }
        
        .discussion-action {
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .discussion-action:hover {
            color: #667eea;
        }
        
        .grades-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 30px;
        }
        
        .grade-summary {
            background: rgba(102, 126, 234, 0.15);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .grade-summary h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .grade-summary .avg-grade {
            font-size: 48px;
            font-weight: 800;
            color: #667eea;
        }
        
        .grades-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        
        .grades-table th {
            text-align: left;
            padding: 12px 15px;
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
        }
        
        .grades-table td {
            padding: 20px 15px;
            background: rgba(255, 255, 255, 0.03);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .grades-table td:first-child {
            border-left: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px 0 0 12px;
        }
        
        .grades-table td:last-child {
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 0 12px 12px 0;
        }
        
        .grade-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .grade-excellent {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }
        
        .grade-good {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }
        
        .grade-average {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }
        
        .grade-poor {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
        
        .grade-input {
            width: 80px;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: white;
            font-size: 14px;
            font-family: 'Outfit', sans-serif;
        }
        
        .grade-input:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .save-grade-btn {
            padding: 8px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
            font-family: 'Outfit', sans-serif;
        }
        
        .students-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 30px;
        }
        
        .students-grid {
            display: grid;
            gap: 15px;
        }
        
        .student-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        
        .student-card:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(102, 126, 234, 0.3);
        }
        
        .student-info-wrapper {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .student-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
        }
        
        .student-details h4 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .student-details p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .student-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .remove-btn {
            padding: 8px 16px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 8px;
            color: #ef4444;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
            font-family: 'Outfit', sans-serif;
            transition: all 0.3s;
        }
        
        .remove-btn:hover {
            background: rgba(239, 68, 68, 0.25);
        }
        
        .progress-badge {
            padding: 6px 12px;
            background: rgba(102, 126, 234, 0.15);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #667eea;
        }
        
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        
        .sidebar-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 25px;
        }
        
        .sidebar-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .announcement-item {
            padding: 16px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            margin-bottom: 12px;
        }
        
        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 10px;
        }
        
        .announcement-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .announcement-content {
            flex: 1;
            margin-left: 15px;
        }
        
        .announcement-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .announcement-text {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.5;
        }
        
        .announcement-time {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.4);
            margin-top: 8px;
        }
        
        .teacher-info {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
        }
        
        .teacher-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            flex-shrink: 0;
        }
        
        .teacher-details h4 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .teacher-details p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .enroll-btn {
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
            margin-top: 15px;
            font-family: 'Outfit', sans-serif;
        }
        
        .enroll-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .enrolled-badge {
            width: 100%;
            padding: 16px;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 16px;
            color: #10b981;
            font-weight: 700;
            text-align: center;
            margin-top: 15px;
        }
        
        .progress-card {
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
        }
        
        .progress-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        
        .progress-bar {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 4px;
            transition: width 1s;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.5);
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 1200px) {
            .main-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .course-title {
                font-size: 32px;
            }

            .course-stats {
                flex-wrap: wrap;
                gap: 20px;
            }

            .filter-form {
                flex-direction: column;
            }

            .search-input,
            .filter-select {
                width: 100%;
            }
            
            .grades-table {
                font-size: 12px;
            }
            
            .grades-table th,
            .grades-table td {
                padding: 10px 8px;
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
            <a href="<?php echo $user_role === 'teacher' ? 'tdashboard.php' : 'sdashboard.php'; ?>" class="back-btn">
                ← Back to Dashboard
            </a>
            <?php if ($is_instructor): ?>
            <div class="header-actions">
                <a href="upload.php?course_id=<?php echo $course_id; ?>" class="header-btn">
                    📤 Upload Material
                </a>
                <a href="announcement.php?course_id=<?php echo $course_id; ?>" class="header-btn">
                    📢 New Announcement
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="course-header-card">
            <div class="course-header-content">
                <div class="course-badge"><?php echo htmlspecialchars($course['icon'] ?? '🔥'); ?> Active Course</div>
                <h1 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h1>
                <div class="course-meta">
                    <span>👨‍🏫 <?php echo htmlspecialchars($course['instructor_name'] ?? 'Instructor'); ?></span>
                    <span>📋 <?php echo htmlspecialchars($course['course_code'] ?? 'N/A'); ?></span>
                    <span>🎓 <?php echo number_format($course['actual_enrolled_count']); ?> Students Enrolled</span>
                </div>
                <div class="course-stats">
                    <div class="course-stat">
                        <div class="stat-value"><?php echo $course['materials_count']; ?></div>
                        <div class="stat-label">Materials</div>
                    </div>
                    <div class="course-stat">
                        <div class="stat-value"><?php echo number_format($course['rating_average'], 1); ?></div>
                        <div class="stat-label">Rating</div>
                    </div>
                    <div class="course-stat">
                        <div class="stat-value"><?php echo $course['duration_weeks'] ?? 12; ?></div>
                        <div class="stat-label">Weeks</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="main-layout">
            <div class="main-content">
                <div class="tabs">
                    <button class="tab active" data-tab="materials">📚 Materials</button>
                    <button class="tab" data-tab="discussions">💬 Discussions</button>
                    <button class="tab" data-tab="grades">📊 Grades</button>
                    <button class="tab" data-tab="students">👥 Students</button>
                </div>
                
                <div class="tab-content active" id="materials-tab">
                    <div class="filter-bar">
                        <form method="GET" action="" class="filter-form">
                            <input type="hidden" name="id" value="<?php echo $course_id; ?>">
                            <input type="text" name="search" class="search-input" placeholder="🔍 Search materials..." value="<?php echo htmlspecialchars($search_query); ?>">
                            <select name="category" class="filter-select" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <option value="lecture_notes" <?php echo $category_filter === 'lecture_notes' ? 'selected' : ''; ?>>Lecture Notes</option>
                                <option value="assignment" <?php echo $category_filter === 'assignment' ? 'selected' : ''; ?>>Assignments</option>
                                <option value="video" <?php echo $category_filter === 'video' ? 'selected' : ''; ?>>Videos</option>
                                <option value="reference" <?php echo $category_filter === 'reference' ? 'selected' : ''; ?>>References</option>
                                <option value="quiz" <?php echo $category_filter === 'quiz' ? 'selected' : ''; ?>>Quizzes</option>
                            </select>
                            <select name="sort" class="filter-select" onchange="this.form.submit()">
                                <option value="latest" <?php echo $sort_by === 'latest' ? 'selected' : ''; ?>>Sort by: Latest</option>
                                <option value="oldest" <?php echo $sort_by === 'oldest' ? 'selected' : ''; ?>>Sort by: Oldest</option>
                                <option value="popular" <?php echo $sort_by === 'popular' ? 'selected' : ''; ?>>Sort by: Most Popular</option>
                                <option value="rated" <?php echo $sort_by === 'rated' ? 'selected' : ''; ?>>Sort by: Highest Rated</option>
                            </select>
                        </form>
                    </div>
                    
                    <div class="materials-grid">
                        <?php if (empty($materials)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">📚</div>
                                <h3>No materials found</h3>
                                <p style="margin-top: 10px;">
                                    <?php if ($is_instructor): ?>
                                        <a href="upload.php?course_id=<?php echo $course_id; ?>" class="action-btn-primary action-btn">Upload First Material</a>
                                    <?php else: ?>
                                        Check back later for course materials.
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($materials as $material): 
                                $category_data = getCategoryDisplay($material['category']);
                            ?>
                                <div class="material-card" onclick="window.location='material-detail.php?id=<?php echo $material['material_id']; ?>'">
                                    <div class="material-header">
                                        <div class="material-icon"><?php echo $category_data['icon']; ?></div>
                                        <div class="material-info">
                                            <div class="material-title"><?php echo htmlspecialchars($material['title']); ?></div>
                                            <div class="material-meta">
                                                <span>📁 <?php echo $category_data['label']; ?></span>
                                                <span>📅 <?php echo timeAgo($material['created_at']); ?></span>
                                                <span>👤 <?php echo htmlspecialchars($material['uploader_name']); ?></span>
                                                <?php if ($material['due_date']): ?>
                                                    <span>⏰ Due <?php echo date('M j', strtotime($material['due_date'])); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="material-description">
                                        <?php echo htmlspecialchars($material['short_description'] ?? substr($material['description'], 0, 150) . '...'); ?>
                                    </p>
                                    <?php 
                                    $conn_temp = getDBConnection();
                                    $tags_stmt = $conn_temp->prepare("SELECT tag_name FROM material_tags WHERE material_id = ?");
                                    $tags_stmt->bind_param("i", $material['material_id']);
                                    $tags_stmt->execute();
                                    $tags_result = $tags_stmt->get_result();
                                    $tags = $tags_result->fetch_all(MYSQLI_ASSOC);
                                    $tags_stmt->close();
                                    $conn_temp->close();
                                    
                                    if (!empty($tags)):
                                    ?>
                                        <div class="material-tags">
                                            <?php foreach ($tags as $tag): ?>
                                                <span class="tag"><?php echo htmlspecialchars($tag['tag_name']); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="material-footer">
                                        <div class="material-stats">
                                            <span>👁️ <?php echo number_format($material['views_count']); ?> views</span>
                                            <?php if ($material['downloads_count'] > 0): ?>
                                                <span>⬇️ <?php echo number_format($material['downloads_count']); ?> downloads</span>
                                            <?php endif; ?>
                                            <?php if ($material['rating_count'] > 0): ?>
                                                <span>⭐ <?php echo number_format($material['rating_average'], 1); ?> (<?php echo $material['rating_count']; ?> ratings)</span>
                                            <?php endif; ?>
                                            <span>💬 <?php echo $material['comment_count']; ?> comments</span>
                                        </div>
                                        <div class="material-actions">
                                            <a href="material-detail.php?id=<?php echo $material['material_id']; ?>" class="action-btn" onclick="event.stopPropagation();">View</a>
                                            <?php if ($material['allow_download'] && $material['file_path']): ?>
                                                <a href="download.php?id=<?php echo $material['material_id']; ?>" class="action-btn action-btn-primary" onclick="event.stopPropagation();">Download</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="tab-content" id="discussions-tab">
                    <div class="discussion-form">
                        <textarea class="discussion-textarea" id="newDiscussion" placeholder="Start a new discussion or ask a question..."></textarea>
                        <button class="action-btn action-btn-primary" onclick="postDiscussion()">Post Discussion</button>
                    </div>
                    
                    <div id="discussionsContainer">
                        <?php if (empty($discussions)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">💬</div>
                                <h3>No discussions yet</h3>
                                <p style="margin-top: 10px;">Be the first to start a discussion!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($discussions as $discussion): ?>
                                <div class="discussion-item">
                                    <div class="discussion-header">
                                        <div class="discussion-avatar"><?php echo getInitials($discussion['user_name']); ?></div>
                                        <div class="discussion-info">
                                            <div class="discussion-author"><?php echo htmlspecialchars($discussion['user_name']); ?></div>
                                            <div class="discussion-time"><?php echo timeAgo($discussion['created_at']); ?></div>
                                        </div>
                                    </div>
                                    <?php if ($discussion['title']): ?>
                                        <h4 style="font-size: 18px; margin-bottom: 10px;"><?php echo htmlspecialchars($discussion['title']); ?></h4>
                                    <?php endif; ?>
                                    <p class="discussion-text"><?php echo nl2br(htmlspecialchars($discussion['message'])); ?></p>
                                    <div class="discussion-actions">
                                        <span class="discussion-action" onclick="likeDiscussion(<?php echo $discussion['discussion_id']; ?>, this)">
                                            👍 <span class="like-count"><?php echo $discussion['likes_count']; ?></span> Likes
                                        </span>
                                        <span class="discussion-action">💬 <?php echo $discussion['reply_count']; ?> Replies</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="tab-content" id="grades-tab">
                    <div class="grades-container">
                        <?php if (!$is_instructor && !empty($grades)): ?>
                            <div class="grade-summary">
                                <h3>Your Average Grade</h3>
                                <div class="avg-grade"><?php echo number_format($student_avg_grade, 1); ?>%</div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (empty($grades)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">📊</div>
                                <h3>No grades available</h3>
                                <p style="margin-top: 10px;">
                                    <?php if ($is_instructor): ?>
                                        Start grading student assignments.
                                    <?php else: ?>
                                        Grades will appear here once assignments are graded.
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <table class="grades-table">
                                <thead>
                                    <tr>
                                        <?php if ($is_instructor): ?>
                                            <th>Student</th>
                                        <?php endif; ?>
                                        <th>Assignment</th>
                                        <th>Grade</th>
                                        <th>Percentage</th>
                                        <th>Feedback</th>
                                        <?php if ($is_instructor): ?>
                                            <th>Actions</th>
                                        <?php else: ?>
                                            <th>Date</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grades as $grade): 
                                        $percentage = ($grade['grade'] / $grade['max_grade']) * 100;
                                        $grade_class = $percentage >= 90 ? 'grade-excellent' : 
                                                      ($percentage >= 75 ? 'grade-good' : 
                                                      ($percentage >= 60 ? 'grade-average' : 'grade-poor'));
                                    ?>
                                        <tr>
                                            <?php if ($is_instructor): ?>
                                                <td><?php echo htmlspecialchars($grade['student_name']); ?></td>
                                            <?php endif; ?>
                                            <td><?php echo htmlspecialchars($grade['assignment_name']); ?></td>
                                            <td>
                                                <?php if ($is_instructor): ?>
                                                    <input type="number" class="grade-input" value="<?php echo $grade['grade']; ?>" 
                                                           data-grade-id="<?php echo $grade['grade_id']; ?>" 
                                                           min="0" max="<?php echo $grade['max_grade']; ?>" step="0.01">
                                                    / <?php echo $grade['max_grade']; ?>
                                                <?php else: ?>
                                                    <?php echo $grade['grade']; ?> / <?php echo $grade['max_grade']; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="grade-badge <?php echo $grade_class; ?>"><?php echo number_format($percentage, 1); ?>%</span></td>
                                            <td style="max-width: 300px;"><?php echo htmlspecialchars($grade['feedback'] ?? 'No feedback'); ?></td>
                                            <?php if ($is_instructor): ?>
                                                <td>
                                                    <button class="save-grade-btn" onclick="updateGrade(<?php echo $grade['grade_id']; ?>)">Save</button>
                                                </td>
                                            <?php else: ?>
                                                <td><?php echo date('M j, Y', strtotime($grade['graded_at'])); ?></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="tab-content" id="students-tab">
                    <div class="students-container">
                        <h3 style="margin-bottom: 20px;">Enrolled Students (<?php echo count($students); ?>)</h3>
                        
                        <?php if (empty($students)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">👥</div>
                                <h3>No students enrolled</h3>
                                <p style="margin-top: 10px;">Students will appear here once they enroll.</p>
                            </div>
                        <?php else: ?>
                            <div class="students-grid">
                                <?php foreach ($students as $student): ?>
                                    <div class="student-card">
                                        <div class="student-info-wrapper">
                                            <div class="student-avatar"><?php echo getInitials($student['full_name']); ?></div>
                                            <div class="student-details">
                                                <h4><?php echo htmlspecialchars($student['full_name']); ?></h4>
                                                <p><?php echo htmlspecialchars($student['email']); ?></p>
                                                <p style="margin-top: 5px;">
                                                    <?php if ($student['last_login']): ?>
                                                        Last active: <?php echo timeAgo($student['last_login']); ?>
                                                    <?php else: ?>
                                                        Never logged in
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="student-actions">
                                            <span class="progress-badge"><?php echo $student['progress_percentage']; ?>% Complete</span>
                                            <?php if ($is_instructor): ?>
                                                <button class="remove-btn" onclick="removeStudent(<?php echo $student['enrollment_id']; ?>, '<?php echo htmlspecialchars($student['full_name']); ?>')">
                                                    Remove
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="sidebar">
                <div class="sidebar-card">
                    <h3 class="sidebar-title">Instructor</h3>
                    <div class="teacher-info">
                        <div class="teacher-avatar"><?php echo $instructor_initials; ?></div>
                        <div class="teacher-details">
                            <h4><?php echo htmlspecialchars($course['instructor_name'] ?? 'Instructor'); ?></h4>
                            <p>Professor of <?php echo htmlspecialchars($course['department'] ?? 'Computer Science'); ?></p>
                        </div>
                    </div>
                    <?php if ($is_enrolled): ?>
                        <div class="enrolled-badge">
                            ✅ Enrolled
                        </div>
                    <?php elseif (!$is_instructor): ?>
                        <form method="POST" action="enroll.php">
                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                            <button type="submit" class="enroll-btn">Enroll Now</button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <?php if ($is_enrolled): ?>
                <div class="sidebar-card">
                    <h3 class="sidebar-title">Your Progress</h3>
                    <div class="progress-card">
                        <div class="progress-info">
                            <span style="font-weight: 600;">Course Completion</span>
                            <span style="font-weight: 700; color: #667eea;"><?php echo $enrollment_progress; ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $enrollment_progress; ?>%"></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($announcements)): ?>
                <div class="sidebar-card">
                    <h3 class="sidebar-title">📢 Announcements</h3>
                    
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="announcement-item">
                            <div class="announcement-header">
                                <div class="announcement-icon"><?php echo htmlspecialchars($announcement['icon'] ?? '📌'); ?></div>
                                <div class="announcement-content">
                                    <div class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                                    <p class="announcement-text">
                                        <?php echo htmlspecialchars(substr($announcement['message'], 0, 100)); ?>
                                        <?php if (strlen($announcement['message']) > 100): ?>...<?php endif; ?>
                                    </p>
                                    <div class="announcement-time"><?php echo timeAgo($announcement['created_at']); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="sidebar-card">
                    <h3 class="sidebar-title">Quick Actions</h3>
                    <button class="enroll-btn" onclick="window.location='ai-tutor.php?course_id=<?php echo $course_id; ?>'">
                        🤖 Ask AI Tutor
                    </button>
                    <?php if ($is_enrolled): ?>
                        <button class="enroll-btn" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); margin-top: 10px;" onclick="window.location='student-quiz.php?course_id=<?php echo $course_id; ?>'">
                            🎯 Take Quiz
                        </button>
                        <button class="enroll-btn" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); margin-top: 10px;" onclick="window.location='studygroup.php?course_id=<?php echo $course_id; ?>'">
                            👥 Join Study Group
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const courseId = <?php echo $course_id; ?>;
        const userId = <?php echo $user_id; ?>;
        const isInstructor = <?php echo $is_instructor ? 'true' : 'false'; ?>;
        
        // Tab switching
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                this.classList.add('active');
                
                const tabName = this.dataset.tab;
                document.getElementById(tabName + '-tab').classList.add('active');
            });
        });
        
        // Post Discussion
        function postDiscussion() {
            const message = document.getElementById('newDiscussion').value.trim();
            if (!message) {
                alert('Please write a message');
                return;
            }
            
            fetch('ajax/post_discussion.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    course_id: courseId,
                    user_id: userId,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Discussion posted successfully!');
                    location.reload();
                } else {
                    alert('Error posting discussion');
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        // Like Discussion
        function likeDiscussion(discussionId, element) {
            fetch('ajax/like_discussion.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    discussion_id: discussionId,
                    user_id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const likeCountSpan = element.querySelector('.like-count');
                    likeCountSpan.textContent = data.likes_count;
                    element.style.color = '#667eea';
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        // Update Grade
        function updateGrade(gradeId) {
            const input = document.querySelector(`input[data-grade-id="${gradeId}"]`);
            const newGrade = parseFloat(input.value);
            
            if (isNaN(newGrade) || newGrade < 0) {
                alert('Please enter a valid grade');
                return;
            }
            
            fetch('ajax/update_grade.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    grade_id: gradeId,
                    grade: newGrade,
                    user_id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Grade updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating grade: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating grade');
            });
        }
        
        // Remove Student
        function removeStudent(enrollmentId, studentName) {
            if (!confirm(`Are you sure you want to remove ${studentName} from this course?`)) {
                return;
            }
            
            fetch('ajax/remove_student.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    enrollment_id: enrollmentId,
                    user_id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Student removed successfully!');
                    location.reload();
                } else {
                    alert('Error removing student: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error removing student');
            });
        }
        
        // Animations
        document.querySelectorAll('.material-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 150);
        });
        
        document.querySelectorAll('.announcement-item').forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            setTimeout(() => {
                item.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, 400 + (index * 100));
        });

        window.addEventListener('load', function() {
            document.querySelectorAll('.progress-fill').forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 300);
            });
        });
    </script>
</body>
</html>