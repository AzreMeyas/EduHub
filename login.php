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
    }
    input:focus {
        outline: none;
        border-color: #667eea;
        background: rgba(255,255,255,0.1);
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
    }
    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102,126,234,0.5);
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
        <form id="loginForm">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" placeholder="your.email@example.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>
        <div class="signup-text">
            Don't have an account? <a href="signup.html">Sign Up</a>
        </div>
    </div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.querySelector('.login-btn');
    btn.textContent = 'Logging in...';
    btn.disabled = true;
    setTimeout(() => window.location.href = 'dashboard.html', 1200);
});
</script>
</body>
</html>
