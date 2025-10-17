<?php
// ajax/remove_student.php
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
$enrollment_id = intval($data['enrollment_id']);
$user_id = intval($data['user_id']);

$conn = getDBConnection();

// Verify the teacher owns this course
$verify_query = "
    SELECT e.enrollment_id 
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    WHERE e.enrollment_id = ? AND c.instructor_id = ?
";
$verify_stmt = $conn->prepare($verify_query);
$verify_stmt->bind_param("ii", $enrollment_id, $user_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    $verify_stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Not authorized to remove this student']);
    exit();
}
$verify_stmt->close();

// Delete enrollment
$delete_query = "DELETE FROM enrollments WHERE enrollment_id = ?";
$delete_stmt = $conn->prepare($delete_query);
$delete_stmt->bind_param("i", $enrollment_id);
$success = $delete_stmt->execute();
$delete_stmt->close();
$conn->close();

echo json_encode(['success' => $success]);
?>