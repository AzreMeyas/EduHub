<?php
// submit_quiz.php - Handle Quiz Submission
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

$user_id = $_SESSION['user_id'];
$quiz_id = intval($input['quiz_id']);
$answers = $input['answers'];
$time_spent = intval($input['time_spent']);

$conn = getDBConnection();

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Get quiz details
    $quiz_query = "SELECT * FROM quizzes WHERE quiz_id = ?";
    $stmt = $conn->prepare($quiz_query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $quiz = $stmt->get_result()->fetch_assoc();
    
    if (!$quiz) {
        throw new Exception('Quiz not found');
    }
    
    // Create quiz attempt
    $attempt_query = "INSERT INTO quiz_attempts (quiz_id, user_id, time_spent_seconds, status, started_at) 
                      VALUES (?, ?, ?, 'in_progress', NOW())";
    $stmt = $conn->prepare($attempt_query);
    $stmt->bind_param("iii", $quiz_id, $user_id, $time_spent);
    $stmt->execute();
    $attempt_id = $conn->insert_id;
    
    // Get all questions with correct answers
    $questions_query = "SELECT qq.question_id, qq.points, qo.option_id, qo.is_correct
                        FROM quiz_questions qq
                        LEFT JOIN quiz_options qo ON qq.question_id = qo.question_id
                        WHERE qq.quiz_id = ?
                        ORDER BY qq.order_number, qo.order_number";
    $stmt = $conn->prepare($questions_query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Organize questions and their correct answers
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        if (!isset($questions[$row['question_id']])) {
            $questions[$row['question_id']] = [
                'points' => $row['points'],
                'options' => []
            ];
        }
        $questions[$row['question_id']]['options'][$row['option_id']] = $row['is_correct'];
    }
    
    // Calculate score
    $total_points = 0;
    $earned_points = 0;
    $correct_count = 0;
    $incorrect_count = 0;
    
    $question_ids = array_keys($questions);
    
    foreach ($question_ids as $index => $question_id) {
        $question = $questions[$question_id];
        $selected_option = isset($answers[$index]) ? intval($answers[$index]) : null;
        
        $total_points += $question['points'];
        
        $is_correct = false;
        if ($selected_option && isset($question['options'][$selected_option])) {
            $is_correct = $question['options'][$selected_option] == 1;
        }
        
        if ($is_correct) {
            $earned_points += $question['points'];
            $correct_count++;
        } else {
            $incorrect_count++;
        }
        
        // Save answer
        $answer_query = "INSERT INTO quiz_answers (attempt_id, question_id, selected_option_id, is_correct, points_earned) 
                        VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($answer_query);
        $points = $is_correct ? $question['points'] : 0;
        $stmt->bind_param("iiiii", $attempt_id, $question_id, $selected_option, $is_correct, $points);
        $stmt->execute();
    }
    
    // Calculate percentage
    $percentage = $total_points > 0 ? ($earned_points / $total_points) * 100 : 0;
    
    // Update attempt with final score
    $update_attempt = "UPDATE quiz_attempts 
                       SET score = ?, total_points = ?, percentage = ?, status = 'completed', completed_at = NOW()
                       WHERE attempt_id = ?";
    $stmt = $conn->prepare($update_attempt);
    $stmt->bind_param("dddi", $earned_points, $total_points, $percentage, $attempt_id);
    $stmt->execute();
    
    // Get review data if show_correct_answers is enabled
    $review = [];
    if ($quiz['show_correct_answers']) {
        $review_query = "SELECT 
                            qq.question_text,
                            qa.is_correct,
                            selected_opt.option_text as selected_option_text,
                            correct_opt.option_text as correct_option_text
                        FROM quiz_answers qa
                        JOIN quiz_questions qq ON qa.question_id = qq.question_id
                        LEFT JOIN quiz_options selected_opt ON qa.selected_option_id = selected_opt.option_id
                        LEFT JOIN quiz_options correct_opt ON qq.question_id = correct_opt.question_id AND correct_opt.is_correct = 1
                        WHERE qa.attempt_id = ?
                        ORDER BY qq.order_number";
        $stmt = $conn->prepare($review_query);
        $stmt->bind_param("i", $attempt_id);
        $stmt->execute();
        $review_result = $stmt->get_result();
        
        while ($row = $review_result->fetch_assoc()) {
            $review[] = $row;
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return results
    echo json_encode([
        'success' => true,
        'attempt_id' => $attempt_id,
        'score' => round($earned_points, 2),
        'total_points' => round($total_points, 2),
        'percentage' => round($percentage, 2),
        'correct_count' => $correct_count,
        'incorrect_count' => $incorrect_count,
        'time_spent' => $time_spent,
        'review' => $review
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>