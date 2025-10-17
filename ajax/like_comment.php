<?php
require_once '../config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$comment_id = intval($data['comment_id']);
$user_id = intval($data['user_id']);

$conn = getDBConnection();

$check_query = "SELECT like_id FROM comment_likes WHERE comment_id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $comment_id, $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    $delete_query = "DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("ii", $comment_id, $user_id);
    $delete_stmt->execute();
    $delete_stmt->close();
} else {
    $insert_query = "INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("ii", $comment_id, $user_id);
    $insert_stmt->execute();
    $insert_stmt->close();
}

$check_stmt->close();

$count_query = "SELECT COUNT(*) as likes_count FROM comment_likes WHERE comment_id = ?";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("i", $comment_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_data = $count_result->fetch_assoc();
$count_stmt->close();

$update_query = "UPDATE material_comments SET likes_count = ? WHERE comment_id = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("ii", $count_data['likes_count'], $comment_id);
$update_stmt->execute();
$update_stmt->close();

$conn->close();

echo json_encode(['success' => true, 'likes_count' => $count_data['likes_count']]);
?>