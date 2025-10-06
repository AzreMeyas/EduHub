<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Detail - EduHub</title>
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
        
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }
        
        .material-content {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 40px;
            margin-bottom: 30px;
        }
        
        .material-header {
            display: flex;
            gap: 25px;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .material-icon-large {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            flex-shrink: 0;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .material-info {
            flex: 1;
        }
        
        .material-title-large {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 15px;
        }
        
        .material-meta-large {
            display: flex;
            gap: 25px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 20px;
        }
        
        .material-tags-large {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .tag-large {
            padding: 8px 16px;
            background: rgba(102, 126, 234, 0.15);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            color: #667eea;
        }
        
        .material-description {
            font-size: 16px;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 30px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
        }
        
        .btn-primary {
            padding: 16px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            color: white;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            padding: 16px 32px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        /* Discussions Section */
        .discussions-section {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 40px;
        }
        
        .section-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .comment-form {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .comment-textarea {
            width: 100%;
            min-height: 120px;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 15px;
            font-family: 'Outfit', sans-serif;
            resize: vertical;
            margin-bottom: 15px;
        }
        
        .comment-textarea:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
            background: rgba(255, 255, 255, 0.08);
        }
        
        .comment-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .comment-item:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(102, 126, 234, 0.3);
        }
        
        .comment-header {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .comment-avatar {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .comment-info {
            flex: 1;
        }
        
        .comment-author {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .comment-time {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .comment-text {
            font-size: 15px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 15px;
        }
        
        .comment-actions {
            display: flex;
            gap: 20px;
            font-size: 14px;
        }
        
        .comment-action {
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .comment-action:hover {
            color: #667eea;
        }
        
        .replies {
            margin-left: 60px;
            margin-top: 15px;
            padding-left: 20px;
            border-left: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Sidebar */
        .sidebar-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .sidebar-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        /* Rating Section */
        .rating-overview {
            text-align: center;
            padding: 25px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            margin-bottom: 25px;
        }
        
        .rating-score {
            font-size: 56px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .rating-stars {
            font-size: 24px;
            color: #ffa500;
            margin-bottom: 10px;
        }
        
        .rating-count {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .rating-bars {
            margin-top: 25px;
        }
        
        .rating-bar-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        
        .rating-label {
            font-size: 13px;
            width: 60px;
        }
        
        .rating-bar-bg {
            flex: 1;
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .rating-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #ffa500, #ff6b6b);
            border-radius: 4px;
        }
        
        .rating-bar-count {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            width: 30px;
            text-align: right;
        }
        
        .rate-button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ffa500 0%, #ff6b6b 100%);
            border: none;
            border-radius: 16px;
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .rate-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 165, 0, 0.4);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .stat-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        /* Related Materials */
        .related-item {
            display: flex;
            gap: 15px;
            padding: 16px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .related-item:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(5px);
        }
        
        .related-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        
        .related-info h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .related-info p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
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
        <div class="header">
            <a href="course-view.html" class="back-btn">
                ← Back to Course
            </a>
        </div>
        
        <div class="main-grid">
            <!-- Main Content -->
            <div>
                <!-- Material Content -->
                <div class="material-content">
                    <div class="material-header">
                        <div class="material-icon-large">📄</div>
                        <div class="material-info">
                            <h1 class="material-title-large">Introduction to Algorithms</h1>
                            <div class="material-meta-large">
                                <span>👤 Dr. John Smith</span>
                                <span>📅 2 days ago</span>
                                <span>📁 Lecture Notes</span>
                                <span>📄 PDF, 2.5 MB</span>
                            </div>
                            <div class="material-tags-large">
                                <span class="tag-large">Algorithms</span>
                                <span class="tag-large">Data Structures</span>
                                <span class="tag-large">Week 3</span>
                                <span class="tag-large">Important</span>
                            </div>
                        </div>
                    </div>
                    
                    <p class="material-description">
                        This comprehensive guide covers fundamental sorting algorithms including Bubble Sort, Quick Sort, Merge Sort, and Heap Sort. Each algorithm is explained with detailed time complexity analysis, space complexity considerations, and practical code implementations. The document includes visual diagrams, pseudocode, and real-world applications to help you understand when to use each algorithm effectively.
                        <br><br>
                        Topics covered:
                        <br>• Sorting algorithm fundamentals
                        <br>• Time and space complexity analysis
                        <br>• Comparative analysis of different algorithms
                        <br>• Best practices and optimization techniques
                        <br>• Practice problems with solutions
                    </p>
                    
                    <div class="action-buttons">
                        <button class="btn-primary">📥 Download Material</button>
                        <button class="btn-secondary">👁️ Preview</button>
                        <button class="btn-secondary">🔖 Bookmark</button>
                        <button class="btn-secondary">📤 Share</button>
                    </div>
                </div>
                
                <!-- Discussions Section -->
                <div class="discussions-section">
                    <h2 class="section-title">💬 Discussions (24)</h2>
                    
                    <div class="comment-form">
                        <textarea class="comment-textarea" placeholder="Share your thoughts, ask questions, or help others..."></textarea>
                        <button class="btn-primary">Post Comment</button>
                    </div>
                    
                    <div class="comment-item">
                        <div class="comment-header">
                            <div class="comment-avatar">SA</div>
                            <div class="comment-info">
                                <div class="comment-author">Sarah Anderson</div>
                                <div class="comment-time">2 hours ago</div>
                            </div>
                        </div>
                        <p class="comment-text">
                            This is an excellent resource! The visual diagrams really helped me understand the merge sort algorithm. Could someone explain when to use Quick Sort vs Merge Sort in practical scenarios?
                        </p>
                        <div class="comment-actions">
                            <span class="comment-action">👍 12 Likes</span>
                            <span class="comment-action">💬 Reply</span>
                        </div>
                        
                        <div class="replies">
                            <div class="comment-item">
                                <div class="comment-header">
                                    <div class="comment-avatar" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">MJ</div>
                                    <div class="comment-info">
                                        <div class="comment-author">Mike Johnson</div>
                                        <div class="comment-time">1 hour ago</div>
                                    </div>
                                </div>
                                <p class="comment-text">
                                    Great question! Quick Sort is generally faster for in-memory sorting and has better cache performance. Merge Sort is stable and guaranteed O(n log n) worst case, making it better for linked lists or when stability is required.
                                </p>
                                <div class="comment-actions">
                                    <span class="comment-action">👍 8 Likes</span>
                                    <span class="comment-action">💬 Reply</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="comment-item">
                        <div class="comment-header">
                            <div class="comment-avatar" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">DL</div>
                            <div class="comment-info">
                                <div class="comment-author">David Lee</div>
                                <div class="comment-time">5 hours ago</div>
                            </div>
                        </div>
                        <p class="comment-text">
                            The complexity analysis section is really thorough! I especially appreciate the comparison table. One suggestion - could we get some practice problems added at the end?
                        </p>
                        <div class="comment-actions">
                            <span class="comment-action">👍 15 Likes</span>
                            <span class="comment-action">💬 Reply</span>
                        </div>
                    </div>
                    
                    <div class="comment-item">
                        <div class="comment-header">
                            <div class="comment-avatar" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">EM</div>
                            <div class="comment-info">
                                <div class="comment-author">Emma Martinez</div>
                                <div class="comment-time">1 day ago</div>
                            </div>
                        </div>
                        <p class="comment-text">
                            Has anyone implemented these algorithms in Python? I'd love to see some code examples!
                        </p>
                        <div class="comment-actions">
                            <span class="comment-action">👍 6 Likes</span>
                            <span class="comment-action">💬 Reply</span>
                        </div>
                    </div>
                    
                    <button class="btn-secondary" style="width: 100%; margin-top: 20px;">
                        Load More Comments
                    </button>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div>
                <!-- Rating Card -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">⭐ Rating & Reviews</h3>
                    
                    <div class="rating-overview">
                        <div class="rating-score">4.8</div>
                        <div class="rating-stars">★★★★★</div>
                        <div class="rating-count">Based on 156 ratings</div>
                    </div>
                    
                    <div class="rating-bars">
                        <div class="rating-bar-item">
                            <span class="rating-label">5 ★</span>
                            <div class="rating-bar-bg">
                                <div class="rating-bar-fill" style="width: 85%"></div>
                            </div>
                            <span class="rating-bar-count">120</span>
                        </div>
                        <div class="rating-bar-item">
                            <span class="rating-label">4 ★</span>
                            <div class="rating-bar-bg">
                                <div class="rating-bar-fill" style="width: 10%"></div>
                            </div>
                            <span class="rating-bar-count">18</span>
                        </div>
                        <div class="rating-bar-item">
                            <span class="rating-label">3 ★</span>
                            <div class="rating-bar-bg">
                                <div class="rating-bar-fill" style="width: 3%"></div>
                            </div>
                            <span class="rating-bar-count">12</span>
                        </div>
                        <div class="rating-bar-item">
                            <span class="rating-label">2 ★</span>
                            <div class="rating-bar-bg">
                                <div class="rating-bar-fill" style="width: 2%"></div>
                            </div>
                            <span class="rating-bar-count">4</span>
                        </div>
                        <div class="rating-bar-item">
                            <span class="rating-label">1 ★</span>
                            <div class="rating-bar-bg">
                                <div class="rating-bar-fill" style="width: 1%"></div>
                            </div>
                            <span class="rating-bar-count">2</span>
                        </div>
                    </div>
                    
                    <button class="rate-button" style="margin-top: 20px;">Rate This Material</button>
                </div>
                
                <!-- Stats Card -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">📊 Statistics</h3>
                    
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value">1,234</div>
                            <div class="stat-label">Views</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">856</div>
                            <div class="stat-label">Downloads</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">156</div>
                            <div class="stat-label">Ratings</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">24</div>
                            <div class="stat-label">Comments</div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">⚡ Quick Actions</h3>
                    
                    <button class="btn-primary" style="width: 100%; margin-bottom: 10px;" onclick="window.location='ai-tutor.html'">
                        🤖 Ask AI About This
                    </button>
                    <button class="btn-primary" style="width: 100%; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);" onclick="window.location='quiz.html'">
                        🎯 Take Quiz
                    </button>
                </div>
                
                <!-- Related Materials -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">🔗 Related Materials</h3>
                    
                    <div class="related-item">
                        <div class="related-icon">📄</div>
                        <div class="related-info">
                            <h4>Advanced Sorting Techniques</h4>
                            <p>Week 4 • Lecture Notes</p>
                        </div>
                    </div>
                    
                    <div class="related-item">
                        <div class="related-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">🎥</div>
                        <div class="related-info">
                            <h4>Algorithm Visualization</h4>
                            <p>Week 3 • Video • 28 min</p>
                        </div>
                    </div>
                    
                    <div class="related-item">
                        <div class="related-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">📝</div>
                        <div class="related-info">
                            <h4>Practice Problems Set 3</h4>
                            <p>Week 3 • Assignment</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Animate elements on load
        window.addEventListener('load', function() {
            // Animate rating bars
            document.querySelectorAll('.rating-bar-fill').forEach((bar, index) => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.transition = 'width 1s ease-out';
                    bar.style.width = width;
                }, 300 + (index * 100));
            });
            
            // Animate stats
            document.querySelectorAll('.stat-item').forEach((stat, index) => {
                stat.style.opacity = '0';
                stat.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    stat.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    stat.style.opacity = '1';
                    stat.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Animate comments
            document.querySelectorAll('.comment-item').forEach((comment, index) => {
                comment.style.opacity = '0';
                comment.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    comment.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                    comment.style.opacity = '1';
                    comment.style.transform = 'translateX(0)';
                }, 200 + (index * 100));
            });
        });
        
        // Comment actions
        document.querySelectorAll('.comment-action').forEach(action => {
            action.addEventListener('click', function(e) {
                e.stopPropagation();
                if (this.textContent.includes('Likes')) {
                    const likes = parseInt(this.textContent.match(/\d+/)[0]);
                    this.textContent = `👍 ${likes + 1} Likes`;
                    this.style.color = '#667eea';
                }
            });
        });
        
        // Rate button
        document.querySelector('.rate-button').addEventListener('click', function() {
            const rating = prompt('Rate this material (1-5 stars):');
            if (rating && rating >= 1 && rating <= 5) {
                alert(`Thank you for rating this material ${rating} stars!`);
                // Here you would send the rating to the backend
            }
        });
        
        // Auto-expand textarea
        const textarea = document.querySelector('.comment-textarea');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Related items click
        document.querySelectorAll('.related-item').forEach(item => {
            item.addEventListener('click', function() {
                this.style.transform = 'translateX(10px)';
                setTimeout(() => {
                    this.style.transform = 'translateX(5px)';
                }, 100);
            });
        });
    </script>
</body>
</html>