<?php
// signup.php
session_start();
require_once 'config.php';

// Initialize variables
$error_message = '';
$success_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    // Sanitize and validate input
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    $remember_me = isset($_POST['remember_me']) ? 1 : 0;
    $auth_provider = 'local';
    
    // Validation
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    } elseif (strlen($full_name) < 3) {
        $errors[] = "Full name must be at least 3 characters";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if (!in_array($role, ['student', 'teacher'])) {
        $errors[] = "Invalid role selected";
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Email already exists. Please use a different email or <a href='login.php' style='color: #667eea;'>login</a>";
        }
        $stmt->close();
    }
    
    // If no errors, insert user
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare insert statement
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, remember_me, auth_provider, last_login, is_active) VALUES (?, ?, ?, ?, ?, ?, NOW(), 1)");
        $stmt->bind_param("ssssis", $full_name, $email, $hashed_password, $role, $remember_me, $auth_provider);
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            
            // Initialize user statistics
            $stat_stmt = $conn->prepare("INSERT INTO user_statistics (user_id) VALUES (?)");
            $stat_stmt->bind_param("i", $user_id);
            $stat_stmt->execute();
            $stat_stmt->close();
            
            // Set session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;
            $_SESSION['logged_in'] = true;
            
            // Set remember me cookie if checked
            if ($remember_me) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), "/", "", true, true);
            }
            
            $success_message = "Account created successfully! Redirecting...";
            
            // Redirect based on role
            $redirect_url = ($role === 'teacher') ? 'tdashboard.php' : 'sdashboard.php';
            header("refresh:2;url=$redirect_url");
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        
        $stmt->close();
    }
    
    // Store errors in error message
    if (!empty($errors)) {
        $error_message = implode('<br>', $errors);
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - EduHub</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');
        
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }
        
        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        } 
        .bg-gradient {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.4;
            animation: float 20s infinite;
        }
        
        .bg-gradient:nth-child(1) {
            width: 600px;
            height: 600px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            top: -300px;
            left: -200px;
            animation-delay: 0s;
        }
        
        .bg-gradient:nth-child(2) {
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            bottom: -200px;
            right: -150px;
            animation-delay: -10s;
        }
        
        .bg-gradient:nth-child(3) {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            top: 50%;
            left: 50%;
            animation-delay: -5s;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(100px, -100px) scale(1.1); }
            66% { transform: translate(-80px, 80px) scale(0.9); }
        }
        
        /* Particles */
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: particle-float 15s infinite;
        }
        
        @keyframes particle-float {
            0%, 100% { 
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { 
                transform: translateY(-100vh) translateX(100px);
                opacity: 0;
            }
        }
        
        .container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1200px;
            padding: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: start;
            margin: auto;
        }
        
        /* Left Side - Branding */
        .branding-side {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.4);
        }
        
        .logo-text h1 {
            font-size: 42px;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea 0%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .logo-text p {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 5px;
        }
        
        .welcome-text {
            margin-bottom: 20px;
        }
        
        .welcome-text h2 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .welcome-text p {
            font-size: 18px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .features {
            display: grid;
            gap: 20px;
            margin-top: 30px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            transition: all 0.3s;
        }
        
        .feature-item:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(10px);
        }
        
        .feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        
        .feature-text h4 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .feature-text p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        /* Right Side - Login Form */
        .login-side {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-card {
            width: 100%;
            max-width: 480px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(40px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 35px;
            padding: 50px 45px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 200px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-radius: 35px 35px 0 0;
            z-index: 0;
        }
        
        .login-content {
            position: relative;
            z-index: 1;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-header h2 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .login-header p {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 14px;
            margin-bottom: 25px;
            font-size: 14px;
            animation: slideDown 0.3s ease;
        }
        
        .alert-error {
            background: rgba(245, 87, 108, 0.15);
            border: 1px solid rgba(245, 87, 108, 0.3);
            color: #ff6b6b;
        }
        
        .alert-success {
            background: rgba(56, 239, 125, 0.15);
            border: 1px solid rgba(56, 239, 125, 0.3);
            color: #38ef7d;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Tabs */
        .role-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 35px;
            background: rgba(255, 255, 255, 0.05);
            padding: 6px;
            border-radius: 16px;
        }
        
        .role-tab {
            padding: 14px;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            font-size: 15px;
            color: rgba(255, 255, 255, 0.6);
            border: 1px solid transparent;
        }
        
        .role-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .role-tab:hover:not(.active) {
            background: rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.9);
        }
        
        /* Form */
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            pointer-events: none;
        }
        
        .form-input {
            width: 100%;
            padding: 16px 20px 16px 52px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 15px;
            font-family: 'Outfit', sans-serif;
            transition: all 0.3s;
        }
        
        .form-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(102, 126, 234, 0.5);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 20px;
            color: rgba(255, 255, 255, 0.5);
            transition: color 0.3s;
        }
        
        .password-toggle:hover {
            color: rgba(255, 255, 255, 0.9);
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }
        
        .forgot-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .forgot-link:hover {
            color: #f093fb;
        }
        
        .login-btn {
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
            position: relative;
            overflow: hidden;
        }
        
        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }
        
        .login-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(102, 126, 234, 0.5);
        }
        
        .login-btn:hover::before {
            left: 100%;
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .divider {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 30px 0;
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .social-login {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 30px;
        }
        
        .social-btn {
            padding: 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            color: white;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .social-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        
        .signup-prompt {
            text-align: center;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .signup-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s;
        }
        
        .signup-link:hover {
            color: #f093fb;
        }
        
        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
                max-width: 500px;
            }
            
            .branding-side {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .login-card {
                padding: 40px 30px;
            }
            
            .welcome-text h2 {
                font-size: 36px;
            }
            
            .social-login {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="bg-gradient"></div>
        <div class="bg-gradient"></div>
        <div class="bg-gradient"></div>
    </div>
    
    <div class="container">
        <!-- Left Side - Branding -->
        <div class="branding-side">
            <div class="logo">
                <div class="logo-icon">🎓</div>
                <div class="logo-text">
                    <h1>EduHub</h1>
                    <p>Learn Without Limits</p>
                </div>
            </div>
            
            <div class="welcome-text">
                <h2>Create Your Account ✨</h2>
                <p>Join EduHub and start your learning journey</p>
            </div>
            
            <div class="features">
                <div class="feature-item">
                    <div class="feature-icon">🤖</div>
                    <div class="feature-text">
                        <h4>AI-Powered Learning</h4>
                        <p>Get instant help from our intelligent tutoring system</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">📚</div>
                    <div class="feature-text">
                        <h4>Rich Course Library</h4>
                        <p>Access hundreds of courses from top instructors</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">👥</div>
                    <div class="feature-text">
                        <h4>Collaborative Learning</h4>
                        <p>Join study groups and learn together</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="login-side">
            <div class="login-card">
                <div class="login-content">
                    <div class="login-header">
                        <h2>Hello, There! 👋</h2>
                        <p>Sign UP to continue your learning journey</p>
                    </div>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-error">
                            ⚠️ <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            ✅ <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Role Selection Tabs -->
                    <div class="role-tabs">
                        <div class="role-tab active" data-role="student">
                            🎓 Student
                        </div>
                        <div class="role-tab" data-role="teacher">
                            👨‍🏫 Teacher
                        </div>
                    </div>
                    
                    <!-- Sign Up Form -->
                    <form id="signupForm" method="POST" action="">
                        <!-- Hidden field for role -->
                        <input type="hidden" name="role" id="roleInput" value="student">
                        
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <div class="input-wrapper">
                                <span class="input-icon">👤</span>
                                <input type="text" name="full_name" class="form-input" placeholder="Your full name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <div class="input-wrapper">
                                <span class="input-icon">📧</span>
                                <input type="email" name="email" class="form-input" placeholder="your.email@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <div class="input-wrapper">
                                <span class="input-icon">🔒</span>
                                <input type="password" name="password" class="form-input" id="passwordInput" placeholder="Create a password" required minlength="6">
                                <span class="password-toggle" onclick="togglePassword()">👁️</span>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="checkbox-wrapper">
                                <input type="checkbox" name="remember_me">
                                <span>Remember me</span>
                            </label>
                            <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
                        </div>
                        
                        <button type="submit" class="login-btn">Sign Up</button>
                    </form>
                    
                    <div class="divider">or continue with</div>
                    
                    <div class="social-login">
                        <button class="social-btn" onclick="socialLogin('google')">
                            <span>🔵</span> Google
                        </button>
                        <button class="social-btn" onclick="socialLogin('microsoft')">
                            <span>📘</span> Microsoft
                        </button>
                    </div>
                    
                    <div class="signup-prompt">
                        Already have an account? <a href="login.php" class="signup-link">Log In</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Create floating particles
        function createParticles() {
            const bgAnimation = document.querySelector('.bg-animation');
            for (let i = 0; i < 30; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
                bgAnimation.appendChild(particle);
            }
        }
        
        createParticles();
        
        // Role tab switching
        document.querySelectorAll('.role-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                const role = this.dataset.role;
                document.getElementById('roleInput').value = role;
                
                // Add animation effect
                const loginCard = document.querySelector('.login-card');
                loginCard.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    loginCard.style.transform = 'scale(1)';
                }, 100);
            });
        });
        
        // Password toggle
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const toggle = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggle.textContent = '👁️‍🗨️';
            } else {
                passwordInput.type = 'password';
                toggle.textContent = '👁️';
            }
        }
        
        // Form submission with loading state
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const btn = this.querySelector('.login-btn');
            btn.textContent = 'Creating Account...';
            btn.disabled = true;
        });
        
        // Social login function
        function socialLogin(provider) {
            // Redirect to social auth handler
            window.location.href = 'social-auth.php?provider=' + provider;
        }
        
        // Animate elements on load
        window.addEventListener('load', () => {
            const loginCard = document.querySelector('.login-card');
            const features = document.querySelectorAll('.feature-item');
            
            loginCard.style.opacity = '0';
            loginCard.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                loginCard.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                loginCard.style.opacity = '1';
                loginCard.style.transform = 'translateY(0)';
            }, 200);
            
            features.forEach((feature, index) => {
                feature.style.opacity = '0';
                feature.style.transform = 'translateX(-30px)';
                
                setTimeout(() => {
                    feature.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    feature.style.opacity = '1';
                    feature.style.transform = 'translateX(0)';
                }, 400 + (index * 150));
            });
        });
        
        // Input focus effects
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
        
        // Add hover effect to logo
        const logoIcon = document.querySelector('.logo-icon');
        if (logoIcon) {
            logoIcon.addEventListener('mouseenter', function() {
                this.style.transform = 'rotate(360deg) scale(1.1)';
                this.style.transition = 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            });
            
            logoIcon.addEventListener('mouseleave', function() {
                this.style.transform = 'rotate(0deg) scale(1)';
            });
        }
    </script>
</body>
</html>