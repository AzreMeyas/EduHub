<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$teacher_id = $_SESSION['user_id'];
$submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
$grade = isset($_POST['grade']) ? floatval($_POST['grade']) : null;
$feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';

// Validate inputs
if ($submission_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid submission ID']);
    exit();
}

if ($grade === null || $grade < 0 || $grade > 100) {
    echo json_encode(['success' => false, 'message' => 'Grade must be between 0 and 100']);
    exit();
}

try {
    $conn = getDBConnection();
    
    // Verify that the teacher owns the course for this submission
    $verify_query = "
        SELECT asub.submission_id, m.course_id, c.instructor_id
        FROM assignment_submissions asub
        INNER JOIN materials m ON asub.material_id = m.material_id
        INNER JOIN courses c ON m.course_id = c.course_id
        WHERE asub.submission_id = ? AND c.instructor_id = ?
    ";
    
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $submission_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $submission = $result->fetch_assoc();
    $stmt->close();
    
    if (!$submission) {
        echo json_encode(['success' => false, 'message' => 'Submission not found or access denied']);
        $conn->close();
        exit();
    }
    
    // Update the submission with grade and feedback
    $update_query = "
        UPDATE assignment_submissions 
        SET grade = ?, 
            feedback = ?, 
            status = 'graded',
            graded_at = NOW()
        WHERE submission_id = ?
    ";
    
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("dsi", $grade, $feedback, $submission_id);
    $stmt->execute();
    $stmt->close();
    
    // Also insert/update in student_grades table for tracking
    $insert_grade_query = "
        INSERT INTO student_grades 
            (course_id, user_id, assignment_name, grade, max_grade, feedback, graded_by, graded_at)
        SELECT 
            m.course_id,
            asub.user_id,
            m.title,
            ?,
            100.00,
            ?,
            ?,
            NOW()
        FROM assignment_submissions asub
        INNER JOIN materials m ON asub.material_id = m.material_id
        WHERE asub.submission_id = ?
        ON DUPLICATE KEY UPDATE
            grade = VALUES(grade),
            feedback = VALUES(feedback),
            graded_by = VALUES(graded_by),
            updated_at = NOW()
    ";
    
    $stmt = $conn->prepare($insert_grade_query);
    $stmt->bind_param("dsii", $grade, $feedback, $teacher_id, $submission_id);
    $stmt->execute();
    $stmt->close();
    
    $conn->close();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Grade submitted successfully',
        'grade' => $grade
    ]);
    
} catch (Exception $e) {
    error_log("Grade submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>