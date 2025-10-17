<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduHub - Creative Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
    </style>
    <link rel="stylesheet" href="dashboard.css">

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
                <!-- <div class="widget">
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
                </div> -->
                
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
            if (notificationDot) {
                notificationDot.style.animation = 'none';
                setTimeout(() => {
                    notificationDot.style.animation = 'ping 2s infinite';
                }, 10);
            }
        }, 5000);
    </script>
</body>
</html>