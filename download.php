<?php
require_once 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$material_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($material_id === 0) {
    die("Invalid material ID");
}

$conn = getDBConnection();

// Fetch material details
$query = "SELECT * FROM materials WHERE material_id = ? AND allow_download = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $material_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Material not found or download not allowed");
}

$material = $result->fetch_assoc();
$stmt->close();

// Update download count
$update_query = "UPDATE materials SET downloads_count = downloads_count + 1 WHERE material_id = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("i", $material_id);
$update_stmt->execute();
$update_stmt->close();

$conn->close();

// Fix: The file path stored in database is relative (e.g., /uploads/materials/filename.pdf)
// We need to construct the actual server path correctly
$file_path = __DIR__ . $material['file_path']; // This will give us the full server path

// Alternative if files are stored with just filename
if (!file_exists($file_path)) {
    // Try looking in uploads/materials/ directory
    $file_path = __DIR__ . '/uploads/materials/' . basename($material['file_path']);
}

if (!file_exists($file_path)) {
    die("File not found on server. Path: " . htmlspecialchars($material['file_path']));
}

// Get file extension
$file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

// Set appropriate content type
$content_types = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'ppt' => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'zip' => 'application/zip',
    'rar' => 'application/x-rar-compressed',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'mp4' => 'video/mp4',
];

$content_type = $content_types[$file_extension] ?? 'application/octet-stream';

// Set headers for download
header('Content-Description: File Transfer');
header('Content-Type: ' . $content_type);
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

// Clear output buffer
ob_clean();
flush();

// Read file and output
readfile($file_path);
exit();
?>