<?php
require_once 'auth-check.php';
require_once 'config.php';

// For student dashboard only
checkRole('student');

// Get logged-in user ID
$user_id = $_SESSION['user_id'];

// Get database connection
$conn = getDBConnection();

// Fetch user information
$user_query = "SELECT full_name FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$user_name = explode(' ', $user['full_name'])[0]; // Get first name

// Fetch user statistics
$stats_query = "SELECT * FROM user_statistics WHERE user_id = ?";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats_result = $stmt->get_result();
$stats = $stats_result->fetch_assoc();

// If no stats exist, create default
if (!$stats) {
    $stats = [
        'active_courses' => 0,
        'avg_score' => 0,
        'total_study_hours' => 0
    ];
}

// Fetch enrolled courses with dynamic statistics
$courses_query = "
    SELECT 
        c.course_id,
        c.title,
        c.description,
        c.icon,
        c.color,
        e.progress_percentage,
        e.hours_remaining,
        e.status,
        e.badge_label,
        -- Dynamically calculate total lessons
        (SELECT COUNT(*) FROM materials WHERE course_id = c.course_id) as total_lessons,
        -- Dynamically calculate total hours
        ROUND(
            (COALESCE((SELECT SUM(duration_minutes) FROM materials WHERE course_id = c.course_id), 0) / 60) +
            (COALESCE((SELECT SUM(file_size) FROM materials WHERE course_id = c.course_id), 0) / (1024 * 1024) * 6 / 60),
            2
        ) as total_hours
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    WHERE e.user_id = ?
    ORDER BY e.last_accessed DESC, e.enrolled_at DESC
";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$courses_result = $stmt->get_result();
$courses = $courses_result->fetch_all(MYSQLI_ASSOC);

// Fetch recent activities
$activities_query = "
    SELECT icon, title, created_at
    FROM activities
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 4
";
$stmt = $conn->prepare($activities_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$activities_result = $stmt->get_result();
$activities = $activities_result->fetch_all(MYSQLI_ASSOC);

// Fetch upcoming events
$events_query = "
    SELECT icon, color, title, event_datetime
    FROM events
    WHERE user_id = ? AND event_datetime > NOW() AND is_completed = 0
    ORDER BY event_datetime ASC
    LIMIT 3
";
$stmt = $conn->prepare($events_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$events_result = $stmt->get_result();
$events = $events_result->fetch_all(MYSQLI_ASSOC);

// Fetch notification count (unread)
$notif_query = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($notif_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notif_result = $stmt->get_result();
$notif_data = $notif_result->fetch_assoc();
$unread_count = $notif_data['unread_count'];

$conn->close();

// Helper function to format time ago
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return "Just now";
    if ($diff < 3600) return floor($diff / 60) . " minutes ago";
    if ($diff < 86400) return floor($diff / 3600) . " hours ago";
    if ($diff < 604800) return floor($diff / 86400) . " days ago";
    return date('M j', $timestamp);
}

// Helper function to format event datetime
function formatEventTime($datetime) {
    $timestamp = strtotime($datetime);
    $today = strtotime('today');
    $tomorrow = strtotime('tomorrow');
    
    if (date('Y-m-d', $timestamp) == date('Y-m-d', $today)) {
        return "Today, " . date('g:i A', $timestamp);
    } elseif (date('Y-m-d', $timestamp) == date('Y-m-d', $tomorrow)) {
        return "Tomorrow, " . date('g:i A', $timestamp);
    } else {
        $diff = floor(($timestamp - time()) / 86400);
        if ($diff <= 7) {
            return "In $diff days";
        }
        return date('M j, g:i A', $timestamp);
    }
}

// Get greeting based on time
$hour = date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduHub - Dashboard</title>
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
            overflow-x: hidden;
        }

        /* Animated Background */
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
            animation-delay: 5s;
        }

        .bg-gradient:nth-child(3) {
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            bottom: -100px;
            left: 30%;
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(100px, -100px) scale(1.1); }
            66% { transform: translate(-100px, 100px) scale(0.9); }
        }

        .dashboard {
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
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }

        .logo-text {
            color: white;
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .search-container {
            flex: 1;
            max-width: 600px;
            margin: 0 50px;
            position: relative;
        }

        .search-box {
            width: 100%;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 18px 60px 18px 25px;
            color: white;
            font-size: 16px;
            transition: all 0.4s;
        }

        .search-box:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(102, 126, 234, 0.5);
            box-shadow: 0 0 30px rgba(102, 126, 234, 0.3);
        }

        .search-box::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .search-icon {
            position: absolute;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 24px;
            cursor: pointer;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .icon-btn {
            width: 55px;
            height: 55px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            font-size: 24px;
        }

        .icon-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-3px);
        }

        .notification-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 12px;
            height: 12px;
            background: #ff4757;
            border-radius: 50%;
            border: 2px solid #0f0f1e;
            animation: ping 2s infinite;
        }

        @keyframes ping {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        .user-profile {
            width: 55px;
            height: 55px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s;
        }

        .user-profile:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }

        /* Notification Panel */
        .notification-panel {
            position: fixed;
            right: -450px;
            top: 0;
            width: 420px;
            height: 100vh;
            background: rgba(15, 15, 30, 0.98);
            backdrop-filter: blur(20px);
            border-left: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1000;
            transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            box-shadow: -10px 0 50px rgba(0, 0, 0, 0.5);
        }

        .notification-panel.active {
            right: 0;
        }

        .notification-header {
            padding: 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: rgba(15, 15, 30, 0.98);
            backdrop-filter: blur(20px);
            z-index: 10;
        }

        .notification-title {
            font-size: 24px;
            font-weight: 700;
            color: white;
        }

        .close-btn {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 20px;
        }

        .close-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: rotate(90deg);
        }

        .notification-content {
            padding: 20px 30px;
        }

        .notification-section {
            margin-bottom: 40px;
        }

        .section-header-small {
            font-size: 14px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }

        .activity-item {
            display: flex;
            gap: 15px;
            padding: 18px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            margin-bottom: 12px;
            transition: all 0.3s;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .activity-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(-5px);
            border-color: rgba(102, 126, 234, 0.3);
        }

        .activity-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            color: white;
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 6px;
        }

        .activity-time {
            color: rgba(255, 255, 255, 0.4);
            font-size: 13px;
        }

        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Main Layout */
        .main-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
        }

        /* Sidebar */
        .sidebar {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 30px 20px;
            height: fit-content;
        }

        .nav-group {
            margin-bottom: 30px;
        }

        .nav-title {
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            padding: 0 15px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 16px 20px;
            color: rgba(255, 255, 255, 0.7);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 8px;
            position: relative;
            overflow: hidden;
            text-decoration: none;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .nav-item:hover::before,
        .nav-item.active::before {
            opacity: 0.15;
        }

        .nav-item.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-item span:first-child {
            font-size: 22px;
            z-index: 1;
        }

        .nav-item span:last-child {
            font-weight: 500;
            z-index: 1;
        }

        /* Main Content */
        .main-content {
            min-height: 600px;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 35px;
            padding: 50px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.4);
        }

        .hero-section::before {
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

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-greeting {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 10px;
        }

        .hero-title {
            font-size: 42px;
            font-weight: 800;
            color: white;
            margin-bottom: 15px;
        }

        .hero-description {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 30px;
            max-width: 600px;
        }

        .hero-stats {
            display: flex;
            gap: 40px;
        }

        .hero-stat {
            display: flex;
            flex-direction: column;
        }

        .hero-stat-value {
            font-size: 32px;
            font-weight: 800;
            color: white;
        }

        .hero-stat-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Courses Section */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 28px;
            font-weight: 700;
            color: white;
        }

        .filter-tabs {
            display: flex;
            gap: 10px;
        }

        .filter-tab {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .filter-tab:hover,
        .filter-tab.active {
            background: rgba(102, 126, 234, 0.2);
            border-color: rgba(102, 126, 234, 0.4);
            color: white;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }

        .course-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            overflow: hidden;
            transition: all 0.4s;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: inherit;
        }

        .course-card:hover {
            transform: translateY(-10px);
            border-color: rgba(102, 126, 234, 0.4);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.3);
        }

        .course-image {
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 80px;
            position: relative;
            overflow: hidden;
        }

        .course-image::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, transparent 70%);
            animation: float 10s infinite;
        }

        .course-content {
            padding: 30px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .course-top {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .course-badge {
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
        }

        .course-badge.in_progress {
            background: rgba(102, 126, 234, 0.2);
            color: #667eea;
        }

        .course-badge.not_started {
            background: rgba(56, 239, 125, 0.2);
            color: #38ef7d;
        }

        .course-badge.completed {
            background: rgba(255, 184, 0, 0.2);
            color: #ffb800;
        }

        .course-title {
            font-size: 22px;
            font-weight: 700;
            color: white;
            margin-bottom: 12px;
        }

        .course-desc {
            color: rgba(255, 255, 255, 0.6);
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 25px;
            flex: 1;
        }

        .course-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .course-meta {
            display: flex;
            gap: 20px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
        }

        .progress-bar-container {
            flex: 1;
            max-width: 200px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            transition: width 0.6s ease;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.6);
        }

        .empty-state-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .empty-state-title {
            font-size: 24px;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
        }

        .empty-state-desc {
            font-size: 16px;
            margin-bottom: 30px;
        }

        .explore-btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .explore-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        @media (max-width: 1400px) {
            .courses-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 1200px) {
            .main-layout {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .courses-grid {
                grid-template-columns: 1fr;
            }
            
            .search-container {
                display: none;
            }

            .notification-panel {
                width: 100%;
                right: -100%;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="bg-gradient"></div>
        <div class="bg-gradient"></div>
        <div class="bg-gradient"></div>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <!-- Notification Panel -->
    <div class="notification-panel" id="notificationPanel">
        <div class="notification-header">
            <h2 class="notification-title">Notifications</h2>
            <div class="close-btn" id="closePanel">✕</div>
        </div>
        
        <div class="notification-content">
            <!-- Recent Activity Section -->
            <div class="notification-section">
                <div class="section-header-small">Recent Activity</div>
                <?php if (empty($activities)): ?>
                    <div class="activity-item">
                        <div class="activity-content">
                            <div class="activity-title">No recent activity</div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <div class="activity-item">
                            <div class="activity-icon" style="background: <?php echo htmlspecialchars($event['color']); ?>;"><?php echo htmlspecialchars($event['icon']); ?></div>
                            <div class="activity-content">
                                <div class="activity-title"><?php echo htmlspecialchars($event['title']); ?></div>
                                <div class="activity-time"><?php echo formatEventTime($event['event_datetime']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="dashboard">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <div class="logo">🎓</div>
                <div class="logo-text">EduHub</div>
            </div>
            
            <div class="search-container">
                <input type="text" class="search-box" placeholder="Search courses, topics, or materials...">
                <div class="search-icon">🔍</div>
            </div>
            
            <div class="header-actions">
                <div class="icon-btn" id="notificationBtn">
                    🔔
                    <?php if ($unread_count > 0): ?>
                        <div class="notification-dot"></div>
                    <?php endif; ?>
                </div>
                <div class="user-profile"><?php echo strtoupper(substr($user_name, 0, 2)); ?></div>
            </div>
        </div>
        
        <!-- Main Layout -->
        <div class="main-layout">
            <!-- Left Sidebar -->
            <div class="sidebar">
                <div class="nav-group">
                    <div class="nav-title">Main</div>
                    <a href="dashboard.php" class="nav-item active">
                        <span>🏠</span>
                        <span>Dashboard</span>
                    </a>
                    <a href="browsecourse.php" class="nav-item">
                        <span>🔍</span>
                        <span>Explore</span>
                    </a>
                </div>
                
                <div class="nav-group">
                    <div class="nav-title">Learning</div>
                    <a href="ai.php" class="nav-item">
                        <span>🤖</span>
                        <span>AI Tutor</span>
                    </a>
                    <a href="student-quiz.php" class="nav-item">
                        <span>🎯</span>
                        <span>Quizzes</span>
                    </a>
                    <a href="studygroup.php" class="nav-item">
                        <span>👥</span>
                        <span>Study Groups</span>
                    </a>
                    <a href="logout.php" class="nav-item">
                        <span>➜]</span>
                        <span>Log Out</span>
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="main-content">
                <!-- Hero Section -->
                <div class="hero-section">
                    <div class="hero-content">
                        <div class="hero-greeting"><?php echo $greeting; ?>, <?php echo htmlspecialchars($user_name); ?>! ☀️</div>
                        <h1 class="hero-title">Ready to continue learning?</h1>
                        <p class="hero-description">You're making great progress! Keep up the momentum and unlock new achievements.</p>
                        <div class="hero-stats">
                            <div class="hero-stat">
                                <div class="hero-stat-value"><?php echo $stats['active_courses']; ?></div>
                                <div class="hero-stat-label">Active Courses</div>
                            </div>
                            <div class="hero-stat">
                                <div class="hero-stat-value"><?php echo number_format($stats['avg_score'], 0); ?>%</div>
                                <div class="hero-stat-label">Avg Score</div>
                            </div>
                            <div class="hero-stat">
                                <div class="hero-stat-value"><?php echo number_format($stats['total_study_hours'], 0); ?>h</div>
                                <div class="hero-stat-label">Study Time</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Courses Section -->
                <div class="section-header">
                    <h2 class="section-title">My Courses</h2>
                    <div class="filter-tabs">
                        <div class="filter-tab active" data-filter="all">All</div>
                        <div class="filter-tab" data-filter="in_progress">In Progress</div>
                        <div class="filter-tab" data-filter="completed">Completed</div>
                    </div>
                </div>
                
                <?php if (empty($courses)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📚</div>
                        <div class="empty-state-title">No Courses Yet</div>
                        <div class="empty-state-desc">Start your learning journey by exploring our course catalog</div>
                        <a href="explore.php" class="explore-btn">Explore Courses</a>
                    </div>
                <?php else: ?>
                    <div class="courses-grid">
                        <?php foreach ($courses as $course): 
                            // Determine badge text
                            $badgeText = $course['badge_label'] ?: ucfirst(str_replace('_', ' ', $course['status']));
                            $badgeClass = $course['status'];
                        ?>
                            <a href="courseview.php?id=<?php echo $course['course_id']; ?>" class="course-card" data-status="<?php echo $course['status']; ?>">
                                <div class="course-image" style="background: <?php echo htmlspecialchars($course['color']); ?>;">
                                    <?php echo htmlspecialchars($course['icon']); ?>
                                </div>
                                <div class="course-content">
                                    <div class="course-top">
                                        <div class="course-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($badgeText); ?></div>
                                    </div>
                                    <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                                    <p class="course-desc"><?php echo htmlspecialchars($course['description']); ?></p>
                                    <div class="course-footer">
                                        <div class="course-meta">
                                            <span>📄 <?php echo $course['total_lessons']; ?> lessons</span>
                                        </div>
                                        <div class="progress-bar-container">
                                            <div class="progress-label">
                                                <span>Progress</span>
                                                <span><?php echo $course['progress_percentage']; ?>%</span>
                                            </div>
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?php echo $course['progress_percentage']; ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Notification panel toggle
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationPanel = document.getElementById('notificationPanel');
        const closePanel = document.getElementById('closePanel');
        const overlay = document.getElementById('overlay');

        function openNotifications() {
            notificationPanel.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeNotifications() {
            notificationPanel.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        notificationBtn.addEventListener('click', openNotifications);
        closePanel.addEventListener('click', closeNotifications);
        overlay.addEventListener('click', closeNotifications);

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && notificationPanel.classList.contains('active')) {
                closeNotifications();
            }
        });

        // Smooth hover effects for course cards
        document.querySelectorAll('.course-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
            });
        });
        
        // Search focus effect
        const searchBox = document.querySelector('.search-box');
        searchBox.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.3s';
        });
        
        searchBox.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
        
        // Filter tabs functionality
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Filter courses
                const filter = this.getAttribute('data-filter');
                document.querySelectorAll('.course-card').forEach(card => {
                    if (filter === 'all') {
                        card.style.display = 'flex';
                    } else {
                        const status = card.getAttribute('data-status');
                        card.style.display = status === filter ? 'flex' : 'none';
                    }
                });
            });
        });
        
        // Animate progress bars on page load
        window.addEventListener('load', () => {
            document.querySelectorAll('.progress-fill').forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 300);
            });
        });
    </script>
</body>
</html>