<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduHub - Creative Dashboard</title>
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
            overflow-x: hidden;
        }
        
        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
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
            animation-delay: 0s;
        }
        
        .bg-gradient:nth-child(2) {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            top: 50%;
            right: -100px;
            animation-delay: 5s;
        }
        
        .bg-gradient:nth-child(3) {
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            bottom: -100px;
            left: 30%;
            animation-delay: 10s;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(100px, -100px) scale(1.1); }
            66% { transform: translate(-100px, 100px) scale(0.9); }
        }
        
        .dashboard {
            position: relative;
            z-index: 1;
            padding: 30px;
            max-width: 1600px;
            margin: 0 auto;
        }
        
        /* Glassmorphic Header */
        .header {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 25px 40px;
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
            animation: pulse 3s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .logo-text {
            color: white;
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .search-container {
            flex: 1;
            max-width: 600px;
            margin: 0 50px;
            position: relative;
        }
        
        .search-box {
            width: 100%;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 18px 60px 18px 25px;
            color: white;
            font-size: 16px;
            transition: all 0.4s;
        }
        
        .search-box:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(102, 126, 234, 0.5);
            box-shadow: 0 0 30px rgba(102, 126, 234, 0.3);
        }
        
        .search-box::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .search-icon {
            position: absolute;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 24px;
            cursor: pointer;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .icon-btn {
            width: 55px;
            height: 55px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            font-size: 24px;
        }
        
        .icon-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-3px);
        }
        
        .notification-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 12px;
            height: 12px;
            background: #ff4757;
            border-radius: 50%;
            border: 2px solid #0f0f1e;
            animation: ping 2s infinite;
        }
        
        @keyframes ping {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; transform: scale(1.2); }
        }
        
        .user-profile {
            width: 55px;
            height: 55px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s;
        }
        
        .user-profile:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }
        
        /* Main Layout */
        .main-layout {
            display: grid;
            grid-template-columns: 280px 1fr 350px;
            gap: 30px;
        }
        
        /* Sidebar */
        .sidebar {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 30px 20px;
            height: fit-content;
        }
        
        .nav-group {
            margin-bottom: 30px;
        }
        
        .nav-title {
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            padding: 0 15px;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 16px 20px;
            color: rgba(255, 255, 255, 0.7);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 8px;
            position: relative;
            overflow: hidden;
        }
        
        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .nav-item:hover::before,
        .nav-item.active::before {
            opacity: 0.15;
        }
        
        .nav-item.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .nav-item span:first-child {
            font-size: 22px;
            z-index: 1;
        }
        
        .nav-item span:last-child {
            font-weight: 500;
            z-index: 1;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 35px;
            padding: 50px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.4);
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero-greeting {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 10px;
        }
        
        .hero-title {
            font-size: 42px;
            font-weight: 800;
            color: white;
            margin-bottom: 15px;
        }
        
        .hero-description {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 30px;
            max-width: 600px;
        }
        
        .hero-stats {
            display: flex;
            gap: 40px;
        }
        
        .hero-stat {
            display: flex;
            flex-direction: column;
        }
        
        .hero-stat-value {
            font-size: 32px;
            font-weight: 800;
            color: white;
        }
        
        .hero-stat-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 30px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.4s;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            border-color: rgba(102, 126, 234, 0.3);
        }
        
        .stat-card:hover::before {
            opacity: 1;
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .stat-icon {
            font-size: 36px;
        }
        
        .stat-trend {
            background: rgba(17, 153, 142, 0.2);
            color: #38ef7d;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .stat-value {
            font-size: 38px;
            font-weight: 800;
            color: white;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }
        
        .stat-label {
            color: rgba(255, 255, 255, 0.6);
            font-size: 15px;
            position: relative;
            z-index: 1;
        }
        
        /* Courses Section */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 28px;
            font-weight: 700;
            color: white;
        }
        
        .view-all {
            color: #667eea;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .view-all:hover {
            gap: 12px;
        }
        
        .courses-scroll {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .course-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            overflow: hidden;
            transition: all 0.4s;
            cursor: pointer;
        }
        
        .course-card:hover {
            transform: translateY(-10px);
            border-color: rgba(102, 126, 234, 0.4);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.3);
        }
        
        .course-image {
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 70px;
            position: relative;
            overflow: hidden;
        }
        
        .course-image::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, transparent 70%);
            animation: float 10s infinite;
        }
        
        .course-image.orange {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .course-image.blue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .course-image.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        
        .course-content {
            padding: 25px;
        }
        
        .course-top {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .course-badge {
            background: rgba(102, 126, 234, 0.2);
            color: #667eea;
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .course-title {
            font-size: 20px;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
        }
        
        .course-desc {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .course-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .course-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .progress-circle {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: conic-gradient(#667eea 0deg 252deg, rgba(255, 255, 255, 0.1) 252deg 360deg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: white;
        }
        
        /* Right Sidebar */
        .right-sidebar {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        
        .widget {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 25px;
        }
        
        .widget-title {
            font-size: 18px;
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
        }
        
        .activity-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            margin-bottom: 12px;
            transition: all 0.3s;
        }
        
        .activity-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        
        .activity-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            color: white;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .activity-time {
            color: rgba(255, 255, 255, 0.4);
            font-size: 12px;
        }
        
        .quick-action {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .quick-action:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateY(-3px);
        }
        
        .quick-action-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .quick-action-label {
            color: white;
            font-weight: 600;
            font-size: 14px;
        }
        
        /* Responsive */
        @media (max-width: 1400px) {
            .main-layout {
                grid-template-columns: 1fr;
            }
            
            .sidebar, .right-sidebar {
                display: none;
            }
            
            .courses-scroll {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .search-container {
                display: none;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .hero-section {
                padding: 30px;
            }
            
            .hero-title {
                font-size: 28px;
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
    
    <div class="dashboard">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <div class="logo">🎓</div>
                <div class="logo-text">EduHub</div>
            </div>
            
            <div class="search-container">
                <input type="text" class="search-box" placeholder="Search anything... Try 'AI Quiz' or 'Data Science'">
                <div class="search-icon">🔍</div>
            </div>
            
            <div class="header-actions">
                <div class="icon-btn">⚡</div>
                <div class="icon-btn">
                    🔔
                    <div class="notification-dot"></div>
                </div>
                <div class="icon-btn">💬</div>
                <div class="user-profile">JD</div>
            </div>
        </div>
        
        <!-- Main Layout -->
        <div class="main-layout">
            <!-- Left Sidebar -->
            <div class="sidebar">
                <div class="nav-group">
                    <div class="nav-title">Main</div>
                    <div class="nav-item active">
                        <span>🏠</span>
                        <span>Dashboard</span>
                    </div>
                    <div class="nav-item">
                        <span>📚</span>
                        <span>My Courses</span>
                    </div>
                    <div class="nav-item">
                        <span>🔍</span>
                        <span>Explore</span>
                    </div>
                </div>
                
                <div class="nav-group">
                    <div class="nav-title">Learning</div>
                    <div class="nav-item">
                        <span>🤖</span>
                        <span>AI Tutor</span>
                    </div>
                    <div class="nav-item">
                        <span>🎯</span>
                        <span>Quizzes</span>
                    </div>
                    <div class="nav-item">
                        <span>👥</span>
                        <span>Study Groups</span>
                    </div>
                    <div class="nav-item">
                        <span>📊</span>
                        <span>Progress</span>
                    </div>
                </div>
                
                <div class="nav-group">
                    <div class="nav-title">Other</div>
                    <div class="nav-item">
                        <span>💬</span>
                        <span>Messages</span>
                    </div>
                    <div class="nav-item">
                        <span>⚙️</span>
                        <span>Settings</span>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="main-content">
                <!-- Hero Section -->
                <div class="hero-section">
                    <div class="hero-content">
                        <div class="hero-greeting">Good morning, John! ☀️</div>
                        <h1 class="hero-title">Ready to continue learning?</h1>
                        <p class="hero-description">You're making great progress! Keep up the momentum and unlock new achievements.</p>
                        <div class="hero-stats">
                            <div class="hero-stat">
                                <div class="hero-stat-value">12</div>
                                <div class="hero-stat-label">Active Courses</div>
                            </div>
                            <div class="hero-stat">
                                <div class="hero-stat-value">89%</div>
                                <div class="hero-stat-label">Avg Score</div>
                            </div>
                            <div class="hero-stat">
                                <div class="hero-stat-value">42h</div>
                                <div class="hero-stat-label">Study Time</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">📄</div>
                            <div class="stat-trend">↑ 23%</div>
                        </div>
                        <div class="stat-value">156</div>
                        <div class="stat-label">Materials Accessed</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">✅</div>
                            <div class="stat-trend">↑ 12%</div>
                        </div>
                        <div class="stat-value">24</div>
                        <div class="stat-label">Quizzes Completed</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">🏆</div>
                            <div class="stat-trend">↑ 8</div>
                        </div>
                        <div class="stat-value">18</div>
                        <div class="stat-label">Achievements</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">⭐</div>
                            <div class="stat-trend">↑ 0.3</div>
                        </div>
                        <div class="stat-value">4.8</div>
                        <div class="stat-label">Average Rating</div>
                    </div>
                </div>
                
                <!-- Courses -->
                <div class="section-header">
                    <h2 class="section-title">Continue Learning</h2>
                    <div class="view-all">View All →</div>
                </div>
                
                <div class="courses-scroll">
                    <div class="course-card">
                        <div class="course-image blue">
                            🚀
                        </div>
                        <div class="course-content">
                            <div class="course-top">
                                <div class="course-badge">In Progress</div>
                            </div>
                            <h3 class="course-title">Machine Learning Basics</h3>
                            <p class="course-desc">Dive into AI, neural networks, and predictive models</p>
                            <div class="course-footer">
                                <div class="course-meta">
                                    <span>📄 32 lessons</span>
                                    <span>⏱️ 20h left</span>
                                </div>
                                <div class="progress-circle">30%</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="course-card">
                        <div class="course-image green">
                            📊
                        </div>
                        <div class="course-content">
                            <div class="course-top">
                                <div class="course-badge">New</div>
                            </div>
                            <h3 class="course-title">Data Science Analytics</h3>
                            <p class="course-desc">Analyze data, create visualizations, and tell stories</p>
                            <div class="course-footer">
                                <div class="course-meta">
                                    <span>📄 28 lessons</span>
                                    <span>⏱️ 15h total</span>
                                </div>
                                <div class="progress-circle">0%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Sidebar -->
            <div class="right-sidebar">
                <!-- Quick Actions -->
                <div class="widget">
                    <h3 class="widget-title">Quick Actions</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="quick-action">
                            <div class="quick-action-icon">🤖</div>
                            <div class="quick-action-label">AI Tutor</div>
                        </div>
                        <div class="quick-action">
                            <div class="quick-action-icon">📝</div>
                            <div class="quick-action-label">Take Quiz</div>
                        </div>
                        <div class="quick-action">
                            <div class="quick-action-icon">👥</div>
                            <div class="quick-action-label">Join Group</div>
                        </div>
                        <div class="quick-action">
                            <div class="quick-action-icon">📚</div>
                            <div class="quick-action-label">Browse</div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="widget">
                    <h3 class="widget-title">Recent Activity</h3>
                    
                    <div class="activity-item">
                        <div class="activity-icon">📄</div>
                        <div class="activity-content">
                            <div class="activity-title">New Material</div>
                            <div class="activity-time">2 hours ago</div>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon">✅</div>
                        <div class="activity-content">
                            <div class="activity-title">Quiz Passed</div>
                            <div class="activity-time">5 hours ago</div>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon">💬</div>
                        <div class="activity-content">
                            <div class="activity-title">New Reply</div>
                            <div class="activity-time">1 day ago</div>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon">🏆</div>
                        <div class="activity-content">
                            <div class="activity-title">Achievement</div>
                            <div class="activity-time">2 days ago</div>
                        </div>
                    </div>
                </div>
                
                <!-- Upcoming Events -->
                <div class="widget">
                    <h3 class="widget-title">Upcoming</h3>
                    
                    <div class="activity-item">
                        <div class="activity-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">📅</div>
                        <div class="activity-content">
                            <div class="activity-title">Midterm Exam</div>
                            <div class="activity-time">Tomorrow, 10:00 AM</div>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">🎯</div>
                        <div class="activity-content">
                            <div class="activity-title">Assignment Due</div>
                            <div class="activity-time">In 3 days</div>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">👥</div>
                        <div class="activity-content">
                            <div class="activity-title">Study Session</div>
                            <div class="activity-time">Friday, 3:00 PM</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Add smooth hover effects
        document.querySelectorAll('.course-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
            });
        });
        
        // Search focus effect
        const searchBox = document.querySelector('.search-box');
        searchBox.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.3s';
        });
        
        searchBox.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
        
        // Nav item click effect
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });
        
        // Quick action click effect
        document.querySelectorAll('.quick-action').forEach(action => {
            action.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'translateY(-3px)';
                }, 100);
            });
        });
        
        // Simulate dynamic data
        setInterval(() => {
            const notificationDot = document.querySelector('.notification-dot');
            notificationDot.style.animation = 'none';
            setTimeout(() => {
                notificationDot.style.animation = 'ping 2s infinite';
            }, 10);
        }, 5000);
    </script>
</body>
</html><div class="course-image">
                            💻
                        </div>
                        <div class="course-content">
                            <div class="course-top">
                                <div class="course-badge">In Progress</div>
                            </div>
                            <h3 class="course-title">Computer Science Fundamentals</h3>
                            <p class="course-desc">Master algorithms, data structures, and programming concepts</p>
                            <div class="course-footer">
                                <div class="course-meta">
                                    <span>📄 24 lessons</span>
                                    <span>⏱️ 12h left</span>
                                </div>
                                <div class="progress-circle">70%</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="course-card">
                        <div class="course-image orange">
                            🎨
                        </div>
                        <div class="course-content">
                            <div class="course-top">
                                <div class="course-badge">In Progress</div>
                            </div>
                            <h3 class="course-title">UI/UX Design Mastery</h3>
                            <p class="course-desc">Learn design thinking, prototyping, and user research</p>
                            <div class="course-footer">
                                <div class="course-meta">
                                    <span>📄 18 lessons</span>
                                    <span>⏱️ 8h left</span>
                                </div>
                                <div class="progress-circle">45%</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="course-card">
                       