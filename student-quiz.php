<?php
// student-quizzes.php - Student Quiz Dashboard
session_start();
require_once 'config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get all quizzes from enrolled courses
$quizzes_query = "SELECT 
    q.quiz_id,
    q.title as quiz_title,
    q.description,
    q.duration_minutes,
    q.total_questions,
    q.passing_score,
    q.max_attempts,
    c.course_id,
    c.title as course_title,
    c.icon as course_icon,
    c.color as course_color,
    u.full_name as instructor_name,
    (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.quiz_id AND user_id = ? AND status = 'completed') as attempts_count,
    (SELECT MAX(percentage) FROM quiz_attempts WHERE quiz_id = q.quiz_id AND user_id = ? AND status = 'completed') as best_score,
    (SELECT status FROM quiz_attempts WHERE quiz_id = q.quiz_id AND user_id = ? ORDER BY attempt_id DESC LIMIT 1) as last_attempt_status
FROM quizzes q
JOIN courses c ON q.course_id = c.course_id
JOIN enrollments e ON c.course_id = e.course_id
JOIN users u ON q.created_by = u.user_id
WHERE e.user_id = ? AND q.is_published = 1
ORDER BY c.title, q.title";

$stmt = $conn->prepare($quizzes_query);
$stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$quizzes_result = $stmt->get_result();

$quizzes_by_course = [];
while ($row = $quizzes_result->fetch_assoc()) {
    $course_id = $row['course_id'];
    if (!isset($quizzes_by_course[$course_id])) {
        $quizzes_by_course[$course_id] = [
            'course_title' => $row['course_title'],
            'course_icon' => $row['course_icon'],
            'course_color' => $row['course_color'],
            'quizzes' => []
        ];
    }
    $quizzes_by_course[$course_id]['quizzes'][] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Quizzes - EduHub</title>
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
            padding: 40px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .page-header {
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .page-header p {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 30px;
            transition: all 0.3s;
        }
        
        .back-button:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(-5px);
        }
        
        .course-section {
            margin-bottom: 50px;
        }
        
        .course-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
        }
        
        .course-icon {
            font-size: 32px;
        }
        
        .course-info h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .quiz-count {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .quizzes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 25px;
        }
        
        .quiz-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 30px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .quiz-card:hover {
            transform: translateY(-5px);
            border-color: rgba(102, 126, 234, 0.5);
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.2);
        }
        
        .quiz-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }
        
        .quiz-header-section {
            margin-bottom: 20px;
        }
        
        .quiz-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .quiz-description {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .quiz-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .meta-icon {
            font-size: 18px;
        }
        
        .quiz-stats {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
        }
        
        .stat {
            flex: 1;
            text-align: center;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .quiz-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .status-not-started {
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
        }
        
        .status-in-progress {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
        }
        
        .status-completed {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
        }
        
        .status-max-attempts {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }
        
        .quiz-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            flex: 1;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .best-score {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(16, 185, 129, 0.15);
            border-radius: 10px;
            font-weight: 700;
            color: #10b981;
            margin-bottom: 15px;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 40px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
        }
        
        .empty-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .empty-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .empty-text {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        @media (max-width: 768px) {
            .quizzes-grid {
                grid-template-columns: 1fr;
            }
            
            .quiz-meta {
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
        <a href="sdashboard.php" class="back-button">
            ← Back to Dashboard
        </a>
        
        <div class="page-header">
            <h1>🎯 My Quizzes</h1>
            <p>Test your knowledge with quizzes from your enrolled courses</p>
        </div>
        
        <?php if (empty($quizzes_by_course)): ?>
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h2 class="empty-title">No Quizzes Available</h2>
                <p class="empty-text">There are no quizzes available in your enrolled courses yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($quizzes_by_course as $course_id => $course_data): ?>
                <div class="course-section">
                    <div class="course-header">
                        <div class="course-icon"><?php echo $course_data['course_icon']; ?></div>
                        <div class="course-info">
                            <h2><?php echo htmlspecialchars($course_data['course_title']); ?></h2>
                            <div class="quiz-count">
                                <?php echo count($course_data['quizzes']); ?> 
                                <?php echo count($course_data['quizzes']) == 1 ? 'Quiz' : 'Quizzes'; ?> Available
                            </div>
                        </div>
                    </div>
                    
                    <div class="quizzes-grid">
                        <?php foreach ($course_data['quizzes'] as $quiz): ?>
                            <?php
                                $attempts_remaining = $quiz['max_attempts'] - $quiz['attempts_count'];
                                $can_attempt = $attempts_remaining > 0;
                                $has_best_score = !is_null($quiz['best_score']);
                                
                                // Determine status
                                if ($quiz['attempts_count'] == 0) {
                                    $status_class = 'status-not-started';
                                    $status_text = 'Not Started';
                                } elseif (!$can_attempt) {
                                    $status_class = 'status-max-attempts';
                                    $status_text = 'Max Attempts Reached';
                                } elseif ($quiz['last_attempt_status'] == 'in_progress') {
                                    $status_class = 'status-in-progress';
                                    $status_text = 'In Progress';
                                } else {
                                    $status_class = 'status-completed';
                                    $status_text = 'Completed';
                                }
                            ?>
                            <div class="quiz-card">
                                <div class="quiz-header-section">
                                    <span class="quiz-status <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                    <h3 class="quiz-title"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h3>
                                    <?php if ($quiz['description']): ?>
                                        <p class="quiz-description"><?php echo htmlspecialchars($quiz['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="quiz-meta">
                                    <div class="meta-item">
                                        <span class="meta-icon">❓</span>
                                        <span><?php echo $quiz['total_questions']; ?> Questions</span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-icon">⏱️</span>
                                        <span><?php echo $quiz['duration_minutes']; ?> Minutes</span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-icon">🎯</span>
                                        <span>Pass: <?php echo $quiz['passing_score']; ?>%</span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-icon">🔄</span>
                                        <span><?php echo $attempts_remaining; ?> Attempts Left</span>
                                    </div>
                                </div>
                                
                                <?php if ($has_best_score): ?>
                                    <div class="best-score">
                                        <span>🏆</span>
                                        <span>Best Score: <?php echo round($quiz['best_score']); ?>%</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="quiz-stats">
                                    <div class="stat">
                                        <div class="stat-value"><?php echo $quiz['attempts_count']; ?></div>
                                        <div class="stat-label">Attempts</div>
                                    </div>
                                    <div class="stat">
                                        <div class="stat-value"><?php echo $quiz['max_attempts']; ?></div>
                                        <div class="stat-label">Max Attempts</div>
                                    </div>
                                    <div class="stat">
                                        <div class="stat-value"><?php echo $has_best_score ? round($quiz['best_score']) . '%' : '--'; ?></div>
                                        <div class="stat-label">Best Score</div>
                                    </div>
                                </div>
                                
                                <div class="quiz-actions">
                                    <?php if ($can_attempt): ?>
                                        <a href="quiz.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-primary">
                                            <?php echo $quiz['attempts_count'] > 0 ? '🔄 Retake Quiz' : '▶️ Start Quiz'; ?>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-primary" disabled>
                                            🚫 No Attempts Left
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($quiz['attempts_count'] > 0): ?>
                                        <a href="quiz-results.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-secondary">
                                            📊 View Results
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <div style="margin-top: 15px; font-size: 12px; color: rgba(255, 255, 255, 0.5);">
                                    Instructor: <?php echo htmlspecialchars($quiz['instructor_name']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>