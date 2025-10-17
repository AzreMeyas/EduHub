<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EduHub</title>
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
        
        .admin-container {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .admin-sidebar {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding: 30px 20px;
        }
        
        .admin-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
            padding: 0 10px;
        }
        
        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .logo-text {
            font-size: 20px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .admin-badge {
            display: inline-block;
            padding: 4px 10px;
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            margin-left: 8px;
        }
        
        .nav-section {
            margin-bottom: 30px;
        }
        
        .nav-label {
            font-size: 11px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
            padding: 0 12px;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 6px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }
        
        .nav-item.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .nav-icon {
            font-size: 20px;
        }
        
        .nav-text {
            font-size: 14px;
            font-weight: 600;
        }
        
        /* Main Content */
        .admin-main {
            padding: 30px;
            overflow-y: auto;
        }
        
        .admin-header {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 25px 35px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-title h1 {
            font-size: 32px;
            margin-bottom: 8px;
        }
        
        .header-title p {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .header-actions {
            display: flex;
            gap: 12px;
        }
        
        .header-btn {
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .header-btn:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .header-btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 28px;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(102, 126, 234, 0.3);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            background: rgba(102, 126, 234, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-trend {
            padding: 6px 10px;
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .stat-trend.down {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 900;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .content-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 30px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 700;
        }
        
        .card-action {
            color: #667eea;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        
        /* Users Table */
        .users-table {
            width: 100%;
        }
        
        .table-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            margin-bottom: 12px;
            font-size: 13px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .table-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
            padding: 16px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            margin-bottom: 10px;
            align-items: center;
            transition: all 0.3s;
        }
        
        .table-row:hover {
            background: rgba(255, 255, 255, 0.08);
        }
        
        .user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }
        
        .user-info h4 {
            font-size: 14px;
            margin-bottom: 3px;
        }
        
        .user-info p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .role-badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .role-teacher {
            background: rgba(102, 126, 234, 0.15);
            color: #667eea;
        }
        
        .role-student {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
        }
        
        .role-admin {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .status-active {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
        }
        
        .status-inactive {
            background: rgba(156, 163, 175, 0.15);
            color: #9ca3af;
        }
        
        .action-btns {
            display: flex;
            gap: 8px;
        }
        
        .action-btn {
            width: 32px;
            height: 32px;
            background: rgba(255, 255, 255, 0.08);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .action-btn:hover {
            background: rgba(102, 126, 234, 0.2);
        }
        
        /* Activity Feed */
        .activity-item {
            display: flex;
            gap: 15px;
            padding: 16px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            margin-bottom: 12px;
        }
        
        .activity-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .activity-icon.green {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        }
        
        .activity-icon.red {
            background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
        }
        
        .activity-icon.blue {
            background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .activity-desc {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 5px;
        }
        
        .activity-time {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.4);
        }
        
        /* Charts Placeholder */
        .chart-placeholder {
            height: 300px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.4);
            font-size: 48px;
        }
        
        @media (max-width: 1400px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 1024px) {
            .admin-container {
                grid-template-columns: 1fr;
            }
            
            .admin-sidebar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="bg-gradient"></div>
        <div class="bg-gradient"></div>
    </div>
    
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="admin-logo">
                <div class="logo-icon">⚡</div>
                <div>
                    <div class="logo-text">EduHub</div>
                    <span class="admin-badge">ADMIN</span>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-label">Dashboard</div>
                <div class="nav-item active">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Overview</span>
                </div>
                <div class="nav-item">
                    <span class="nav-icon">📈</span>
                    <span class="nav-text">Analytics</span>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-label">Management</div>
                <div class="nav-item">
                    <span class="nav-icon">👥</span>
                    <span class="nav-text">Users</span>
                </div>
                <div class="nav-item">
                    <span class="nav-icon">📚</span>
                    <span class="nav-text">Courses</span>
                </div>
                <div class="nav-item">
                    <span class="nav-icon">📄</span>
                    <span class="nav-text">Materials</span>
                </div>
                <div class="nav-item">
                    <span class="nav-icon">💬</span>
                    <span class="nav-text">Discussions</span>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-label">System</div>
                <div class="nav-item">
                    <span class="nav-icon">⚙️</span>
                    <span class="nav-text">Settings</span>
                </div>
                <div class="nav-item">
                    <span class="nav-icon">🔐</span>
                    <span class="nav-text">Security</span>
                </div>
                <div class="nav-item">
                    <span class="nav-icon">📧</span>
                    <span class="nav-text">Notifications</span>
                </div>
                <div class="nav-item">
                    <span class="nav-icon">📝</span>
                    <span class="nav-text">Logs</span>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <div class="header-title">
                    <h1>Admin Dashboard</h1>
                    <p>Welcome back, Administrator</p>
                </div>
                <div class="header-actions">
                    <button class="header-btn">Export Report</button>
                    <button class="header-btn header-btn-primary">+ Add User</button>
                </div>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">👥</div>
                        <div class="stat-trend">↑ 12%</div>
                    </div>
                    <div class="stat-value">2,547</div>
                    <div class="stat-label">Total Users</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">📚</div>
                        <div class="stat-trend">↑ 8%</div>
                    </div>
                    <div class="stat-value">342</div>
                    <div class="stat-label">Active Courses</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">📄</div>
                        <div class="stat-trend">↑ 23%</div>
                    </div>
                    <div class="stat-value">5,832</div>
                    <div class="stat-label">Total Materials</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">⭐</div>
                        <div class="stat-trend down">↓ 2%</div>
                    </div>
                    <div class="stat-value">4.8</div>
                    <div class="stat-label">Avg Rating</div>
                </div>
            </div>
            
            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Users Management -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Users</h3>
                        <span class="card-action">View All →</span>
                    </div>
                    
                    <div class="users-table">
                        <div class="table-header">
                            <div>User</div>
                            <div>Role</div>
                            <div>Status</div>
                            <div>Joined</div>
                            <div>Actions</div>
                        </div>
                        
                        <div class="table-row">
                            <div class="user-cell">
                                <div class="user-avatar">JS</div>
                                <div class="user-info">
                                    <h4>John Smith</h4>
                                    <p>john.smith@email.com</p>
                                </div>
                            </div>
                            <div>
                                <span class="role-badge role-teacher">Teacher</span>
                            </div>
                            <div>
                                <span class="status-badge status-active">Active</span>
                            </div>
                            <div style="font-size: 13px; color: rgba(255,255,255,0.6);">2 days ago</div>
                            <div class="action-btns">
                                <button class="action-btn">✏️</button>
                                <button class="action-btn">🗑️</button>
                            </div>
                        </div>
                        
                        <div class="table-row">
                            <div class="user-cell">
                                <div class="user-avatar" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">SA</div>
                                <div class="user-info">
                                    <h4>Sarah Anderson</h4>
                                    <p>sarah.a@email.com</p>
                                </div>
                            </div>
                            <div>
                                <span class="role-badge role-student">Student</span>
                            </div>
                            <div>
                                <span class="status-badge status-active">Active</span>
                            </div>
                            <div style="font-size: 13px; color: rgba(255,255,255,0.6);">5 days ago</div>
                            <div class="action-btns">
                                <button class="action-btn">✏️</button>
                                <button class="action-btn">🗑️</button>
                            </div>
                        </div>
                        
                        <div class="table-row">
                            <div class="user-cell">
                                <div class="user-avatar" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">MJ</div>
                                <div class="user-info">
                                    <h4>Mike Johnson</h4>
                                    <p>mike.j@email.com</p>
                                </div>
                            </div>
                            <div>
                                <span class="role-badge role-teacher">Teacher</span>
                            </div>
                            <div>
                                <span class="status-badge status-active">Active</span>
                            </div>
                            <div style="font-size: 13px; color: rgba(255,255,255,0.6);">1 week ago</div>
                            <div class="action-btns">
                                <button class="action-btn">✏️</button>
                                <button class="action-btn">🗑️</button>
                            </div>
                        </div>
                        
                        <div class="table-row">
                            <div class="user-cell">
                                <div class="user-avatar" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">EM</div>
                                <div class="user-info">
                                    <h4>Emma Martinez</h4>
                                    <p>emma.m@email.com</p>
                                </div>
                            </div>
                            <div>
                                <span class="role-badge role-student">Student</span>
                            </div>
                            <div>
                                <span class="status-badge status-inactive">Inactive</span>
                            </div>
                            <div style="font-size: 13px; color: rgba(255,255,255,0.6);">2 weeks ago</div>
                            <div class="action-btns">
                                <button class="action-btn">✏️</button>
                                <button class="action-btn">🗑️</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Activity</h3>
                        <span class="card-action">View All →</span>
                    </div>
                    
                    <div class="activity-feed">
                        <div class="activity-item">
                            <div class="activity-icon green">✓</div>
                            <div class="activity-content">
                                <div class="activity-title">New User Registered</div>
                                <div class="activity-desc">Sarah Anderson joined as a student</div>
                                <div class="activity-time">2 minutes ago</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon">📚</div>
                            <div class="activity-content">
                                <div class="activity-title">Course Created</div>
                                <div class="activity-desc">Dr. Smith created "Advanced Algorithms"</div>
                                <div class="activity-time">1 hour ago</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon blue">📄</div>
                            <div class="activity-content">
                                <div class="activity-title">Material Uploaded</div>
                                <div class="activity-desc">New lecture notes added to CS-401</div>
                                <div class="activity-time">3 hours ago</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon red">⚠️</div>
                            <div class="activity-content">
                                <div class="activity-title">Report Submitted</div>
                                <div class="activity-desc">User reported inappropriate content</div>
                                <div class="activity-time">5 hours ago</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon green">💬</div>
                            <div class="activity-content">
                                <div class="activity-title">Discussion Started</div>
                                <div class="activity-desc">New thread in Data Science course</div>
                                <div class="activity-time">1 day ago</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Analytics Charts -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">User Growth Analytics</h3>
                    <span class="card-action">Export →</span>
                </div>
                <div class="chart-placeholder">
                    📊 Chart Visualization
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Animate stats on load
        window.addEventListener('load', () => {
            document.querySelectorAll('.stat-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Animate table rows
            document.querySelectorAll('.table-row').forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                    row.style.opacity = '1';
                    row.style.transform = 'translateX(0)';
                }, 400 + (index * 100));
            });
        });
        
        // Nav item click
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>
