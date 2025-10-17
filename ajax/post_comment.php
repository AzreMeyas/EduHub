<?php
require_once '../config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$material_id = intval($data['material_id']);
$user_id = intval($data['user_id']);
$comment_text = trim($data['comment_text']);
$parent_comment_id = isset($data['parent_comment_id']) ? intval($data['parent_comment_id']) : null;

if (empty($comment_text)) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit();
}

$conn = getDBConnection();

if ($parent_comment_id) {
    $insert_query = "INSERT INTO material_comments (material_id, user_id, comment_text, parent_comment_id) VALUES (?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("iisi", $material_id, $user_id, $comment_text, $parent_comment_id);
} else {
    $insert_query = "INSERT INTO material_comments (material_id, user_id, comment_text) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("iis", $material_id, $user_id, $comment_text);
}

$success = $insert_stmt->execute();
$insert_stmt->close();

if ($success) {
    $update_query = "UPDATE materials SET comments_count = (SELECT COUNT(*) FROM material_comments WHERE material_id = ?) WHERE material_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ii", $material_id, $material_id);
    $update_stmt->execute();
    $update_stmt->close();
}

$conn->close();

echo json_encode(['success' => $success]);
?>