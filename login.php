<?php
// login.php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $redirect = ($_SESSION['role'] === 'teacher') ? 'tdashboard.php' : 'sdashboard.php';
    header("Location: $redirect");
    exit();
}

$error_message = '';
$success_message = '';

// Check for logout message
if (isset($_GET['logout'])) {
    $success_message = "You have been successfully logged out.";
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']) ? 1 : 0;
    
    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password";
    } else {
        // Fetch user from database
        $stmt = $conn->prepare("SELECT user_id, full_name, email, password, role, is_active FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check if account is active
            if (!$user['is_active']) {
                $error_message = "Your account has been deactivated. Please contact support.";
            } 
            // Verify password
            elseif (password_verify($password, $user['password'])) {
                // Update last login and remember_me status
                $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW(), remember_me = ? WHERE user_id = ?");
                $update_stmt->bind_param("ii", $remember_me, $user['user_id']);
                $update_stmt->execute();
                $update_stmt->close();
                
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                
                // Set remember me cookie if checked
                if ($remember_me) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (86400 * 30), "/", "", true, true);
                }
                
                // Redirect based on role
                $redirect_url = ($user['role'] === 'teacher') ? 'tdashboard.php' : 'sdashboard.php';
                header("Location: $redirect_url");
                exit();
            } else {
                $error_message = "Invalid email or password";
            }
        } else {
            $error_message = "Invalid email or password";
        }
        
        $stmt->close();
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - EduHub</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap');
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Outfit', sans-serif;
        background: #0f0f1e;
        color: white;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }
    .login-card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 40px;
        width: 100%;
        max-width: 400px;
        backdrop-filter: blur(20px);
        box-shadow: 0 15px 50px rgba(0,0,0,0.4);
        text-align: center;
    }
    .logo {
        font-size: 40px;
        margin-bottom: 10px;
    }
    h2 {
        font-weight: 700;
        margin-bottom: 20px;
    }
    
    /* Alert Messages */
    .alert {
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 14px;
        text-align: left;
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
    
    .form-group {
        text-align: left;
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 14px;
    }
    input {
        width: 100%;
        padding: 12px 15px;
        border-radius: 10px;
        border: 1px solid rgba(255,255,255,0.1);
        background: rgba(255,255,255,0.07);
        color: white;
        font-size: 14px;
        font-family: 'Outfit', sans-serif;
    }
    input::placeholder {
        color: rgba(255,255,255,0.4);
    }
    input:focus {
        outline: none;
        border-color: #667eea;
        background: rgba(255,255,255,0.1);
    }
    
    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        font-size: 13px;
        text-align: left;
    }
    
    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }
    
    .checkbox-wrapper input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: #667eea;
    }
    
    .forgot-link {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }
    
    .forgot-link:hover {
        color: #f093fb;
    }
    
    .login-btn {
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: 0.3s;
        margin-top: 10px;
        font-family: 'Outfit', sans-serif;
    }
    .login-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102,126,234,0.5);
    }
    .login-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
    .signup-text {
        margin-top: 20px;
        font-size: 14px;
        color: rgba(255,255,255,0.7);
    }
    .signup-text a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }
    .signup-text a:hover {
        color: #f093fb;
    }
</style>
</head>
<body>
    <div class="login-card">
        <div class="logo">🎓</div>
        <h2>Sign In to EduHub</h2>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                ⚠️ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                ✅ <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <form id="loginForm" method="POST" action="">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="your.email@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <div class="form-options">
                <label class="checkbox-wrapper">
                    <input type="checkbox" name="remember_me">
                    <span>Remember me</span>
                </label>
                <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
            </div>
            
            <button type="submit" class="login-btn">Login</button>
        </form>
        <div class="signup-text">
            Don't have an account? <a href="signup.php">Sign Up</a>
        </div>
    </div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const btn = document.querySelector('.login-btn');
    btn.textContent = 'Logging in...';
    btn.disabled = true;
});
</script>
</body>
</html>