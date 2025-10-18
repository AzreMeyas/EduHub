<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material View - EduHub</title>
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
            font-weight: 600;
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
            flex-wrap: wrap;
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
            flex-wrap: wrap;
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
            display: flex;
            align-items: center;
            gap: 8px;
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
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .btn-secondary.bookmarked {
            background: rgba(255, 165, 0, 0.15);
            border-color: rgba(255, 165, 0, 0.3);
            color: #ffa500;
        }
        
        /* Viewer Section */
        .viewer-section {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            min-height: 500px;
        }
        
        .pdf-viewer {
            background: white;
            border-radius: 16px;
            padding: 40px;
            color: #333;
            min-height: 600px;
        }
        
        .pdf-viewer h2 {
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .pdf-viewer p {
            color: #555;
            line-height: 1.8;
            margin-bottom: 15px;
        }
        
        .video-player {
            width: 100%;
            aspect-ratio: 16/9;
            background: #000;
            border-radius: 16px;
            overflow: hidden;
            position: relative;
        }
        
        .video-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .play-btn {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
            color: #000;
        }
        
        .progress-bar {
            flex: 1;
            height: 6px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
            cursor: pointer;
        }
        
        .progress-fill {
            height: 100%;
            background: #667eea;
            border-radius: 3px;
            width: 30%;
        }
        
        .time-display {
            font-size: 14px;
            color: white;
        }
        
        .assignment-viewer {
            padding: 30px;
        }
        
        .assignment-section {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .assignment-section h3 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #667eea;
        }
        
        .deadline-badge {
            display: inline-block;
            padding: 8px 16px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            color: #ef4444;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .submission-box {
            background: rgba(255, 255, 255, 0.05);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .submission-box:hover {
            background: rgba(102, 126, 234, 0.1);
            border-color: rgba(102, 126, 234, 0.5);
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
        
        .rating-display {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .rating-count {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 20px;
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
        
        .star-rating {
            display: flex;
            justify-content: center;
            gap: 8px;
            font-size: 36px;
            margin-top: 20px;
        }
        
        .star {
            cursor: pointer;
            transition: all 0.3s;
            color: rgba(255, 255, 255, 0.2);
        }
        
        .star:hover,
        .star.active {
            color: #ffa500;
            transform: scale(1.2);
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
        
        /* Share Modal */
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
            max-width: 500px;
            width: 90%;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .modal-title {
            font-size: 24px;
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
        
        .share-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .share-option {
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .share-option:hover {
            background: rgba(102, 126, 234, 0.2);
            border-color: rgba(102, 126, 234, 0.4);
        }
        
        .share-option-icon {
            font-size: 32px;
            margin-bottom: 8px;
        }
        
        .share-option-label {
            font-size: 13px;
            font-weight: 600;
        }
        
        .share-link {
            display: flex;
            gap: 10px;
        }
        
        .link-input {
            flex: 1;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 14px;
        }
        
        .copy-btn {
            padding: 14px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .copy-btn:hover {
            transform: translateY(-2px);
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
            <a href="courseview.php" class="back-btn">
                ← Back to Course
            </a>
        </div>
        
        <div class="main-grid">
            <!-- Main Content -->
            <div>
                <!-- Material Content -->
                <div class="material-content">
                    <div class="material-header">
                        <div class="material-icon-large" id="materialIcon">📄</div>
                        <div class="material-info">
                            <h1 class="material-title-large" id="materialTitle">Introduction to Algorithms</h1>
                            <div class="material-meta-large">
                                <span>👤 Dr. John Smith</span>
                                <span>📅 2 days ago</span>
                                <span id="materialType">📁 Lecture Notes</span>
                                <span id="materialSize">📄 PDF, 2.5 MB</span>
                            </div>
                            <div class="material-tags-large">
                                <span class="tag-large">Algorithms</span>
                                <span class="tag-large">Data Structures</span>
                                <span class="tag-large">Week 3</span>
                                <span class="tag-large">Important</span>
                            </div>
                        </div>
                    </div>
                    
                    <p class="material-description" id="materialDescription">
                        This comprehensive guide covers fundamental sorting algorithms including Bubble Sort, Quick Sort, Merge Sort, and Heap Sort. Each algorithm is explained with detailed time complexity analysis, space complexity considerations, and practical code implementations.
                    </p>
                    
                    <div class="action-buttons">
                        <button class="btn-primary" id="downloadBtn">
                            <span>📥</span> Download
                        </button>
                        <button class="btn-secondary" id="bookmarkBtn">
                            <span>🔖</span> Bookmark
                        </button>
                        <button class="btn-secondary" onclick="showShareModal()">
                            <span>📤</span> Share
                        </button>
                    </div>
                </div>
                
                <!-- Dynamic Viewer Section -->
                <div class="viewer-section" id="viewerSection">
                    <!-- Content will be dynamically loaded here -->
                </div>
                
                <!-- Discussions Section -->
                <div class="discussions-section">
                    <h2 class="section-title">💬 Discussions (<span id="commentCount">24</span>)</h2>
                    
                    <div class="comment-form">
                        <textarea class="comment-textarea" id="newComment" placeholder="Share your thoughts, ask questions, or help others..."></textarea>
                        <button class="btn-primary" onclick="postComment()">Post Comment</button>
                    </div>
                    
                    <div id="commentsContainer">
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
                                <span class="comment-action" onclick="likeComment(this)">👍 12 Likes</span>
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
                                        <span class="comment-action" onclick="likeComment(this)">👍 8 Likes</span>
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
                                <span class="comment-action" onclick="likeComment(this)">👍 15 Likes</span>
                                <span class="comment-action">💬 Reply</span>
                            </div>
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
                        <div class="rating-display">
                            <span style="color: #ffa500;">★★★★★</span>
                        </div>
                        <div class="rating-count">Based on 156 ratings</div>
                        
                        <div class="star-rating" id="starRating">
                            <span class="star" data-rating="1">★</span>
                            <span class="star" data-rating="2">★</span>
                            <span class="star" data-rating="3">★</span>
                            <span class="star" data-rating="4">★</span>
                            <span class="star" data-rating="5">★</span>
                        </div>
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
                    
                    <button class="btn-primary" style="width: 100%; margin-bottom: 10px;">
                        🤖 Ask AI About This
                    </button>
                    <button class="btn-primary" style="width: 100%; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        🎯 Take Quiz (AI)
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
    
    <!-- Share Modal -->
    <div id="shareModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">📤 Share Material</h2>
                <button class="close-btn" onclick="closeShareModal()">×</button>
            </div>
            
            <div class="share-options">
                <div class="share-option" onclick="shareVia('facebook')">
                    <div class="share-option-icon">f</div>
                    <div class="share-option-label">Facebook</div>
                </div>
                <div class="share-option" onclick="shareVia('twitter')">
                    <div class="share-option-icon">𝕏</div>
                    <div class="share-option-label">Twitter</div>
                </div>
                <div class="share-option" onclick="shareVia('linkedin')">
                    <div class="share-option-icon">in</div>
                    <div class="share-option-label">LinkedIn</div>
                </div>
                <div class="share-option" onclick="shareVia('email')">
                    <div class="share-option-icon">✉️</div>
                    <div class="share-option-label">Email</div>
                </div>
                <div class="share-option" onclick="shareVia('whatsapp')">
                    <div class="share-option-icon">💬</div>
                    <div class="share-option-label">WhatsApp</div>
                </div>
                <div class="share-option" onclick="shareVia('copy')">
                    <div class="share-option-icon">🔗</div>
                    <div class="share-option-label">Copy Link</div>
                </div>
            </div>
            
            <div class="share-link">
                <input type="text" class="link-input" id="shareLink" value="https://eduhub.com/material/intro-to-algorithms" readonly>
                <button class="copy-btn" onclick="copyLink()">Copy</button>
            </div>
        </div>
    </div>
    
    <script>
        // Material types configuration
        const materialTypes = {
            lecture: {
                icon: '📄',
                name: 'Lecture Notes',
                viewer: 'pdf'
            },
            video: {
                icon: '🎥',
                name: 'Video Lecture',
                viewer: 'video'
            },
            assignment: {
                icon: '📝',
                name: 'Assignment',
                viewer: 'assignment'
            },
            resource: {
                icon: '📚',
                name: 'Resource',
                viewer: 'other'
            }
        };
        
        // Current material type (can be changed)
        let currentType = 'lecture';
        let userRating = 0;
        let isBookmarked = false;
        
        // Initialize material view
        function initMaterialView(type = 'lecture') {
            currentType = type;
            const material = materialTypes[type];
            
            // Update header
            document.getElementById('materialIcon').textContent = material.icon;
            document.getElementById('materialType').textContent = `📁 ${material.name}`;
            
            // Load appropriate viewer
            loadViewer(type);
        }
        
        // Load dynamic viewer based on material type
        function loadViewer(type) {
            const viewer = document.getElementById('viewerSection');
            
            if (type === 'lecture') {
                viewer.innerHTML = `
                    <div class="pdf-viewer">
                        <h2>Introduction to Algorithms - Lecture Notes</h2>
                        <p><strong>Chapter 1: Sorting Fundamentals</strong></p>
                        <p>In computer science, sorting is one of the most fundamental operations. It involves arranging elements in a particular order, typically in ascending or descending sequence. Efficient sorting algorithms are crucial for optimizing data processing and improving overall system performance.</p>
                        
                        <p><strong>1.1 Why Sorting Matters</strong></p>
                        <p>Sorting is used in numerous real-world applications including database management, search engines, analytics platforms, and more. The efficiency of sorting algorithms can directly impact the performance of these systems.</p>
                        
                        <p><strong>1.2 Sorting Algorithm Families</strong></p>
                        <p>• <strong>Bubble Sort:</strong> Simple but inefficient O(n²) algorithm<br/>
                        • <strong>Quick Sort:</strong> Efficient O(n log n) average case<br/>
                        • <strong>Merge Sort:</strong> Guaranteed O(n log n), stable sorting<br/>
                        • <strong>Heap Sort:</strong> O(n log n) in-place sorting</p>
                        
                        <p><strong>1.3 Time Complexity Analysis</strong></p>
                        <p>When analyzing sorting algorithms, we consider:</p>
                        <p>• Best case scenario: when the input is already sorted<br/>
                        • Average case: typical random input<br/>
                        • Worst case: when the input is in reverse order</p>
                        
                        <p style="color: #999; font-size: 14px; margin-top: 40px;">--- Page 1 of 28 ---</p>
                    </div>
                `;
            } else if (type === 'video') {
                viewer.innerHTML = `
                    <div class="video-player">
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; position: relative;">
                            <svg width="100" height="100" viewBox="0 0 100 100" fill="white">
                                <polygon points="35,20 35,80 80,50"></polygon>
                            </svg>
                            <div class="video-controls">
                                <div class="play-btn" onclick="alert('Video player would be here')">▶</div>
                                <div class="progress-bar">
                                    <div class="progress-fill"></div>
                                </div>
                                <div class="time-display">12:45 / 45:30</div>
                            </div>
                        </div>
                    </div>
                `;
            } else if (type === 'assignment') {
                viewer.innerHTML = `
                    <div class="assignment-viewer">
                        <div class="assignment-section">
                            <h3>Assignment Details</h3>
                            <div class="deadline-badge">Due in 3 days</div>
                            <p style="color: rgba(255,255,255,0.8); line-height: 1.8; margin-bottom: 15px;">
                                <strong>Assignment 3: Graph Algorithms</strong><br/>
                                Implement the following algorithms in your preferred programming language (Python, Java, or C++)
                            </p>
                        </div>
                        
                        <div class="assignment-section">
                            <h3>Requirements</h3>
                            <ul style="color: rgba(255,255,255,0.8); margin-left: 20px; line-height: 1.8;">
                                <li>Implement BFS (Breadth-First Search)</li>
                                <li>Implement DFS (Depth-First Search)</li>
                                <li>Implement Dijkstra's shortest path algorithm</li>
                                <li>Include test cases and complexity analysis</li>
                                <li>Submit code with documentation</li>
                            </ul>
                        </div>
                        
                        <div class="assignment-section">
                            <h3>Submission</h3>
                            <div class="submission-box" onclick="alert('File upload would be here')">
                                <div style="font-size: 40px; margin-bottom: 10px;">📁</div>
                                <p style="font-weight: 600; margin-bottom: 5px;">Click to upload or drag and drop</p>
                                <p style="font-size: 13px; color: rgba(255,255,255,0.5);">ZIP, RAR, or individual files (Max 50MB)</p>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                viewer.innerHTML = `
                    <div class="pdf-viewer">
                        <h2>Resource Material</h2>
                        <p>This resource provides additional information and supplementary materials for the course.</p>
                        <p><strong>Contents:</strong></p>
                        <ul style="margin-left: 20px; color: #555; line-height: 1.8;">
                            <li>Reference links and external resources</li>
                            <li>Code examples and snippets</li>
                            <li>Additional reading materials</li>
                            <li>Useful tools and libraries</li>
                        </ul>
                    </div>
                `;
            }
        }
        
        // Bookmark functionality
        document.getElementById('bookmarkBtn').addEventListener('click', function() {
            isBookmarked = !isBookmarked;
            if (isBookmarked) {
                this.classList.add('bookmarked');
                alert('Material bookmarked!');
            } else {
                this.classList.remove('bookmarked');
                alert('Bookmark removed');
            }
        });
        
        // Download functionality
        document.getElementById('downloadBtn').addEventListener('click', function() {
            alert('Downloading: Introduction to Algorithms.pdf');
        });
        
        // Star rating
        document.querySelectorAll('.star').forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                userRating = rating;
                
                document.querySelectorAll('.star').forEach((s, index) => {
                    if (index < rating) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
                
                alert(`Thank you! You rated this material ${rating} stars.`);
            });
            
            star.addEventListener('mouseover', function() {
                const rating = this.dataset.rating;
                document.querySelectorAll('.star').forEach((s, index) => {
                    if (index < rating) {
                        s.style.color = '#ffa500';
                    } else {
                        s.style.color = 'rgba(255, 255, 255, 0.2)';
                    }
                });
            });
        });
        
        document.getElementById('starRating').addEventListener('mouseleave', function() {
            document.querySelectorAll('.star').forEach((s, index) => {
                if (index < userRating) {
                    s.style.color = '#ffa500';
                } else {
                    s.style.color = 'rgba(255, 255, 255, 0.2)';
                }
            });
        });
        
        // Share functionality
        function showShareModal() {
            document.getElementById('shareModal').classList.add('active');
        }
        
        function closeShareModal() {
            document.getElementById('shareModal').classList.remove('active');
        }
        
        function shareVia(platform) {
            const link = document.getElementById('shareLink').value;
            const title = 'Introduction to Algorithms - Lecture Notes';
            
            const shareUrls = {
                facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(link)}`,
                twitter: `https://twitter.com/intent/tweet?url=${encodeURIComponent(link)}&text=${encodeURIComponent(title)}`,
                linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(link)}`,
                email: `mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent(link)}`,
                whatsapp: `https://wa.me/?text=${encodeURIComponent(title + ' ' + link)}`,
                copy: null
            };
            
            if (platform === 'copy') {
                copyLink();
            } else {
                alert(`Sharing via ${platform}: ${shareUrls[platform]}`);
            }
        }
        
        function copyLink() {
            const link = document.getElementById('shareLink');
            link.select();
            document.execCommand('copy');
            alert('Link copied to clipboard!');
        }
        
        // Comment functionality
        function postComment() {
            const comment = document.getElementById('newComment').value.trim();
            if (!comment) {
                alert('Please write a comment');
                return;
            }
            alert('Comment posted successfully!');
            document.getElementById('newComment').value = '';
        }
        
        function likeComment(element) {
            const likes = parseInt(element.textContent.match(/\d+/)[0]);
            element.textContent = `👍 ${likes + 1} Likes`;
            element.style.color = '#667eea';
        }
        
        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('shareModal');
            if (event.target === modal) {
                closeShareModal();
            }
        };
        
        // Initialize on load
        window.addEventListener('load', function() {
            initMaterialView('assignment');
            
            // Animate elements
            document.querySelectorAll('.stat-item').forEach((stat, index) => {
                stat.style.opacity = '0';
                stat.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    stat.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    stat.style.opacity = '1';
                    stat.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
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
        
        // Auto-expand textarea
        const textarea = document.getElementById('newComment');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }
    </script>
</body>
</html>