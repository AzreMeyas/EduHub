<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Materials - EduHub</title>
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
        }
        
        .bg-gradient:nth-child(2) {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            top: 50%;
            right: -100px;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(100px, -100px) scale(1.1); }
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
        
        .back-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
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
        }
        
        .header-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        /* Course Header Card */
        .course-header-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 35px;
            padding: 40px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .course-header-card::before {
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
        
        .course-header-content {
            position: relative;
            z-index: 1;
        }
        
        .course-badge {
            display: inline-block;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .course-title {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 15px;
        }
        
        .course-meta {
            display: flex;
            gap: 30px;
            font-size: 15px;
            opacity: 0.9;
        }
        
        .course-stats {
            display: flex;
            gap: 40px;
            margin-top: 30px;
        }
        
        .course-stat {
            text-align: center;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 13px;
            opacity: 0.9;
        }
        
        /* Main Layout */
        .main-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            gap: 10px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 8px;
            margin-bottom: 25px;
        }
        
        .tab {
            flex: 1;
            padding: 14px 24px;
            border-radius: 18px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
        }
        
        /* Search & Filter Bar */
        .filter-bar {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 20px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .search-input {
            flex: 1;
            min-width: 250px;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 15px;
        }
        
        .search-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .filter-select {
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 15px;
            cursor: pointer;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        /* Material Cards */
        .materials-grid {
            display: grid;
            gap: 20px;
        }
        
        .material-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 30px;
            transition: all 0.4s;
            cursor: pointer;
        }
        
        .material-card:hover {
            transform: translateY(-5px);
            border-color: rgba(102, 126, 234, 0.4);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.3);
        }
        
        .material-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }
        
        .material-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }
        
        .material-info {
            flex: 1;
            margin-left: 20px;
        }
        
        .material-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .material-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .material-description {
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .material-tags {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .tag {
            padding: 6px 14px;
            background: rgba(102, 126, 234, 0.15);
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            color: #667eea;
        }
        
        .material-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .material-stats {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .material-actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            background: rgba(102, 126, 234, 0.2);
            border-color: rgba(102, 126, 234, 0.4);
        }
        
        .action-btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        /* Sidebar */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        
        .sidebar-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 25px;
        }
        
        .sidebar-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .announcement-item {
            padding: 16px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            margin-bottom: 12px;
        }
        
        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 10px;
        }
        
        .announcement-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .announcement-content {
            flex: 1;
            margin-left: 15px;
        }
        
        .announcement-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .announcement-text {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.5;
        }
        
        .announcement-time {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.4);
            margin-top: 8px;
        }
        
        .teacher-info {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
        }
        
        .teacher-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
        }
        
        .teacher-details h4 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .teacher-details p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .enroll-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            color: white;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
        }
        
        .enroll-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .enrolled-badge {
            width: 100%;
            padding: 16px;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 16px;
            color: #10b981;
            font-weight: 700;
            text-align: center;
            margin-top: 15px;
        }
        
        .progress-card {
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
        }
        
        .progress-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        
        .progress-bar {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 4px;
            transition: width 1s;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(10px);
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: rgba(26, 26, 36, 0.95);
            backdrop-filter: blur(40px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 40px;
            max-width: 700px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .modal-title {
            font-size: 28px;
            font-weight: 800;
        }
        
        .close-btn {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.08);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .close-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            transform: rotate(90deg);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 15px;
            font-family: 'Outfit', sans-serif;
        }
        
        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
            background: rgba(255, 255, 255, 0.08);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .file-upload {
            padding: 30px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-upload:hover {
            background: rgba(102, 126, 234, 0.1);
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .file-upload-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            color: white;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        @media (max-width: 1200px) {
            .main-layout {
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
            <a href="dashboard.html" class="back-btn">
                ← Back to Dashboard
            </a>
            <div class="header-actions">
                <button class="header-btn" onclick="showModal('uploadModal')">
                    📤 Upload Material
                </button>
                <button class="header-btn" onclick="showModal('announcementModal')">
                    📢 New Announcement
                </button>
            </div>
        </div>
        
        <!-- Course Header -->
        <div class="course-header-card">
            <div class="course-header-content">
                <div class="course-badge">🔥 Active Course</div>
                <h1 class="course-title">Advanced Computer Science</h1>
                <div class="course-meta">
                    <span>👨‍🏫 Dr. John Smith</span>
                    <span>📋 CS-401</span>
                    <span>🎓 120 Students Enrolled</span>
                </div>
                <div class="course-stats">
                    <div class="course-stat">
                        <div class="stat-value">24</div>
                        <div class="stat-label">Materials</div>
                    </div>
                    <div class="course-stat">
                        <div class="stat-value">4.8</div>
                        <div class="stat-label">Rating</div>
                    </div>
                    <div class="course-stat">
                        <div class="stat-value">12</div>
                        <div class="stat-label">Weeks</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="main-layout">
            <!-- Main Content -->
            <div class="main-content">
                <!-- Tabs -->
                <div class="tabs">
                    <div class="tab active">📚 Materials</div>
                    <div class="tab">💬 Discussions</div>
                    <div class="tab">📊 Grades</div>
                    <div class="tab">👥 Students</div>
                </div>
                
                <!-- Filter Bar -->
                <div class="filter-bar">
                    <input type="text" class="search-input" placeholder="🔍 Search materials...">
                    <select class="filter-select">
                        <option>All Categories</option>
                        <option>Lecture Notes</option>
                        <option>Assignments</option>
                        <option>Videos</option>
                        <option>References</option>
                    </select>
                    <select class="filter-select">
                        <option>Sort by: Latest</option>
                        <option>Sort by: Oldest</option>
                        <option>Sort by: Most Popular</option>
                        <option>Sort by: Highest Rated</option>
                    </select>
                </div>
                
                <!-- Materials Grid -->
                <div class="materials-grid">
                    <div class="material-card" onclick="window.location='material-detail.html'">
                        <div class="material-header">
                            <div class="material-icon">📄</div>
                            <div class="material-info">
                                <div class="material-title">Introduction to Algorithms</div>
                                <div class="material-meta">
                                    <span>📁 Lecture Notes</span>
                                    <span>📅 2 days ago</span>
                                    <span>👤 Dr. John Smith</span>
                                </div>
                            </div>
                        </div>
                        <p class="material-description">
                            Comprehensive guide covering sorting algorithms, time complexity analysis, and optimization techniques. Includes code examples and practice problems.
                        </p>
                        <div class="material-tags">
                            <span class="tag">Algorithms</span>
                            <span class="tag">Data Structures</span>
                            <span class="tag">Week 3</span>
                        </div>
                        <div class="material-footer">
                            <div class="material-stats">
                                <span>👁️ 156 views</span>
                                <span>⬇️ 89 downloads</span>
                                <span>⭐ 4.8 (24 ratings)</span>
                                <span>💬 12 comments</span>
                            </div>
                            <div class="material-actions">
                                <button class="action-btn">View</button>
                                <button class="action-btn-primary">Download</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="material-card">
                        <div class="material-header">
                            <div class="material-icon">🎥</div>
                            <div class="material-info">
                                <div class="material-title">Dynamic Programming Explained</div>
                                <div class="material-meta">
                                    <span>🎬 Video Lecture</span>
                                    <span>📅 5 days ago</span>
                                    <span>👤 Dr. John Smith</span>
                                </div>
                            </div>
                        </div>
                        <p class="material-description">
                            Step-by-step video tutorial on dynamic programming concepts with real-world examples and coding demonstrations.
                        </p>
                        <div class="material-tags">
                            <span class="tag">Dynamic Programming</span>
                            <span class="tag">Video</span>
                            <span class="tag">Week 4</span>
                        </div>
                        <div class="material-footer">
                            <div class="material-stats">
                                <span>👁️ 203 views</span>
                                <span>⏱️ 45 min</span>
                                <span>⭐ 4.9 (31 ratings)</span>
                                <span>💬 18 comments</span>
                            </div>
                            <div class="material-actions">
                                <button class="action-btn">View</button>
                                <button class="action-btn-primary">Watch</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="material-card">
                        <div class="material-icon">📝</div>
                        <div class="material-info">
                            <div class="material-title">Assignment 3: Graph Algorithms</div>
                            <div class="material-meta">
                                <span>✏️ Assignment</span>
                                <span>📅 1 week ago</span>
                                <span>⏰ Due in 3 days</span>
                            </div>
                        </div>
                        <p class="material-description">
                            Implement BFS, DFS, and Dijkstra's algorithm. Submit your code along with time complexity analysis.
                        </p>
                        <div class="material-tags">
                            <span class="tag">Assignment</span>
                            <span class="tag">Graphs</span>
                            <span class="tag">Due Soon</span>
                        </div>
                        <div class="material-footer">
                            <div class="material-stats">
                                <span>📊 78/120 submitted</span>
                                <span>💬 25 comments</span>
                            </div>
                            <div class="material-actions">
                                <button class="action-btn">View Details</button>
                                <button class="action-btn-primary">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Teacher Info -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">Instructor</h3>
                    <div class="teacher-info">
                        <div class="teacher-avatar">JS</div>
                        <div class="teacher-details">
                            <h4>Dr. John Smith</h4>
                            <p>Professor of Computer Science</p>
                        </div>
                    </div>
                    <div class="enrolled-badge">
                        ✅ Enrolled
                    </div>
                </div>
                
                <!-- Progress -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">Your Progress</h3>
                    <div class="progress-card">
                        <div class="progress-info">
                            <span style="font-weight: 600;">Course Completion</span>
                            <span style="font-weight: 700; color: #667eea;">75%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Announcements -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">📢 Announcements</h3>
                    
                    <div class="announcement-item">
                        <div class="announcement-header">
                            <div class="announcement-icon">📌</div>
                            <div class="announcement-content">
                                <div class="announcement-title">Midterm Exam Schedule</div>
                                <p class="announcement-text">
                                    The midterm exam will be held on Friday, Oct 15 at 10:00 AM. Please review chapters 1-5.
                                </p>
                                <div class="announcement-time">2 days ago</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="announcement-item">
                        <div class="announcement-header">
                            <div class="announcement-icon">📚</div>
                            <div class="announcement-content">
                                <div class="announcement-title">New Study Materials</div>
                                <p class="announcement-text">
                                    Additional practice problems for dynamic programming have been uploaded.
                                </p>
                                <div class="announcement-time">5 days ago</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="announcement-item">
                        <div class="announcement-header">
                            <div class="announcement-icon">⏰</div>
                            <div class="announcement-content">
                                <div class="announcement-title">Office Hours Update</div>
                                <p class="announcement-text">
                                    Office hours moved to Tuesday 2-4 PM this week.
                                </p>
                                <div class="announcement-time">1 week ago</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">Quick Actions</h3>
                    <button class="enroll-btn" onclick="window.location='ai-tutor.html'">
                        🤖 Ask AI Tutor
                    </button>
                    <button class="enroll-btn" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); margin-top: 10px;" onclick="window.location='quiz.html'">
                        🎯 Take Quiz
                    </button>
                    <button class="enroll-btn" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); margin-top: 10px;" onclick="window.location='study-groups.html'">
                        👥 Join Study Group
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Upload Material Modal -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">📤 Upload Material</h2>
                <button class="close-btn" onclick="closeModal('uploadModal')">×</button>
            </div>
            
            <form>
                <div class="form-group">
                    <label class="form-label">Material Title</label>
                    <input type="text" class="form-input" placeholder="e.g., Introduction to Algorithms" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select class="form-select" required>
                        <option value="">Select category</option>
                        <option>Lecture Notes</option>
                        <option>Assignment</option>
                        <option>Video Lecture</option>
                        <option>Reference Material</option>
                        <option>Quiz</option>
                        <option>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-textarea" placeholder="Describe the material content..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tags (comma-separated)</label>
                    <input type="text" class="form-input" placeholder="e.g., algorithms, week3, important">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Upload File</label>
                    <div class="file-upload" onclick="document.getElementById('fileInput').click()">
                        <div class="file-upload-icon">📁</div>
                        <p style="margin-bottom: 5px; font-weight: 600;">Click to upload or drag and drop</p>
                        <p style="font-size: 13px; color: rgba(255,255,255,0.5);">PDF, DOC, PPT, Video (Max 100MB)</p>
                    </div>
                    <input type="file" id="fileInput" style="display: none;">
                </div>
                
                <button type="submit" class="submit-btn">Upload Material</button>
            </form>
        </div>
    </div>
    
    <!-- Announcement Modal -->
    <div id="announcementModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">📢 New Announcement</h2>
                <button class="close-btn" onclick="closeModal('announcementModal')">×</button>
            </div>
            
            <form>
                <div class="form-group">
                    <label class="form-label">Announcement Title</label>
                    <input type="text" class="form-input" placeholder="e.g., Exam Schedule Update" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea class="form-textarea" placeholder="Write your announcement..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <select class="form-select" required>
                        <option value="">Select priority</option>
                        <option>🔴 High - Urgent</option>
                        <option>🟡 Medium - Important</option>
                        <option>🟢 Low - General Info</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" style="width: auto;">
                        <span class="form-label" style="margin: 0;">Send notification to all enrolled students</span>
                    </label>
                </div>
                
                <button type="submit" class="submit-btn">Post Announcement</button>
            </form>
        </div>
    </div>
    
    <script>
        function showModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
        
        // Animate progress bars on load
        window.addEventListener('load', function() {
            document.querySelectorAll('.progress-fill').forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 300);
            });
        });
        
        // Tab switching
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                // Here you would load different content based on selected tab
            });
        });
        
        // Search and filter functionality
        const searchInput = document.querySelector('.search-input');
        searchInput.addEventListener('input', function() {
            // Implement search logic here
            console.log('Searching for:', this.value);
        });
        
        // File upload drag and drop
        const fileUpload = document.querySelector('.file-upload');
        
        fileUpload.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUpload.style.background = 'rgba(102, 126, 234, 0.2)';
        });
        
        fileUpload.addEventListener('dragleave', () => {
            fileUpload.style.background = 'rgba(255, 255, 255, 0.05)';
        });
        
        fileUpload.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUpload.style.background = 'rgba(255, 255, 255, 0.05)';
            const files = e.dataTransfer.files;
            console.log('Files dropped:', files);
            // Handle file upload
        });
        
        // Material card animations
        document.querySelectorAll('.material-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 150);
        });
        
        // Announcement animations
        document.querySelectorAll('.announcement-item').forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            setTimeout(() => {
                item.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, 400 + (index * 100));
        });
    </script>
</body>
</html>