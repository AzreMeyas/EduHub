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
$rating = floatval($data['rating']);

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating']);
    exit();
}

$conn = getDBConnection();

$check_query = "SELECT rating_id FROM material_ratings WHERE material_id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $material_id, $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    $update_query = "UPDATE material_ratings SET rating = ? WHERE material_id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("dii", $rating, $material_id, $user_id);
    $update_stmt->execute();
    $update_stmt->close();
} else {
    $insert_query = "INSERT INTO material_ratings (material_id, user_id, rating) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("iid", $material_id, $user_id, $rating);
    $insert_stmt->execute();
    $insert_stmt->close();
}

$check_stmt->close();

$update_material = "
    UPDATE materials 
    SET rating_average = (SELECT AVG(rating) FROM material_ratings WHERE material_id = ?),
        rating_count = (SELECT COUNT(*) FROM material_ratings WHERE material_id = ?)
    WHERE material_id = ?
";
$update_stmt = $conn->prepare($update_material);
$update_stmt->bind_param("iii", $material_id, $material_id, $material_id);
$update_stmt->execute();
$update_stmt->close();

$conn->close();

echo json_encode(['success' => true, 'rating' => $rating]);
?>