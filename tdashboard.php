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

$conn = getDBConnection();
$teacher_id = $_SESSION['user_id'];
$teacher_name = $_SESSION['full_name'];

// Get teacher's initials for avatar
$name_parts = explode(' ', $teacher_name);
$initials = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));

// Fetch teacher's courses - check if we should show all or just 3
$show_all_courses = isset($_GET['show_all']) && $_GET['show_all'] === '1';
$courses_limit = $show_all_courses ? 1000 : 3;

$courses_query = "
    SELECT 
        c.course_id,
        c.course_code,
        c.title,
        c.icon,
        c.color,
        c.enrolled_count,
        c.rating_average,
        c.materials_count,
        (SELECT COUNT(*) FROM materials WHERE course_id = c.course_id AND category = 'assignment') as assignment_count,
        (SELECT COUNT(*) FROM material_comments mc 
         JOIN materials m ON mc.material_id = m.material_id 
         WHERE m.course_id = c.course_id) as discussion_count
    FROM courses c
    WHERE c.instructor_id = ?
    ORDER BY c.created_at DESC
    LIMIT ?
";
$courses_stmt = $conn->prepare($courses_query);
$courses_stmt->bind_param("ii", $teacher_id, $courses_limit);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();
$courses = $courses_result->fetch_all(MYSQLI_ASSOC);
$courses_stmt->close();

// Get total course count for "View All" button
$total_courses_query = "SELECT COUNT(*) as total FROM courses WHERE instructor_id = ?";
$total_stmt = $conn->prepare($total_courses_query);
$total_stmt->bind_param("i", $teacher_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_courses = $total_result->fetch_assoc()['total'];
$total_stmt->close();

// Fetch recent activities
$activities_query = "
    SELECT 
        'submission' as activity_type,
        CONCAT(u.full_name, ' submitted ', m.title) as activity_text,
        asub.submitted_at as activity_time,
        c.title as course_name
    FROM assignment_submissions asub
    JOIN materials m ON asub.material_id = m.material_id
    JOIN courses c ON m.course_id = c.course_id
    JOIN users u ON asub.user_id = u.user_id
    WHERE c.instructor_id = ?
    
    UNION ALL
    
    SELECT 
        'comment' as activity_type,
        CONCAT(u.full_name, ' posted a new question in the discussion forum') as activity_text,
        mc.created_at as activity_time,
        '' as course_name
    FROM material_comments mc
    JOIN materials m ON mc.material_id = m.material_id
    JOIN courses c ON m.course_id = c.course_id
    JOIN users u ON mc.user_id = u.user_id
    WHERE c.instructor_id = ? AND mc.parent_comment_id IS NULL
    
    UNION ALL
    
    SELECT 
        'material' as activity_type,
        CONCAT('You uploaded new material: ', m.title) as activity_text,
        m.created_at as activity_time,
        c.title as course_name
    FROM materials m
    JOIN courses c ON m.course_id = c.course_id
    WHERE m.uploaded_by = ?
    
    UNION ALL
    
    SELECT 
        'rating' as activity_type,
        CONCAT(c.title, ' received a ', mr.rating, '-star rating from a student') as activity_text,
        mr.created_at as activity_time,
        c.title as course_name
    FROM material_ratings mr
    JOIN materials m ON mr.material_id = m.material_id
    JOIN courses c ON m.course_id = c.course_id
    WHERE c.instructor_id = ? AND mr.rating >= 4.5
    
    ORDER BY activity_time DESC
    LIMIT 5
";
$activities_stmt = $conn->prepare($activities_query);
$activities_stmt->bind_param("iiii", $teacher_id, $teacher_id, $teacher_id, $teacher_id);
$activities_stmt->execute();
$activities_result = $activities_stmt->get_result();
$activities = $activities_result->fetch_all(MYSQLI_ASSOC);
$activities_stmt->close();

// Fetch pending reviews (assignments not graded yet)
$pending_reviews_query = "
    SELECT 
        asub.submission_id,
        asub.material_id,
        asub.submitted_at,
        u.full_name as student_name,
        u.user_id,
        m.title as assignment_title,
        m.due_date,
        CASE 
            WHEN asub.submitted_at > m.due_date THEN 'late'
            WHEN m.due_date < NOW() THEN 'due_passed'
            WHEN DATE(m.due_date) = CURDATE() THEN 'due_today'
            WHEN DATE(m.due_date) = DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 'due_tomorrow'
            ELSE 'on_time'
        END as submission_status,
        DATEDIFF(NOW(), m.due_date) as days_diff
    FROM assignment_submissions asub
    JOIN materials m ON asub.material_id = m.material_id
    JOIN courses c ON m.course_id = c.course_id
    JOIN users u ON asub.user_id = u.user_id
    WHERE c.instructor_id = ? 
    AND asub.status = 'submitted'
    AND asub.grade IS NULL
    ORDER BY 
        CASE 
            WHEN m.due_date < NOW() THEN 1
            WHEN DATE(m.due_date) = CURDATE() THEN 2
            ELSE 3
        END,
        asub.submitted_at ASC
    LIMIT 3
";
$pending_stmt = $conn->prepare($pending_reviews_query);
$pending_stmt->bind_param("i", $teacher_id);
$pending_stmt->execute();
$pending_result = $pending_stmt->get_result();
$pending_reviews = $pending_result->fetch_all(MYSQLI_ASSOC);
$pending_stmt->close();

// Get total pending reviews count
$total_pending_query = "
    SELECT COUNT(*) as total
    FROM assignment_submissions asub
    JOIN materials m ON asub.material_id = m.material_id
    JOIN courses c ON m.course_id = c.course_id
    WHERE c.instructor_id = ? AND asub.status = 'submitted' AND asub.grade IS NULL
";
$total_pending_stmt = $conn->prepare($total_pending_query);
$total_pending_stmt->bind_param("i", $teacher_id);
$total_pending_stmt->execute();
$total_pending_result = $total_pending_stmt->get_result();
$total_pending = $total_pending_result->fetch_assoc()['total'];
$total_pending_stmt->close();

$conn->close();

// Helper function to format time ago
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return $diff . " seconds ago";
    if ($diff < 3600) return floor($diff / 60) . " minutes ago";
    if ($diff < 86400) return floor($diff / 3600) . " hours ago";
    if ($diff < 604800) return floor($diff / 86400) . " days ago";
    return date('M j, Y', $timestamp);
}

// Helper function to get student initials
function getInitials($name) {
    $parts = explode(' ', $name);
    return strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}

// Helper function to get review badge
function getReviewBadge($status, $days_diff) {
    if ($status === 'late') {
        $days = abs($days_diff);
        return ['text' => $days . ' day' . ($days > 1 ? 's' : '') . ' late', 'color' => '#ef4444'];
    } elseif ($status === 'due_today') {
        return ['text' => 'Due today', 'color' => '#ef4444'];
    } elseif ($status === 'due_tomorrow') {
        return ['text' => 'Due tomorrow', 'color' => '#f97316'];
    }
    return ['text' => 'On time', 'color' => '#10b981'];
}

// Helper function to get activity icon
function getActivityIcon($type) {
    $icons = [
        'submission' => ['icon' => '👤', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
        'comment' => ['icon' => '💬', 'gradient' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'],
        'material' => ['icon' => '📚', 'gradient' => 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)'],
        'rating' => ['icon' => '⭐', 'gradient' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)'],
        'enrollment' => ['icon' => '🎓', 'gradient' => 'linear-gradient(135deg, #f59e0b 0%, #f97316 100%)']
    ];
    return $icons[$type] ?? $icons['submission'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - EduHub</title>
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
        
        /* Header */
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
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
        }
        
        .user-info h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .user-info p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 15px;
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
            font-size: 15px;
            text-decoration: none;
            display: inline-block;
        }
        
        .header-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .header-btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .action-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: white;
            display: block;
        }
        
        .action-card:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }
        
        .action-icon {
            font-size: 42px;
            margin-bottom: 15px;
        }
        
        .action-label {
            font-weight: 600;
            font-size: 15px;
        }
        
        /* Main Content Grid */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }
        
        /* My Courses Section */
        .section-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 35px;
            margin-bottom: 30px;
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
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .view-all-btn {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .view-all-btn:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .courses-list {
            display: grid;
            gap: 20px;
        }
        
        .course-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 25px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .course-item:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(102, 126, 234, 0.3);
            transform: translateX(5px);
        }
        
        .course-item-header {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .course-thumbnail {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            flex-shrink: 0;
        }
        
        .course-info {
            flex: 1;
        }
        
        .course-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .course-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
        
        .course-stats {
            display: flex;
            gap: 25px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.7);
            flex-wrap: wrap;
        }
        
        .course-actions {
            display: flex;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            flex-wrap: wrap;
        }
        
        .course-btn {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .course-btn:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
        }
        
        /* Recent Activity */
        .activity-list {
            display: grid;
            gap: 15px;
        }
        
        .activity-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            gap: 15px;
            transition: all 0.3s;
        }
        
        .activity-item:hover {
            background: rgba(255, 255, 255, 0.06);
        }
        
        .activity-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-text {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 8px;
        }
        
        .activity-time {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        /* Pending Reviews */
        .review-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .review-item:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(102, 126, 234, 0.3);
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }
        
        .review-student {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .student-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
        }
        
        .review-info h4 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 3px;
        }
        
        .review-info p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .review-badge {
            padding: 6px 12px;
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .review-actions {
            display: flex;
            gap: 10px;
            margin-top: 12px;
        }
        
        .review-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .review-btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .review-btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .review-btn:hover {
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: rgba(255, 255, 255, 0.5);
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        @media (max-width: 1200px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                padding: 20px;
            }
            
            .header-left {
                width: 100%;
            }
            
            .header-actions {
                width: 100%;
                flex-direction: column;
            }
            
            .header-btn {
                width: 100%;
                text-align: center;
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .section-card {
                padding: 20px;
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
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="user-avatar"><?php echo $initials; ?></div>
                <div class="user-info">
                    <h1>Welcome back, <?php echo htmlspecialchars($teacher_name); ?>! 👋</h1>
                    <p>Teacher Dashboard • Computer Science Department</p>
                </div>
            </div>
            <div class="header-actions">
                <a href="logout.php" class="header-btn header-btn-secondary">Logout</a>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="createcourse.php" class="action-card">
                <div class="action-icon">➕</div>
                <div class="action-label">Create Course</div>
            </a>
            
            <a href="upload.php" class="action-card">
                <div class="action-icon">📤</div>
                <div class="action-label">Upload Material</div>
            </a>
            
            <a href="announcement.php" class="action-card">
                <div class="action-icon">📢</div>
                <div class="action-label">Post Announcement</div>
            </a>
            
            <a href="createquiz.php" class="action-card">
                <div class="action-icon">🎯</div>
                <div class="action-label">Create Quiz</div>
            </a>
            
            <a href="grading.php" class="action-card">
                <div class="action-icon">✏️</div>
                <div class="action-label">Grade Assignments</div>
            </a>
        </div>
        
        <!-- Main Content -->
        <div class="main-grid">
            <!-- Left Column -->
            <div>
                <!-- My Courses -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">📚 My Courses</h2>
                        <?php if ($total_courses > 3 && !$show_all_courses): ?>
                            <a href="tdashboard.php?show_all=1" class="view-all-btn">View All (<?php echo $total_courses; ?>) →</a>
                        <?php elseif ($show_all_courses): ?>
                            <a href="tdashboard.php" class="view-all-btn">← Show Less</a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (empty($courses)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📚</div>
                            <p>You haven't created any courses yet.</p>
                            <p style="margin-top: 10px;">
                                <a href="createcourse.php" class="view-all-btn">Create Your First Course</a>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="courses-list">
                            <?php 
                            $gradient_colors = [
                                'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                                'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                                'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)'
                            ];
                            foreach ($courses as $index => $course): 
                                $gradient = !empty($course['color']) ? $course['color'] : $gradient_colors[$index % 3];
                            ?>
                                <div class="course-item" data-course-id="<?php echo $course['course_id']; ?>">
                                    <div class="course-item-header">
                                        <div class="course-thumbnail" style="background: <?php echo htmlspecialchars($gradient); ?>;">
                                            <?php echo htmlspecialchars($course['icon'] ?? '💻'); ?>
                                        </div>
                                        <div class="course-info">
                                            <h3 class="course-name"><?php echo htmlspecialchars($course['title']); ?></h3>
                                            <div class="course-meta">
                                                <span>📋 <?php echo htmlspecialchars($course['course_code'] ?? 'N/A'); ?></span>
                                                <span>🎓 <?php echo number_format($course['enrolled_count']); ?> Students</span>
                                                <span>⭐ <?php echo number_format($course['rating_average'], 1); ?> Rating</span>
                                            </div>
                                            <div class="course-stats">
                                                <span>📚 <?php echo $course['materials_count']; ?> Materials</span>
                                                <span>📝 <?php echo $course['assignment_count']; ?> Assignments</span>
                                                <span>💬 <?php echo $course['discussion_count']; ?> Discussions</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="course-actions">
                                        <a href="courseview.php?id=<?php echo $course['course_id']; ?>" class="course-btn">View Materials</a>
                                        <a href="managecourse.php?course_id=<?php echo $course['course_id']; ?>" class="course-btn">Manage Course</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Activity -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">⚡ Recent Activity</h2>
                    </div>
                    
                    <?php if (empty($activities)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">⚡</div>
                            <p>No recent activities to display.</p>
                        </div>
                    <?php else: ?>
                        <div class="activity-list">
                            <?php foreach ($activities as $activity): 
                                $icon_data = getActivityIcon($activity['activity_type']);
                            ?>
                                <div class="activity-item">
                                    <div class="activity-icon" style="background: <?php echo $icon_data['gradient']; ?>;">
                                        <?php echo $icon_data['icon']; ?>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-text">
                                            <?php echo htmlspecialchars($activity['activity_text']); ?>
                                        </div>
                                        <div class="activity-time"><?php echo timeAgo($activity['activity_time']); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Right Column -->
            <div>
                <!-- Pending Reviews -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">📝 Pending Reviews</h2>
                        <span style="font-size: 13px; color: rgba(255,255,255,0.6);"><?php echo $total_pending; ?> total</span>
                    </div>
                    
                    <?php if (empty($pending_reviews)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">✅</div>
                            <p>All caught up! No pending reviews.</p>
                        </div>
                    <?php else: ?>
                        <?php 
                        $avatar_colors = [
                            'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                            'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                            'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)'
                        ];
                        foreach ($pending_reviews as $index => $review): 
                            $badge = getReviewBadge($review['submission_status'], $review['days_diff']);
                            $initials = getInitials($review['student_name']);
                        ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="review-student">
                                        <div class="student-avatar" style="background: <?php echo $avatar_colors[$index % 3]; ?>;">
                                            <?php echo $initials; ?>
                                        </div>
                                        <div class="review-info">
                                            <h4><?php echo htmlspecialchars($review['student_name']); ?></h4>
                                            <p><?php echo htmlspecialchars($review['assignment_title']); ?></p>
                                        </div>
                                    </div>
                                    <div class="review-badge" style="background: rgba(<?php 
                                        if ($badge['color'] === '#ef4444') echo '239, 68, 68';
                                        elseif ($badge['color'] === '#f97316') echo '249, 115, 22';
                                        else echo '16, 185, 129';
                                    ?>, 0.15); color: <?php echo $badge['color']; ?>;">
                                        <?php echo $badge['text']; ?>
                                    </div>
                                </div>
                                <div class="review-actions">
                                    <button class="review-btn review-btn-primary" onclick="window.location='grade-assignment.php?submission_id=<?php echo $review['submission_id']; ?>'">
                                        Review Now
                                    </button>
                                    <button class="review-btn review-btn-secondary" onclick="markForLater(<?php echo $review['submission_id']; ?>)">
                                        Later
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if ($total_pending > 3): ?>
                            <a href="grading.php" class="view-all-btn" style="width: 100%; margin-top: 15px; display: block; text-align: center;">
                                View All Pending Reviews →
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Quick Links -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">🔗 Quick Links</h2>
                    </div>
                    
                    <div style="display: grid; gap: 10px;">
                        <a href="browsecourse.php" class="course-btn" style="width: 100%; text-align: left; padding: 14px 20px;">
                            📚 Browse All Courses
                        </a>
                        <a href="students.php" class="course-btn" style="width: 100%; text-align: left; padding: 14px 20px;">
                            👥 Student Directory
                        </a>
                        <a href="reports.php" class="course-btn" style="width: 100%; text-align: left; padding: 14px 20px;">
                            📈 Generate Reports
                        </a>
                        <a href="resources.php" class="course-btn" style="width: 100%; text-align: left; padding: 14px 20px;">
                            💡 Teaching Resources
                        </a>
                        <a href="support.php" class="course-btn" style="width: 100%; text-align: left; padding: 14px 20px;">
                            🆘 Help & Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Mark for later function
        function markForLater(submissionId) {
            event.target.closest('.review-item').style.opacity = '0.5';
            setTimeout(() => {
                event.target.closest('.review-item').style.opacity = '1';
                alert('Assignment marked for later review');
            }, 300);
        }

        // Handle course card clicks - MAIN FIX
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handler to course cards
            document.querySelectorAll('.course-item').forEach(function(card) {
                card.addEventListener('click', function(e) {
                    // Don't navigate if clicking on a button or link
                    if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.closest('a') || e.target.closest('button')) {
                        return;
                    }
                    const courseId = this.dataset.courseId;
                    if (courseId) {
                        window.location.href = 'courseview.php?id=' + courseId;
                    }
                });
            });

            // Prevent event bubbling on buttons
            document.querySelectorAll('.course-btn, .review-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });

            // Animate cards on load
            document.querySelectorAll('.action-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'scale(1)';
                }, 200 + (index * 50));
            });
            
            // Animate course items
            document.querySelectorAll('.course-item').forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, 500 + (index * 150));
            });
            
            // Animate activity items
            document.querySelectorAll('.activity-item').forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, 800 + (index * 100));
            });
            
            // Animate review items
            document.querySelectorAll('.review-item').forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateX(20px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, 500 + (index * 100));
            });
        });
    </script>
</body>
</html>