<?php
require_once 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$material_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($material_id === 0) {
    header("Location: " . ($user_role === 'teacher' ? 'tdashboard.php' : 'sdashboard.php'));
    exit();
}

$conn = getDBConnection();

// Fetch material details with all related information
$material_query = "
    SELECT 
        m.*,
        c.title as course_title,
        c.course_code,
        c.course_id,
        u.full_name as uploader_name,
        (SELECT COUNT(*) FROM material_comments WHERE material_id = m.material_id) as total_comments
    FROM materials m
    LEFT JOIN courses c ON m.course_id = c.course_id
    LEFT JOIN users u ON m.uploaded_by = u.user_id
    WHERE m.material_id = ?
";
$material_stmt = $conn->prepare($material_query);
$material_stmt->bind_param("i", $material_id);
$material_stmt->execute();
$material_result = $material_stmt->get_result();

if ($material_result->num_rows === 0) {
    die("Material not found");
}

$material = $material_result->fetch_assoc();
$material_stmt->close();

// Update view count
$update_views = $conn->prepare("UPDATE materials SET views_count = views_count + 1 WHERE material_id = ?");
$update_views->bind_param("i", $material_id);
$update_views->execute();
$update_views->close();

// Fetch material tags
$tags_query = "SELECT tag_name FROM material_tags WHERE material_id = ? ORDER BY tag_name";
$tags_stmt = $conn->prepare($tags_query);
$tags_stmt->bind_param("i", $material_id);
$tags_stmt->execute();
$tags_result = $tags_stmt->get_result();
$tags = $tags_result->fetch_all(MYSQLI_ASSOC);
$tags_stmt->close();

// Check if user has bookmarked this material
$bookmark_query = "SELECT bookmark_id FROM material_bookmarks WHERE material_id = ? AND user_id = ?";
$bookmark_stmt = $conn->prepare($bookmark_query);
$bookmark_stmt->bind_param("ii", $material_id, $user_id);
$bookmark_stmt->execute();
$bookmark_result = $bookmark_stmt->get_result();
$is_bookmarked = $bookmark_result->num_rows > 0;
$bookmark_stmt->close();

// Check if user has rated this material
$user_rating_query = "SELECT rating FROM material_ratings WHERE material_id = ? AND user_id = ?";
$user_rating_stmt = $conn->prepare($user_rating_query);
$user_rating_stmt->bind_param("ii", $material_id, $user_id);
$user_rating_stmt->execute();
$user_rating_result = $user_rating_stmt->get_result();
$user_rating = 0;
if ($user_rating_result->num_rows > 0) {
    $user_rating_data = $user_rating_result->fetch_assoc();
    $user_rating = $user_rating_data['rating'];
}
$user_rating_stmt->close();

// Fetch rating distribution
$rating_dist_query = "
    SELECT five_star, four_star, three_star, two_star, one_star 
    FROM rating_distribution 
    WHERE material_id = ?
";
$rating_dist_stmt = $conn->prepare($rating_dist_query);
$rating_dist_stmt->bind_param("i", $material_id);
$rating_dist_stmt->execute();
$rating_dist_result = $rating_dist_stmt->get_result();
$rating_distribution = $rating_dist_result->fetch_assoc();
$rating_dist_stmt->close();

// If no rating distribution exists, create default
if (!$rating_distribution) {
    $rating_distribution = [
        'five_star' => 0,
        'four_star' => 0,
        'three_star' => 0,
        'two_star' => 0,
        'one_star' => 0
    ];
}

// Fetch comments with user info and likes
$comments_query = "
    SELECT 
        mc.*,
        u.full_name as commenter_name,
        (SELECT COUNT(*) FROM comment_likes WHERE comment_id = mc.comment_id) as likes_count
    FROM material_comments mc
    LEFT JOIN users u ON mc.user_id = u.user_id
    WHERE mc.material_id = ? AND mc.parent_comment_id IS NULL
    ORDER BY mc.created_at DESC
";
$comments_stmt = $conn->prepare($comments_query);
$comments_stmt->bind_param("i", $material_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();
$comments = $comments_result->fetch_all(MYSQLI_ASSOC);
$comments_stmt->close();

// Fetch replies for each comment
foreach ($comments as &$comment) {
    $replies_query = "
        SELECT 
            mc.*,
            u.full_name as commenter_name,
            (SELECT COUNT(*) FROM comment_likes WHERE comment_id = mc.comment_id) as likes_count
        FROM material_comments mc
        LEFT JOIN users u ON mc.user_id = u.user_id
        WHERE mc.parent_comment_id = ?
        ORDER BY mc.created_at ASC
    ";
    $replies_stmt = $conn->prepare($replies_query);
    $replies_stmt->bind_param("i", $comment['comment_id']);
    $replies_stmt->execute();
    $replies_result = $replies_stmt->get_result();
    $comment['replies'] = $replies_result->fetch_all(MYSQLI_ASSOC);
    $replies_stmt->close();
}

// Fetch related materials from the same course
$related_query = "
    SELECT m.*, 
           (SELECT COUNT(*) FROM material_comments WHERE material_id = m.material_id) as comment_count
    FROM materials m
    WHERE m.course_id = ? AND m.material_id != ?
    ORDER BY m.created_at DESC
    LIMIT 3
";
$related_stmt = $conn->prepare($related_query);
$related_stmt->bind_param("ii", $material['course_id'], $material_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
$related_materials = $related_result->fetch_all(MYSQLI_ASSOC);
$related_stmt->close();

// Helper function for time ago
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return "just now";
    if ($diff < 3600) return floor($diff / 60) . " minutes ago";
    if ($diff < 86400) return floor($diff / 3600) . " hours ago";
    if ($diff < 604800) return floor($diff / 86400) . " days ago";
    if ($diff < 2592000) return floor($diff / 604800) . " weeks ago";
    return date('M j, Y', $timestamp);
}

// Helper function for category display
function getCategoryDisplay($category) {
    $categories = [
        'lecture_notes' => ['icon' => '📄', 'label' => 'Lecture Notes'],
        'video' => ['icon' => '🎥', 'label' => 'Video Lecture'],
        'assignment' => ['icon' => '📝', 'label' => 'Assignment'],
        'reference' => ['icon' => '📚', 'label' => 'Reference'],
        'quiz' => ['icon' => '🎯', 'label' => 'Quiz'],
        'other' => ['icon' => '📁', 'label' => 'Other']
    ];
    return $categories[$category] ?? $categories['other'];
}

// Get category data
$category_data = getCategoryDisplay($material['category']);

// Get user initials for avatars
function getInitials($name) {
    $name_parts = explode(' ', $name);
    $initials = strtoupper(substr($name_parts[0], 0, 1));
    if (isset($name_parts[1])) {
        $initials .= strtoupper(substr($name_parts[1], 0, 1));
    }
    return $initials;
}

// Format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Get file extension
$file_extension = '';
if ($material['file_path']) {
    $file_extension = strtolower(pathinfo($material['file_path'], PATHINFO_EXTENSION));
}

$submission = null;
if ($material['category'] === 'assignment' && $user_role === 'student') {
    $submission_query = "
        SELECT * FROM assignment_submissions 
        WHERE material_id = ? AND user_id = ?
    ";
    $submission_stmt = $conn->prepare($submission_query);
    $submission_stmt->bind_param("ii", $material_id, $user_id);
    $submission_stmt->execute();
    $submission_result = $submission_stmt->get_result();
    if ($submission_result->num_rows > 0) {
        $submission = $submission_result->fetch_assoc();
    }
    $submission_stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($material['title']); ?> - EduHub</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: #0f0f1e;
            min-height: 100vh;
            color: white;
        }
        
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }
        
        .bg-gradient {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.3;
            animation: float 20s infinite;
        }
        
        .bg-gradient:nth-child(1) {
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            top: -200px;
            left: -200px;
        }
        
        .bg-gradient:nth-child(2) {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            bottom: -100px;
            right: -100px;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(80px, -80px); }
        }
        
        .container {
            position: relative;
            z-index: 1;
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 25px 40px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .back-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }
        
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }
        
        .material-content {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 40px;
            margin-bottom: 30px;
        }
        
        .material-header {
            display: flex;
            gap: 25px;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .material-icon-large {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            flex-shrink: 0;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .material-info {
            flex: 1;
        }
        
        .material-title-large {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 15px;
        }
        
        .material-meta-large {
            display: flex;
            gap: 25px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .material-tags-large {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .tag-large {
            padding: 8px 16px;
            background: rgba(102, 126, 234, 0.15);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            color: #667eea;
        }
        
        .material-description {
            font-size: 16px;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 30px;
            white-space: pre-line;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        
        .btn-primary {
            padding: 16px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            color: white;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-family: 'Outfit', sans-serif;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            padding: 16px 32px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Outfit', sans-serif;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .btn-secondary.bookmarked {
            background: rgba(255, 165, 0, 0.15);
            border-color: rgba(255, 165, 0, 0.3);
            color: #ffa500;
        }
        
        /* VIEWERS */
        .viewer-section {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            min-height: 500px;
        }
        
        /* PDF Viewer */
        .pdf-viewer-container {
            width: 100%;
            height: 800px;
            background: #1a1a2e;
            border-radius: 16px;
            overflow: hidden;
        }
        
        .pdf-viewer-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        /* Video Player */
        .video-player-container {
            width: 100%;
            background: #000;
            border-radius: 16px;
            overflow: hidden;
        }
        
        .video-player-container video {
            width: 100%;
            height: auto;
            display: block;
        }
        
        /* Image Viewer */
        .image-viewer-container {
            width: 100%;
            background: #1a1a2e;
            border-radius: 16px;
            overflow: hidden;
            text-align: center;
            padding: 20px;
        }
        
        .image-viewer-container img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
        }
        
        /* Document Preview */
        .document-preview {
            background: white;
            border-radius: 16px;
            padding: 40px;
            color: #333;
            min-height: 600px;
        }
        
        .document-preview h2 {
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .document-preview p {
            color: #555;
            line-height: 1.8;
            margin-bottom: 15px;
        }
        
        .download-prompt {
            text-align: center;
            padding: 60px 20px;
        }
        
        .download-prompt-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .download-prompt h3 {
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .download-prompt p {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 30px;
        }
        
        .assignment-viewer {
            padding: 30px;
        }
        
        .assignment-section {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .assignment-section h3 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #667eea;
        }
        
        .deadline-badge {
            display: inline-block;
            padding: 8px 16px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            color: #ef4444;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .discussions-section {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 40px;
        }
        
        .section-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .comment-form {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .comment-textarea {
            width: 100%;
            min-height: 120px;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 15px;
            font-family: 'Outfit', sans-serif;
            resize: vertical;
            margin-bottom: 15px;
        }
        
        .comment-textarea:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
            background: rgba(255, 255, 255, 0.08);
        }
        
        .comment-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .comment-item:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(102, 126, 234, 0.3);
        }
        
        .comment-header {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .comment-avatar {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .comment-info {
            flex: 1;
        }
        
        .comment-author {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .comment-time {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .comment-text {
            font-size: 15px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 15px;
        }
        
        .comment-actions {
            display: flex;
            gap: 20px;
            font-size: 14px;
        }
        
        .comment-action {
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .comment-action:hover {
            color: #667eea;
        }
        
        .replies {
            margin-left: 60px;
            margin-top: 15px;
            padding-left: 20px;
            border-left: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .sidebar-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .rating-overview {
            text-align: center;
            padding: 25px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            margin-bottom: 25px;
        }
        
        .rating-score {
            font-size: 56px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .rating-display {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .rating-count {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 20px;
        }
        
        .rating-bars {
            margin-top: 25px;
        }
        
        .rating-bar-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        
        .rating-label {
            font-size: 13px;
            width: 60px;
        }
        
        .rating-bar-bg {
            flex: 1;
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .rating-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #ffa500, #ff6b6b);
            border-radius: 4px;
        }
        
        .rating-bar-count {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            width: 30px;
            text-align: right;
        }
        
        .star-rating {
            display: flex;
            justify-content: center;
            gap: 8px;
            font-size: 36px;
            margin-top: 20px;
        }
        
        .star {
            cursor: pointer;
            transition: all 0.3s;
            color: rgba(255, 255, 255, 0.2);
        }
        
        .star:hover,
        .star.active {
            color: #ffa500;
            transform: scale(1.2);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .stat-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .related-item {
            display: flex;
            gap: 15px;
            padding: 16px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: white;
        }
        
        .related-item:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(5px);
        }
        
        .related-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        
        .related-info h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .related-info p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(10px);
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: rgba(26, 26, 36, 0.95);
            backdrop-filter: blur(40px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .modal-title {
            font-size: 24px;
            font-weight: 800;
        }
        
        .close-btn {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.08);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .close-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            transform: rotate(90deg);
        }
        
        .share-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .share-option {
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .share-option:hover {
            background: rgba(102, 126, 234, 0.2);
            border-color: rgba(102, 126, 234, 0.4);
        }
        
        .share-option-icon {
            font-size: 32px;
            margin-bottom: 8px;
        }
        
        .share-option-label {
            font-size: 13px;
            font-weight: 600;
        }
        
        .share-link {
            display: flex;
            gap: 10px;
        }
        
        .link-input {
            flex: 1;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 14px;
        }
        
        .copy-btn {
            padding: 14px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Outfit', sans-serif;
        }
        
        .copy-btn:hover {
            transform: translateY(-2px);
        }

        .submission-section {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 40px;
            margin-bottom: 30px;
        }

        .submission-form {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 12px;
            color: rgba(255, 255, 255, 0.9);
        }

        .file-upload-area {
            border: 2px dashed rgba(102, 126, 234, 0.3);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            background: rgba(102, 126, 234, 0.05);
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-upload-area:hover {
            border-color: rgba(102, 126, 234, 0.6);
            background: rgba(102, 126, 234, 0.1);
        }

        .file-upload-area.dragover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.15);
        }

        .file-upload-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .file-upload-text {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 8px;
        }

        .file-upload-hint {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
        }

        .file-input {
            display: none;
        }

        .selected-file {
            background: rgba(102, 126, 234, 0.15);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 14px;
            padding: 20px;
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .selected-file-icon {
            font-size: 32px;
        }

        .selected-file-info {
            flex: 1;
        }

        .selected-file-name {
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 5px;
        }

        .selected-file-size {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }

        .remove-file-btn {
            padding: 8px 16px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 10px;
            color: #ef4444;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .remove-file-btn:hover {
            background: rgba(239, 68, 68, 0.25);
        }

        .submission-textarea {
            width: 100%;
            min-height: 150px;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 15px;
            font-family: 'Outfit', sans-serif;
            resize: vertical;
        }

        .submission-textarea:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
            background: rgba(255, 255, 255, 0.08);
        }

        .submit-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            color: white;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Outfit', sans-serif;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.5);
        }

        .submit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .submission-status {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
        }

        .status-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .status-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #10b981;
        }

        .status-message {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 20px;
        }

        .submission-details {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 14px;
            padding: 20px;
            text-align: left;
            margin-top: 20px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.6);
        }

        .detail-value {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
        }

        .grade-badge {
            display: inline-block;
            padding: 8px 20px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 12px;
            font-size: 24px;
            font-weight: 800;
            color: white;
        }

        .resubmit-btn {
            margin-top: 20px;
            padding: 14px 28px;
            background: rgba(102, 126, 234, 0.15);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 14px;
            color: #667eea;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Outfit', sans-serif;
        }

        .resubmit-btn:hover {
            background: rgba(102, 126, 234, 0.25);
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 15px;
            display: none;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 4px;
            width: 0%;
            transition: width 0.3s;
        }

        .upload-status {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            display: none;
        }
        
        @media (max-width: 1200px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .material-title-large {
                font-size: 24px;
            }
            
            .material-header {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="bg-gradient"></div>
        <div class="bg-gradient"></div>
    </div>
    
    <div class="container">
        <div class="header">
            <a href="courseview.php?id=<?php echo $material['course_id']; ?>" class="back-btn">
                ← Back to Course
            </a>
        </div>
        
        <div class="main-grid">
            <!-- Main Content -->
            <div>
                <!-- Material Content -->
                <div class="material-content">
                    <div class="material-header">
                        <div class="material-icon-large"><?php echo $category_data['icon']; ?></div>
                        <div class="material-info">
                            <h1 class="material-title-large"><?php echo htmlspecialchars($material['title']); ?></h1>
                            <div class="material-meta-large">
                                <span>👤 <?php echo htmlspecialchars($material['uploader_name']); ?></span>
                                <span>📅 <?php echo timeAgo($material['created_at']); ?></span>
                                <span>📁 <?php echo $category_data['label']; ?></span>
                                <?php if ($material['file_size']): ?>
                                    <span>📄 <?php echo strtoupper($material['file_type']); ?>, <?php echo formatFileSize($material['file_size']); ?></span>
                                <?php endif; ?>
                                <?php if ($material['duration_minutes']): ?>
                                    <span>⏱️ <?php echo $material['duration_minutes']; ?> minutes</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($tags)): ?>
                                <div class="material-tags-large">
                                    <?php foreach ($tags as $tag): ?>
                                        <span class="tag-large"><?php echo htmlspecialchars($tag['tag_name']); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($material['short_description']): ?>
                        <p class="material-description"><?php echo htmlspecialchars($material['short_description']); ?></p>
                    <?php endif; ?>
                    
                    <div class="action-buttons">
                        <?php if ($material['allow_download'] && $material['file_path']): ?>
                            <a href="download.php?id=<?php echo $material_id; ?>" class="btn-primary">
                                <span>📥</span> Download
                            </a>
                        <?php endif; ?>
                        <button class="btn-secondary <?php echo $is_bookmarked ? 'bookmarked' : ''; ?>" id="bookmarkBtn" onclick="toggleBookmark()">
                            <span><?php echo $is_bookmarked ? '⭐' : '🔖'; ?></span> 
                            <?php echo $is_bookmarked ? 'Bookmarked' : 'Bookmark'; ?>
                        </button>
                        <button class="btn-secondary" onclick="showShareModal()">
                            <span>📤</span> Share
                        </button>
                    </div>
                </div>
                
                <!-- Dynamic Viewer Section -->
                                <!-- Dynamic Viewer Section -->
                <div class="viewer-section">
                    <?php 
                    // Determine how to display the file based on extension
                    if ($material['file_path'] && $file_extension):
                        if ($file_extension === 'pdf'):
                            // PDF Viewer
                    ?>
                        <div class="pdf-viewer-container">
                            <iframe src="<?php echo htmlspecialchars($material['file_path']); ?>#toolbar=1&navpanes=1&scrollbar=1"></iframe>
                        </div>
                    <?php 
                        elseif ($file_extension === 'mp4' || $file_extension === 'webm' || $file_extension === 'ogg'):
                            // Video Player
                    ?>
                        <div class="video-player-container">
                            <video controls>
                                <source src="<?php echo htmlspecialchars($material['file_path']); ?>" type="video/<?php echo $file_extension; ?>">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    <?php 
                        elseif ($file_extension === 'jpg' || $file_extension === 'jpeg' || $file_extension === 'png' || $file_extension === 'gif'):
                            // Image Viewer
                    ?>
                        <div class="image-viewer-container">
                            <img src="<?php echo htmlspecialchars($material['file_path']); ?>" alt="<?php echo htmlspecialchars($material['title']); ?>">
                        </div>
                    <?php 
                        else:
                            // For other file types, show download prompt
                    ?>
                        <div class="download-prompt">
                            <div class="download-prompt-icon">📄</div>
                            <h3><?php echo htmlspecialchars($material['title']); ?></h3>
                            <p>This file type cannot be previewed in the browser.</p>
                            <?php if ($material['description']): ?>
                                <div class="document-preview">
                                    <h2>Description</h2>
                                    <p><?php echo nl2br(htmlspecialchars($material['description'])); ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if ($material['allow_download']): ?>
                                <a href="download.php?id=<?php echo $material_id; ?>" class="btn-primary">
                                    <span>📥</span> Download to View
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php 
                        endif; // Close the file type check
                    endif; // Close the file_path check
                    ?>
                    
                    <?php if ($material['category'] === 'assignment'): ?>
                        <div class="assignment-viewer">
                            <div class="assignment-section">
                                <h3>Assignment Details</h3>
                                <?php if ($material['due_date']): ?>
                                    <div class="deadline-badge">
                                        📅 Due: <?php echo date('M j, Y \a\t g:i A', strtotime($material['due_date'])); ?>
                                    </div>
                                <?php endif; ?>
                                <p style="color: rgba(255,255,255,0.8); line-height: 1.8; margin-top: 20px;">
                                    <?php echo nl2br(htmlspecialchars($material['description'])); ?>
                                </p>
                            </div>
                        </div>

                        <?php if ($user_role === 'student'): ?>
                            <!-- Student Assignment Submission Section -->
                            <div class="submission-section">
                                <h2 class="section-title">📤 Your Submission</h2>
                                
                                <?php if ($submission): ?>
                                    <!-- Show submission status if already submitted -->
                                    <div class="submission-status">
                                        <div class="status-icon">
                                            <?php echo $submission['grade'] !== null ? '🎉' : '✅'; ?>
                                        </div>
                                        <h3 class="status-title">
                                            <?php echo $submission['grade'] !== null ? 'Assignment Graded' : 'Assignment Submitted'; ?>
                                        </h3>
                                        <p class="status-message">
                                            <?php 
                                            if ($submission['grade'] !== null) {
                                                echo 'Your assignment has been graded by the instructor.';
                                            } else {
                                                echo 'Your assignment has been submitted successfully. Waiting for grading.';
                                            }
                                            ?>
                                        </p>
                                        
                                        <div class="submission-details">
                                            <div class="detail-item">
                                                <span class="detail-label">Submitted On</span>
                                                <span class="detail-value"><?php echo date('M j, Y \a\t g:i A', strtotime($submission['submitted_at'])); ?></span>
                                            </div>
                                            <?php if ($submission['file_path']): ?>
                                                <div class="detail-item">
                                                    <span class="detail-label">File</span>
                                                    <span class="detail-value">
                                                        <a href="<?php echo htmlspecialchars($submission['file_path']); ?>" 
                                                        target="_blank" 
                                                        style="color: #667eea; text-decoration: underline;">
                                                            <?php echo basename($submission['file_path']); ?>
                                                        </a>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($submission['submission_text']): ?>
                                                <div class="detail-item" style="display: block;">
                                                    <span class="detail-label">Notes</span>
                                                    <p style="margin-top: 10px; color: rgba(255,255,255,0.8);">
                                                        <?php echo nl2br(htmlspecialchars($submission['submission_text'])); ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($submission['grade'] !== null): ?>
                                                <div class="detail-item">
                                                    <span class="detail-label">Grade</span>
                                                    <span class="grade-badge"><?php echo number_format($submission['grade'], 0); ?>/100</span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($submission['feedback']): ?>
                                                <div class="detail-item" style="display: block;">
                                                    <span class="detail-label">Instructor Feedback</span>
                                                    <p style="margin-top: 10px; color: rgba(255,255,255,0.8); background: rgba(102, 126, 234, 0.1); padding: 15px; border-radius: 10px;">
                                                        <?php echo nl2br(htmlspecialchars($submission['feedback'])); ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($submission['grade'] === null): ?>
                                            <button class="resubmit-btn" onclick="showResubmitForm()">
                                                🔄 Update Submission
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Resubmit form (hidden by default) -->
                                    <div id="resubmitForm" style="display: none; margin-top: 30px;">
                                        <h3 style="margin-bottom: 20px;">Update Your Submission</h3>
                                        <form id="submissionForm" class="submission-form" enctype="multipart/form-data">
                                            <div class="form-group">
                                                <label class="form-label">Upload File (PDF only) *</label>
                                                <div class="file-upload-area" id="fileUploadArea">
                                                    <div class="file-upload-icon">📄</div>
                                                    <div class="file-upload-text">Click to upload or drag and drop</div>
                                                    <div class="file-upload-hint">PDF files only (Max 10MB)</div>
                                                </div>
                                                <input type="file" id="fileInput" name="assignment_file" class="file-input" accept=".pdf" required>
                                                <div id="selectedFileDisplay"></div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label class="form-label">Additional Notes (Optional)</label>
                                                <textarea id="submissionText" name="submission_text" class="submission-textarea" 
                                                        placeholder="Add any notes or comments about your submission..."><?php echo htmlspecialchars($submission['submission_text'] ?? ''); ?></textarea>
                                            </div>
                                            
                                            <div class="progress-bar" id="progressBar">
                                                <div class="progress-fill" id="progressFill"></div>
                                            </div>
                                            <div class="upload-status" id="uploadStatus"></div>
                                            
                                            <button type="submit" class="submit-btn" id="submitBtn">
                                                📤 Update Submission
                                            </button>
                                        </form>
                                    </div>
                                    
                                <?php else: ?>
                                    <!-- Show submission form if not submitted yet -->
                                    <form id="submissionForm" class="submission-form" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label class="form-label">Upload File (PDF only) *</label>
                                            <div class="file-upload-area" id="fileUploadArea">
                                                <div class="file-upload-icon">📄</div>
                                                <div class="file-upload-text">Click to upload or drag and drop</div>
                                                <div class="file-upload-hint">PDF files only (Max 10MB)</div>
                                            </div>
                                            <input type="file" id="fileInput" name="assignment_file" class="file-input" accept=".pdf" required>
                                            <div id="selectedFileDisplay"></div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Additional Notes (Optional)</label>
                                            <textarea id="submissionText" name="submission_text" class="submission-textarea" 
                                                    placeholder="Add any notes or comments about your submission..."></textarea>
                                        </div>
                                        
                                        <div class="progress-bar" id="progressBar">
                                            <div class="progress-fill" id="progressFill"></div>
                                        </div>
                                        <div class="upload-status" id="uploadStatus"></div>
                                        
                                        <button type="submit" class="submit-btn" id="submitBtn">
                                            📤 Submit Assignment
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Discussions Section -->
                <div class="discussions-section">
                    <h2 class="section-title">💬 Discussions (<span id="commentCount"><?php echo $material['total_comments']; ?></span>)</h2>
                    
                    <div class="comment-form">
                        <textarea class="comment-textarea" id="newComment" placeholder="Share your thoughts, ask questions, or help others..."></textarea>
                        <button class="btn-primary" onclick="postComment()">Post Comment</button>
                    </div>
                    
                    <div id="commentsContainer">
                        <?php if (empty($comments)): ?>
                            <p style="text-align: center; color: rgba(255,255,255,0.5); padding: 40px;">
                                No comments yet. Be the first to comment!
                            </p>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment-item">
                                    <div class="comment-header">
                                        <div class="comment-avatar"><?php echo getInitials($comment['commenter_name']); ?></div>
                                        <div class="comment-info">
                                            <div class="comment-author"><?php echo htmlspecialchars($comment['commenter_name']); ?></div>
                                            <div class="comment-time"><?php echo timeAgo($comment['created_at']); ?></div>
                                        </div>
                                    </div>
                                    <p class="comment-text"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                                    <div class="comment-actions">
                                        <span class="comment-action" onclick="likeComment(<?php echo $comment['comment_id']; ?>, this)">
                                            👍 <span class="like-count"><?php echo $comment['likes_count']; ?></span> Likes
                                        </span>
                                        <span class="comment-action" onclick="showReplyForm(<?php echo $comment['comment_id']; ?>)">💬 Reply</span>
                                    </div>
                                    
                                    <?php if (!empty($comment['replies'])): ?>
                                        <div class="replies">
                                            <?php foreach ($comment['replies'] as $reply): ?>
                                                <div class="comment-item">
                                                    <div class="comment-header">
                                                        <div class="comment-avatar" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                                            <?php echo getInitials($reply['commenter_name']); ?>
                                                        </div>
                                                        <div class="comment-info">
                                                            <div class="comment-author"><?php echo htmlspecialchars($reply['commenter_name']); ?></div>
                                                            <div class="comment-time"><?php echo timeAgo($reply['created_at']); ?></div>
                                                        </div>
                                                    </div>
                                                    <p class="comment-text"><?php echo nl2br(htmlspecialchars($reply['comment_text'])); ?></p>
                                                    <div class="comment-actions">
                                                        <span class="comment-action" onclick="likeComment(<?php echo $reply['comment_id']; ?>, this)">
                                                            👍 <span class="like-count"><?php echo $reply['likes_count']; ?></span> Likes
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div id="reply-form-<?php echo $comment['comment_id']; ?>" style="display: none; margin-top: 15px; margin-left: 60px;">
                                        <textarea class="comment-textarea" id="reply-text-<?php echo $comment['comment_id']; ?>" placeholder="Write your reply..." style="min-height: 80px;"></textarea>
                                        <div style="display: flex; gap: 10px;">
                                            <button class="btn-primary" onclick="postReply(<?php echo $comment['comment_id']; ?>)">Post Reply</button>
                                            <button class="btn-secondary" onclick="hideReplyForm(<?php echo $comment['comment_id']; ?>)">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div>
                <!-- Rating Card -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">⭐ Rating & Reviews</h3>
                    
                    <div class="rating-overview">
                        <div class="rating-score"><?php echo number_format($material['rating_average'], 1); ?></div>
                        <div class="rating-display">
                            <?php 
                            $stars = round($material['rating_average']);
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $stars ? '<span style="color: #ffa500;">★</span>' : '<span style="color: rgba(255,255,255,0.2);">★</span>';
                            }
                            ?>
                        </div>
                        <div class="rating-count">Based on <?php echo $material['rating_count']; ?> ratings</div>
                        
                        <div class="star-rating" id="starRating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?php echo $i <= $user_rating ? 'active' : ''; ?>" data-rating="<?php echo $i; ?>" onclick="rateMaterial(<?php echo $i; ?>)">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="rating-bars">
                        <?php 
                        $total_ratings = $rating_distribution['five_star'] + $rating_distribution['four_star'] + 
                                        $rating_distribution['three_star'] + $rating_distribution['two_star'] + 
                                        $rating_distribution['one_star'];
                        
                        $rating_map = ['five_star', 'four_star', 'three_star', 'two_star', 'one_star'];
                        foreach ([5, 4, 3, 2, 1] as $index => $star):
                            $count = $rating_distribution[$rating_map[$index]];
                            $percentage = $total_ratings > 0 ? ($count / $total_ratings) * 100 : 0;
                        ?>
                            <div class="rating-bar-item">
                                <span class="rating-label"><?php echo $star; ?> ★</span>
                                <div class="rating-bar-bg">
                                    <div class="rating-bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <span class="rating-bar-count"><?php echo $count; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Stats Card -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">📊 Statistics</h3>
                    
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($material['views_count']); ?></div>
                            <div class="stat-label">Views</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($material['downloads_count']); ?></div>
                            <div class="stat-label">Downloads</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $material['rating_count']; ?></div>
                            <div class="stat-label">Ratings</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $material['total_comments']; ?></div>
                            <div class="stat-label">Comments</div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">⚡ Quick Actions</h3>
                    
                    <button class="btn-primary" style="width: 100%; margin-bottom: 10px;" onclick="window.location='ai-tutor.php?course_id=<?php echo $material['course_id']; ?>'">
                        🤖 Ask AI About This
                    </button>
                    <button class="btn-primary" style="width: 100%; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);" onclick="window.location='quiz.php?material_id=<?php echo $material_id; ?>'">
                        🎯 Take Quiz (AI)
                    </button>
                </div>
                
                <!-- Related Materials -->
                <?php if (!empty($related_materials)): ?>
                    <div class="sidebar-card">
                        <h3 class="sidebar-title">🔗 Related Materials</h3>
                        
                        <?php foreach ($related_materials as $related): 
                            $related_category = getCategoryDisplay($related['category']);
                        ?>
                            <a href="material-detail.php?id=<?php echo $related['material_id']; ?>" class="related-item">
                                <div class="related-icon"><?php echo $related_category['icon']; ?></div>
                                <div class="related-info">
                                    <h4><?php echo htmlspecialchars($related['title']); ?></h4>
                                    <p><?php echo $related_category['label']; ?> • <?php echo $related['comment_count']; ?> comments</p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Share Modal -->
    <div id="shareModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">📤 Share Material</h2>
                <button class="close-btn" onclick="closeShareModal()">×</button>
            </div>
            
            <div class="share-options">
                <div class="share-option" onclick="shareVia('facebook')">
                    <div class="share-option-icon">f</div>
                    <div class="share-option-label">Facebook</div>
                </div>
                <div class="share-option" onclick="shareVia('twitter')">
                    <div class="share-option-icon">𝕏</div>
                    <div class="share-option-label">Twitter</div>
                </div>
                <div class="share-option" onclick="shareVia('linkedin')">
                    <div class="share-option-icon">in</div>
                    <div class="share-option-label">LinkedIn</div>
                </div>
                <div class="share-option" onclick="shareVia('email')">
                    <div class="share-option-icon">✉️</div>
                    <div class="share-option-label">Email</div>
                </div>
                <div class="share-option" onclick="shareVia('whatsapp')">
                    <div class="share-option-icon">💬</div>
                    <div class="share-option-label">WhatsApp</div>
                </div>
                <div class="share-option" onclick="shareVia('copy')">
                    <div class="share-option-icon">🔗</div>
                    <div class="share-option-label">Copy Link</div>
                </div>
            </div>
            
            <div class="share-link">
                <input type="text" class="link-input" id="shareLink" value="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/material-detail.php?id=' . $material_id; ?>" readonly>
                <button class="copy-btn" onclick="copyLink()">Copy</button>
            </div>
        </div>
    </div>
    
    <script>
        const materialId = <?php echo $material_id; ?>;
        const userId = <?php echo $user_id; ?>;
        let isBookmarked = <?php echo $is_bookmarked ? 'true' : 'false'; ?>;
        let userRating = <?php echo $user_rating; ?>;
        
        // Bookmark functionality
        function toggleBookmark() {
            fetch('ajax/toggle_bookmark.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    material_id: materialId,
                    user_id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    isBookmarked = data.bookmarked;
                    const btn = document.getElementById('bookmarkBtn');
                    if (isBookmarked) {
                        btn.classList.add('bookmarked');
                        btn.innerHTML = '<span>⭐</span> Bookmarked';
                    } else {
                        btn.classList.remove('bookmarked');
                        btn.innerHTML = '<span>🔖</span> Bookmark';
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        // Rating functionality
        function rateMaterial(rating) {
            fetch('ajax/rate_material.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    material_id: materialId,
                    user_id: userId,
                    rating: rating
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    userRating = rating;
                    document.querySelectorAll('.star').forEach((s, index) => {
                        if (index < rating) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                    alert(`Thank you! You rated this material ${rating} stars.`);
                    setTimeout(() => location.reload(), 1000);
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        // Comment functionality
        function postComment() {
            const comment = document.getElementById('newComment').value.trim();
            if (!comment) {
                alert('Please write a comment');
                return;
            }
            
            fetch('ajax/post_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    material_id: materialId,
                    user_id: userId,
                    comment_text: comment
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Comment posted successfully!');
                    location.reload();
                } else {
                    alert('Error posting comment');
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        // Reply functionality
        function showReplyForm(commentId) {
            document.getElementById('reply-form-' + commentId).style.display = 'block';
        }
        
        function hideReplyForm(commentId) {
            document.getElementById('reply-form-' + commentId).style.display = 'none';
        }
        
        function postReply(parentCommentId) {
            const replyText = document.getElementById('reply-text-' + parentCommentId).value.trim();
            if (!replyText) {
                alert('Please write a reply');
                return;
            }
            
            fetch('ajax/post_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    material_id: materialId,
                    user_id: userId,
                    comment_text: replyText,
                    parent_comment_id: parentCommentId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Reply posted successfully!');
                    location.reload();
                } else {
                    alert('Error posting reply');
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        // Like comment functionality
        function likeComment(commentId, element) {
            fetch('ajax/like_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    comment_id: commentId,
                    user_id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const likeCountSpan = element.querySelector('.like-count');
                    likeCountSpan.textContent = data.likes_count;
                    element.style.color = '#667eea';
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        // Share functionality
        function showShareModal() {
            document.getElementById('shareModal').classList.add('active');
        }
        
        function closeShareModal() {
            document.getElementById('shareModal').classList.remove('active');
        }
        
        function shareVia(platform) {
            const link = document.getElementById('shareLink').value;
            const title = <?php echo json_encode($material['title']); ?>;
            
            const shareUrls = {
                facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(link)}`,
                twitter: `https://twitter.com/intent/tweet?url=${encodeURIComponent(link)}&text=${encodeURIComponent(title)}`,
                linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(link)}`,
                email: `mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent(link)}`,
                whatsapp: `https://wa.me/?text=${encodeURIComponent(title + ' ' + link)}`,
                copy: null
            };
            
            if (platform === 'copy') {
                copyLink();
            } else {
                window.open(shareUrls[platform], '_blank');
            }
        }
        
        function copyLink() {
            const link = document.getElementById('shareLink');
            link.select();
            document.execCommand('copy');
            alert('Link copied to clipboard!');
        }
        
        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('shareModal');
            if (event.target === modal) {
                closeShareModal();
            }
        };
        
        // Initialize on load
        window.addEventListener('load', function() {
            // Animate elements
            document.querySelectorAll('.stat-item').forEach((stat, index) => {
                stat.style.opacity = '0';
                stat.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    stat.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    stat.style.opacity = '1';
                    stat.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            document.querySelectorAll('.comment-item').forEach((comment, index) => {
                comment.style.opacity = '0';
                comment.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    comment.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                    comment.style.opacity = '1';
                    comment.style.transform = 'translateX(0)';
                }, 200 + (index * 100));
            });
        });
        
        // Auto-expand textarea
        const textarea = document.getElementById('newComment');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }

        // File upload functionality
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileInput = document.getElementById('fileInput');
        const selectedFileDisplay = document.getElementById('selectedFileDisplay');
        const submissionForm = document.getElementById('submissionForm');
        let selectedFile = null;

        if (fileUploadArea && fileInput) {
            // Click to upload
            fileUploadArea.addEventListener('click', () => {
                fileInput.click();
            });
            
            // File selection
            fileInput.addEventListener('change', (e) => {
                handleFileSelect(e.target.files[0]);
            });
            
            // Drag and drop
            fileUploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                fileUploadArea.classList.add('dragover');
            });
            
            fileUploadArea.addEventListener('dragleave', () => {
                fileUploadArea.classList.remove('dragover');
            });
            
            fileUploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                fileUploadArea.classList.remove('dragover');
                const file = e.dataTransfer.files[0];
                if (file && file.type === 'application/pdf') {
                    fileInput.files = e.dataTransfer.files;
                    handleFileSelect(file);
                } else {
                    alert('Please upload a PDF file only');
                }
            });
        }

        function handleFileSelect(file) {
            if (!file) return;
            
            // Validate file type
            if (file.type !== 'application/pdf') {
                alert('Please upload a PDF file only');
                fileInput.value = '';
                return;
            }
            
            // Validate file size (10MB max)
            if (file.size > 10 * 1024 * 1024) {
                alert('File size must be less than 10MB');
                fileInput.value = '';
                return;
            }
            
            selectedFile = file;
            
            // Display selected file
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            selectedFileDisplay.innerHTML = `
                <div class="selected-file">
                    <div class="selected-file-icon">📄</div>
                    <div class="selected-file-info">
                        <div class="selected-file-name">${file.name}</div>
                        <div class="selected-file-size">${fileSize} MB</div>
                    </div>
                    <button type="button" class="remove-file-btn" onclick="removeFile()">Remove</button>
                </div>
            `;
        }

        function removeFile() {
            selectedFile = null;
            fileInput.value = '';
            selectedFileDisplay.innerHTML = '';
        }

        function showResubmitForm() {
            document.getElementById('resubmitForm').style.display = 'block';
            document.getElementById('resubmitForm').scrollIntoView({ behavior: 'smooth' });
        }

        // Form submission
        if (submissionForm) {
            submissionForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                if (!selectedFile) {
                    alert('Please select a PDF file to upload');
                    return;
                }
                
                const submitBtn = document.getElementById('submitBtn');
                const progressBar = document.getElementById('progressBar');
                const progressFill = document.getElementById('progressFill');
                const uploadStatus = document.getElementById('uploadStatus');
                
                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.textContent = 'Uploading...';
                
                // Show progress bar
                progressBar.style.display = 'block';
                uploadStatus.style.display = 'block';
                uploadStatus.textContent = 'Preparing upload...';
                
                // Prepare form data
                const formData = new FormData();
                formData.append('material_id', materialId);
                formData.append('user_id', userId);
                formData.append('assignment_file', selectedFile);
                formData.append('submission_text', document.getElementById('submissionText').value);
                
                try {
                    // Upload with progress
                    const xhr = new XMLHttpRequest();
                    
                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            const percentComplete = (e.loaded / e.total) * 100;
                            progressFill.style.width = percentComplete + '%';
                            uploadStatus.textContent = `Uploading: ${Math.round(percentComplete)}%`;
                        }
                    });
                    
                    xhr.addEventListener('load', () => {
                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                uploadStatus.textContent = 'Upload complete!';
                                alert('Assignment submitted successfully!');
                                location.reload();
                            } else {
                                throw new Error(response.message || 'Upload failed');
                            }
                        } else {
                            throw new Error('Upload failed');
                        }
                    });
                    
                    xhr.addEventListener('error', () => {
                        throw new Error('Network error occurred');
                    });
                    
                    xhr.open('POST', 'ajax/submit_assignment.php');
                    xhr.send(formData);
                    
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error submitting assignment: ' + error.message);
                    submitBtn.disabled = false;
                    submitBtn.textContent = '📤 Submit Assignment';
                    progressBar.style.display = 'none';
                    uploadStatus.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>