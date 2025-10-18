<?php
// quiz-results.php - View Quiz Attempt History
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

$conn = getDBConnection();

// Get quiz details
$quiz_query = "SELECT q.*, c.title as course_title 
               FROM quizzes q 
               JOIN courses c ON q.course_id = c.course_id 
               WHERE q.quiz_id = ?";
$stmt = $conn->prepare($quiz_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if (!$quiz) {
    die("Quiz not found");
}

// Get all attempts for this quiz
$attempts_query = "SELECT * FROM quiz_attempts 
                   WHERE quiz_id = ? AND user_id = ? AND status = 'completed'
                   ORDER BY completed_at DESC";
$stmt = $conn->prepare($attempts_query);
$stmt->bind_param("ii", $quiz_id, $user_id);
$stmt->execute();
$attempts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - <?php echo htmlspecialchars($quiz['title']); ?></title>
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
            padding: 40px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
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
        
        .header {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .header p {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .attempts-list {
            display: grid;
            gap: 20px;
        }
        
        .attempt-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
        }
        
        .attempt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .attempt-number {
            font-size: 18px;
            font-weight: 700;
        }
        
        .attempt-date {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .attempt-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        
        .stat-box {
            text-align: center;
            padding: 15px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .score-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 18px;
        }
        
        .score-pass {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
        }
        
        .score-fail {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 25px;
        }
        
        .empty-state h2 {
            font-size: 24px;
            margin: 20px 0 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="student-quiz.php" class="back-button">← Back to Quizzes</a>
        
        <div class="header">
            <h1>📊 <?php echo htmlspecialchars($quiz['title']); ?></h1>
            <p><?php echo htmlspecialchars($quiz['course_title']); ?></p>
        </div>
        
        <?php if (empty($attempts)): ?>
            <div class="empty-state">
                <div style="font-size: 64px;">📝</div>
                <h2>No Attempts Yet</h2>
                <p style="color: rgba(255, 255, 255, 0.6);">You haven't taken this quiz yet.</p>
            </div>
        <?php else: ?>
            <div class="attempts-list">
                <?php foreach ($attempts as $index => $attempt): ?>
                    <?php
                        $passed = $attempt['percentage'] >= $quiz['passing_score'];
                        $time_mins = floor($attempt['time_spent_seconds'] / 60);
                        $time_secs = $attempt['time_spent_seconds'] % 60;
                    ?>
                    <div class="attempt-card">
                        <div class="attempt-header">
                            <div class="attempt-number">Attempt #<?php echo count($attempts) - $index; ?></div>
                            <div class="attempt-date">
                                <?php echo date('M d, Y - h:i A', strtotime($attempt['completed_at'])); ?>
                            </div>
                        </div>
                        
                        <div class="attempt-stats">
                            <div class="stat-box">
                                <div class="stat-value score-badge <?php echo $passed ? 'score-pass' : 'score-fail'; ?>">
                                    <?php echo round($attempt['percentage']); ?>%
                                </div>
                                <div class="stat-label">Score</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-value"><?php echo round($attempt['score'], 1); ?></div>
                                <div class="stat-label">Points</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-value"><?php echo $time_mins; ?>:<?php echo str_pad($time_secs, 2, '0', STR_PAD_LEFT); ?></div>
                                <div class="stat-label">Time</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-value"><?php echo $passed ? '✓' : '✗'; ?></div>
                                <div class="stat-label"><?php echo $passed ? 'Passed' : 'Failed'; ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>