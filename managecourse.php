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

// Handle Delete Course
if (isset($_POST['delete_course']) && $_POST['confirm_name'] === $course['title']) {
    // Delete in cascade order
    
    // 1. Delete course materials
    $stmt = $conn->prepare("DELETE FROM materials WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    
    // 2. Delete enrollments
    $stmt = $conn->prepare("DELETE FROM enrollments WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    
    // 3. Delete study group messages for groups in this course
    $stmt = $conn->prepare("DELETE sgm FROM study_group_messages sgm 
                           JOIN study_groups sg ON sgm.group_id = sg.group_id 
                           WHERE sg.course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    
    // 4. Delete study group members for groups in this course
    $stmt = $conn->prepare("DELETE sgmem FROM study_group_members sgmem 
                           JOIN study_groups sg ON sgmem.group_id = sg.group_id 
                           WHERE sg.course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    
    // 5. Delete study groups
    $stmt = $conn->prepare("DELETE FROM study_groups WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    
    // 6. Finally delete the course
    $stmt = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    
    $_SESSION['success_message'] = "Course deleted successfully!";
    header("Location: tdashboard.php");
    exit();
}

// Handle Remove Student
if (isset($_POST['remove_student'])) {
    $student_id = $_POST['student_id'];
    $stmt = $conn->prepare("DELETE FROM enrollments WHERE course_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $course_id, $student_id);
    $stmt->execute();
    $_SESSION['success_message'] = "Student removed successfully!";
    header("Location: manage-course.php?course_id=" . $course_id);
    exit();
}

// Get enrolled students
$stmt = $conn->prepare("SELECT u.user_id, u.full_name, u.email, e.enrolled_at 
                       FROM enrollments e 
                       JOIN users u ON e.user_id = u.user_id 
                       WHERE e.course_id = ? 
                       ORDER BY u.full_name");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get course statistics
$stmt = $conn->prepare("SELECT COUNT(*) as material_count FROM materials WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$material_count = $stmt->get_result()->fetch_assoc()['material_count'];

$stmt = $conn->prepare("SELECT COUNT(*) as enrollment_count FROM enrollments WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$enrollment_count = $stmt->get_result()->fetch_assoc()['enrollment_count'];

$stmt = $conn->prepare("SELECT COUNT(*) as group_count FROM study_groups WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$group_count = $stmt->get_result()->fetch_assoc()['group_count'];

function getInitials($name) {
    $parts = explode(' ', $name);
    return strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Course - EduHub</title>
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
        
        /* Header */
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
        
        .course-thumbnail {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }
        
        .course-header-info h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .course-header-info p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 15px;
        }

        /* Stats Bar */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 20px;
            text-align: center;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        /* Management Grid */
        .management-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
        }
        
        .management-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 35px;
            cursor: pointer;
            transition: all 0.4s;
            position: relative;
            overflow: hidden;
        }
        
        .management-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: float-slow 8s infinite;
        }
        
        @keyframes float-slow {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-20px, 20px); }
        }
        
        .management-card:hover {
            transform: translateY(-8px);
            border-color: rgba(102, 126, 234, 0.4);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.3);
        }
        
        .management-card.danger:hover {
            border-color: rgba(239, 68, 68, 0.4);
            box-shadow: 0 20px 50px rgba(239, 68, 68, 0.3);
        }
        
        .card-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin-bottom: 25px;
        }
        
        .card-icon.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        
        .card-icon.orange {
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        }
        
        .card-icon.red {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        .card-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        
        .card-description {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .card-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .card-action:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            align-items: center;
            justify-content: center;
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
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .modal-title {
            font-size: 26px;
            font-weight: 700;
        }
        
        .modal-close {
            background: rgba(255, 255, 255, 0.08);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            color: white;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .modal-close:hover {
            background: rgba(239, 68, 68, 0.2);
            transform: rotate(90deg);
        }
        
        .modal-text {
            font-size: 16px;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 25px;
        }
        
        .modal-warning {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .modal-warning h4 {
            color: #ef4444;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .modal-warning ul {
            list-style: none;
            padding-left: 0;
        }
        
        .modal-warning li {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
        }
        
        .modal-warning li::before {
            content: '•';
            position: absolute;
            left: 0;
            color: #ef4444;
        }
        
        .modal-input {
            width: 100%;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            color: white;
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            margin-bottom: 25px;
            transition: all 0.3s;
        }
        
        .modal-input:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
            background: rgba(255, 255, 255, 0.08);
        }
        
        .modal-actions {
            display: flex;
            gap: 15px;
        }
        
        .modal-btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 14px;
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .modal-btn-cancel {
            background: rgba(255, 255, 255, 0.08);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .modal-btn-cancel:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .modal-btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        .modal-btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.4);
        }
        
        .modal-btn-danger:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Student List */
        .student-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .student-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }

        .student-item:hover {
            background: rgba(255, 255, 255, 0.06);
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .student-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
        }

        .student-details h4 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .student-details p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
        }

        .remove-student-btn {
            padding: 8px 16px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 10px;
            color: #ef4444;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .remove-student-btn:hover {
            background: rgba(239, 68, 68, 0.25);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .management-grid {
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
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <a href="tdashboard.php" class="back-btn">←</a>
                <div class="course-thumbnail">💻</div>
                <div class="course-header-info">
                    <h1>Manage Course</h1>
                    <p><?php echo htmlspecialchars($course['title']); ?> • <?php echo htmlspecialchars($course['course_code']); ?></p>
                </div>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-value"><?php echo $material_count; ?></div>
                <div class="stat-label">Course Materials</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $enrollment_count; ?></div>
                <div class="stat-label">Enrolled Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $group_count; ?></div>
                <div class="stat-label">Study Groups</div>
            </div>
        </div>
        
        <!-- Management Options -->
        <div class="management-grid">
            <!-- Edit Course Materials -->
            <div class="management-card" onclick="window.location='editmaterail.php?course_id=<?php echo $course_id; ?>'">
                <div class="card-icon">📝</div>
                <h3 class="card-title">Edit Course Materials</h3>
                <p class="card-description">Add, update, or remove course materials, lectures, assignments, and resources.</p>
                <div class="card-action">
                    Open Editor →
                </div>
            </div>
            
            <!-- Remove Students -->
            <div class="management-card" onclick="openRemoveStudentsModal()">
                <div class="card-icon green">👥</div>
                <h3 class="card-title">Remove Students</h3>
                <p class="card-description">View and manage enrolled students. Remove students from the course if needed.</p>
                <div class="card-action">
                    Manage Students →
                </div>
            </div>
            
            <!-- Delete Course -->
            <div class="management-card danger" onclick="openDeleteModal()">
                <div class="card-icon red">🗑️</div>
                <h3 class="card-title">Delete Course</h3>
                <p class="card-description">Permanently delete this course. This action cannot be undone and will remove all associated data.</p>
                <div class="card-action">
                    Delete Course →
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h2 class="modal-title">🗑️ Delete Course</h2>
                    <button type="button" class="modal-close" onclick="closeDeleteModal()">×</button>
                </div>
                
                <p class="modal-text">
                    Are you sure you want to permanently delete <strong><?php echo htmlspecialchars($course['title']); ?></strong>?
                </p>
                
                <div class="modal-warning">
                    <h4>⚠️ Warning: This action cannot be undone</h4>
                    <ul>
                        <li>All <?php echo $material_count; ?> course materials will be permanently deleted</li>
                        <li>All <?php echo $enrollment_count; ?> students will be unenrolled</li>
                        <li>All <?php echo $group_count; ?> study groups will be deleted</li>
                        <li>All assignments and grades will be removed</li>
                        <li>This data cannot be recovered</li>
                    </ul>
                </div>
                
                <p class="modal-text">
                    Please type <strong><?php echo htmlspecialchars($course['title']); ?></strong> to confirm:
                </p>
                
                <input type="text" name="confirm_name" id="confirmInput" class="modal-input" placeholder="Type course name here..." required>
                <input type="hidden" name="delete_course" value="1">
                
                <div class="modal-actions">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" class="modal-btn modal-btn-danger" id="confirmDeleteBtn" disabled>Delete Course</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Remove Students Modal -->
    <div id="removeStudentsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">👥 Remove Students</h2>
                <button class="modal-close" onclick="closeRemoveStudentsModal()">×</button>
            </div>
            
            <p class="modal-text">
                Students enrolled in <strong><?php echo htmlspecialchars($course['title']); ?></strong>
            </p>
            
            <?php if (empty($students)): ?>
                <p style="text-align: center; padding: 40px; color: rgba(255,255,255,0.5);">
                    No students enrolled yet
                </p>
            <?php else: ?>
                <div class="student-list">
                    <?php 
                    $colors = [
                        'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                        'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                        'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
                        'linear-gradient(135deg, #f59e0b 0%, #f97316 100%)',
                        'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                    ];
                    foreach ($students as $index => $student): 
                    ?>
                        <div class="student-item" id="student-<?php echo $student['user_id']; ?>">
                            <div class="student-info">
                                <div class="student-avatar" style="background: <?php echo $colors[$index % 5]; ?>;">
                                    <?php echo getInitials($student['full_name']); ?>
                                </div>
                                <div class="student-details">
                                    <h4><?php echo htmlspecialchars($student['full_name']); ?></h4>
                                    <p><?php echo htmlspecialchars($student['email']); ?> • Enrolled: <?php echo date('M Y', strtotime($student['enrolled_at'])); ?></p>
                                </div>
                            </div>
                            <button type="button" class="remove-student-btn" 
                                    onclick="removeStudent(<?php echo $student['user_id']; ?>, '<?php echo htmlspecialchars($student['full_name'], ENT_QUOTES); ?>')">
                                Remove
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="modal-actions">
                <button class="modal-btn modal-btn-cancel" onclick="closeRemoveStudentsModal()" style="flex: none; width: 100%;">Close</button>
            </div>
        </div>
    </div>
    
    <script>
        const courseTitle = <?php echo json_encode($course['title']); ?>;
        
        // Delete Modal Functions
        function openDeleteModal() {
            document.getElementById('deleteModal').classList.add('active');
            document.getElementById('confirmInput').value = '';
            document.getElementById('confirmDeleteBtn').disabled = true;
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        // Enable delete button when course name matches
        document.getElementById('confirmInput').addEventListener('input', function() {
            const input = this.value.trim();
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            if (input === courseTitle) {
                confirmBtn.disabled = false;
            } else {
                confirmBtn.disabled = true;
            }
        });

        // Remove Students Modal Functions
        function openRemoveStudentsModal() {
            document.getElementById('removeStudentsModal').classList.add('active');
        }

        function closeRemoveStudentsModal() {
            document.getElementById('removeStudentsModal').classList.remove('active');
        }

        function removeStudent(studentId, studentName) {
            if (confirm(`Are you sure you want to remove ${studentName} from this course?\n\nThe student will lose access to all course materials.`)) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="remove_student" value="1">
                    <input type="hidden" name="student_id" value="${studentId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modals when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        document.getElementById('removeStudentsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRemoveStudentsModal();
            }
        });

        // Animate cards on load
        window.addEventListener('load', () => {
            document.querySelectorAll('.management-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 150);
            });
        });
    </script>
</body>
</html>