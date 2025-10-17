-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2025 at 09:57 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `learning_platform`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `activity_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` enum('material','quiz','message','achievement','enrollment','assignment') NOT NULL,
  `icon` varchar(10) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`activity_id`, `user_id`, `activity_type`, `icon`, `title`, `description`, `created_at`) VALUES
(1, 1, 'material', '📄', 'New Material', 'New learning material added to Machine Learning Basics', '2025-10-06 00:00:00'),
(2, 1, 'quiz', '✅', 'Quiz Passed', 'Completed quiz in Data Structures with 95%', '2025-10-05 21:00:00'),
(3, 1, 'message', '💬', 'New Reply', 'Sarah Johnson replied to your discussion post', '2025-10-05 02:00:00'),
(4, 1, 'achievement', '🏆', 'Achievement', 'Earned \"Fast Learner\" badge', '2025-10-04 02:00:00'),
(5, 1, 'enrollment', '📚', 'Started New Course', 'Enrolled in a new course', '2025-10-16 12:06:37'),
(6, 1, 'enrollment', '📚', 'Started New Course', 'Enrolled in a new course', '2025-10-16 12:33:36'),
(7, 1, 'enrollment', '📚', 'Purchased Course', 'Successfully purchased and enrolled in a new course', '2025-10-16 12:55:14'),
(8, 1, 'enrollment', '📚', 'Started New Course', 'Enrolled in a new course', '2025-10-16 12:55:34');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `posted_by` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `priority` enum('high','medium','low') DEFAULT 'medium',
  `icon` varchar(10) DEFAULT NULL,
  `send_notification` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `course_id`, `posted_by`, `title`, `message`, `priority`, `icon`, `send_notification`, `created_at`, `updated_at`) VALUES
(1, 3, 2, 'Midterm Exam Schedule', 'The midterm exam will be held on Friday, Oct 15 at 10:00 AM. Please review chapters 1-5.', 'high', '📌', 1, '2025-10-04 02:00:00', '2025-10-15 14:05:16'),
(2, 3, 2, 'New Study Materials', 'Additional practice problems for dynamic programming have been uploaded.', 'medium', '📚', 1, '2025-10-01 02:00:00', '2025-10-15 14:05:16'),
(3, 3, 2, 'Office Hours Update', 'Office hours moved to Tuesday 2-4 PM this week.', 'low', '⏰', 0, '2025-09-29 02:00:00', '2025-10-15 14:05:16'),
(4, 7, 2, 'this is an announcement', 'this is the message', 'low', '📚', 1, '2025-10-16 00:34:47', '2025-10-16 00:34:47'),
(5, 5, 2, 'a', 'a', 'high', '🔴', 0, '2025-10-16 00:36:29', '2025-10-16 00:36:29');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_submissions`
--

CREATE TABLE `assignment_submissions` (
  `submission_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `submission_text` text DEFAULT NULL,
  `grade` decimal(5,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `status` enum('submitted','graded','late','missing') DEFAULT 'submitted',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `graded_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignment_submissions`
--

INSERT INTO `assignment_submissions` (`submission_id`, `material_id`, `user_id`, `file_path`, `submission_text`, `grade`, `feedback`, `status`, `submitted_at`, `graded_at`) VALUES
(1, 3, 1, '/uploads/assignments/user1_assignment3.pdf', 'Completed all three algorithms with analysis', 57.00, 'Good job! Some areas could use improvement.', 'graded', '2025-10-03 09:30:00', '2025-10-16 08:12:50'),
(2, 3, 3, '/uploads/assignments/user3_assignment3.pdf', 'BFS and DFS implementations completed', NULL, NULL, 'submitted', '2025-10-02 04:20:00', NULL),
(4, 11, 1, 'uploads/assignments/user1_material11_1760634324.pdf', 'submitted assignment', NULL, NULL, 'submitted', '2025-10-16 17:05:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `comment_likes`
--

CREATE TABLE `comment_likes` (
  `like_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comment_likes`
--

INSERT INTO `comment_likes` (`like_id`, `comment_id`, `user_id`, `created_at`) VALUES
(1, 1, 1, '2025-10-06 00:10:00'),
(2, 1, 3, '2025-10-06 00:15:00'),
(3, 1, 5, '2025-10-06 00:20:00'),
(4, 1, 7, '2025-10-06 00:25:00'),
(5, 1, 8, '2025-10-06 00:30:00'),
(6, 1, 9, '2025-10-06 00:35:00'),
(7, 2, 1, '2025-10-06 01:00:00'),
(8, 2, 6, '2025-10-06 01:05:00'),
(9, 2, 8, '2025-10-06 01:10:00'),
(10, 3, 1, '2025-10-06 01:30:00'),
(11, 3, 3, '2025-10-06 01:35:00'),
(12, 3, 5, '2025-10-06 01:40:00'),
(13, 4, 1, '2025-10-05 03:00:00'),
(14, 4, 7, '2025-10-05 04:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `coupon_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_purchase_amount` decimal(10,2) DEFAULT 0.00,
  `max_discount_amount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `valid_from` timestamp NOT NULL DEFAULT current_timestamp(),
  `valid_until` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`coupon_id`, `code`, `discount_type`, `discount_value`, `min_purchase_amount`, `max_discount_amount`, `usage_limit`, `used_count`, `valid_from`, `valid_until`, `is_active`, `created_by`, `created_at`) VALUES
(1, 'WELCOME50', 'percentage', 50.00, 0.00, NULL, 100, 0, '2025-10-16 12:32:20', '2025-11-15 12:32:20', 1, 2, '2025-10-16 12:32:20'),
(2, 'SAVE10', 'fixed', 10.00, 20.00, NULL, 50, 0, '2025-10-16 12:32:20', '2025-12-15 12:32:20', 1, 2, '2025-10-16 12:32:20'),
(3, 'FREESHIP', 'percentage', 100.00, 0.00, NULL, 10, 0, '2025-10-16 12:32:20', '2025-10-23 12:32:20', 1, 2, '2025-10-16 12:32:20');

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usage`
--

CREATE TABLE `coupon_usage` (
  `usage_id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `icon` varchar(10) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `total_lessons` int(11) DEFAULT 0,
  `total_hours` decimal(5,2) DEFAULT 0.00,
  `enrolled_count` int(11) DEFAULT 0,
  `rating_average` decimal(3,2) DEFAULT 0.00,
  `price` decimal(10,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'USD',
  `is_free` tinyint(1) DEFAULT 1,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `discount_percentage` int(11) DEFAULT NULL,
  `duration_weeks` int(11) DEFAULT 0,
  `difficulty_level` enum('Beginner','Intermediate','Advanced') DEFAULT NULL,
  `credits` int(11) DEFAULT 3,
  `max_students` int(11) DEFAULT NULL,
  `materials_count` int(11) DEFAULT 0,
  `status` enum('draft','published','archived') DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `title`, `course_code`, `department`, `instructor_id`, `description`, `short_description`, `icon`, `color`, `total_lessons`, `total_hours`, `enrolled_count`, `rating_average`, `price`, `currency`, `is_free`, `discount_price`, `discount_percentage`, `duration_weeks`, `difficulty_level`, `credits`, `max_students`, `materials_count`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Machine Learning Basics', NULL, NULL, 2, 'Dive into AI, neural networks, and predictive models', NULL, '🚀', 'blue', 32, 20.00, 0, 0.00, 49.99, 'USD', 0, 39.99, 20, 0, NULL, 3, NULL, 0, 'published', '2025-10-15 14:05:15', '2025-10-16 12:32:20'),
(2, 'Data Science Analytics', NULL, NULL, 2, 'Analyze data, create visualizations, and tell stories', NULL, '📊', 'green', 28, 15.00, 0, 0.00, 39.99, 'USD', 0, 29.99, 25, 0, NULL, 3, NULL, 0, 'published', '2025-10-15 14:05:15', '2025-10-16 12:32:20'),
(3, 'Advanced Computer Science', 'CS-401', NULL, 2, 'Master algorithms, data structures, and programming concepts', NULL, '💻', 'purple', 24, 12.00, 120, 4.80, 0.00, 'USD', 1, NULL, NULL, 12, NULL, 3, NULL, 24, 'published', '2025-10-15 14:05:15', '2025-10-15 14:05:16'),
(4, 'UI/UX Design Mastery', NULL, NULL, 2, 'Learn design thinking, prototyping, and user research', NULL, '🎨', 'orange', 18, 8.00, 0, 0.00, 0.00, 'USD', 1, NULL, NULL, 0, NULL, 3, NULL, 0, 'published', '2025-10-15 14:05:15', '2025-10-15 14:57:47'),
(5, 'Machine Learning & AI', 'CS-502', NULL, 2, 'Advanced machine learning concepts and artificial intelligence applications', NULL, '🚀', 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)', 40, 25.00, 95, 4.80, 0.00, 'USD', 1, NULL, NULL, 16, NULL, 3, NULL, 32, 'published', '2025-10-15 14:57:47', '2025-10-15 14:57:47'),
(6, 'Data Structures & Algorithms', 'CS-301', NULL, 2, 'Comprehensive coverage of fundamental data structures and algorithms', NULL, '📊', 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)', 35, 18.00, 156, 4.70, 0.00, 'USD', 1, NULL, NULL, 14, NULL, 3, NULL, 28, 'published', '2025-10-15 14:57:47', '2025-10-15 14:57:47'),
(7, 'Newly created', 'new-01', 'Computer Science', 2, 'this is for testing purpose only', 'Newly Created for testing', '💻', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', 0, 0.00, 0, 0.00, 0.00, 'USD', 1, NULL, NULL, 1, '', 3, 19, 3, 'published', '2025-10-15 15:35:58', '2025-10-16 00:31:56');

-- --------------------------------------------------------

--
-- Table structure for table `course_discussions`
--

CREATE TABLE `course_discussions` (
  `discussion_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `parent_discussion_id` int(11) DEFAULT NULL,
  `likes_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_discussions`
--

INSERT INTO `course_discussions` (`discussion_id`, `course_id`, `user_id`, `title`, `message`, `parent_discussion_id`, `likes_count`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 'Question about midterm exam', 'What topics will be covered in the midterm exam?', NULL, 0, '2025-10-15 04:30:00', '2025-10-16 00:06:44'),
(2, 3, 3, 'Group study session', 'Anyone interested in forming a study group for algorithms?', NULL, 0, '2025-10-14 09:20:00', '2025-10-16 00:06:44'),
(3, 3, 6, 'Clarification needed', 'Can someone explain the difference between BFS and DFS?', NULL, 0, '2025-10-13 03:15:00', '2025-10-16 00:06:44'),
(4, 3, 2, NULL, 'The midterm will cover chapters 1-5, including sorting algorithms and time complexity analysis.', 1, 0, '2025-10-15 05:00:00', '2025-10-16 00:06:44'),
(5, 3, 8, NULL, 'I would love to join! When and where?', 2, 0, '2025-10-14 10:45:00', '2025-10-16 00:06:44'),
(6, 3, 7, NULL, 'BFS explores level by level, while DFS goes as deep as possible first. Check the lecture notes!', 3, 0, '2025-10-13 04:30:00', '2025-10-16 00:06:44'),
(7, 3, 2, NULL, 'new post', NULL, 1, '2025-10-16 00:23:02', '2025-10-16 00:23:11');

-- --------------------------------------------------------

--
-- Table structure for table `course_discussion_likes`
--

CREATE TABLE `course_discussion_likes` (
  `like_id` int(11) NOT NULL,
  `discussion_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_discussion_likes`
--

INSERT INTO `course_discussion_likes` (`like_id`, `discussion_id`, `user_id`, `created_at`) VALUES
(1, 7, 2, '2025-10-16 00:23:11');

-- --------------------------------------------------------

--
-- Stand-in structure for view `course_revenue`
-- (See below for the actual view)
--
CREATE TABLE `course_revenue` (
`course_id` int(11)
,`title` varchar(255)
,`price` decimal(10,2)
,`total_sales` bigint(21)
,`total_revenue` decimal(32,2)
,`avg_sale_price` decimal(14,6)
);

-- --------------------------------------------------------

--
-- Table structure for table `course_schedule`
--

CREATE TABLE `course_schedule` (
  `schedule_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room_location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_schedule`
--

INSERT INTO `course_schedule` (`schedule_id`, `course_id`, `day_of_week`, `start_time`, `end_time`, `room_location`, `created_at`) VALUES
(1, 7, 'Monday', '21:35:00', '22:36:00', '000', '2025-10-15 15:35:58'),
(2, 7, 'Wednesday', '09:35:00', '10:35:00', '000', '2025-10-15 15:35:58');

-- --------------------------------------------------------

--
-- Table structure for table `course_tags`
--

CREATE TABLE `course_tags` (
  `tag_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `tag_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `progress_percentage` int(11) DEFAULT 0,
  `hours_remaining` decimal(5,2) DEFAULT NULL,
  `status` enum('not_started','in_progress','completed','dropped') DEFAULT 'not_started',
  `badge_label` varchar(50) DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_accessed` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `user_id`, `course_id`, `progress_percentage`, `hours_remaining`, `status`, `badge_label`, `payment_id`, `enrolled_at`, `last_accessed`, `completed_at`) VALUES
(1, 1, 1, 30, 20.00, 'in_progress', 'In Progress', NULL, '2025-09-15 04:00:00', '2025-10-06 02:30:00', NULL),
(2, 1, 2, 0, 15.00, 'not_started', 'New', NULL, '2025-10-05 08:00:00', NULL, NULL),
(3, 1, 3, 70, 12.00, 'in_progress', 'In Progress', NULL, '2025-09-01 03:00:00', '2025-10-05 10:20:00', NULL),
(4, 1, 4, 45, 8.00, 'in_progress', 'In Progress', NULL, '2025-09-20 05:00:00', '2025-10-04 07:45:00', NULL),
(6, 1, 6, 0, 18.00, 'not_started', 'New', NULL, '2025-10-16 12:33:36', NULL, NULL),
(8, 1, 7, 0, 0.00, 'not_started', 'New', NULL, '2025-10-16 12:55:34', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_type` enum('exam','assignment','session','meeting','deadline') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(10) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `event_datetime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_completed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `user_id`, `event_type`, `title`, `description`, `icon`, `color`, `event_datetime`, `is_completed`, `created_at`) VALUES
(1, 1, 'exam', 'Midterm Exam', 'Machine Learning Basics midterm examination', '📅', 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)', '2025-10-07 04:00:00', 0, '2025-10-15 14:05:15'),
(2, 1, 'assignment', 'Assignment Due', 'Complete Data Science project submission', '🎯', 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)', '2025-10-09 17:59:00', 0, '2025-10-15 14:05:15'),
(3, 1, 'session', 'Study Session', 'Group study session for CS Fundamentals', '👥', 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)', '2025-10-11 09:00:00', 0, '2025-10-15 14:05:15');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `material_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `category` enum('lecture_notes','assignment','video','reference','quiz','other') NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `icon` varchar(10) DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `week_number` int(11) DEFAULT NULL,
  `difficulty_level` enum('beginner','intermediate','advanced') DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 1,
  `allow_comments` tinyint(1) DEFAULT 1,
  `allow_download` tinyint(1) DEFAULT 1,
  `due_date` timestamp NULL DEFAULT NULL,
  `views_count` int(11) DEFAULT 0,
  `downloads_count` int(11) DEFAULT 0,
  `rating_average` decimal(3,2) DEFAULT 0.00,
  `rating_count` int(11) DEFAULT 0,
  `comments_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`material_id`, `course_id`, `uploaded_by`, `title`, `description`, `short_description`, `category`, `file_path`, `file_type`, `file_size`, `icon`, `duration_minutes`, `week_number`, `difficulty_level`, `is_published`, `allow_comments`, `allow_download`, `due_date`, `views_count`, `downloads_count`, `rating_average`, `rating_count`, `comments_count`, `created_at`, `updated_at`) VALUES
(1, 3, 2, 'Introduction to Algorithms', 'This comprehensive guide covers fundamental sorting algorithms including Bubble Sort, Quick Sort, Merge Sort, and Heap Sort. Each algorithm is explained with detailed time complexity analysis, space complexity considerations, and practical code implementations. The document includes visual diagrams, pseudocode, and real-world applications to help you understand when to use each algorithm effectively.\n\nTopics covered:\n• Sorting algorithm fundamentals\n• Time and space complexity analysis\n• Comparative analysis of different algorithms\n• Best practices and optimization techniques\n• Practice problems with solutions', NULL, 'lecture_notes', '/materials/course3/introduction_to_algorithms.pdf', 'PDF', 2621440, '📄', NULL, NULL, NULL, 1, 1, 1, NULL, 1235, 859, 4.80, 156, 24, '2025-10-04 02:00:00', '2025-10-15 23:47:52'),
(2, 3, 2, 'Dynamic Programming Explained', 'Step-by-step video tutorial on dynamic programming concepts with real-world examples and coding demonstrations.', NULL, 'video', NULL, NULL, NULL, '🎥', 45, NULL, NULL, 1, 1, 1, NULL, 203, 0, 4.90, 31, 18, '2025-10-01 04:00:00', '2025-10-15 14:05:16'),
(3, 3, 2, 'Assignment 3: Graph Algorithms', 'Implement BFS, DFS, and Dijkstra\'s algorithm. Submit your code along with time complexity analysis.', NULL, 'assignment', NULL, NULL, NULL, '📝', NULL, NULL, NULL, 1, 1, 1, '2025-10-09 17:59:59', 120, 0, 0.00, 0, 25, '2025-09-29 03:00:00', '2025-10-15 14:05:16'),
(4, 3, 2, 'Advanced Sorting Techniques', 'Deep dive into advanced sorting methods and their applications', NULL, 'lecture_notes', NULL, NULL, NULL, '📄', NULL, NULL, NULL, 1, 1, 1, NULL, 90, 45, 4.70, 18, 8, '2025-10-08 04:00:00', '2025-10-16 12:06:26'),
(5, 3, 2, 'Algorithm Visualization', 'Visual demonstration of various algorithms in action', NULL, 'video', NULL, NULL, NULL, '🎥', 28, NULL, NULL, 1, 1, 1, NULL, 156, 0, 4.90, 25, 12, '2025-10-04 05:00:00', '2025-10-15 14:05:16'),
(6, 3, 2, 'Practice Problems Set 3', 'Comprehensive practice problems for Week 3 topics', NULL, 'assignment', NULL, NULL, NULL, '📝', NULL, NULL, NULL, 1, 1, 1, NULL, 234, 102, 4.60, 34, 28, '2025-10-04 06:00:00', '2025-10-15 14:05:16'),
(7, 3, 2, 'Binary Search Trees', 'Detailed explanation of BST operations and implementations', NULL, 'lecture_notes', NULL, NULL, NULL, '📄', NULL, NULL, NULL, 1, 1, 1, NULL, 98, 54, 4.70, 19, 7, '2025-10-13 14:57:47', '2025-10-15 14:57:47'),
(8, 3, 2, 'Quiz 2: Algorithms', 'Test your knowledge of sorting and searching algorithms', NULL, 'quiz', NULL, NULL, NULL, '🎯', NULL, NULL, NULL, 1, 1, 1, NULL, 145, 0, 0.00, 0, 0, '2025-10-14 14:57:47', '2025-10-15 14:57:47'),
(10, 7, 2, 'video', 'big thing', 'a short course thing', 'video', 'uploads/materials/68f0338b546d5_1760572299.mp4', 'video/mp4', 446057, '🎥', NULL, 1, 'beginner', 1, 1, 1, NULL, 13, 0, 4.00, 1, 0, '2025-10-15 23:51:39', '2025-10-16 17:01:10'),
(11, 7, 2, 'assignment', 'fulll assignemtn', 'an assignment', 'assignment', 'uploads/materials/68f03cfc393a1_1760574716.pdf', 'application/pdf', 460830, '📝', NULL, 2, 'beginner', 1, 1, 1, NULL, 12, 0, 3.00, 1, 0, '2025-10-16 00:31:56', '2025-10-16 19:05:17');

-- --------------------------------------------------------

--
-- Table structure for table `material_bookmarks`
--

CREATE TABLE `material_bookmarks` (
  `bookmark_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `material_bookmarks`
--

INSERT INTO `material_bookmarks` (`bookmark_id`, `material_id`, `user_id`, `created_at`) VALUES
(1, 1, 1, '2025-10-04 04:00:00'),
(2, 1, 3, '2025-10-04 05:30:00'),
(3, 1, 6, '2025-10-04 08:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `material_comments`
--

CREATE TABLE `material_comments` (
  `comment_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `likes_count` int(11) DEFAULT 0,
  `parent_comment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `material_comments`
--

INSERT INTO `material_comments` (`comment_id`, `material_id`, `user_id`, `comment_text`, `likes_count`, `parent_comment_id`, `created_at`, `updated_at`) VALUES
(1, 1, 6, 'This is an excellent resource! The visual diagrams really helped me understand the merge sort algorithm. Could someone explain when to use Quick Sort vs Merge Sort in practical scenarios?', 12, NULL, '2025-10-06 00:00:00', '2025-10-15 14:05:16'),
(2, 1, 3, 'Great question! Quick Sort is generally faster for in-memory sorting and has better cache performance. Merge Sort is stable and guaranteed O(n log n) worst case, making it better for linked lists or when stability is required.', 8, 1, '2025-10-05 21:00:00', '2025-10-15 14:05:16'),
(3, 2, 8, 'The complexity analysis section is really thorough! I especially appreciate the comparison table. One suggestion - could we get some practice problems added at the end?', 15, NULL, '2025-10-05 21:00:00', '2025-10-15 14:05:16'),
(4, 3, 9, 'Has anyone implemented these algorithms in Python? I\'d love to see some code examples!', 6, NULL, '2025-10-05 02:00:00', '2025-10-15 14:05:16');

-- --------------------------------------------------------

--
-- Table structure for table `material_ratings`
--

CREATE TABLE `material_ratings` (
  `rating_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` decimal(2,1) NOT NULL CHECK (`rating` >= 0 and `rating` <= 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `material_ratings`
--

INSERT INTO `material_ratings` (`rating_id`, `material_id`, `user_id`, `rating`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 5.0, '2025-10-04 06:01:00', '2025-10-15 14:05:16'),
(2, 1, 3, 4.5, '2025-10-04 08:31:00', '2025-10-15 14:05:16'),
(3, 2, 1, 5.0, '2025-10-02 03:16:00', '2025-10-15 14:05:16'),
(4, 2, 3, 4.8, '2025-10-02 05:20:00', '2025-10-15 14:05:16'),
(5, 10, 2, 4.0, '2025-10-15 23:52:28', '2025-10-15 23:52:37'),
(6, 11, 2, 3.0, '2025-10-16 00:32:25', '2025-10-16 00:32:25');

-- --------------------------------------------------------

--
-- Table structure for table `material_shares`
--

CREATE TABLE `material_shares` (
  `share_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `share_type` enum('link','email','social') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `material_shares`
--

INSERT INTO `material_shares` (`share_id`, `material_id`, `user_id`, `share_type`, `created_at`) VALUES
(1, 1, 1, 'link', '2025-10-04 09:00:00'),
(2, 1, 6, 'social', '2025-10-05 03:30:00'),
(3, 1, 8, 'email', '2025-10-05 10:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `material_tags`
--

CREATE TABLE `material_tags` (
  `tag_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `tag_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `material_tags`
--

INSERT INTO `material_tags` (`tag_id`, `material_id`, `tag_name`) VALUES
(1, 1, 'Algorithms'),
(2, 1, 'Data Structures'),
(3, 1, 'Week 3'),
(4, 2, 'Dynamic Programming'),
(5, 2, 'Video'),
(6, 2, 'Week 4'),
(7, 3, 'Assignment'),
(8, 3, 'Graphs'),
(9, 3, 'Due Soon'),
(10, 1, 'Important'),
(11, 4, 'Sorting'),
(12, 4, 'Advanced'),
(13, 4, 'Week 4'),
(14, 5, 'Visualization'),
(15, 5, 'Week 3'),
(16, 5, 'Interactive'),
(17, 6, 'Practice'),
(18, 6, 'Week 3'),
(19, 6, 'Exercises');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 1, 'New Course Available', 'Check out the new Advanced Python course', 'info', 0, '2025-10-06 01:00:00'),
(2, 1, 'Quiz Reminder', 'You have a pending quiz in Machine Learning', 'warning', 0, '2025-10-06 00:30:00'),
(3, 1, 'Achievement Unlocked', 'Congratulations! You earned a new badge', 'success', 1, '2025-10-05 08:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `token_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`token_id`, `user_id`, `token`, `expires_at`, `used`, `created_at`) VALUES
(1, 1, 'reset_token_abc123xyz789', '2025-10-07 08:30:00', 0, '2025-10-15 14:05:15'),
(2, 3, 'reset_token_def456uvw012', '2025-10-06 04:00:00', 1, '2025-10-15 14:05:15');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `payment_method` enum('card','paypal','bank_transfer','free') DEFAULT 'card',
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_gateway` varchar(50) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `user_id`, `course_id`, `amount`, `currency`, `payment_method`, `payment_status`, `transaction_id`, `payment_gateway`, `payment_date`) VALUES
(1, 1, 1, 39.99, 'USD', 'card', 'completed', '1', NULL, '2025-10-16 12:32:20'),
(2, 3, 2, 29.99, 'USD', 'paypal', 'completed', '1', NULL, '2025-10-16 12:32:20'),
(3, 1, 6, 0.00, 'USD', 'free', 'completed', NULL, NULL, '2025-10-16 12:33:36'),
(5, 1, 7, 0.00, 'USD', 'free', 'completed', NULL, NULL, '2025-10-16 12:55:34');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `quiz_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `duration_minutes` int(11) NOT NULL DEFAULT 15,
  `total_questions` int(11) DEFAULT 0,
  `passing_score` decimal(5,2) DEFAULT 70.00,
  `max_attempts` int(11) DEFAULT 3,
  `is_published` tinyint(1) DEFAULT 1,
  `show_correct_answers` tinyint(1) DEFAULT 1,
  `randomize_questions` tinyint(1) DEFAULT 0,
  `randomize_options` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`quiz_id`, `course_id`, `created_by`, `title`, `description`, `duration_minutes`, `total_questions`, `passing_score`, `max_attempts`, `is_published`, `show_correct_answers`, `randomize_questions`, `randomize_options`, `created_at`, `updated_at`) VALUES
(1, 7, 2, 'd', 'dd', 15, 1, 70.00, 3, 1, 1, 1, 1, '2025-10-16 09:34:05', '2025-10-16 09:34:05');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_answers`
--

CREATE TABLE `quiz_answers` (
  `answer_id` int(11) NOT NULL,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option_id` int(11) DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `points_earned` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_answers`
--

INSERT INTO `quiz_answers` (`answer_id`, `attempt_id`, `question_id`, `selected_option_id`, `is_correct`, `points_earned`) VALUES
(3, 3, 1, 3, 1, 1.00),
(4, 4, 1, 2, 0, 0.00),
(7, 7, 1, 3, 1, 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `attempt_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` decimal(5,2) DEFAULT 0.00,
  `total_points` decimal(5,2) DEFAULT 0.00,
  `percentage` decimal(5,2) DEFAULT 0.00,
  `time_spent_seconds` int(11) DEFAULT 0,
  `status` enum('in_progress','completed') DEFAULT 'in_progress',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`attempt_id`, `quiz_id`, `user_id`, `score`, `total_points`, `percentage`, `time_spent_seconds`, `status`, `started_at`, `completed_at`) VALUES
(3, 1, 1, 1.00, 1.00, 100.00, 2, 'completed', '2025-10-16 17:19:59', '2025-10-16 17:19:59'),
(4, 1, 1, 0.00, 1.00, 0.00, 21, 'completed', '2025-10-16 17:22:34', '2025-10-16 17:22:34'),
(7, 1, 1, 1.00, 1.00, 100.00, 15, 'completed', '2025-10-16 17:40:52', '2025-10-16 17:40:52');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_options`
--

CREATE TABLE `quiz_options` (
  `option_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `order_number` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_options`
--

INSERT INTO `quiz_options` (`option_id`, `question_id`, `option_text`, `is_correct`, `order_number`) VALUES
(1, 1, 'd', 0, 0),
(2, 1, 'd', 0, 1),
(3, 1, 'f', 1, 2),
(4, 1, 'g', 0, 3);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `question_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `points` decimal(5,2) DEFAULT 1.00,
  `order_number` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`question_id`, `quiz_id`, `question_text`, `difficulty`, `points`, `order_number`) VALUES
(1, 1, 'd', 'medium', 1.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `rating_distribution`
--

CREATE TABLE `rating_distribution` (
  `distribution_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `five_star` int(11) DEFAULT 0,
  `four_star` int(11) DEFAULT 0,
  `three_star` int(11) DEFAULT 0,
  `two_star` int(11) DEFAULT 0,
  `one_star` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rating_distribution`
--

INSERT INTO `rating_distribution` (`distribution_id`, `material_id`, `five_star`, `four_star`, `three_star`, `two_star`, `one_star`, `updated_at`) VALUES
(1, 1, 120, 18, 12, 4, 2, '2025-10-15 14:05:16');

-- --------------------------------------------------------

--
-- Table structure for table `student_grades`
--

CREATE TABLE `student_grades` (
  `grade_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assignment_name` varchar(255) NOT NULL,
  `grade` decimal(5,2) NOT NULL,
  `max_grade` decimal(5,2) DEFAULT 100.00,
  `feedback` text DEFAULT NULL,
  `graded_by` int(11) DEFAULT NULL,
  `graded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_grades`
--

INSERT INTO `student_grades` (`grade_id`, `course_id`, `user_id`, `assignment_name`, `grade`, `max_grade`, `feedback`, `graded_by`, `graded_at`, `updated_at`) VALUES
(1, 3, 1, 'Assignment 1: Sorting Algorithms', 95.00, 100.00, 'Excellent work! Your implementation was clean and efficient.', 2, '2025-10-10 08:30:00', '2025-10-16 00:06:44'),
(2, 3, 1, 'Assignment 2: Binary Search Trees', 90.00, 100.00, 'Good job, but the balancing logic needs improvement.', 2, '2025-10-16 00:24:15', '2025-10-16 00:24:15'),
(3, 3, 1, 'Quiz 1: Data Structures', 92.00, 100.00, 'Well done!', 2, '2025-10-08 10:20:00', '2025-10-16 00:06:44'),
(4, 3, 3, 'Assignment 1: Sorting Algorithms', 90.00, 100.00, 'Great implementation!', 2, '2025-10-10 08:35:00', '2025-10-16 00:06:44'),
(5, 3, 3, 'Assignment 2: Binary Search Trees', 85.00, 100.00, 'Good effort, review the rotation concepts.', 2, '2025-10-12 03:20:00', '2025-10-16 00:06:44'),
(6, 3, 6, 'Assignment 1: Sorting Algorithms', 78.00, 100.00, 'Good start, but needs optimization.', 2, '2025-10-10 08:40:00', '2025-10-16 00:06:44'),
(7, 3, 6, 'Quiz 1: Data Structures', 88.00, 100.00, 'Nice work!', 2, '2025-10-08 10:25:00', '2025-10-16 00:06:44'),
(8, 3, 1, 'Assignment 3: Graph Algorithms', 57.00, 100.00, 'Good job! Some areas could use improvement.', 2, '2025-10-16 08:12:50', '2025-10-16 08:12:50');

-- --------------------------------------------------------

--
-- Stand-in structure for view `student_purchases`
-- (See below for the actual view)
--
CREATE TABLE `student_purchases` (
`user_id` int(11)
,`full_name` varchar(255)
,`course_id` int(11)
,`course_title` varchar(255)
,`amount` decimal(10,2)
,`payment_method` enum('card','paypal','bank_transfer','free')
,`payment_status` enum('pending','completed','failed','refunded')
,`payment_date` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `study_groups`
--

CREATE TABLE `study_groups` (
  `group_id` int(11) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `course_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `max_members` int(11) DEFAULT 10,
  `meeting_schedule` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT '?',
  `status` enum('active','inactive','full') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `study_groups`
--

INSERT INTO `study_groups` (`group_id`, `group_name`, `description`, `course_id`, `created_by`, `max_members`, `meeting_schedule`, `icon`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Algorithm Study Squad', 'Focused on mastering sorting algorithms and data structures. We meet twice a week to discuss problems and share solutions.', 1, 1, 12, 'Mon, Thu 7PM', '💻', 'active', '2025-10-16 18:09:41', '2025-10-16 18:09:41'),
(3, 'UI/UX Design Club', 'Collaborative design reviews, portfolio feedback, and weekly design challenges. All skill levels welcome!', 3, 1, 10, 'Wed 5PM', '🎨', 'active', '2025-10-16 18:09:41', '2025-10-16 18:09:41'),
(4, 'Machine Learning Masters', 'Advanced ML topics including neural networks, deep learning, and AI ethics. Prerequisites: Linear algebra & Python.', 4, 1, 12, 'Mon, Wed 8PM', '🚀', 'full', '2025-10-16 18:09:41', '2025-10-16 18:09:41'),
(5, 'Web Development Warriors', 'Building real projects together! From frontend frameworks to backend APIs. Share code and learn best practices.', 5, 1, 15, 'Sat 2PM', '🌐', 'active', '2025-10-16 18:09:41', '2025-10-16 18:09:41'),
(6, 'Mobile App Dev Circle', 'iOS and Android development study group. Working on cross-platform apps using React Native and Flutter.', 6, 1, 10, 'Thu 7PM', '📱', 'active', '2025-10-16 18:09:41', '2025-10-16 18:09:41'),
(7, 'ddd', 'fff', 1, 1, 6, '7pm', '💻', 'active', '2025-10-16 18:31:36', '2025-10-16 18:31:36');

-- --------------------------------------------------------

--
-- Table structure for table `study_group_members`
--

CREATE TABLE `study_group_members` (
  `member_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `study_group_members`
--

INSERT INTO `study_group_members` (`member_id`, `group_id`, `user_id`, `role`, `joined_at`) VALUES
(2, 1, 2, 'member', '2025-10-16 18:09:41'),
(3, 1, 3, 'member', '2025-10-16 18:09:41'),
(6, 3, 1, 'admin', '2025-10-16 18:09:41'),
(7, 6, 1, 'member', '2025-10-16 18:14:13'),
(8, 5, 1, 'member', '2025-10-16 18:14:20'),
(10, 7, 1, 'member', '2025-10-16 18:33:00'),
(11, 4, 1, 'member', '2025-10-16 18:41:50');

-- --------------------------------------------------------

--
-- Table structure for table `study_group_messages`
--

CREATE TABLE `study_group_messages` (
  `message_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `study_group_messages`
--

INSERT INTO `study_group_messages` (`message_id`, `group_id`, `user_id`, `message`, `created_at`) VALUES
(1, 1, 1, 'hello', '2025-10-16 18:29:11');

-- --------------------------------------------------------

--
-- Table structure for table `study_group_resources`
--

CREATE TABLE `study_group_resources` (
  `resource_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `resource_type` enum('document','link','video','other') DEFAULT 'document',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher') NOT NULL,
  `remember_me` tinyint(1) DEFAULT 0,
  `auth_provider` enum('local','google','microsoft') DEFAULT 'local',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `role`, `remember_me`, `auth_provider`, `created_at`, `updated_at`, `last_login`, `is_active`) VALUES
(1, 'John Smith', 'john.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 0, 'local', '2025-10-15 14:05:15', '2025-10-16 19:17:08', '2025-10-16 19:17:08', 1),
(2, 'Sarah Johnson', 'sarah.johnson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 0, 'local', '2025-10-15 14:05:15', '2025-10-16 18:45:23', '2025-10-16 18:45:23', 1),
(3, 'Mike Wilson', 'mike.wilson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 1, 'google', '2025-10-15 14:05:15', '2025-10-15 14:05:15', '2025-10-04 10:45:00', 1),
(4, 'Emily Davis', 'emily.davis@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 0, 'microsoft', '2025-10-15 14:05:15', '2025-10-15 14:05:15', '2025-10-06 02:00:00', 1),
(5, 'Alex Brown', 'alex.brown@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 0, 'local', '2025-10-15 14:05:15', '2025-10-15 14:05:15', '2025-10-03 05:20:00', 1),
(6, 'Sarah Anderson', 'sarah.anderson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 0, 'local', '2025-09-10 04:00:00', '2025-10-15 14:05:16', NULL, 1),
(7, 'Mike Johnson', 'mike.johnson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 0, 'local', '2025-09-12 05:00:00', '2025-10-15 14:05:16', NULL, 1),
(8, 'David Lee', 'david.lee@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 0, 'local', '2025-09-15 03:00:00', '2025-10-15 14:05:16', NULL, 1),
(9, 'Emma Martinez', 'emma.martinez@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 0, 'local', '2025-09-18 08:00:00', '2025-10-15 14:05:16', NULL, 1),
(10, 'Sayem', 'sayem@gmail.com', '$2y$10$7GmPFvOsmJv8AP7Rjb40deDR6ceKUFRhYiAhTcTemcjh7VS.WgqoK', 'teacher', 0, 'local', '2025-10-15 14:05:32', '2025-10-15 14:05:32', '2025-10-15 14:05:32', 1),
(11, 'Sayem', 'sayemt@gmail.com', '$2y$10$LAr/WBlFpDUzW8ChbdmlTeMV5FHDdxK72fJva/qpAPzo1NikVMevW', 'teacher', 0, 'local', '2025-10-15 14:06:15', '2025-10-15 15:59:07', '2025-10-15 15:59:07', 1),
(12, 'Sayem', 'sayems@gmail.com', '$2y$10$fkzpIHmC5Edl5wNBhclqp.8AuZkCucqNaaD47DHvcN7VvS/ZTmUNK', 'student', 0, 'local', '2025-10-15 14:06:39', '2025-10-15 16:31:42', '2025-10-15 16:31:42', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_statistics`
--

CREATE TABLE `user_statistics` (
  `stat_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `active_courses` int(11) DEFAULT 0,
  `avg_score` decimal(5,2) DEFAULT 0.00,
  `total_study_hours` decimal(7,2) DEFAULT 0.00,
  `materials_accessed` int(11) DEFAULT 0,
  `materials_trend` decimal(5,2) DEFAULT 0.00,
  `quizzes_completed` int(11) DEFAULT 0,
  `quizzes_trend` decimal(5,2) DEFAULT 0.00,
  `achievements_earned` int(11) DEFAULT 0,
  `achievements_trend` int(11) DEFAULT 0,
  `average_rating` decimal(3,2) DEFAULT 0.00,
  `rating_trend` decimal(3,2) DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_statistics`
--

INSERT INTO `user_statistics` (`stat_id`, `user_id`, `active_courses`, `avg_score`, `total_study_hours`, `materials_accessed`, `materials_trend`, `quizzes_completed`, `quizzes_trend`, `achievements_earned`, `achievements_trend`, `average_rating`, `rating_trend`, `updated_at`) VALUES
(1, 1, 7, 89.00, 42.00, 156, 23.00, 24, 12.00, 18, 8, 4.80, 0.30, '2025-10-16 12:55:34'),
(2, 10, 0, 0.00, 0.00, 0, 0.00, 0, 0.00, 0, 0, 0.00, 0.00, '2025-10-15 14:05:32'),
(3, 11, 0, 0.00, 0.00, 0, 0.00, 0, 0.00, 0, 0, 0.00, 0.00, '2025-10-15 14:06:15'),
(4, 12, 0, 0.00, 0.00, 0, 0.00, 0, 0.00, 0, 0, 0.00, 0.00, '2025-10-15 14:06:39');

-- --------------------------------------------------------

--
-- Structure for view `course_revenue`
--
DROP TABLE IF EXISTS `course_revenue`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `course_revenue`  AS SELECT `c`.`course_id` AS `course_id`, `c`.`title` AS `title`, `c`.`price` AS `price`, count(distinct `p`.`payment_id`) AS `total_sales`, sum(`p`.`amount`) AS `total_revenue`, avg(`p`.`amount`) AS `avg_sale_price` FROM (`courses` `c` left join `payments` `p` on(`c`.`course_id` = `p`.`course_id` and `p`.`payment_status` = 'completed')) GROUP BY `c`.`course_id`, `c`.`title`, `c`.`price` ;

-- --------------------------------------------------------

--
-- Structure for view `student_purchases`
--
DROP TABLE IF EXISTS `student_purchases`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `student_purchases`  AS SELECT `u`.`user_id` AS `user_id`, `u`.`full_name` AS `full_name`, `c`.`course_id` AS `course_id`, `c`.`title` AS `course_title`, `p`.`amount` AS `amount`, `p`.`payment_method` AS `payment_method`, `p`.`payment_status` AS `payment_status`, `p`.`payment_date` AS `payment_date` FROM ((`users` `u` join `payments` `p` on(`u`.`user_id` = `p`.`user_id`)) join `courses` `c` on(`p`.`course_id` = `c`.`course_id`)) WHERE `u`.`role` = 'student' ORDER BY `p`.`payment_date` DESC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `idx_user_date` (`user_id`,`created_at`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `posted_by` (`posted_by`),
  ADD KEY `idx_course_date` (`course_id`,`created_at`);

--
-- Indexes for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD PRIMARY KEY (`submission_id`),
  ADD UNIQUE KEY `unique_submission` (`material_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_material_status` (`material_id`,`status`);

--
-- Indexes for table `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD PRIMARY KEY (`like_id`),
  ADD UNIQUE KEY `unique_user_like` (`comment_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_comment` (`comment_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`coupon_id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_active` (`is_active`,`valid_until`);

--
-- Indexes for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD PRIMARY KEY (`usage_id`),
  ADD UNIQUE KEY `unique_user_coupon_course` (`user_id`,`coupon_id`,`course_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `idx_coupon` (`coupon_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indexes for table `course_discussions`
--
ALTER TABLE `course_discussions`
  ADD PRIMARY KEY (`discussion_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_course_date` (`course_id`,`created_at`),
  ADD KEY `idx_parent` (`parent_discussion_id`);

--
-- Indexes for table `course_discussion_likes`
--
ALTER TABLE `course_discussion_likes`
  ADD PRIMARY KEY (`like_id`),
  ADD UNIQUE KEY `unique_user_like` (`discussion_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_discussion` (`discussion_id`);

--
-- Indexes for table `course_schedule`
--
ALTER TABLE `course_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `idx_course` (`course_id`);

--
-- Indexes for table `course_tags`
--
ALTER TABLE `course_tags`
  ADD PRIMARY KEY (`tag_id`),
  ADD KEY `idx_course` (`course_id`),
  ADD KEY `idx_tag_name` (`tag_name`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD UNIQUE KEY `unique_enrollment` (`user_id`,`course_id`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `idx_course` (`course_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `idx_user_datetime` (`user_id`,`event_datetime`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_course` (`course_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `material_bookmarks`
--
ALTER TABLE `material_bookmarks`
  ADD PRIMARY KEY (`bookmark_id`),
  ADD UNIQUE KEY `unique_bookmark` (`material_id`,`user_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `material_comments`
--
ALTER TABLE `material_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `parent_comment_id` (`parent_comment_id`),
  ADD KEY `idx_material` (`material_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `material_ratings`
--
ALTER TABLE `material_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `unique_user_rating` (`material_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `material_shares`
--
ALTER TABLE `material_shares`
  ADD PRIMARY KEY (`share_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_material` (`material_id`);

--
-- Indexes for table `material_tags`
--
ALTER TABLE `material_tags`
  ADD PRIMARY KEY (`tag_id`),
  ADD KEY `idx_material` (`material_id`),
  ADD KEY `idx_tag` (`tag_name`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`,`created_at`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`token_id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `idx_user_payment` (`user_id`,`payment_status`),
  ADD KEY `idx_transaction` (`transaction_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`quiz_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `attempt_id` (`attempt_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `quiz_options`
--
ALTER TABLE `quiz_options`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `rating_distribution`
--
ALTER TABLE `rating_distribution`
  ADD PRIMARY KEY (`distribution_id`),
  ADD UNIQUE KEY `material_id` (`material_id`);

--
-- Indexes for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD PRIMARY KEY (`grade_id`),
  ADD UNIQUE KEY `unique_student_assignment` (`course_id`,`user_id`,`assignment_name`),
  ADD KEY `graded_by` (`graded_by`),
  ADD KEY `idx_course_user` (`course_id`,`user_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `study_groups`
--
ALTER TABLE `study_groups`
  ADD PRIMARY KEY (`group_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_course` (`course_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `study_group_members`
--
ALTER TABLE `study_group_members`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `unique_member` (`group_id`,`user_id`),
  ADD KEY `idx_group` (`group_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `study_group_messages`
--
ALTER TABLE `study_group_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_group` (`group_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `study_group_resources`
--
ALTER TABLE `study_group_resources`
  ADD PRIMARY KEY (`resource_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_group` (`group_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `user_statistics`
--
ALTER TABLE `user_statistics`
  ADD PRIMARY KEY (`stat_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `comment_likes`
--
ALTER TABLE `comment_likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  MODIFY `usage_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `course_discussions`
--
ALTER TABLE `course_discussions`
  MODIFY `discussion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `course_discussion_likes`
--
ALTER TABLE `course_discussion_likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `course_schedule`
--
ALTER TABLE `course_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `course_tags`
--
ALTER TABLE `course_tags`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `material_bookmarks`
--
ALTER TABLE `material_bookmarks`
  MODIFY `bookmark_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `material_comments`
--
ALTER TABLE `material_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `material_ratings`
--
ALTER TABLE `material_ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `material_shares`
--
ALTER TABLE `material_shares`
  MODIFY `share_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `material_tags`
--
ALTER TABLE `material_tags`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `quiz_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `quiz_options`
--
ALTER TABLE `quiz_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rating_distribution`
--
ALTER TABLE `rating_distribution`
  MODIFY `distribution_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_grades`
--
ALTER TABLE `student_grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `study_groups`
--
ALTER TABLE `study_groups`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `study_group_members`
--
ALTER TABLE `study_group_members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `study_group_messages`
--
ALTER TABLE `study_group_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `study_group_resources`
--
ALTER TABLE `study_group_resources`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_statistics`
--
ALTER TABLE `user_statistics`
  MODIFY `stat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`posted_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD CONSTRAINT `assignment_submissions_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_submissions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD CONSTRAINT `comment_likes_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `material_comments` (`comment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comment_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `coupons_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD CONSTRAINT `coupon_usage_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`coupon_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_usage_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_usage_ibfk_3` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `course_discussions`
--
ALTER TABLE `course_discussions`
  ADD CONSTRAINT `course_discussions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_discussions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_discussions_ibfk_3` FOREIGN KEY (`parent_discussion_id`) REFERENCES `course_discussions` (`discussion_id`) ON DELETE CASCADE;

--
-- Constraints for table `course_discussion_likes`
--
ALTER TABLE `course_discussion_likes`
  ADD CONSTRAINT `course_discussion_likes_ibfk_1` FOREIGN KEY (`discussion_id`) REFERENCES `course_discussions` (`discussion_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_discussion_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `course_schedule`
--
ALTER TABLE `course_schedule`
  ADD CONSTRAINT `course_schedule_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `course_tags`
--
ALTER TABLE `course_tags`
  ADD CONSTRAINT `course_tags_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_3` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`) ON DELETE SET NULL;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `materials_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `material_bookmarks`
--
ALTER TABLE `material_bookmarks`
  ADD CONSTRAINT `material_bookmarks_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_bookmarks_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `material_comments`
--
ALTER TABLE `material_comments`
  ADD CONSTRAINT `material_comments_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_comments_ibfk_3` FOREIGN KEY (`parent_comment_id`) REFERENCES `material_comments` (`comment_id`) ON DELETE CASCADE;

--
-- Constraints for table `material_ratings`
--
ALTER TABLE `material_ratings`
  ADD CONSTRAINT `material_ratings_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `material_shares`
--
ALTER TABLE `material_shares`
  ADD CONSTRAINT `material_shares_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_shares_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `material_tags`
--
ALTER TABLE `material_tags`
  ADD CONSTRAINT `material_tags_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quizzes_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD CONSTRAINT `quiz_answers_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`attempt_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_options`
--
ALTER TABLE `quiz_options`
  ADD CONSTRAINT `quiz_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`) ON DELETE CASCADE;

--
-- Constraints for table `rating_distribution`
--
ALTER TABLE `rating_distribution`
  ADD CONSTRAINT `rating_distribution_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD CONSTRAINT `student_grades_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_grades_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_grades_ibfk_3` FOREIGN KEY (`graded_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `study_groups`
--
ALTER TABLE `study_groups`
  ADD CONSTRAINT `study_groups_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `study_groups_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `study_group_members`
--
ALTER TABLE `study_group_members`
  ADD CONSTRAINT `study_group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `study_groups` (`group_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `study_group_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `study_group_messages`
--
ALTER TABLE `study_group_messages`
  ADD CONSTRAINT `study_group_messages_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `study_groups` (`group_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `study_group_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `study_group_resources`
--
ALTER TABLE `study_group_resources`
  ADD CONSTRAINT `study_group_resources_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `study_groups` (`group_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `study_group_resources_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_statistics`
--
ALTER TABLE `user_statistics`
  ADD CONSTRAINT `user_statistics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
