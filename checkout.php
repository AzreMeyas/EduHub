<?php
require_once 'auth-check.php';
require_once 'config.php';

checkRole('student');
$user_id = $_SESSION['user_id'];
$course_id = intval($_GET['course_id']);

$conn = getDBConnection();

// Get course details with dynamic statistics
$query = "
    SELECT 
        c.*,
        u.full_name as instructor_name,
        -- Dynamically calculate total lessons
        (SELECT COUNT(*) FROM materials WHERE course_id = c.course_id) as total_lessons,
        -- Dynamically calculate total hours
        ROUND(
            (COALESCE((SELECT SUM(duration_minutes) FROM materials WHERE course_id = c.course_id), 0) / 60) +
            (COALESCE((SELECT SUM(file_size) FROM materials WHERE course_id = c.course_id), 0) / (1024 * 1024) * 6 / 60),
            2
        ) as total_hours
    FROM courses c
    LEFT JOIN users u ON c.instructor_id = u.user_id
    WHERE c.course_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();

if (!$course) {
    $_SESSION['error_message'] = "Course not found.";
    header("Location: courseview.php?id=$course_id");
    exit();
}

// Calculate final price
$final_price = $course['discount_price'] && $course['discount_price'] < $course['price'] 
    ? $course['discount_price'] 
    : $course['price'];

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'];
    
    // In a real application, you would integrate with a payment gateway here
    // For demo purposes, we'll simulate a successful payment
    
    // Create payment record
    $payment_query = "INSERT INTO payments (user_id, course_id, amount, currency, payment_method, payment_status, transaction_id) VALUES (?, ?, ?, ?, ?, 'completed', ?)";
    $transaction_id = 'TXN_' . time() . '_' . $user_id;
    $stmt = $conn->prepare($payment_query);
    $stmt->bind_param("iidsss", $user_id, $course_id, $final_price, $course['currency'], $payment_method, $transaction_id);
    $stmt->execute();
    $payment_id = $conn->insert_id;
    
    // Create enrollment
    $enroll_query = "INSERT INTO enrollments (user_id, course_id, progress_percentage, hours_remaining, status, badge_label, payment_id) VALUES (?, ?, 0, ?, 'not_started', 'New', ?)";
    $stmt = $conn->prepare($enroll_query);
    $stmt->bind_param("iidi", $user_id, $course_id, $course['total_hours'], $payment_id);
    $stmt->execute();
    
    // Update statistics
    $update_stats = "UPDATE user_statistics SET active_courses = active_courses + 1 WHERE user_id = ?";
    $stmt = $conn->prepare($update_stats);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    // Add activity
    $activity_query = "INSERT INTO activities (user_id, activity_type, icon, title, description) VALUES (?, 'enrollment', '📚', 'Purchased Course', 'Successfully purchased and enrolled in a new course')";
    $stmt = $conn->prepare($activity_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $_SESSION['success_message'] = "Payment successful! You are now enrolled in the course.";
    header("Location: courseview.php?id=$course_id");
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - EduHub</title>
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
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(80px, -80px); }
        }
        
        .container {
            position: relative;
            z-index: 1;
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .header h1 {
            font-size: 36px;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea 0%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 30px;
        }
        
        .card-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .course-summary {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .course-thumbnail {
            width: 120px;
            height: 120px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            flex-shrink: 0;
        }
        
        .course-info h3 {
            font-size: 20px;
            margin-bottom: 8px;
        }
        
        .course-info p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .form-input {
            width: 100%;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-size: 15px;
        }
        
        .form-input:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .payment-method {
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-method:hover {
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .payment-method.active {
            background: rgba(102, 126, 234, 0.2);
            border-color: #667eea;
        }
        
        .payment-method input[type="radio"] {
            display: none;
        }
        
        .payment-icon {
            font-size: 32px;
            margin-bottom: 8px;
        }
        
        .payment-name {
            font-size: 14px;
            font-weight: 600;
        }
        
        .order-summary-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .order-summary-row:last-child {
            border-bottom: none;
            font-size: 24px;
            font-weight: 800;
            color: #667eea;
            padding-top: 20px;
        }
        
        .discount-tag {
            display: inline-block;
            padding: 4px 10px;
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            margin-left: 10px;
        }
        
        .pay-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 15px;
            color: white;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }
        
        .pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.5);
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            color: white;
        }
        
        .secure-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
        }
        
        @media (max-width: 968px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="bg-gradient"></div>
    </div>
    
    <div class="container">
        <a href="browsecourse.php" class="back-link">← Back to Courses</a>
        
        <div class="header">
            <h1>Complete Your Purchase</h1>
            <p style="color: rgba(255, 255, 255, 0.6);">You're one step away from starting your learning journey</p>
        </div>
        
        <form method="POST" action="checkout.php?course_id=<?php echo $course_id; ?>">
            <div class="checkout-grid">
                <div>
                    <div class="card">
                        <h2 class="card-title">Course Details</h2>
                        <div class="course-summary">
                            <div class="course-thumbnail" style="background: <?php echo htmlspecialchars($course['color']); ?>;">
                                <?php echo htmlspecialchars($course['icon']); ?>
                            </div>
                            <div class="course-info">
                                <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                                <p>👤 <?php echo htmlspecialchars($course['instructor_name']); ?></p>
                                <p>📚 <?php echo $course['total_lessons']; ?> lessons • ⏱️ <?php echo number_format($course['total_hours'], 1); ?> hours</p>
                                <p>⭐ <?php echo number_format($course['rating_average'], 1); ?> rating</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card" style="margin-top: 30px;">
                        <h2 class="card-title">Payment Method</h2>
                        <div class="payment-methods">
                            <label class="payment-method active">
                                <input type="radio" name="payment_method" value="card" checked required>
                                <div class="payment-icon">💳</div>
                                <div class="payment-name">Credit Card</div>
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="paypal" required>
                                <div class="payment-icon">💰</div>
                                <div class="payment-name">PayPal</div>
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="bank_transfer" required>
                                <div class="payment-icon">🏦</div>
                                <div class="payment-name">Bank</div>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Card Number</label>
                            <input type="text" class="form-input" placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label class="form-label">Expiry Date</label>
                                <input type="text" class="form-input" placeholder="MM/YY" maxlength="5">
                            </div>
                            <div class="form-group">
                                <label class="form-label">CVV</label>
                                <input type="text" class="form-input" placeholder="123" maxlength="4">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="card">
                        <h2 class="card-title">Order Summary</h2>
                        
                        <div class="order-summary-row">
                            <span>Course Price</span>
                            <span><?php echo $course['currency']; ?> <?php echo number_format($course['price'], 2); ?></span>
                        </div>
                        
                        <?php if ($course['discount_price'] && $course['discount_price'] < $course['price']): ?>
                        <div class="order-summary-row">
                            <span>
                                Discount
                                <span class="discount-tag">-<?php echo $course['discount_percentage']; ?>%</span>
                            </span>
                            <span style="color: #10b981;">-<?php echo $course['currency']; ?> <?php echo number_format($course['price'] - $course['discount_price'], 2); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="order-summary-row">
                            <span>Total</span>
                            <span><?php echo $course['currency']; ?> <?php echo number_format($final_price, 2); ?></span>
                        </div>
                        
                        <button type="submit" class="pay-btn">Complete Purchase</button>
                        
                        <div class="secure-badge">
                            🔒 Secure payment powered by EduHub
                        </div>
                    </div>
                    
                    <div class="card" style="margin-top: 30px; background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.3);">
                        <h3 style="font-size: 18px; margin-bottom: 15px;">✓ What You'll Get</h3>
                        <ul style="list-style: none; padding: 0;">
                            <li style="padding: 8px 0; color: rgba(255, 255, 255, 0.8);">📚 Lifetime access to course materials</li>
                            <li style="padding: 8px 0; color: rgba(255, 255, 255, 0.8);">📱 Access on mobile and desktop</li>
                            <li style="padding: 8px 0; color: rgba(255, 255, 255, 0.8);">🎓 Certificate of completion</li>
                            <li style="padding: 8px 0; color: rgba(255, 255, 255, 0.8);">💬 Direct instructor support</li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <script>
        // Payment method selection
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('active'));
                this.classList.add('active');
                this.querySelector('input[type="radio"]').checked = true;
            });
        });
    </script>
</body>
</html>