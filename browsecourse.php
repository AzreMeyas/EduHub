<?php
require_once 'auth-check.php';
require_once 'config.php';

// For student access only
checkRole('student');

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Handle enrollment (for free courses only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_course_id'])) {
    $course_id = intval($_POST['enroll_course_id']);
    
    // Check if already enrolled
    $check_query = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        // Get course details
        $course_query = "SELECT total_hours, is_free, price FROM courses WHERE course_id = ?";
        $stmt = $conn->prepare($course_query);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $course_data = $stmt->get_result()->fetch_assoc();
        
        // Check if course is free
        if (!$course_data['is_free'] && $course_data['price'] > 0) {
            $_SESSION['error_message'] = "This is a paid course. Please complete the payment to enroll.";
            header("Location: checkout.php?course_id=$course_id");
            exit();
        }
        
        // Enroll the student (free course)
        $enroll_query = "INSERT INTO enrollments (user_id, course_id, progress_percentage, hours_remaining, status, badge_label) VALUES (?, ?, 0, ?, 'not_started', 'New')";
        $stmt = $conn->prepare($enroll_query);
        $stmt->bind_param("iid", $user_id, $course_id, $course_data['total_hours']);
        
        if ($stmt->execute()) {
            // Create free payment record
            $payment_query = "INSERT INTO payments (user_id, course_id, amount, payment_method, payment_status) VALUES (?, ?, 0.00, 'free', 'completed')";
            $stmt = $conn->prepare($payment_query);
            $stmt->bind_param("ii", $user_id, $course_id);
            $stmt->execute();
            
            // Update or create user statistics with dynamic count
            $stats_check = "SELECT stat_id FROM user_statistics WHERE user_id = ?";
            $stmt = $conn->prepare($stats_check);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stats_result = $stmt->get_result();
            
            if ($stats_result->num_rows > 0) {
                // Update existing stats - count active courses dynamically
                $update_stats = "
                    UPDATE user_statistics 
                    SET active_courses = (
                        SELECT COUNT(*) FROM enrollments 
                        WHERE user_id = ? AND status IN ('not_started', 'in_progress')
                    )
                    WHERE user_id = ?
                ";
                $stmt = $conn->prepare($update_stats);
                $stmt->bind_param("ii", $user_id, $user_id);
                $stmt->execute();
            } else {
                // Create new stats record
                $insert_stats = "
                    INSERT INTO user_statistics (user_id, active_courses) 
                    VALUES (?, 1)
                ";
                $stmt = $conn->prepare($insert_stats);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
            }
            
            // Add activity
            $activity_query = "INSERT INTO activities (user_id, activity_type, icon, title, description) VALUES (?, 'enrollment', '📚', 'Started New Course', 'Enrolled in a new course')";
            $stmt = $conn->prepare($activity_query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            $_SESSION['success_message'] = "Successfully enrolled in the course!";
            header("Location: browsecourse.php");
            exit();
        }
    } else {
        $_SESSION['info_message'] = "You are already enrolled in this course!";
        header("Location: browsecourse.php");
        exit();
    }
}

// Get filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$level = isset($_GET['level']) ? $_GET['level'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'popular';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Build query with dynamic statistics calculation
$query = "
    SELECT 
        c.course_id,
        c.course_code,
        c.title,
        c.description,
        c.icon,
        c.color,
        c.difficulty_level,
        c.instructor_id,
        c.is_free,
        c.price,
        c.discount_price,
        c.discount_percentage,
        c.currency,
        c.rating_average,
        u.full_name as instructor_name,
        CASE WHEN e.enrollment_id IS NOT NULL THEN 1 ELSE 0 END as is_enrolled,
        -- Dynamically calculate total lessons
        (SELECT COUNT(*) FROM materials WHERE course_id = c.course_id) as total_lessons,
        -- Dynamically calculate enrolled count
        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id) as enrolled_count,
        -- Dynamically calculate total hours
        ROUND(
            (COALESCE((SELECT SUM(duration_minutes) FROM materials WHERE course_id = c.course_id), 0) / 60) +
            (COALESCE((SELECT SUM(file_size) FROM materials WHERE course_id = c.course_id), 0) / (1024 * 1024) * 6 / 60),
            2
        ) as total_hours
    FROM courses c
    LEFT JOIN users u ON c.instructor_id = u.user_id
    LEFT JOIN enrollments e ON c.course_id = e.course_id AND e.user_id = ?
    WHERE c.status = 'published'
";

$params = [$user_id];
$types = "i";

// Add search filter
if (!empty($search)) {
    $query .= " AND (c.title LIKE ? OR c.description LIKE ? OR u.full_name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

// Add level filter
if (!empty($level) && $level !== 'all') {
    $query .= " AND c.difficulty_level = ?";
    $params[] = $level;
    $types .= "s";
}

// Add category filter (based on course title keywords)
if (!empty($category) && $category !== 'all') {
    $query .= " AND c.title LIKE ?";
    $category_param = "%$category%";
    $params[] = $category_param;
    $types .= "s";
}

// Add sorting
switch ($sort) {
    case 'newest':
        $query .= " ORDER BY c.created_at DESC";
        break;
    case 'rating':
        $query .= " ORDER BY c.rating_average DESC";
        break;
    case 'price':
        $query .= " ORDER BY c.title ASC"; // All free, so just alphabetical
        break;
    default: // popular
        $query .= " ORDER BY c.enrolled_count DESC";
}

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$courses_result = $stmt->get_result();
$courses = $courses_result->fetch_all(MYSQLI_ASSOC);

// Get total course count
$total_query = "SELECT COUNT(*) as total FROM courses WHERE status = 'published'";
$total_result = $conn->query($total_query);
$total_data = $total_result->fetch_assoc();
$total_courses = $total_data['total'];

// Get total students (unique enrollments)
$students_query = "SELECT COUNT(DISTINCT user_id) as total FROM enrollments";
$students_result = $conn->query($students_query);
$students_data = $students_result->fetch_assoc();
$total_students = $students_data['total'];

// Get average rating
$rating_query = "SELECT AVG(rating_average) as avg_rating FROM courses WHERE status = 'published' AND rating_average > 0";
$rating_result = $conn->query($rating_query);
$rating_data = $rating_result->fetch_assoc();
$avg_rating = $rating_data['avg_rating'] ? number_format($rating_data['avg_rating'], 1) : '4.8';

$conn->close();

// Helper function to get instructor initials
function getInitials($name) {
    $words = explode(' ', $name);
    if (count($words) >= 2) {
        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    }
    return strtoupper(substr($name, 0, 2));
}

// Helper function to format number
function formatNumber($num) {
    if ($num >= 1000) {
        return number_format($num / 1000, 1) . 'k';
    }
    return $num;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Courses - EduHub</title>
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
        
        .alert {
            padding: 15px 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
        }
        
        .alert-info {
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #3b82f6;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 25px 40px;
            margin-bottom: 30px;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .header-title h1 {
            font-size: 36px;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea 0%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .header-title p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 16px;
        }
        
        .back-btn {
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }
        
        .search-filter-bar {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 15px;
        }
        
        .search-input {
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 15px;
        }
        
        .search-input:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .filter-select {
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 15px;
            cursor: pointer;
        }
        
        .filter-select option {
            background: #1a1a2e;
            color: white;
        }
        
        .categories {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        
        .category-chip {
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            text-decoration: none;
            color: white;
        }
        
        .category-chip:hover,
        .category-chip.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
        }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 30px;
        }
        
        .course-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            overflow: hidden;
            transition: all 0.4s;
            position: relative;
        }
        
        .course-card:hover {
            transform: translateY(-10px);
            border-color: rgba(102, 126, 234, 0.4);
            box-shadow: 0 25px 60px rgba(102, 126, 234, 0.3);
        }
        
        .enrolled-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            padding: 8px 16px;
            background: rgba(16, 185, 129, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            z-index: 2;
        }
        
        .course-thumbnail {
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 80px;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        
        .course-thumbnail::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, transparent 70%);
            animation: float-slow 8s infinite;
        }
        
        @keyframes float-slow {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-30px, 30px); }
        }
        
        .course-level {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 8px 16px;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
        }
        
        .course-content {
            padding: 28px;
        }
        
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .course-category {
            padding: 6px 14px;
            background: rgba(102, 126, 234, 0.15);
            color: #667eea;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .course-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }
        
        .stars {
            color: #ffa500;
        }
        
        .course-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.3;
            cursor: pointer;
        }
        
        .course-title:hover {
            color: #667eea;
        }
        
        .course-instructor {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 15px;
        }
        
        .instructor-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
        }
        
        .course-description {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .course-stats {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .course-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .course-price {
            font-size: 28px;
            font-weight: 800;
            color: #10b981;
        }

        .course-price.paid {
            color: #667eea;
        }

        .original-price {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.4);
            text-decoration: line-through;
            margin-right: 10px;
        }

        .discount-badge {
            display: inline-block;
            padding: 4px 10px;
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            margin-left: 8px;
        }
        
        .price-label {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 3px;
        }
        
        .enroll-btn, .view-btn {
            padding: 12px 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 14px;
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .enroll-btn:hover, .view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.5);
        }
        
        .view-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .featured-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 30px;
            padding: 50px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .featured-section::before {
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
        
        .featured-content {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
        }
        
        .featured-text h2 {
            font-size: 42px;
            font-weight: 900;
            margin-bottom: 20px;
        }
        
        .featured-text p {
            font-size: 18px;
            opacity: 0.95;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .featured-stats {
            display: flex;
            gap: 40px;
        }
        
        .featured-stat-value {
            font-size: 36px;
            font-weight: 900;
            margin-bottom: 5px;
        }
        
        .featured-stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: rgba(255, 255, 255, 0.6);
        }

        .empty-state-icon {
            font-size: 100px;
            margin-bottom: 20px;
        }

        .empty-state-title {
            font-size: 28px;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
        }
        
        @media (max-width: 1200px) {
            .courses-grid {
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            }
            
            .featured-content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .search-filter-bar {
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
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                ✓ <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['info_message'])): ?>
            <div class="alert alert-info">
                ℹ <?php echo htmlspecialchars($_SESSION['info_message']); ?>
            </div>
            <?php unset($_SESSION['info_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                ✕ <?php echo htmlspecialchars($_SESSION['error_message']); ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Featured Section -->
        <div class="featured-section">
            <div class="featured-content">
                <div class="featured-text">
                    <h2>🚀 Discover Your Next Course</h2>
                    <p>Explore hundreds of courses from top instructors. Learn at your own pace with lifetime access on mobile and desktop.</p>
                    <div class="featured-stats">
                        <div>
                            <div class="featured-stat-value"><?php echo $total_courses; ?>+</div>
                            <div class="featured-stat-label">Courses</div>
                        </div>
                        <div>
                            <div class="featured-stat-value"><?php echo formatNumber($total_students); ?>+</div>
                            <div class="featured-stat-label">Students</div>
                        </div>
                        <div>
                            <div class="featured-stat-value"><?php echo $avg_rating; ?>★</div>
                            <div class="featured-stat-label">Avg Rating</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="header-title">
                    <h1>Browse All Courses</h1>
                    <p>Find the perfect course to boost your skills</p>
                </div>
                <a href="sdashboard.php" class="back-btn">← Dashboard</a>
            </div>
            
            <form method="GET" action="browsecourse.php">
                <div class="search-filter-bar">
                    <input type="text" name="search" class="search-input" placeholder="🔍 Search courses, instructors, or topics..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="level" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Levels</option>
                        <option value="Beginner" <?php echo $level === 'Beginner' ? 'selected' : ''; ?>>Beginner</option>
                        <option value="Intermediate" <?php echo $level === 'Intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                        <option value="Advanced" <?php echo $level === 'Advanced' ? 'selected' : ''; ?>>Advanced</option>
                    </select>
                    <select name="sort" class="filter-select" onchange="this.form.submit()">
                        <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Sort by: Popular</option>
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Sort by: Newest</option>
                        <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Sort by: Rating</option>
                    </select>
                </div>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
            </form>
        </div>
        
        <!-- Categories -->
        <div class="categories">
            <a href="browsecourse.php" class="category-chip <?php echo empty($category) ? 'active' : ''; ?>">All Courses</a>
            <a href="browsecourse.php?category=Computer Science" class="category-chip <?php echo $category === 'Computer Science' ? 'active' : ''; ?>">💻 Computer Science</a>
            <a href="browsecourse.php?category=Design" class="category-chip <?php echo $category === 'Design' ? 'active' : ''; ?>">🎨 Design</a>
            <a href="browsecourse.php?category=Data Science" class="category-chip <?php echo $category === 'Data Science' ? 'active' : ''; ?>">📊 Data Science</a>
            <a href="browsecourse.php?category=Machine Learning" class="category-chip <?php echo $category === 'Machine Learning' ? 'active' : ''; ?>">🚀 AI & ML</a>
            <a href="browsecourse.php?category=Web Development" class="category-chip <?php echo $category === 'Web Development' ? 'active' : ''; ?>">🌐 Web Development</a>
            <a href="browsecourse.php?category=Mobile" class="category-chip <?php echo $category === 'Mobile' ? 'active' : ''; ?>">📱 Mobile Dev</a>
            <a href="browsecourse.php?category=Cloud" class="category-chip <?php echo $category === 'Cloud' ? 'active' : ''; ?>">☁️ Cloud Computing</a>
        </div>
        
        <!-- Courses Grid -->
        <?php if (empty($courses)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🔍</div>
                <div class="empty-state-title">No Courses Found</div>
                <p>Try adjusting your search or filter criteria</p>
            </div>
        <?php else: ?>
            <div class="courses-grid">
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <?php if ($course['is_enrolled']): ?>
                            <div class="enrolled-badge">✓ Enrolled</div>
                        <?php endif; ?>
                        
                        <div class="course-thumbnail" style="background: <?php echo htmlspecialchars($course['color']); ?>;" onclick="window.location.href='courseview.php?id=<?php echo $course['course_id']; ?>'">
                            <?php echo htmlspecialchars($course['icon']); ?>
                            <?php if ($course['difficulty_level']): ?>
                                <div class="course-level"><?php echo htmlspecialchars($course['difficulty_level']); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="course-content">
                            <div class="course-header">
                                <span class="course-category"><?php echo htmlspecialchars($course['course_code'] ?: 'Course'); ?></span>
                                <div class="course-rating">
                                    <span class="stars">⭐</span>
                                    <span><?php echo number_format($course['rating_average'], 1); ?> (<?php echo formatNumber($course['enrolled_count']); ?>)</span>
                                </div>
                            </div>
                            
                            <h3 class="course-title" onclick="window.location.href='courseview.php?id=<?php echo $course['course_id']; ?>'"><?php echo htmlspecialchars($course['title']); ?></h3>
                            
                            <div class="course-instructor">
                                <div class="instructor-avatar"><?php echo getInitials($course['instructor_name'] ?: 'Unknown'); ?></div>
                                <span><?php echo htmlspecialchars($course['instructor_name'] ?: 'Instructor'); ?></span>
                            </div>
                            
                            <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
                            
                            <div class="course-stats">
                                <span>📚 <?php echo $course['total_lessons']; ?> Lessons</span>
                                <span>⏱️ <?php echo number_format($course['total_hours'], 0); ?> hours</span>
                                <span>👥 <?php echo formatNumber($course['enrolled_count']); ?> students</span>
                            </div>
                            
                            <div class="course-footer">
                                <div>
                                    <?php if ($course['is_free']): ?>
                                        <div class="course-price">Free</div>
                                        <div class="price-label">Full access</div>
                                    <?php else: ?>
                                        <div>
                                            <?php if ($course['discount_price'] && $course['discount_price'] < $course['price']): ?>
                                                <span class="original-price"><?php echo $course['currency']; ?> <?php echo number_format($course['price'], 2); ?></span>
                                                <span class="discount-badge">-<?php echo $course['discount_percentage']; ?>%</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="course-price paid">
                                            <?php 
                                            $display_price = $course['discount_price'] && $course['discount_price'] < $course['price'] 
                                                ? $course['discount_price'] 
                                                : $course['price'];
                                            echo $course['currency'] . ' ' . number_format($display_price, 2); 
                                            ?>
                                        </div>
                                        <div class="price-label">One-time payment</div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($course['is_enrolled']): ?>
                                    <a href="courseview.php?id=<?php echo $course['course_id']; ?>" class="view-btn">View Course</a>
                                <?php elseif ($course['is_free']): ?>
                                    <form method="POST" action="browsecourse.php" style="display: inline;">
                                        <input type="hidden" name="enroll_course_id" value="<?php echo $course['course_id']; ?>">
                                        <button type="submit" class="enroll-btn">Enroll Now</button>
                                    </form>
                                <?php else: ?>
                                    <a href="checkout.php?course_id=<?php echo $course['course_id']; ?>" class="enroll-btn">Buy Now</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Animate cards on load
        window.addEventListener('load', () => {
            document.querySelectorAll('.course-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s, transform 0.5s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>