<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - EduHub</title>
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
            max-width: 1600px;
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
        
        .user-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
        }
        
        .user-info h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .user-info p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 15px;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
        }
        
        .header-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 15px;
        }
        
        .header-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .header-btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 30px;
            transition: all 0.4s;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
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
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(102, 126, 234, 0.4);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.3);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }
        
        .stat-icon.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        
        .stat-icon.orange {
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        }
        
        .stat-icon.pink {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-value {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 15px;
        }
        
        .stat-change {
            display: inline-block;
            padding: 6px 12px;
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .stat-change.negative {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .action-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .action-card:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }
        
        .action-icon {
            font-size: 42px;
            margin-bottom: 15px;
        }
        
        .action-label {
            font-weight: 600;
            font-size: 15px;
        }
        
        /* Main Content Grid */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }
        
        /* My Courses Section */
        .section-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 35px;
            margin-bottom: 30px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .view-all-btn {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .view-all-btn:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .courses-list {
            display: grid;
            gap: 20px;
        }
        
        .course-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 25px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .course-item:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(102, 126, 234, 0.3);
            transform: translateX(5px);
        }
        
        .course-item-header {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .course-thumbnail {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            flex-shrink: 0;
        }
        
        .course-info {
            flex: 1;
        }
        
        .course-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .course-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 8px;
        }
        
        .course-stats {
            display: flex;
            gap: 25px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .course-actions {
            display: flex;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .course-btn {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .course-btn:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
        }
        
        /* Recent Activity */
        .activity-list {
            display: grid;
            gap: 15px;
        }
        
        .activity-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            gap: 15px;
            transition: all 0.3s;
        }
        
        .activity-item:hover {
            background: rgba(255, 255, 255, 0.06);
        }
        
        .activity-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-text {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 8px;
        }
        
        .activity-time {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        /* Pending Reviews */
        .review-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .review-item:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(102, 126, 234, 0.3);
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }
        
        .review-student {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .student-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
        }
        
        .review-info h4 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 3px;
        }
        
        .review-info p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .review-badge {
            padding: 6px 12px;
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .review-actions {
            display: flex;
            gap: 10px;
            margin-top: 12px;
        }
        
        .review-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .review-btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .review-btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .review-btn:hover {
            transform: translateY(-2px);
        }
        
        /* Schedule */
        .schedule-item {
            background: rgba(255, 255, 255, 0.03);
            border-left: 4px solid #667eea;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 12px;
        }
        
        .schedule-time {
            font-size: 13px;
            color: #667eea;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .schedule-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .schedule-details {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        @media (max-width: 1200px) {
            .main-grid {
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
                <div class="user-avatar">JS</div>
                <div class="user-info">
                    <h1>Welcome back, Dr. Smith! 👋</h1>
                    <p>Teacher Dashboard • Computer Science Department</p>
                </div>
            </div>
            <div class="header-actions">
                <button class="header-btn">➕ Create Course</button>
                <button class="header-btn header-btn-secondary">⚙️ Settings</button>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value">6</div>
                        <div class="stat-label">Active Courses</div>
                    </div>
                    <div class="stat-icon">📚</div>
                </div>
                <div class="stat-change">+2 this semester</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value">847</div>
                        <div class="stat-label">Total Students</div>
                    </div>
                    <div class="stat-icon green">👥</div>
                </div>
                <div class="stat-change">+124 this month</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value">4.8</div>
                        <div class="stat-label">Avg. Rating</div>
                    </div>
                    <div class="stat-icon orange">⭐</div>
                </div>
                <div class="stat-change">+0.3 vs last sem</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value">23</div>
                        <div class="stat-label">Pending Reviews</div>
                    </div>
                    <div class="stat-icon pink">📝</div>
                </div>
                <div class="stat-change negative">Requires attention</div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="action-card" onclick="window.location='create-course.html'">
                <div class="action-icon">➕</div>
                <div class="action-label">Create Course</div>
            </div>
            
            <div class="action-card" onclick="window.location='upload-material.html'">
                <div class="action-icon">📤</div>
                <div class="action-label">Upload Material</div>
            </div>
            
            <div class="action-card" onclick="window.location='announcements.html'">
                <div class="action-icon">📢</div>
                <div class="action-label">Post Announcement</div>
            </div>
            
            <div class="action-card" onclick="window.location='create-quiz.html'">
                <div class="action-icon">🎯</div>
                <div class="action-label">Create Quiz</div>
            </div>
            
            <div class="action-card" onclick="window.location='grade-assignments.html'">
                <div class="action-icon">✏️</div>
                <div class="action-label">Grade Assignments</div>
            </div>
            
            <div class="action-card" onclick="window.location='analytics.html'">
                <div class="action-icon">📊</div>
                <div class="action-label">View Analytics</div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-grid">
            <!-- Left Column -->
            <div>
                <!-- My Courses -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">📚 My Courses</h2>
                        <a href="courses.html" class="view-all-btn">View All →</a>
                    </div>
                    
                    <div class="courses-list">
                        <div class="course-item" onclick="window.location='courseview.php'">
                            <div class="course-item-header">
                                <div class="course-thumbnail">💻</div>
                                <div class="course-info">
                                    <h3 class="course-name">Advanced Computer Science</h3>
                                    <div class="course-meta">
                                        <span>📋 CS-401</span>
                                        <span>🎓 120 Students</span>
                                        <span>⭐ 4.9 Rating</span>
                                    </div>
                                    <div class="course-stats">
                                        <span>📚 24 Materials</span>
                                        <span>📝 5 Assignments</span>
                                        <span>💬 48 Discussions</span>
                                    </div>
                                </div>
                            </div>
                            <div class="course-actions">
                                <button class="course-btn">Manage Course</button>
                                <button class="course-btn">View Materials</button>
                                <button class="course-btn">Analytics</button>
                            </div>
                        </div>
                        
                        <div class="course-item">
                            <div class="course-item-header">
                                <div class="course-thumbnail" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">🚀</div>
                                <div class="course-info">
                                    <h3 class="course-name">Machine Learning & AI</h3>
                                    <div class="course-meta">
                                        <span>📋 CS-502</span>
                                        <span>🎓 95 Students</span>
                                        <span>⭐ 4.8 Rating</span>
                                    </div>
                                    <div class="course-stats">
                                        <span>📚 32 Materials</span>
                                        <span>📝 7 Assignments</span>
                                        <span>💬 62 Discussions</span>
                                    </div>
                                </div>
                            </div>
                            <div class="course-actions">
                                <button class="course-btn">Manage Course</button>
                                <button class="course-btn">View Materials</button>
                                <button class="course-btn">Analytics</button>
                            </div>
                        </div>
                        
                        <div class="course-item">
                            <div class="course-item-header">
                                <div class="course-thumbnail" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">📊</div>
                                <div class="course-info">
                                    <h3 class="course-name">Data Structures & Algorithms</h3>
                                    <div class="course-meta">
                                        <span>📋 CS-301</span>
                                        <span>🎓 156 Students</span>
                                        <span>⭐ 4.7 Rating</span>
                                    </div>
                                    <div class="course-stats">
                                        <span>📚 28 Materials</span>
                                        <span>📝 6 Assignments</span>
                                        <span>💬 73 Discussions</span>
                                    </div>
                                </div>
                            </div>
                            <div class="course-actions">
                                <button class="course-btn">Manage Course</button>
                                <button class="course-btn">View Materials</button>
                                <button class="course-btn">Analytics</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">⚡ Recent Activity</h2>
                    </div>
                    
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">👤</div>
                            <div class="activity-content">
                                <div class="activity-text">
                                    <strong>Sarah Anderson</strong> submitted Assignment 3 in <strong>Advanced CS</strong>
                                </div>
                                <div class="activity-time">5 minutes ago</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">💬</div>
                            <div class="activity-content">
                                <div class="activity-text">
                                    <strong>Mike Johnson</strong> posted a new question in the discussion forum
                                </div>
                                <div class="activity-time">12 minutes ago</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">📚</div>
                            <div class="activity-content">
                                <div class="activity-text">
                                    You uploaded new material: <strong>Dynamic Programming Guide</strong>
                                </div>
                                <div class="activity-time">2 hours ago</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">⭐</div>
                            <div class="activity-content">
                                <div class="activity-text">
                                    <strong>Advanced CS</strong> received a 5-star rating from a student
                                </div>
                                <div class="activity-time">3 hours ago</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);">🎓</div>
                            <div class="activity-content">
                                <div class="activity-text">
                                    15 new students enrolled in <strong>Machine Learning & AI</strong>
                                </div>
                                <div class="activity-time">5 hours ago</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div>
                <!-- Pending Reviews -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">📝 Pending Reviews</h2>
                        <span style="font-size: 13px; color: rgba(255,255,255,0.6);">23 total</span>
                    </div>
                    
                    <div class="review-item">
                        <div class="review-header">
                            <div class="review-student">
                                <div class="student-avatar">SA</div>
                                <div class="review-info">
                                    <h4>Sarah Anderson</h4>
                                    <p>Assignment 3: Graph Algorithms</p>
                                </div>
                            </div>
                            <div class="review-badge">Due today</div>
                        </div>
                        <div class="review-actions">
                            <button class="review-btn review-btn-primary">Review Now</button>
                            <button class="review-btn review-btn-secondary">Later</button>
                        </div>
                    </div>
                    
                    <div class="review-item">
                        <div class="review-header">
                            <div class="review-student">
                                <div class="student-avatar" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">MJ</div>
                                <div class="review-info">
                                    <h4>Mike Johnson</h4>
                                    <p>Assignment 2: Dynamic Programming</p>
                                </div>
                            </div>
                            <div class="review-badge">2 days late</div>
                        </div>
                        <div class="review-actions">
                            <button class="review-btn review-btn-primary">Review Now</button>
                            <button class="review-btn review-btn-secondary">Later</button>
                        </div>
                    </div>
                    
                    <div class="review-item">
                        <div class="review-header">
                            <div class="review-student">
                                <div class="student-avatar" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">DL</div>
                                <div class="review-info">
                                    <h4>David Lee</h4>
                                    <p>Final Project Proposal</p>
                                </div>
                            </div>
                            <div class="review-badge" style="background: rgba(249, 115, 22, 0.15); color: #f97316;">Due tomorrow</div>
                        </div>
                        <div class="review-actions">
                            <button class="review-btn review-btn-primary">Review Now</button>
                            <button class="review-btn review-btn-secondary">Later</button>
                        </div>
                    </div>
                    
                    <button class="view-all-btn" style="width: 100%; margin-top: 15px;">
                        View All Pending Reviews →
                    </button>
                </div>
                
                <!-- Today's Schedule -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">📅 Today's Schedule</h2>
                    </div>
                    
                    <div class="schedule-item">
                        <div class="schedule-time">09:00 AM - 10:30 AM</div>
                        <div class="schedule-title">Advanced CS Lecture</div>
                        <div class="schedule-details">Room 301 • 120 students</div>
                    </div>
                    
                    <div class="schedule-item" style="border-left-color: #f093fb;">
                        <div class="schedule-time" style="color: #f093fb;">11:00 AM - 12:00 PM</div>
                        <div class="schedule-title">Office Hours</div>
                        <div class="schedule-details">Room 205 • Open to all students</div>
                    </div>
                    
                    <div class="schedule-item" style="border-left-color: #10b981;">
                        <div class="schedule-time" style="color: #10b981;">02:00 PM - 03:30 PM</div>
                        <div class="schedule-title">ML & AI Lab Session</div>
                        <div class="schedule-details">Lab 402 • 95 students</div>
                    </div>
                    
                    <div class="schedule-item" style="border-left-color: #f59e0b;">
                        <div class="schedule-time" style="color: #f59e0b;">04:00 PM - 05:00 PM</div>
                        <div class="schedule-title">Faculty Meeting</div>
                        <div class="schedule-details">Conference Room A</div>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">📊 This Week</h2>
                    </div>
                    
                    <div class="stats-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 0;">
                        <div class="stat-item" style="text-align: center; background: rgba(255,255,255,0.05); border-radius: 16px; padding: 20px;">
                            <div class="stat-value" style="font-size: 32px;">142</div>
                            <div class="stat-label">New Submissions</div>
                        </div>
                        <div class="stat-item" style="text-align: center; background: rgba(255,255,255,0.05); border-radius: 16px; padding: 20px;">
                            <div class="stat-value" style="font-size: 32px;">8</div>
                            <div class="stat-label">Materials Posted</div>
                        </div>
                        <div class="stat-item" style="text-align: center; background: rgba(255,255,255,0.05); border-radius: 16px; padding: 20px;">
                            <div class="stat-value" style="font-size: 32px;">256</div>
                            <div class="stat-label">Forum Replies</div>
                        </div>
                        <div class="stat-item" style="text-align: center; background: rgba(255,255,255,0.05); border-radius: 16px; padding: 20px;">
                            <div class="stat-value" style="font-size: 32px;">12</div>
                            <div class="stat-label">Announcements</div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">🔗 Quick Links</h2>
                    </div>
                    
                    <div style="display: grid; gap: 10px;">
                        <button class="course-btn" style="width: 100%; text-align: left; padding: 14px 20px;" onclick="window.location='browsecourse.php'">
                            📚 Browse All Courses
                        </button>
                        <button class="course-btn" style="width: 100%; text-align: left; padding: 14px 20px;" onclick="window.location='students.html'">
                            👥 Student Directory
                        </button>
                        <button class="course-btn" style="width: 100%; text-align: left; padding: 14px 20px;" onclick="window.location='reports.html'">
                            📈 Generate Reports
                        </button>
                        <button class="course-btn" style="width: 100%; text-align: left; padding: 14px 20px;" onclick="window.location='resources.html'">
                            💡 Teaching Resources
                        </button>
                        <button class="course-btn" style="width: 100%; text-align: left; padding: 14px 20px;" onclick="window.location='support.html'">
                            🆘 Help & Support
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Animate cards on load
        window.addEventListener('load', () => {
            // Animate stat cards
            document.querySelectorAll('.stat-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Animate action cards
            document.querySelectorAll('.action-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'scale(1)';
                }, 600 + (index * 50);
            });
            
            // Animate course items
            document.querySelectorAll('.course-item').forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, 800 + (index * 150);
            });
            
            // Animate activity items
            document.querySelectorAll('.activity-item').forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, 1000 + (index * 100);
            });
            
            // Animate review items
            document.querySelectorAll('.review-item').forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateX(20px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, 800 + (index * 100);
            });
            
            // Animate schedule items
            document.querySelectorAll('.schedule-item').forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateX(20px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, 1200 + (index * 100);
            });
        });
        
        // Add click animations to action cards
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 100);
            });
        });
        
        // Review button interactions
        document.querySelectorAll('.review-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (this.classList.contains('review-btn-primary')) {
                    alert('Opening assignment for review...');
                } else {
                    this.closest('.review-item').style.opacity = '0.5';
                    setTimeout(() => {
                        this.closest('.review-item').style.opacity = '1';
                    }, 300);
                }
            });
        });
        
        // Course button interactions
        document.querySelectorAll('.course-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                console.log('Button clicked:', this.textContent);
            });
        });
        
        // Notification system (simulated)
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 30px;
                right: 30px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px 30px;
                border-radius: 16px;
                box-shadow: 0 10px 40px rgba(102, 126, 234, 0.4);
                z-index: 1000;
                font-weight: 600;
                animation: slideIn 0.5s ease-out;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.5s ease-out';
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }
        
        // Simulate periodic updates
        setInterval(() => {
            const activities = [
                'New submission received',
                'Student posted a question',
                'Course material viewed',
                'New enrollment detected'
            ];
            const randomActivity = activities[Math.floor(Math.random() * activities.length)];
            // Uncomment to enable notifications
            // showNotification(randomActivity);
        }, 30000);
        
        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>