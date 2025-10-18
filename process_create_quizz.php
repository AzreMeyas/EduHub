<?php
// process_create_quiz.php - Process Quiz Creation
session_start();

// Disable error display and log errors instead
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'config.php';

// Set JSON header immediately
header('Content-Type: application/json');

// Start output buffering to catch any unexpected output
ob_start();

try {
    // Check if user is logged in and is a teacher
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
        throw new Exception('Unauthorized access - Please log in as a teacher');
    }

$teacher_id = $_SESSION['user_id'];

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
    exit();
}

// Validate required fields
$required_fields = ['course_id', 'title', 'duration_minutes', 'passing_score', 'questions'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
        exit();
    }
}

$course_id = intval($input['course_id']);
$title = trim($input['title']);
$description = isset($input['description']) ? trim($input['description']) : '';
$duration_minutes = intval($input['duration_minutes']);
$passing_score = floatval($input['passing_score']);
$max_attempts = isset($input['max_attempts']) ? intval($input['max_attempts']) : 3;
$randomize_questions = isset($input['randomize_questions']) ? (bool)$input['randomize_questions'] : false;
$randomize_options = isset($input['randomize_options']) ? (bool)$input['randomize_options'] : false;
$show_correct_answers = isset($input['show_correct_answers']) ? (bool)$input['show_correct_answers'] : true;
$questions = $input['questions'];

// Validate
if (empty($questions) || !is_array($questions)) {
    echo json_encode(['success' => false, 'error' => 'At least one question is required']);
    exit();
}

if ($duration_minutes < 5 || $duration_minutes > 180) {
    echo json_encode(['success' => false, 'error' => 'Duration must be between 5 and 180 minutes']);
    exit();
}

if ($passing_score < 0 || $passing_score > 100) {
    echo json_encode(['success' => false, 'error' => 'Passing score must be between 0 and 100']);
    exit();
}

$conn = getDBConnection();

try {
    // Verify course belongs to teacher
    $verify_query = "SELECT course_id FROM courses WHERE course_id = ? AND instructor_id = ?";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $course_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('You do not have permission to create a quiz for this course');
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    // Insert quiz
    $quiz_query = "INSERT INTO quizzes 
                   (course_id, created_by, title, description, duration_minutes, 
                    total_questions, passing_score, max_attempts, randomize_questions, 
                    randomize_options, show_correct_answers, is_published) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
    
    $stmt = $conn->prepare($quiz_query);
    $total_questions = count($questions);
    $stmt->bind_param("iissidiiiii", 
        $course_id, 
        $teacher_id, 
        $title, 
        $description, 
        $duration_minutes, 
        $total_questions, 
        $passing_score, 
        $max_attempts,
        $randomize_questions,
        $randomize_options,
        $show_correct_answers
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create quiz: ' . $stmt->error);
    }
    
    $quiz_id = $conn->insert_id;
    
    // Insert questions and options
    $question_query = "INSERT INTO quiz_questions 
                       (quiz_id, question_text, question_type, difficulty, points, order_number) 
                       VALUES (?, ?, 'multiple_choice', ?, 1.00, ?)";
    
    $option_query = "INSERT INTO quiz_options 
                     (question_id, option_text, is_correct, order_number) 
                     VALUES (?, ?, ?, ?)";
    
    foreach ($questions as $index => $question) {
        // Validate question
        if (empty($question['text']) || empty($question['options'])) {
            throw new Exception("Question " . ($index + 1) . " is incomplete");
        }
        
        $question_text = trim($question['text']);
        $difficulty = isset($question['difficulty']) ? $question['difficulty'] : 'medium';
        $order_number = isset($question['order_number']) ? $question['order_number'] : ($index + 1);
        
        // Insert question
        $stmt = $conn->prepare($question_query);
        $stmt->bind_param("issi", $quiz_id, $question_text, $difficulty, $order_number);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert question: ' . $stmt->error);
        }
        
        $question_id = $conn->insert_id;
        
        // Insert options
        if (!is_array($question['options']) || count($question['options']) < 2) {
            throw new Exception("Question " . ($index + 1) . " must have at least 2 options");
        }
        
        $has_correct_answer = false;
        
        foreach ($question['options'] as $opt_index => $option) {
            if (empty($option['text'])) {
                throw new Exception("Question " . ($index + 1) . " has an empty option");
            }
            
            $option_text = trim($option['text']);
            $is_correct = isset($option['is_correct']) ? (bool)$option['is_correct'] : false;
            
            if ($is_correct) {
                $has_correct_answer = true;
            }
            
            $stmt = $conn->prepare($option_query);
            $stmt->bind_param("isii", $question_id, $option_text, $is_correct, $opt_index);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to insert option: ' . $stmt->error);
            }
        }
        
        if (!$has_correct_answer) {
            throw new Exception("Question " . ($index + 1) . " must have at least one correct answer");
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success
    echo json_encode([
        'success' => true,
        'quiz_id' => $quiz_id,
        'message' => 'Quiz created successfully',
        'total_questions' => $total_questions
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>