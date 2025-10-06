<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Courses - EduHub</title>
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
        
        .header {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 25px 40px;
            margin-bottom: 30px;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .header-title h1 {
            font-size: 36px;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea 0%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .header-title p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 16px;
        }
        
        .back-btn {
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
        }
        
        .search-filter-bar {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 15px;
        }
        
        .search-input {
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 15px;
        }
        
        .search-input:focus {
            outline: none;
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
        
        .categories {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        
        .category-chip {
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .category-chip:hover,
        .category-chip.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
        }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 30px;
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
            box-shadow: 0 25px 60px rgba(102, 126, 234, 0.3);
        }
        
        .course-thumbnail {
            height: 220px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 80px;
            position: relative;
            overflow: hidden;
        }
        
        .course-thumbnail::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, transparent 70%);
            animation: float-slow 8s infinite;
        }
        
        @keyframes float-slow {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-30px, 30px); }
        }
        
        .course-thumbnail.blue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .course-thumbnail.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        
        .course-thumbnail.orange {
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        }
        
        .course-thumbnail.pink {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .course-level {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 8px 16px;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
        }
        
        .course-content {
            padding: 28px;
        }
        
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .course-category {
            padding: 6px 14px;
            background: rgba(102, 126, 234, 0.15);
            color: #667eea;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .course-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }
        
        .stars {
            color: #ffa500;
        }
        
        .course-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.3;
        }
        
        .course-instructor {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 15px;
        }
        
        .instructor-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
        }
        
        .course-description {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .course-stats {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .course-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .course-price {
            font-size: 28px;
            font-weight: 800;
            color: #10b981;
        }
        
        .price-label {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 3px;
        }
        
        .enroll-btn {
            padding: 12px 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 14px;
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .enroll-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.5);
        }
        
        .featured-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 30px;
            padding: 50px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .featured-section::before {
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
        
        .featured-content {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
        }
        
        .featured-text h2 {
            font-size: 42px;
            font-weight: 900;
            margin-bottom: 20px;
        }
        
        .featured-text p {
            font-size: 18px;
            opacity: 0.95;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .featured-stats {
            display: flex;
            gap: 40px;
        }
        
        .featured-stat-value {
            font-size: 36px;
            font-weight: 900;
            margin-bottom: 5px;
        }
        
        .featured-stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        
        @media (max-width: 1200px) {
            .courses-grid {
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            }
            
            .featured-content {
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
        <!-- Featured Section -->
        <div class="featured-section">
            <div class="featured-content">
                <div class="featured-text">
                    <h2>🚀 Discover Your Next Course</h2>
                    <p>Explore hundreds of courses from top instructors. Learn at your own pace with lifetime access on mobile and desktop.</p>
                    <div class="featured-stats">
                        <div>
                            <div class="featured-stat-value">500+</div>
                            <div class="featured-stat-label">Courses</div>
                        </div>
                        <div>
                            <div class="featured-stat-value">50K+</div>
                            <div class="featured-stat-label">Students</div>
                        </div>
                        <div>
                            <div class="featured-stat-value">4.8★</div>
                            <div class="featured-stat-label">Avg Rating</div>
                        </div>
                    </div>
                </div>
                <div class="featured-image">
                    <!-- Placeholder for featured course image -->
                </div>
            </div>
        </div>
        
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="header-title">
                    <h1>Browse All Courses</h1>
                    <p>Find the perfect course to boost your skills</p>
                </div>
                <a href="dashboard.html" class="back-btn">← Dashboard</a>
            </div>
            
            <div class="search-filter-bar">
                <input type="text" class="search-input" placeholder="🔍 Search courses, instructors, or topics...">
                <select class="filter-select">
                    <option>All Levels</option>
                    <option>Beginner</option>
                    <option>Intermediate</option>
                    <option>Advanced</option>
                </select>
                <select class="filter-select">
                    <option>Sort by: Popular</option>
                    <option>Sort by: Newest</option>
                    <option>Sort by: Rating</option>
                    <option>Sort by: Price</option>
                </select>
            </div>
        </div>
        
        <!-- Categories -->
        <div class="categories">
            <div class="category-chip active">All Courses</div>
            <div class="category-chip">💻 Computer Science</div>
            <div class="category-chip">🎨 Design</div>
            <div class="category-chip">📊 Data Science</div>
            <div class="category-chip">🚀 AI & ML</div>
            <div class="category-chip">🌐 Web Development</div>
            <div class="category-chip">📱 Mobile Dev</div>
            <div class="category-chip">☁️ Cloud Computing</div>
        </div>
        
        <!-- Courses Grid -->
        <div class="courses-grid">
            <div class="course-card">
                <div class="course-thumbnail">
                    💻
                    <div class="course-level">Intermediate</div>
                </div>
                <div class="course-content">
                    <div class="course-header">
                        <span class="course-category">Computer Science</span>
                        <div class="course-rating">
                            <span class="stars">⭐</span>
                            <span>4.9 (1.2k)</span>
                        </div>
                    </div>
                    
                    <h3 class="course-title">Advanced Algorithms & Data Structures</h3>
                    
                    <div class="course-instructor">
                        <div class="instructor-avatar">JS</div>
                        <span>Dr. John Smith</span>
                    </div>
                    
                    <p class="course-description">
                        Master advanced algorithms, graph theory, dynamic programming, and complex data structures. Perfect for technical interviews.
                    </p>
                    
                    <div class="course-stats">
                        <span>📚 42 Lessons</span>
                        <span>⏱️ 28 hours</span>
                        <span>👥 3.2k students</span>
                    </div>
                    
                    <div class="course-footer">
                        <div>
                            <div class="course-price">Free</div>
                            <div class="price-label">Full access</div>
                        </div>
                        <button class="enroll-btn">Enroll Now</button>
                    </div>
                </div>
            </div>
            
            <div class="course-card">
                <div class="course-thumbnail blue">
                    🎨
                    <div class="course-level">Beginner</div>
                </div>
                <div class="course-content">
                    <div class="course-header">
                        <span class="course-category">Design</span>
                        <div class="course-rating">
                            <span class="stars">⭐</span>
                            <span>4.8 (890)</span>
                        </div>
                    </div>
                    
                    <h3 class="course-title">UI/UX Design Masterclass</h3>
                    
                    <div class="course-instructor">
                        <div class="instructor-avatar" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">SA</div>
                        <span>Sarah Anderson</span>
                    </div>
                    
                    <p class="course-description">
                        Learn design thinking, user research, wireframing, prototyping, and create stunning user interfaces from scratch.
                    </p>
                    
                    <div class="course-stats">
                        <span>📚 36 Lessons</span>
                        <span>⏱️ 22 hours</span>
                        <span>👥 2.8k students</span>
                    </div>
                    
                    <div class="course-footer">
                        <div>
                            <div class="course-price">Free</div>
                            <div class="price-label">Full access</div>
                        </div>
                        <button class="enroll-btn">Enroll Now</button>
                    </div>
                </div>
            </div>
            
            <div class="course-card">
                <div class="course-thumbnail green">
                    📊
                    <div class="course-level">Intermediate</div>
                </div>
                <div class="course-content">
                    <div class="course-header">
                        <span class="course-category">Data Science</span>
                        <div class="course-rating">
                            <span class="stars">⭐</span>
                            <span>4.9 (1.5k)</span>
                        </div>
                    </div>
                    
                    <h3 class="course-title">Data Science & Analytics Bootcamp</h3>
                    
                    <div class="course-instructor">
                        <div class="instructor-avatar" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">MJ</div>
                        <span>Dr. Mike Johnson</span>
                    </div>
                    
                    <p class="course-description">
                        Complete data science course covering Python, statistics, machine learning, and data visualization with real projects.
                    </p>
                    
                    <div class="course-stats">
                        <span>📚 58 Lessons</span>
                        <span>⏱️ 35 hours</span>
                        <span>👥 4.1k students</span>
                    </div>
                    
                    <div class="course-footer">
                        <div>
                            <div class="course-price">Free</div>
                            <div class="price-label">Full access</div>
                        </div>
                        <button class="enroll-btn">Enroll Now</button>
                    </div>
                </div>
            </div>
            
            <div class="course-card">
                <div class="course-thumbnail orange">
                    🚀
                    <div class="course-level">Advanced</div>
                </div>
                <div class="course-content">
                    <div class="course-header">
                        <span class="course-category">AI & ML</span>
                        <div class="course-rating">
                            <span class="stars">⭐</span>
                            <span>5.0 (2.1k)</span>
                        </div>
                    </div>
                    
                    <h3 class="course-title">Machine Learning & Deep Learning</h3>
                    
                    <div class="course-instructor">
                        <div class="instructor-avatar" style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);">DL</div>
                        <span>Dr. David Lee</span>
                    </div>
                    
                    <p class="course-description">
                        Advanced ML course covering neural networks, CNNs, RNNs, transformers, and deploying ML models to production.
                    </p>
                    
                    <div class="course-stats">
                        <span>📚 72 Lessons</span>
                        <span>⏱️ 45 hours</span>
                        <span>👥 5.3k students</span>
                    </div>
                    
                    <div class="course-footer">
                        <div>
                            <div class="course-price">Free</div>
                            <div class="price-label">Full access</div>
                        </div>
                        <button class="enroll-btn">Enroll Now</button>
                    </div>
                </div>
            </div>
            
            <div class="course-card">
                <div class="course-thumbnail pink">
                    🌐
                    <div class="course-level">Beginner</div>
                </div>
                <div class="course-content">
                    <div class="course-header">
                        <span class="course-category">Web Dev</span>
                        <div class="course-rating">
                            <span class="stars">⭐</span>
                            <span>4.7 (950)</span>
                        </div>
                    </div>
                    
                    <h3 class="course-title">Full Stack Web Development</h3>
                    
                    <div class="course-instructor">
                        <div class="instructor-avatar" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">EM</div>
                        <span>Emma Martinez</span>
                    </div>
                    
                    <p class="course-description">
                        Learn HTML, CSS, JavaScript, React, Node.js, and MongoDB. Build real-world projects and deploy to production.
                    </p>
                    
                    <div class="course-stats">
                        <span>📚 64 Lessons</span>
                        <span>⏱️ 40 hours</span>
                        <span>👥 3.7k students</span>
                    </div>
                    
                    <div class="course-footer">
                        <div>
                            <div class="course-price">Free</div>
                            <div class="price-label">Full access</div>
                        </div>
                        <button class="enroll-btn">Enroll Now</button>
                    </div>
                </div>
            </div>
            
            <div class="course-card">
                <div class="course-thumbnail">
                    📱
                    <div class="course-level">Intermediate</div>
                </div>
                <div class="course-content">
                    <div class="course-header">
                        <span class="course-category">Mobile Dev</span>
                        <div class="course-rating">
                            <span class="stars">⭐</span>
                            <span>4.8 (780)</span>
                        </div>
                    </div>
                    
                    <h3 class="course-title">iOS & Android Development</h3>
                    
                    <div class="course-instructor">
                        <div class="instructor-avatar">TC</div>
                        <span>Tom Chen</span>
                    </div>
                    
                    <p class="course-description">
                        Build native mobile apps for iOS and Android using Swift, Kotlin, and cross-platform frameworks like React Native.
                    </p>
                    
                    <div class="course-stats">
                        <span>📚 48 Lessons</span>
                        <span>⏱️ 32 hours</span>
                        <span>👥 2.4k students</span>
                    </div>
                    
                    <div class="course-footer">
                        <div>
                            <div class="course-price">Free</div>
                            <div class="price-label">Full access</div>
                        </div>
                        <button class="enroll-btn">Enroll Now</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Category filter
        document.querySelectorAll('.category-chip').forEach(chip => {
            chip.addEventListener('click', function() {
                document.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
            });
        });
        
        // Animate cards on load
        window.addEventListener('load', () => {
            document.querySelectorAll('.course-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
        
        // Enroll button
        document.querySelectorAll('.enroll-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                alert('Enrollment successful! Course added to your dashboard.');
            });
        });
    </script>
</body>
</html>