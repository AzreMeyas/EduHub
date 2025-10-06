<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Groups - EduHub</title>
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
            max-width: 1400px;
            margin: 0 auto;
        }
        
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
        
        .header-title h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .header-title p {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .create-btn {
            padding: 14px 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .create-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .tab {
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
        }
        
        .groups-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .group-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 30px;
            transition: all 0.4s;
            cursor: pointer;
        }
        
        .group-card:hover {
            transform: translateY(-8px);
            border-color: rgba(102, 126, 234, 0.4);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.3);
        }
        
        .group-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }
        
        .group-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }
        
        .group-status {
            padding: 6px 14px;
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .group-status.full {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }
        
        .group-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .group-course {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 15px;
        }
        
        .group-description {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .group-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 20px;
        }
        
        .group-members {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .member-avatars {
            display: flex;
            margin-left: -5px;
        }
        
        .member-avatar {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: 2px solid #0f0f1e;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            margin-left: -10px;
        }
        
        .member-avatar:first-child {
            margin-left: 0;
        }
        
        .member-count {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .group-actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .btn-join {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        
        .btn-join:hover {
            transform: translateY(-2px);
        }
        
        .btn-view {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .btn-view:hover {
            background: rgba(255, 255, 255, 0.15);
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
            backdrop-filter: blur(10px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
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
            max-width: 600px;
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
        
        @media (max-width: 768px) {
            .groups-grid {
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
        <div class="header">
            <div class="header-title">
                <h1>👥 Study Groups</h1>
                <p>Collaborate and learn together with peers</p>
            </div>
            <button class="create-btn" onclick="showModal()">+ Create Group</button>
        </div>
        
        <div class="tabs">
            <div class="tab active">All Groups</div>
            <div class="tab">My Groups</div>
            <div class="tab">Recommended</div>
        </div>
        
        <div class="groups-grid">
            <div class="group-card">
                <div class="group-header">
                    <div class="group-icon">💻</div>
                    <div class="group-status">Active</div>
                </div>
                
                <h3 class="group-title">Algorithm Study Squad</h3>
                <p class="group-course">📚 Computer Science - CS401</p>
                <p class="group-description">
                    Focused on mastering sorting algorithms and data structures. We meet twice a week to discuss problems and share solutions.
                </p>
                
                <div class="group-meta">
                    <span>👥 8/12 Members</span>
                    <span>📅 Mon, Thu 7PM</span>
                </div>
                
                <div class="group-members">
                    <div class="member-avatars">
                        <div class="member-avatar">JD</div>
                        <div class="member-avatar" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">SA</div>
                        <div class="member-avatar" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">MK</div>
                        <div class="member-avatar" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">RL</div>
                    </div>
                    <span class="member-count">+4 more</span>
                </div>
                
                <div class="group-actions">
                    <button class="action-btn btn-join">Join Group</button>
                    <button class="action-btn btn-view">View Details</button>
                </div>
            </div>
            
            <div class="group-card">
                <div class="group-header">
                    <div class="group-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">📊</div>
                    <div class="group-status">Active</div>
                </div>
                
                <h3 class="group-title">Data Science Explorers</h3>
                <p class="group-course">📚 Data Science Analytics - DS201</p>
                <p class="group-description">
                    Working on machine learning projects and data visualization. Perfect for beginners and intermediate learners.
                </p>
                
                <div class="group-meta">
                    <span>👥 10/15 Members</span>
                    <span>📅 Tue, Fri 6PM</span>
                </div>
                
                <div class="group-members">
                    <div class="member-avatars">
                        <div class="member-avatar">EM</div>
                        <div class="member-avatar" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">DL</div>
                        <div class="member-avatar" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">TC</div>
                    </div>
                    <span class="member-count">+7 more</span>
                </div>
                
                <div class="group-actions">
                    <button class="action-btn btn-join">Join Group</button>
                    <button class="action-btn btn-view">View Details</button>
                </div>
            </div>
            
            <div class="group-card">
                <div class="group-header">
                    <div class="group-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">🎨</div>
                    <div class="group-status">Active</div>
                </div>
                
                <h3 class="group-title">UI/UX Design Club</h3>
                <p class="group-course">📚 Digital Design Fundamentals - DES301</p>
                <p class="group-description">
                    Collaborative design reviews, portfolio feedback, and weekly design challenges. All skill levels welcome!
                </p>
                
                <div class="group-meta">
                    <span>👥 6/10 Members</span>
                    <span>📅 Wed 5PM</span>
                </div>
                
                <div class="group-members">
                    <div class="member-avatars">
                        <div class="member-avatar">NK</div>
                        <div class="member-avatar" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">PP</div>
                    </div>
                    <span class="member-count">+4 more</span>
                </div>
                
                <div class="group-actions">
                    <button class="action-btn btn-join">Join Group</button>
                    <button class="action-btn btn-view">View Details</button>
                </div>
            </div>
            
            <div class="group-card">
                <div class="group-header">
                    <div class="group-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">🚀</div>
                    <div class="group-status full">Full</div>
                </div>
                
                <h3 class="group-title">Machine Learning Masters</h3>
                <p class="group-course">📚 Machine Learning & AI - ML401</p>
                <p class="group-description">
                    Advanced ML topics including neural networks, deep learning, and AI ethics. Prerequisites: Linear algebra & Python.
                </p>
                
                <div class="group-meta">
                    <span>👥 12/12 Members</span>
                    <span>📅 Mon, Wed 8PM</span>
                </div>
                
                <div class="group-members">
                    <div class="member-avatars">
                        <div class="member-avatar">AB</div>
                        <div class="member-avatar" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">CD</div>
                        <div class="member-avatar" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">EF</div>
                    </div>
                    <span class="member-count">+9 more</span>
                </div>
                
                <div class="group-actions">
                    <button class="action-btn btn-view" style="flex: 1;">View Details</button>
                </div>
            </div>
            
            <div class="group-card">
                <div class="group-header">
                    <div class="group-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);">🌐</div>
                    <div class="group-status">Active</div>
                </div>
                
                <h3 class="group-title">Web Development Warriors</h3>
                <p class="group-course">📚 Full Stack Development - WEB301</p>
                <p class="group-description">
                    Building real projects together! From frontend frameworks to backend APIs. Share code and learn best practices.
                </p>
                
                <div class="group-meta">
                    <span>👥 9/15 Members</span>
                    <span>📅 Sat 2PM</span>
                </div>
                
                <div class="group-members">
                    <div class="member-avatars">
                        <div class="member-avatar">GH</div>
                        <div class="member-avatar" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">IJ</div>
                        <div class="member-avatar" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">KL</div>
                    </div>
                    <span class="member-count">+6 more</span>
                </div>
                
                <div class="group-actions">
                    <button class="action-btn btn-join">Join Group</button>
                    <button class="action-btn btn-view">View Details</button>
                </div>
            </div>
            
            <div class="group-card">
                <div class="group-header">
                    <div class="group-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #d946ef 100%);">📱</div>
                    <div class="group-status">Active</div>
                </div>
                
                <h3 class="group-title">Mobile App Dev Circle</h3>
                <p class="group-course">📚 Mobile Application Development - MOB201</p>
                <p class="group-description">
                    iOS and Android development study group. Working on cross-platform apps using React Native and Flutter.
                </p>
                
                <div class="group-meta">
                    <span>👥 5/10 Members</span>
                    <span>📅 Thu 7PM</span>
                </div>
                
                <div class="group-members">
                    <div class="member-avatars">
                        <div class="member-avatar">MN</div>
                        <div class="member-avatar" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">OP</div>
                    </div>
                    <span class="member-count">+3 more</span>
                </div>
                
                <div class="group-actions">
                    <button class="action-btn btn-join">Join Group</button>
                    <button class="action-btn btn-view">View Details</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Create Group Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Create Study Group</h2>
                <button class="close-btn" onclick="closeModal()">×</button>
            </div>
            
            <form>
                <div class="form-group">
                    <label class="form-label">Group Name</label>
                    <input type="text" class="form-input" placeholder="e.g., Algorithm Study Squad" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Course</label>
                    <select class="form-select" required>
                        <option value="">Select a course</option>
                        <option>Computer Science - CS401</option>
                        <option>Data Science Analytics - DS201</option>
                        <option>Digital Design - DES301</option>
                        <option>Machine Learning - ML401</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-textarea" placeholder="What will your group focus on?" required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Maximum Members</label>
                    <input type="number" class="form-input" value="10" min="2" max="50" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Meeting Schedule</label>
                    <input type="text" class="form-input" placeholder="e.g., Mon, Wed 7PM" required>
                </div>
                
                <button type="submit" class="submit-btn">Create Study Group</button>
            </form>
        </div>
    </div>
    
    <script>
        function showModal() {
            document.getElementById('createModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('createModal').classList.remove('active');
        }
        
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal();
            }
        }
        
        // Animate cards on load
        window.addEventListener('load', () => {
            document.querySelectorAll('.group-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
        
        // Tab switching
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>