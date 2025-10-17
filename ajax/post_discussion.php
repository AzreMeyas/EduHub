<?php
// ajax/post_discussion.php
require_once '../config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$course_id = intval($data['course_id']);
$user_id = intval($data['user_id']);
$message = trim($data['message']);

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit();
}

$conn = getDBConnection();

$insert_query = "INSERT INTO course_discussions (course_id, user_id, message) VALUES (?, ?, ?)";
$insert_stmt = $conn->prepare($insert_query);
$insert_stmt->bind_param("iis", $course_id, $user_id, $message);
$success = $insert_stmt->execute();
$insert_stmt->close();
$conn->close();

echo json_encode(['success' => $success]);
?>