<?php
// ajax/update_grade.php
require_once '../config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check if user is a teacher
if ($_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$grade_id = intval($data['grade_id']);
$grade = floatval($data['grade']);
$user_id = intval($data['user_id']);

if ($grade < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid grade']);
    exit();
}

$conn = getDBConnection();

// Update grade
$update_query = "UPDATE student_grades SET grade = ?, graded_by = ?, graded_at = NOW() WHERE grade_id = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("dii", $grade, $user_id, $grade_id);
$success = $update_stmt->execute();
$update_stmt->close();
$conn->close();

echo json_encode(['success' => $success]);
?>