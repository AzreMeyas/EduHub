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

$conn = getDBConnection();

$check_query = "SELECT bookmark_id FROM material_bookmarks WHERE material_id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $material_id, $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    $delete_query = "DELETE FROM material_bookmarks WHERE material_id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("ii", $material_id, $user_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    echo json_encode(['success' => true, 'bookmarked' => false]);
} else {
    $insert_query = "INSERT INTO material_bookmarks (material_id, user_id) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("ii", $material_id, $user_id);
    $insert_stmt->execute();
    $insert_stmt->close();
    echo json_encode(['success' => true, 'bookmarked' => true]);
}

$check_stmt->close();
$conn->close();
?>