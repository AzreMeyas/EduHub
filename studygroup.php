<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Handle create group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    $stmt = $conn->prepare("INSERT INTO study_groups (group_name, description, course_id, created_by, max_members, meeting_schedule) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiss", $_POST['group_name'], $_POST['description'], $_POST['course_id'], $user_id, $_POST['max_members'], $_POST['meeting_schedule']);
    $stmt->execute();
    $new_group_id = $stmt->insert_id;
    
    $stmt = $conn->prepare("INSERT INTO study_group_members (group_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $new_group_id, $user_id);
    $stmt->execute();
    
    header("Location: studygroup.php");
    exit();
}

// Handle send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $stmt = $conn->prepare("INSERT INTO study_group_messages (group_id, user_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $_POST['group_id'], $user_id, $_POST['message']);
    $stmt->execute();
    header("Location: studygroup.php?view=" . $_POST['group_id']);
    exit();
}

// Handle join group
if (isset($_GET['join'])) {
    $stmt = $conn->prepare("INSERT IGNORE INTO study_group_members (group_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $_GET['join'], $user_id);
    $stmt->execute();
    header("Location: studygroup.php?view=" . $_GET['join']);
    exit();
}

// Handle leave group
if (isset($_GET['leave'])) {
    $stmt = $conn->prepare("DELETE FROM study_group_members WHERE group_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $_GET['leave'], $user_id);
    $stmt->execute();
    header("Location: studygroup.php");
    exit();
}

// Handle delete group
if (isset($_GET['delete'])) {
    // Check if user is the creator
    $stmt = $conn->prepare("SELECT created_by FROM study_groups WHERE group_id = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result && $result['created_by'] == $user_id) {
        // Delete messages first
        $stmt = $conn->prepare("DELETE FROM study_group_messages WHERE group_id = ?");
        $stmt->bind_param("i", $_GET['delete']);
        $stmt->execute();
        
        // Delete members
        $stmt = $conn->prepare("DELETE FROM study_group_members WHERE group_id = ?");
        $stmt->bind_param("i", $_GET['delete']);
        $stmt->execute();
        
        // Delete group
        $stmt = $conn->prepare("DELETE FROM study_groups WHERE group_id = ?");
        $stmt->bind_param("i", $_GET['delete']);
        $stmt->execute();
    }
    
    header("Location: studygroup.php");
    exit();
}

// View mode
$view_group = $_GET['view'] ?? null;
$tab = $_GET['tab'] ?? 'all';

// Get single group details if viewing
if ($view_group) {
    $stmt = $conn->prepare("SELECT sg.*, c.title as course_title, c.course_code, u.full_name as creator_name,
                           COUNT(DISTINCT sgm.member_id) as member_count,
                           (SELECT COUNT(*) FROM study_group_members WHERE group_id = sg.group_id AND user_id = ?) as is_member
                           FROM study_groups sg
                           LEFT JOIN courses c ON sg.course_id = c.course_id
                           LEFT JOIN users u ON sg.created_by = u.user_id
                           LEFT JOIN study_group_members sgm ON sg.group_id = sgm.group_id
                           WHERE sg.group_id = ?
                           GROUP BY sg.group_id");
    $stmt->bind_param("ii", $user_id, $view_group);
    $stmt->execute();
    $group = $stmt->get_result()->fetch_assoc();
    
    // Get members
    $stmt = $conn->prepare("SELECT u.user_id, u.full_name FROM study_group_members sgm 
                           JOIN users u ON sgm.user_id = u.user_id 
                           WHERE sgm.group_id = ? ORDER BY sgm.joined_at");
    $stmt->bind_param("i", $view_group);
    $stmt->execute();
    $members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get messages
    $stmt = $conn->prepare("SELECT m.*, u.full_name FROM study_group_messages m 
                           JOIN users u ON m.user_id = u.user_id 
                           WHERE m.group_id = ? ORDER BY m.created_at DESC LIMIT 50");
    $stmt->bind_param("i", $view_group);
    $stmt->execute();
    $messages = array_reverse($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
}

// Get all groups
$query = "SELECT sg.*, c.title as course_title, c.course_code,
          COUNT(sgm.member_id) as member_count,
          (SELECT COUNT(*) FROM study_group_members WHERE group_id = sg.group_id AND user_id = ?) as is_member
          FROM study_groups sg
          LEFT JOIN courses c ON sg.course_id = c.course_id
          LEFT JOIN study_group_members sgm ON sg.group_id = sgm.group_id";

if ($tab === 'my_groups') {
    $query .= " WHERE sg.group_id IN (SELECT group_id FROM study_group_members WHERE user_id = ?)";
}

$query .= " GROUP BY sg.group_id ORDER BY sg.created_at DESC";

if ($tab === 'my_groups') {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
} else {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$groups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get courses
$courses = $conn->query("SELECT course_id, title, course_code FROM courses")->fetch_all(MYSQLI_ASSOC);

function getMembers($conn, $group_id) {
    $stmt = $conn->prepare("SELECT u.full_name FROM study_group_members sgm JOIN users u ON sgm.user_id = u.user_id WHERE sgm.group_id = ? LIMIT 4");
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getInitials($name) {
    $parts = explode(' ', $name);
    return strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}

function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return date('M j', strtotime($datetime));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Groups - EduHub</title>
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
        
        .header-title h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .header-title p {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .create-btn {
            padding: 14px 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .create-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .tab {
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            transition: all 0.3s;
            font-weight: 600;
            text-decoration: none;
            color: white;
            cursor: pointer;
        }
        
        .tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
        }
        
        .groups-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .group-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 30px;
            transition: all 0.4s;
            cursor: pointer;
        }
        
        .group-card:hover {
            transform: translateY(-8px);
            border-color: rgba(102, 126, 234, 0.4);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.3);
        }
        
        .group-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }
        
        .group-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }
        
        .group-status {
            padding: 6px 14px;
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .group-status.full {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }
        
        .group-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .group-course {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 15px;
        }
        
        .group-description {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .group-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 20px;
        }
        
        .group-members {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .member-avatars {
            display: flex;
        }
        
        .member-avatar {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: 2px solid #0f0f1e;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            margin-left: -10px;
        }
        
        .member-avatar:first-child {
            margin-left: 0;
        }
        
        .group-actions {
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
        }
        
        .btn-join {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-joined {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
        }
        
        /* Group View */
        .group-view {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 30px;
        }
        
        .group-info-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .group-info-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }
        
        .leave-btn {
            padding: 10px 20px;
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .leave-btn:hover {
            background: rgba(239, 68, 68, 0.3);
        }
        
        .delete-btn {
            padding: 10px 20px;
            background: rgba(239, 68, 68, 0.3);
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #ef4444;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            margin-left: 10px;
        }
        
        .delete-btn:hover {
            background: rgba(239, 68, 68, 0.5);
        }
        
        .group-actions-header {
            display: flex;
            gap: 10px;
        }
        
        .chat-box {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 25px;
            height: 600px;
            display: flex;
            flex-direction: column;
        }
        
        .chat-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .messages {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        
        .message {
            margin-bottom: 15px;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        
        .message-author {
            font-weight: 600;
            font-size: 14px;
        }
        
        .message-time {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .message-text {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            line-height: 1.5;
        }
        
        .message-form {
            display: flex;
            gap: 10px;
        }
        
        .message-input {
            flex: 1;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-family: 'Outfit', sans-serif;
        }
        
        .message-input:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .send-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 700;
            cursor: pointer;
        }
        
        .members-list {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 25px;
        }
        
        .members-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .member-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            margin-bottom: 10px;
        }
        
        .member-item .member-avatar {
            width: 40px;
            height: 40px;
            margin-left: 0;
        }
        
        .member-name {
            font-weight: 600;
            font-size: 14px;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
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
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .modal-title {
            font-size: 28px;
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
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 15px;
            font-family: 'Outfit', sans-serif;
        }
        
        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
            background: rgba(255, 255, 255, 0.08);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .form-select option {
            background: #1a1a24;
        }
        
        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            color: white;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
        }
        
        @media (max-width: 1024px) {
            .group-view {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .groups-grid {
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
        <?php if ($view_group && $group): ?>
            <!-- GROUP DETAIL VIEW -->
            <a href="studygroup.php" class="back-btn">← Back to Groups</a>
            
            <div class="group-info-card">
                <div class="group-info-header">
                    <div>
                        <div class="group-icon" style="margin-bottom: 15px;">💻</div>
                        <h1 class="group-title"><?php echo htmlspecialchars($group['group_name']); ?></h1>
                        <p class="group-course">📚 <?php echo htmlspecialchars($group['course_title']); ?></p>
                        <div class="group-meta">
                            <span>👥 <?php echo $group['member_count']; ?>/<?php echo $group['max_members']; ?> Members</span>
                            <span>📅 <?php echo htmlspecialchars($group['meeting_schedule']); ?></span>
                        </div>
                    </div>
                    <div class="group-actions-header">
                        <?php if ($group['created_by'] == $user_id): ?>
                            <!-- Creator/Admin sees delete button only -->
                            <a href="?delete=<?php echo $group['group_id']; ?>" class="delete-btn" 
                               onclick="return confirm('Are you sure you want to delete this group? This action cannot be undone and all messages will be lost.')">Delete Group</a>
                        <?php elseif ($group['is_member'] > 0): ?>
                            <!-- Regular member (not creator) sees leave button -->
                            <a href="?leave=<?php echo $group['group_id']; ?>" class="leave-btn" 
                               onclick="return confirm('Leave this group?')">Leave</a>
                        <?php endif; ?>
                        <!-- Non-members see nothing here -->
                    </div>
                </div>
                <p style="color: rgba(255,255,255,0.8); line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($group['description'])); ?>
                </p>
            </div>
            
            <div class="group-view">
                <!-- CHAT -->
                <div class="chat-box">
                    <h2 class="chat-title">💬 Group Chat</h2>
                    
                    <?php if ($group['is_member']): ?>
                        <div class="messages" id="messages">
                            <?php if (empty($messages)): ?>
                                <p style="text-align:center; color: rgba(255,255,255,0.5); padding: 40px;">
                                    No messages yet. Start the conversation!
                                </p>
                            <?php else: ?>
                                <?php foreach ($messages as $msg): ?>
                                    <div class="message">
                                        <div class="message-header">
                                            <span class="message-author"><?php echo htmlspecialchars($msg['full_name']); ?></span>
                                            <span class="message-time"><?php echo timeAgo($msg['created_at']); ?></span>
                                        </div>
                                        <p class="message-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" class="message-form">
                            <input type="hidden" name="send_message" value="1">
                            <input type="hidden" name="group_id" value="<?php echo $group['group_id']; ?>">
                            <input type="text" name="message" class="message-input" placeholder="Type a message..." required>
                            <button type="submit" class="send-btn">Send</button>
                        </form>
                    <?php else: ?>
                        <p style="text-align:center; color: rgba(255,255,255,0.5); padding: 60px 20px;">
                            🔒 Join the group to access chat
                        </p>
                    <?php endif; ?>
                </div>
                
                <!-- MEMBERS SIDEBAR -->
                <div class="members-list">
                    <h3 class="members-title">👥 Members (<?php echo count($members); ?>)</h3>
                    <?php foreach ($members as $member): ?>
                        <div class="member-item">
                            <div class="member-avatar"><?php echo getInitials($member['full_name']); ?></div>
                            <span class="member-name"><?php echo htmlspecialchars($member['full_name']); ?></span>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (!$group['is_member'] && $group['member_count'] < $group['max_members']): ?>
                        <a href="?join=<?php echo $group['group_id']; ?>" class="action-btn btn-join" 
                           style="display: block; margin-top: 20px;">Join Group</a>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php else: ?>
            <!-- GROUPS LIST VIEW -->
            <div class="header">
                <div class="header-title">
                    <h1>📚 Study Groups</h1>
                    <p>Collaborate with peers and ace your courses together</p>
                </div>
                <button class="create-btn" onclick="openModal()">+ Create Group</button>
            </div>
            
            <div class="tabs">
                <a href="?tab=all" class="tab <?php echo $tab === 'all' ? 'active' : ''; ?>">All Groups</a>
                <a href="?tab=my_groups" class="tab <?php echo $tab === 'my_groups' ? 'active' : ''; ?>">My Groups</a>
            </div>
            
            <?php if (empty($groups)): ?>
                <div style="text-align: center; padding: 80px 20px;">
                    <div style="font-size: 64px; margin-bottom: 20px;">📚</div>
                    <h2 style="font-size: 24px; margin-bottom: 10px;">No groups found</h2>
                    <p style="color: rgba(255,255,255,0.6);">
                        <?php echo $tab === 'my_groups' ? "You haven't joined any groups yet" : "Be the first to create a study group!"; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="groups-grid">
                    <?php foreach ($groups as $group): ?>
                        <?php 
                        $group_members = getMembers($conn, $group['group_id']);
                        $is_full = $group['member_count'] >= $group['max_members'];
                        ?>
                        <div class="group-card" onclick="window.location.href='?view=<?php echo $group['group_id']; ?>'">
                            <div class="group-header">
                                <div class="group-icon">💻</div>
                                <span class="group-status <?php echo $is_full ? 'full' : ''; ?>">
                                    <?php echo $is_full ? 'Full' : 'Open'; ?>
                                </span>
                            </div>
                            
                            <h3 class="group-title"><?php echo htmlspecialchars($group['group_name']); ?></h3>
                            <p class="group-course">
                                📚 <?php echo htmlspecialchars($group['course_code'] . ' - ' . $group['course_title']); ?>
                            </p>
                            <p class="group-description">
                                <?php echo htmlspecialchars($group['description']); ?>
                            </p>
                            
                            <div class="group-meta">
                                <span>👥 <?php echo $group['member_count']; ?>/<?php echo $group['max_members']; ?></span>
                                <span>📅 <?php echo htmlspecialchars($group['meeting_schedule']); ?></span>
                            </div>
                            
                            <div class="group-members">
                                <div class="member-avatars">
                                    <?php foreach (array_slice($group_members, 0, 3) as $member): ?>
                                        <div class="member-avatar">
                                            <?php echo getInitials($member['full_name']); ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($group_members) > 3): ?>
                                        <div class="member-avatar">+<?php echo count($group_members) - 3; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="group-actions" onclick="event.stopPropagation()">
                                <?php if ($group['is_member']): ?>
                                    <a href="?view=<?php echo $group['group_id']; ?>" class="action-btn btn-joined">
                                        ✓ Joined
                                    </a>
                                <?php elseif (!$is_full): ?>
                                    <a href="?join=<?php echo $group['group_id']; ?>" class="action-btn btn-join">
                                        Join Group
                                    </a>
                                <?php else: ?>
                                    <span class="action-btn" style="background: rgba(255,255,255,0.03); color: rgba(255,255,255,0.4); cursor: not-allowed;">
                                        Full
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- CREATE GROUP MODAL -->
    <div class="modal" id="createModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Create Study Group</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            
            <form method="POST">
                <input type="hidden" name="create_group" value="1">
                
                <div class="form-group">
                    <label class="form-label">Group Name</label>
                    <input type="text" name="group_name" class="form-input" 
                           placeholder="e.g., Data Structures Study Group" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" 
                              placeholder="What will your group focus on? What are the goals?" required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Course</label>
                    <select name="course_id" class="form-select" required>
                        <option value="">Select a course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['course_id']; ?>">
                                <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Maximum Members</label>
                    <input type="number" name="max_members" class="form-input" 
                           min="2" max="20" value="6" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Meeting Schedule</label>
                    <input type="text" name="meeting_schedule" class="form-input" 
                           placeholder="e.g., Tuesdays & Thursdays at 6 PM" required>
                </div>
                
                <button type="submit" class="submit-btn">Create Group</button>
            </form>
        </div>
    </div>
    
    <script>
        function openModal() {
            document.getElementById('createModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('createModal').classList.remove('active');
        }
        
        // Close modal on outside click
        document.getElementById('createModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Auto-scroll to bottom of messages
        const messagesDiv = document.getElementById('messages');
        if (messagesDiv) {
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
    </script>
</body>
</html>