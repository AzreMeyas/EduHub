<?php
session_start();
require_once 'config.php';

// DEBUG: Let's see what's happening
echo "<!DOCTYPE html><html><head><title>Debug</title></head><body>";
echo "<h2>Debug Information</h2>";

// Check session
echo "<h3>Session Data:</h3>";
echo "Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') . "<br>";
echo "Session role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'NOT SET') . "<br>";

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id'])) {
    echo "<strong style='color:red;'>ERROR: user_id not set in session</strong><br>";
    echo "Redirecting to login.php in 5 seconds...<br>";
    echo "<a href='login.php'>Click here to login</a>";
    echo "</body></html>";
    exit();
}

if ($_SESSION['role'] !== 'teacher') {
    echo "<strong style='color:red;'>ERROR: User role is '" . $_SESSION['role'] . "' not 'teacher'</strong><br>";
    echo "Only teachers can access this page.<br>";
    echo "</body></html>";
    exit();
}

echo "<strong style='color:green;'>✓ User is logged in as teacher</strong><br>";

$teacher_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : null;

echo "<h3>Course Information:</h3>";
echo "Course ID from URL: " . ($course_id ? $course_id : 'NOT PROVIDED') . "<br>";

if (!$course_id) {
    echo "<strong style='color:red;'>ERROR: No course_id in URL</strong><br>";
    echo "Please access this page with: grading-assignment.php?course_id=3<br>";
    echo "<a href='teacher-dashboard.php'>Back to Dashboard</a>";
    echo "</body></html>";
    exit();
}

// Get database connection
$conn = getDBConnection();

// Check if course exists
$check_query = "SELECT course_id, title, course_code, instructor_id FROM courses WHERE course_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course_check = $result->fetch_assoc();
$stmt->close();

if (!$course_check) {
    echo "<strong style='color:red;'>ERROR: Course ID {$course_id} does not exist in database</strong><br>";
    echo "<a href='teacher-dashboard.php'>Back to Dashboard</a>";
    echo "</body></html>";
    $conn->close();
    exit();
}

echo "<strong style='color:green;'>✓ Course exists</strong><br>";
echo "Course Title: " . htmlspecialchars($course_check['title']) . "<br>";
echo "Course Code: " . htmlspecialchars($course_check['course_code']) . "<br>";
echo "Instructor ID: " . $course_check['instructor_id'] . "<br>";
echo "Your Teacher ID: " . $teacher_id . "<br>";

// Verify teacher owns this course
if ($course_check['instructor_id'] != $teacher_id) {
    echo "<strong style='color:red;'>ERROR: You (teacher ID {$teacher_id}) do not own this course (instructor ID {$course_check['instructor_id']})</strong><br>";
    echo "<a href='teacher-dashboard.php'>Back to Dashboard</a>";
    echo "</body></html>";
    $conn->close();
    exit();
}

echo "<strong style='color:green;'>✓ You own this course!</strong><br>";
echo "<hr>";
echo "<h3>Available Courses for Teacher ID {$teacher_id}:</h3>";

// Show all courses this teacher owns
$courses_query = "SELECT course_id, course_code, title FROM courses WHERE instructor_id = ?";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher_courses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($teacher_courses)) {
    echo "<strong style='color:orange;'>You don't have any courses assigned yet.</strong><br>";
} else {
    echo "<ul>";
    foreach ($teacher_courses as $tc) {
        echo "<li>";
        echo htmlspecialchars($tc['course_code']) . " - " . htmlspecialchars($tc['title']);
        echo " <a href='grading-assignment.php?course_id=" . $tc['course_id'] . "'>[View Grading]</a>";
        echo "</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<h3>✅ All checks passed! Loading grading page...</h3>";
echo "<p><a href='grading-assignment.php?course_id={$course_id}'>Continue to Grading Page</a></p>";

$conn->close();
echo "</body></html>";
?>