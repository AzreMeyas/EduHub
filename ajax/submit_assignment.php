<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['user_id'];
$material_id = isset($_POST['material_id']) ? intval($_POST['material_id']) : 0;
$submission_text = isset($_POST['submission_text']) ? trim($_POST['submission_text']) : '';

// Validate inputs
if ($material_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid material ID']);
    exit();
}

// Validate file upload
if (!isset($_FILES['assignment_file']) || $_FILES['assignment_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Please upload a PDF file']);
    exit();
}

$file = $_FILES['assignment_file'];

// Validate file type
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($file_extension !== 'pdf') {
    echo json_encode(['success' => false, 'message' => 'Only PDF files are allowed']);
    exit();
}

// Validate file size (10MB max)
if ($file['size'] > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File size must be less than 10MB']);
    exit();
}

try {
    $conn = getDBConnection();
    
    // Verify material exists and is an assignment
    $material_query = "SELECT material_id, category, course_id FROM materials WHERE material_id = ? AND category = 'assignment'";
    $stmt = $conn->prepare($material_query);
    $stmt->bind_param("i", $material_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $material = $result->fetch_assoc();
    $stmt->close();
    
    if (!$material) {
        echo json_encode(['success' => false, 'message' => 'Assignment not found']);
        $conn->close();
        exit();
    }
    
    // Verify student is enrolled in the course
    $enrollment_query = "SELECT enrollment_id FROM enrollments WHERE user_id = ? AND course_id = ?";
    $stmt = $conn->prepare($enrollment_query);
    $stmt->bind_param("ii", $user_id, $material['course_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'You are not enrolled in this course']);
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();
    
    // Create uploads directory if it doesn't exist
    $upload_dir = '../uploads/assignments/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $filename = 'user' . $user_id . '_material' . $material_id . '_' . time() . '.pdf';
    $file_path = $upload_dir . $filename;
    $db_file_path = 'uploads/assignments/' . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
        $conn->close();
        exit();
    }
    
    // Check if submission already exists
    $check_query = "SELECT submission_id FROM assignment_submissions WHERE material_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $material_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_submission = $result->fetch_assoc();
    $stmt->close();
    
    if ($existing_submission) {
        // Update existing submission
        $update_query = "
            UPDATE assignment_submissions 
            SET file_path = ?, 
                submission_text = ?, 
                status = 'submitted',
                submitted_at = NOW()
            WHERE submission_id = ?
        ";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssi", $db_file_path, $submission_text, $existing_submission['submission_id']);
        $stmt->execute();
        $stmt->close();
        
        $message = 'Assignment updated successfully';
    } else {
        // Insert new submission
        $insert_query = "
            INSERT INTO assignment_submissions 
            (material_id, user_id, file_path, submission_text, status, submitted_at) 
            VALUES (?, ?, ?, ?, 'submitted', NOW())
        ";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iiss", $material_id, $user_id, $db_file_path, $submission_text);
        $stmt->execute();
        $stmt->close();
        
        $message = 'Assignment submitted successfully';
    }
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'file_path' => $db_file_path
    ]);
    
} catch (Exception $e) {
    error_log("Assignment submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>