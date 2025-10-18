<?php
// save_quiz.php - Simplified Quiz Creation Handler
// Place this file in the SAME directory as create-quiz.php

session_start();

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Catch all output
ob_start();

try {
    // 1. Check session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not logged in');
    }
    
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
        throw new Exception('Only teachers can create quizzes');
    }
    
    $teacher_id = $_SESSION['user_id'];
    
    // 2. Get input
    $json = file_get_contents('php://input');
    if (empty($json)) {
        throw new Exception('No data received');
    }
    
    $data = json_decode($json, true);
    if (!$data) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }
    
    // 3. Validate required fields
    if (empty($data['course_id'])) throw new Exception('Course ID required');
    if (empty($data['title'])) throw new Exception('Title required');
    if (empty($data['questions'])) throw new Exception('At least one question required');
    
    // 4. Connect to database
    require_once 'config.php';
    $conn = getDBConnection();
    
    // 5. Start transaction
    $conn->begin_transaction();
    
    // 6. Insert quiz
    $sql = "INSERT INTO quizzes 
            (course_id, created_by, title, description, duration_minutes, 
             passing_score, max_attempts, randomize_questions, randomize_options, 
             show_correct_answers, total_questions) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    $course_id = intval($data['course_id']);
    $title = $data['title'];
    $description = $data['description'] ?? '';
    $duration = intval($data['duration_minutes'] ?? 15);
    $passing = floatval($data['passing_score'] ?? 70);
    $attempts = intval($data['max_attempts'] ?? 3);
    $rand_q = isset($data['randomize_questions']) ? 1 : 0;
    $rand_o = isset($data['randomize_options']) ? 1 : 0;
    $show_ans = isset($data['show_correct_answers']) ? 1 : 0;
    $total_q = count($data['questions']);
    
    $stmt->bind_param("iissdiiiiii", 
        $course_id, $teacher_id, $title, $description, 
        $duration, $passing, $attempts, 
        $rand_q, $rand_o, $show_ans, $total_q
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to insert quiz: ' . $stmt->error);
    }
    
    $quiz_id = $conn->insert_id;
    
    // 7. Insert questions and options
    foreach ($data['questions'] as $index => $q) {
        // Insert question
        $sql_q = "INSERT INTO quiz_questions 
                  (quiz_id, question_text, difficulty, order_number) 
                  VALUES (?, ?, ?, ?)";
        $stmt_q = $conn->prepare($sql_q);
        
        $q_text = $q['text'];
        $q_diff = $q['difficulty'] ?? 'medium';
        $q_order = $index + 1;
        
        $stmt_q->bind_param("issi", $quiz_id, $q_text, $q_diff, $q_order);
        
        if (!$stmt_q->execute()) {
            throw new Exception('Failed to insert question: ' . $stmt_q->error);
        }
        
        $question_id = $conn->insert_id;
        
        // Insert options
        if (!empty($q['options'])) {
            foreach ($q['options'] as $opt_idx => $opt) {
                $sql_o = "INSERT INTO quiz_options 
                          (question_id, option_text, is_correct, order_number) 
                          VALUES (?, ?, ?, ?)";
                $stmt_o = $conn->prepare($sql_o);
                
                $opt_text = $opt['text'];
                $opt_correct = $opt['is_correct'] ? 1 : 0;
                
                $stmt_o->bind_param("isii", $question_id, $opt_text, $opt_correct, $opt_idx);
                
                if (!$stmt_o->execute()) {
                    throw new Exception('Failed to insert option: ' . $stmt_o->error);
                }
            }
        }
    }
    
    // 8. Commit
    $conn->commit();
    $conn->close();
    
    // 9. Clear buffer and send success
    ob_end_clean();
    
    echo json_encode([
        'success' => true,
        'quiz_id' => $quiz_id,
        'message' => 'Quiz created successfully!',
        'total_questions' => $total_q
    ]);
    
} catch (Exception $e) {
    // Rollback if needed
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
        $conn->close();
    }
    
    // Clear buffer and send error
    ob_end_clean();
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>