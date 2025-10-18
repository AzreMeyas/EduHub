<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? null;

if (!$course_id) {
    header("Location: tdashboard.php");
    exit();
}

$conn = getDBConnection();

// Verify teacher owns this course
$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = ? AND instructor_id = ?");
$stmt->bind_param("ii", $course_id, $teacher_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header("Location: tdashboard.php");
    exit();
}

// Handle Delete Material
if (isset($_GET['delete'])) {
    $material_id = $_GET['delete'];
    
    // Verify material belongs to this course
    $stmt = $conn->prepare("SELECT * FROM materials WHERE material_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $material_id, $course_id);
    $stmt->execute();
    $material = $stmt->get_result()->fetch_assoc();
    
    if ($material) {
        // Delete material tags
        $stmt = $conn->prepare("DELETE FROM material_tags WHERE material_id = ?");
        $stmt->bind_param("i", $material_id);
        $stmt->execute();
        
        // Delete material comments
        $stmt = $conn->prepare("DELETE FROM material_comments WHERE material_id = ?");
        $stmt->bind_param("i", $material_id);
        $stmt->execute();
        
        // Delete material ratings
        $stmt = $conn->prepare("DELETE FROM material_ratings WHERE material_id = ?");
        $stmt->bind_param("i", $material_id);
        $stmt->execute();
        
        // Delete the material
        $stmt = $conn->prepare("DELETE FROM materials WHERE material_id = ?");
        $stmt->bind_param("i", $material_id);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Material deleted successfully!";
    }
    
    header("Location: editmaterail.php?course_id=" . $course_id);
    exit();
}

// Get all materials for this course
$stmt = $conn->prepare("SELECT m.*, 
                       (SELECT GROUP_CONCAT(tag_name) FROM material_tags WHERE material_id = m.material_id) as tags
                       FROM materials m 
                       WHERE m.course_id = ? 
                       ORDER BY m.created_at DESC");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$materials = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

function getFileIcon($category) {
    switch($category) {
        case 'lecture_notes': return '📄';
        case 'video': return '🎥';
        case 'assignment': return '📝';
        case 'quiz': return '🎯';
        case 'reference': return '📚';
        default: return '📁';
    }
}

function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Materials - EduHub</title>
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
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .back-btn {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: white;
        }
        
        .back-btn:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: translateX(-3px);
        }
        
        .header-info h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .header-info p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 15px;
        }
        
        .add-btn {
            padding: 14px 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .materials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .material-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 30px;
            transition: all 0.4s;
            position: relative;
        }
        
        .material-card:hover {
            transform: translateY(-8px);
            border-color: rgba(102, 126, 234, 0.4);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.3);
        }
        
        .material-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }
        
        .material-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }
        
        .material-type {
            padding: 6px 12px;
            background: rgba(102, 126, 234, 0.2);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            color: #667eea;
        }
        
        .material-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .material-description {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .material-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 20px;
        }
        
        .material-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .tag {
            padding: 4px 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            font-size: 12px;
        }
        
        .material-actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .btn-delete {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }
        
        .btn-delete:hover {
            background: rgba(239, 68, 68, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        
        .empty-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .empty-title {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .empty-text {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 30px;
        }
        
        @media (max-width: 768px) {
            .materials-grid {
                grid-template-columns: 1fr;
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
            <div class="header-left">
                <a href="manage-course.php?course_id=<?php echo $course_id; ?>" class="back-btn">←</a>
                <div class="header-info">
                    <h1>Edit Course Materials</h1>
                    <p><?php echo htmlspecialchars($course['title']); ?> • <?php echo htmlspecialchars($course['course_code']); ?></p>
                </div>
            </div>
            <a href="upload-material.php?course_id=<?php echo $course_id; ?>" class="add-btn">+ Add Material</a>
        </div>
        
        <?php if (empty($materials)): ?>
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h2 class="empty-title">No Materials Yet</h2>
                <p class="empty-text">Get started by adding your first course material</p>
                <a href="upload-material.php?course_id=<?php echo $course_id; ?>" class="add-btn">+ Add Your First Material</a>
            </div>
        <?php else: ?>
            <div class="materials-grid">
                <?php foreach ($materials as $material): ?>
                    <div class="material-card">
                        <div class="material-header">
                            <div class="material-icon"><?php echo getFileIcon($material['category']); ?></div>
                            <span class="material-type"><?php echo ucwords(str_replace('_', ' ', $material['category'])); ?></span>
                        </div>
                        
                        <h3 class="material-title"><?php echo htmlspecialchars($material['title']); ?></h3>
                        <p class="material-description"><?php echo htmlspecialchars($material['description'] ?? ''); ?></p>
                        
                        <div class="material-meta">
                            <span>👁️ <?php echo $material['views_count']; ?> views</span>
                            <span>⬇️ <?php echo $material['downloads_count']; ?> downloads</span>
                            <?php if ($material['file_size']): ?>
                                <span>📦 <?php echo formatFileSize($material['file_size']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($material['tags']): ?>
                            <div class="material-tags">
                                <?php foreach (explode(',', $material['tags']) as $tag): ?>
                                    <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="material-actions">
                            <a href="edit-single-material.php?material_id=<?php echo $material['material_id']; ?>&course_id=<?php echo $course_id; ?>" 
                               class="action-btn btn-edit">
                                ✏️ Edit
                            </a>
                            <button onclick="confirmDelete(<?php echo $material['material_id']; ?>, '<?php echo htmlspecialchars($material['title'], ENT_QUOTES); ?>')" 
                                    class="action-btn btn-delete">
                                🗑️ Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function confirmDelete(materialId, materialTitle) {
            if (confirm(`Are you sure you want to delete "${materialTitle}"?\n\nThis action cannot be undone and will remove all associated comments, ratings, and data.`)) {
                window.location.href = `editmaterail.php?course_id=<?php echo $course_id; ?>&delete=${materialId}`;
            }
        }
        
        // Success message display
        <?php if (isset($_SESSION['success_message'])): ?>
            showNotification('<?php echo $_SESSION['success_message']; ?>', 'success');
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            const bgColor = type === 'success' ? 
                'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)' : 
                type === 'error' ? 
                'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)' :
                'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            
            notification.style.cssText = `
                position: fixed;
                top: 30px;
                right: 30px;
                background: ${bgColor};
                color: white;
                padding: 20px 30px;
                border-radius: 16px;
                box-shadow: 0 10px 40px rgba(102, 126, 234, 0.4);
                z-index: 10000;
                font-weight: 600;
                animation: slideIn 0.5s ease-out;
                max-width: 400px;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.5s ease-out';
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }
        
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(400px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(400px); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>