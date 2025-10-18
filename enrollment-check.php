<?php
/**
 * Enrollment Check Helper
 * Use this file to check if a student is enrolled in a course
 * Include this in courseview.php and materialview.php
 */

/**
 * Check if user is enrolled in a course
 * @param int $user_id - The user ID
 * @param int $course_id - The course ID
 * @return bool - True if enrolled, False otherwise
 */
function isEnrolled($user_id, $course_id) {
    $conn = getDBConnection();
    
    $query = "SELECT enrollment_id FROM enrollments WHERE user_id = ? AND course_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $is_enrolled = $result->num_rows > 0;
    
    $conn->close();
    
    return $is_enrolled;
}

/**
 * Check if user is enrolled and redirect if not
 * @param int $user_id - The user ID
 * @param int $course_id - The course ID
 * @param string $redirect_url - Where to redirect if not enrolled (default: browsecourse.php)
 */
function requireEnrollment($user_id, $course_id, $redirect_url = 'browsecourse.php') {
    if (!isEnrolled($user_id, $course_id)) {
        $_SESSION['error_message'] = "You must be enrolled in this course to access this content.";
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Get enrollment details with dynamic course statistics
 * @param int $user_id - The user ID
 * @param int $course_id - The course ID
 * @return array|null - Enrollment data or null if not enrolled
 */
function getEnrollmentDetails($user_id, $course_id) {
    $conn = getDBConnection();
    
    $query = "
        SELECT 
            e.*,
            c.title as course_title,
            -- Dynamically calculate total lessons
            (SELECT COUNT(*) FROM materials WHERE course_id = c.course_id) as total_lessons,
            -- Dynamically calculate total hours
            ROUND(
                (COALESCE((SELECT SUM(duration_minutes) FROM materials WHERE course_id = c.course_id), 0) / 60) +
                (COALESCE((SELECT SUM(file_size) FROM materials WHERE course_id = c.course_id), 0) / (1024 * 1024) * 6 / 60),
                2
            ) as total_hours
        FROM enrollments e
        JOIN courses c ON e.course_id = c.course_id
        WHERE e.user_id = ? AND e.course_id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $enrollment = $result->num_rows > 0 ? $result->fetch_assoc() : null;
    
    $conn->close();
    
    return $enrollment;
}

/**
 * Update last accessed time for an enrollment
 * @param int $user_id - The user ID
 * @param int $course_id - The course ID
 */
function updateLastAccessed($user_id, $course_id) {
    $conn = getDBConnection();
    
    $query = "UPDATE enrollments SET last_accessed = NOW() WHERE user_id = ? AND course_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    
    $conn->close();
}

/**
 * Update course progress
 * @param int $user_id - The user ID
 * @param int $course_id - The course ID
 * @param int $progress_percentage - Progress percentage (0-100)
 */
function updateCourseProgress($user_id, $course_id, $progress_percentage) {
    $conn = getDBConnection();
    
    // Calculate hours remaining
    $course_query = "SELECT total_hours FROM courses WHERE course_id = ?";
    $stmt = $conn->prepare($course_query);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $course_result = $stmt->get_result();
    $course = $course_result->fetch_assoc();
    
    $hours_remaining = $course['total_hours'] * (1 - ($progress_percentage / 100));
    
    // Update status based on progress
    $status = 'in_progress';
    $badge_label = 'In Progress';
    
    if ($progress_percentage == 0) {
        $status = 'not_started';
        $badge_label = 'New';
    } elseif ($progress_percentage >= 100) {
        $status = 'completed';
        $badge_label = 'Completed';
        $hours_remaining = 0;
    }
    
    // Update enrollment
    $update_query = "
        UPDATE enrollments 
        SET progress_percentage = ?, 
            hours_remaining = ?, 
            status = ?,
            badge_label = ?,
            completed_at = IF(? >= 100, NOW(), NULL)
        WHERE user_id = ? AND course_id = ?
    ";
    
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("idssdii", 
        $progress_percentage, 
        $hours_remaining, 
        $status, 
        $badge_label,
        $progress_percentage,
        $user_id, 
        $course_id
    );
    $stmt->execute();
    
    $conn->close();
}
?>