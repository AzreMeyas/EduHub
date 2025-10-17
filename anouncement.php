<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Announcement - EduHub</title>
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
            max-width: 900px;
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
        
        .page-title {
            font-size: 28px;
            font-weight: 800;
        }
        
        .form-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 40px;
        }
        
        .form-section {
            margin-bottom: 35px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .required {
            color: #ef4444;
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
            transition: all 0.3s;
        }
        
        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
            background: rgba(255, 255, 255, 0.08);
        }
        
        .form-input::placeholder,
        .form-textarea::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 150px;
        }
        
        .help-text {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 8px;
        }
        
        .character-count {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 8px;
            text-align: right;
        }
        
        /* Course Selection */
        .course-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .course-option {
            padding: 18px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .course-option:hover {
            background: rgba(102, 126, 234, 0.1);
            border-color: rgba(102, 126, 234, 0.3);
        }
        
        .course-option.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .course-code {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 8px;
        }
        
        .course-name {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .course-students {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .course-option.selected .course-code,
        .course-option.selected .course-students {
            color: rgba(255, 255, 255, 0.8);
        }
        
        /* Priority Selection */
        .priority-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px;
        }
        
        .priority-option {
            padding: 18px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .priority-option:hover {
            background: rgba(255, 255, 255, 0.08);
        }
        
        .priority-option.selected {
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
        }
        
        .priority-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .priority-label {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .priority-desc {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .priority-option.selected .priority-desc {
            color: rgba(255, 255, 255, 0.7);
        }
        
        /* Recipient Selection */
        .recipient-checkboxes {
            display: grid;
            gap: 12px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .checkbox-item:hover {
            background: rgba(255, 255, 255, 0.08);
        }
        
        .checkbox-item input[type="checkbox"] {
            cursor: pointer;
            width: 20px;
            height: 20px;
        }
        
        .checkbox-label {
            flex: 1;
            cursor: pointer;
        }
        
        .checkbox-count {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        /* Attachment Area */
        .attachment-area {
            padding: 30px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .attachment-area:hover {
            background: rgba(102, 126, 234, 0.1);
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .attachment-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .attachment-text {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .attachment-subtext {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .file-input {
            display: none;
        }
        
        .attachment-list {
            margin-top: 15px;
        }
        
        .attachment-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            margin-bottom: 8px;
        }
        
        .attachment-item-icon {
            font-size: 20px;
        }
        
        .attachment-item-info {
            flex: 1;
            font-size: 13px;
        }
        
        .attachment-remove {
            padding: 4px 12px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 8px;
            color: #ef4444;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .attachment-remove:hover {
            background: rgba(239, 68, 68, 0.25);
        }
        
        /* Preview Card */
        .preview-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .preview-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .preview-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .preview-item:last-child {
            border-bottom: none;
        }
        
        .preview-label {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }
        
        .preview-value {
            font-weight: 600;
            color: white;
            max-width: 400px;
            text-align: right;
            word-break: break-word;
        }
        
        /* Schedule Section */
        .schedule-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 35px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn {
            padding: 14px 32px;
            border: none;
            border-radius: 16px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            flex: 1;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .info-box {
            padding: 16px;
            background: rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 16px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 25px;
        }
        
        @media (max-width: 768px) {
            .schedule-grid {
                grid-template-columns: 1fr;
            }
            
            .course-selector {
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
            <h1 class="page-title">Post Announcement</h1>
        </div>
        
        <div class="form-container">
            <form id="announcementForm">
                <!-- Info Box -->
                <div class="info-box">
                    📢 Share important updates with your students. Announcements will notify all selected recipients.
                </div>
                
                <!-- Course Selection -->
                <div class="form-section">
                    <div class="section-title">🎓 Select Courses</div>
                    <div class="form-group">
                        <label class="form-label">Post to Courses <span class="required">*</span></label>
                        <div class="course-selector" id="courseSelector">
                            <div class="course-option" data-course-id="cs401" data-course-name="Advanced Computer Science" data-students="120">
                                <div class="course-code">CS-401</div>
                                <div class="course-name">Advanced Computer Science</div>
                                <div class="course-students">120 students</div>
                            </div>
                            <div class="course-option" data-course-id="cs502" data-course-name="Machine Learning & AI" data-students="95">
                                <div class="course-code">CS-502</div>
                                <div class="course-name">Machine Learning & AI</div>
                                <div class="course-students">95 students</div>
                            </div>
                            <div class="course-option" data-course-id="cs301" data-course-name="Data Structures & Algorithms" data-students="156">
                                <div class="course-code">CS-301</div>
                                <div class="course-name">Data Structures & Algorithms</div>
                                <div class="course-students">156 students</div>
                            </div>
                            <div class="course-option" data-course-id="cs201" data-course-name="Introduction to Programming" data-students="200">
                                <div class="course-code">CS-201</div>
                                <div class="course-name">Intro to Programming</div>
                                <div class="course-students">200 students</div>
                            </div>
                        </div>
                        <p class="help-text">Select one or more courses to post this announcement to</p>
                        <input type="hidden" id="selectedCourses" value="">
                    </div>
                </div>
                
                <!-- Announcement Content -->
                <div class="form-section">
                    <div class="section-title">📝 Announcement Content</div>
                    
                    <div class="form-group">
                        <label class="form-label">Title <span class="required">*</span></label>
                        <input type="text" class="form-input" id="title" placeholder="e.g., Midterm Exam Schedule Update" maxlength="100" required>
                        <div class="character-count"><span id="titleCount">0</span>/100</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Message <span class="required">*</span></label>
                        <textarea class="form-textarea" id="message" placeholder="Write your announcement message here..." maxlength="2000" required></textarea>
                        <div class="character-count"><span id="messageCount">0</span>/2000</div>
                    </div>
                </div>
                
                <!-- Priority & Recipients -->
                <div class="form-section">
                    <div class="section-title">⚡ Priority & Recipients</div>
                    
                    <div class="form-group">
                        <label class="form-label">Priority Level <span class="required">*</span></label>
                        <div class="priority-options" id="priorityOptions">
                            <div class="priority-option" data-priority="low">
                                <div class="priority-icon">📚</div>
                                <div class="priority-label">Low</div>
                                <div class="priority-desc">General info/course-material</div>
                            </div>
                            <div class="priority-option selected" data-priority="medium">
                                <div class="priority-icon">⏰</div>
                                <div class="priority-label">Medium</div>
                                <div class="priority-desc">Important update</div>
                            </div>
                            <div class="priority-option" data-priority="high">
                                <div class="priority-icon">📌</div>
                                <div class="priority-label">High</div>
                                <div class="priority-desc">Urgent/Critical</div>
                            </div>
                        </div>
                        <input type="hidden" id="selectedPriority" value="medium" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Recipients <span class="required">*</span></label>
                        <div class="recipient-checkboxes">
                            <label class="checkbox-item">
                                <input type="checkbox" name="recipient" value="all_students" checked>
                                <div class="checkbox-label">All Students in Selected Courses</div>
                                <div class="checkbox-count" id="allStudentsCount">571 students</div>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="recipient" value="send_email">
                                <div class="checkbox-label">Send Email Notification</div>
                                <div class="checkbox-count">Via email to all</div>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="recipient" value="send_notification">
                                <div class="checkbox-label">Send Push Notification</div>
                                <div class="checkbox-count">Real-time alerts</div>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Scheduling -->
                <div class="form-section">
                    <div class="section-title">⏰ Scheduling</div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 20px;">
                            <input type="checkbox" id="scheduleAnnouncement">
                            <span>Schedule announcement for later</span>
                        </label>
                    </div>
                    
                    <div class="schedule-grid" id="scheduleGrid" style="display: none;">
                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-input" id="scheduleDate">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Time</label>
                            <input type="time" class="form-input" id="scheduleTime">
                        </div>
                    </div>
                </div>
                
                <!-- Attachments -->
                <div class="form-section">
                    <div class="section-title">📎 Attachments (Optional)</div>
                    <div class="attachment-area" id="attachmentArea">
                        <div class="attachment-icon">📎</div>
                        <div class="attachment-text">Attach files (optional)</div>
                        <div class="attachment-subtext">Images, PDFs, Documents (Max 5 files, 50MB total)</div>
                        <input type="file" class="file-input" id="fileInput" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                    </div>
                    <div class="attachment-list" id="attachmentList"></div>
                </div>
                
                <!-- Preview -->
                <div class="form-section">
                    <div class="section-title">👁️ Preview</div>
                    <div class="preview-card">
                        <div class="preview-title" id="previewTitle">Announcement Title</div>
                        <div class="preview-item">
                            <span class="preview-label">Posting To:</span>
                            <span class="preview-value" id="previewCourses">No courses selected</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Recipients:</span>
                            <span class="preview-value" id="previewRecipients">0 students</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Priority:</span>
                            <span class="preview-value" id="previewPriority">Medium 🟡</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Schedule:</span>
                            <span class="preview-value" id="previewSchedule">Post immediately</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Email Notification:</span>
                            <span class="preview-value" id="previewEmail">Disabled</span>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="form-actions">
                    <a href="courseview.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">📢 Post Announcement</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const courseOptions = document.querySelectorAll('.course-option');
        const priorityOptions = document.querySelectorAll('.priority-option');
        const announcementForm = document.getElementById('announcementForm');
        const attachmentArea = document.getElementById('attachmentArea');
        const fileInput = document.getElementById('fileInput');
        const scheduleCheckbox = document.getElementById('scheduleAnnouncement');
        const scheduleGrid = document.getElementById('scheduleGrid');
        const titleInput = document.getElementById('title');
        const messageInput = document.getElementById('message');
        
        let selectedCourses = [];
        let attachedFiles = [];
        let selectedPriority = 'medium';
        
        // Course Selection
        courseOptions.forEach(option => {
            option.addEventListener('click', function() {
                this.classList.toggle('selected');
                updateCourseSelection();
            });
        });
        
        function updateCourseSelection() {
            selectedCourses = [];
            let totalStudents = 0;
            
            courseOptions.forEach(option => {
                if (option.classList.contains('selected')) {
                    selectedCourses.push({
                        id: option.dataset.courseId,
                        name: option.dataset.courseName,
                        students: parseInt(option.dataset.students)
                    });
                    totalStudents += parseInt(option.dataset.students);
                }
            });
            
            document.getElementById('selectedCourses').value = selectedCourses.map(c => c.id).join(',');
            document.getElementById('allStudentsCount').textContent = totalStudents + ' students';
            updatePreview();
        }
        
        // Priority Selection
        priorityOptions.forEach(option => {
            option.addEventListener('click', function() {
                priorityOptions.forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                selectedPriority = this.dataset.priority;
                document.getElementById('selectedPriority').value = selectedPriority;
                updatePreview();
            });
        });
        
        // Schedule Toggle
        scheduleCheckbox.addEventListener('change', function() {
            scheduleGrid.style.display = this.checked ? 'grid' : 'none';
            updatePreview();
        });
        
        // Character Count
        titleInput.addEventListener('input', function() {
            document.getElementById('titleCount').textContent = this.value.length;
            updatePreview();
        });
        
        messageInput.addEventListener('input', function() {
            document.getElementById('messageCount').textContent = this.value.length;
        });
        
        // Attachment Upload
        attachmentArea.addEventListener('click', () => fileInput.click());
        
        attachmentArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            attachmentArea.style.background = 'rgba(102, 126, 234, 0.2)';
        });
        
        attachmentArea.addEventListener('dragleave', () => {
            attachmentArea.style.background = 'rgba(255, 255, 255, 0.05)';
        });
        
        attachmentArea.addEventListener('drop', (e) => {
            e.preventDefault();
            attachmentArea.style.background = 'rgba(255, 255, 255, 0.05)';
            handleFiles(e.dataTransfer.files);
        });
        
        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });
        
        function handleFiles(files) {
            if (attachedFiles.length + files.length > 5) {
                alert('Maximum 5 files allowed');
                return;
            }
            
            Array.from(files).forEach(file => {
                attachedFiles.push(file);
                addFileToList(file);
            });
        }
        
        function addFileToList(file) {
            const attachmentItem = document.createElement('div');
            attachmentItem.className = 'attachment-item';
            attachmentItem.innerHTML = `
                <div class="attachment-item-icon">📄</div>
                <div class="attachment-item-info">${file.name} (${formatFileSize(file.size)})</div>
                <div class="attachment-remove" onclick="removeFile('${file.name}')">Remove</div>
            `;
            document.getElementById('attachmentList').appendChild(attachmentItem);
        }
        
        function removeFile(fileName) {
            attachedFiles = attachedFiles.filter(f => f.name !== fileName);
            const items = document.querySelectorAll('.attachment-item');
            items.forEach(item => {
                if (item.textContent.includes(fileName)) {
                    item.remove();
                }
            });
        }
        
        function formatFileSize(bytes) {
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
        
        // Update Preview
        function updatePreview() {
            const title = titleInput.value || 'Announcement Title';
            const coursesText = selectedCourses.length > 0 
                ? selectedCourses.map(c => c.name).join(', ')
                : 'No courses selected';
            const totalStudents = selectedCourses.reduce((sum, c) => sum + c.students, 0);
            
            const priorityIcons = {
                low: '📚 Low',
                medium: '⏰ Medium',
                high: '📌 High'
            };
            
            const sendEmail = document.querySelector('input[name="recipient"][value="send_email"]').checked;
            const scheduleText = scheduleCheckbox.checked 
                ? `${document.getElementById('scheduleDate').value} at ${document.getElementById('scheduleTime').value}`
                : 'Post immediately';
            
            document.getElementById('previewTitle').textContent = title;
            document.getElementById('previewCourses').textContent = coursesText;
            document.getElementById('previewRecipients').textContent = totalStudents + ' students';
            document.getElementById('previewPriority').textContent = priorityIcons[selectedPriority];
            document.getElementById('previewSchedule').textContent = scheduleText || 'Post immediately';
            document.getElementById('previewEmail').textContent = sendEmail ? 'Enabled' : 'Disabled';
        }
        
        // Recipient Checkboxes Update
        document.querySelectorAll('input[name="recipient"]').forEach(checkbox => {
            checkbox.addEventListener('change', updatePreview);
        });
        
        // Schedule Date/Time Update
        document.getElementById('scheduleDate').addEventListener('change', updatePreview);
        document.getElementById('scheduleTime').addEventListener('change', updatePreview);
        
        // Form Submission
        announcementForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validation
            if (selectedCourses.length === 0) {
                alert('Please select at least one course');
                return;
            }
            
            if (!titleInput.value.trim()) {
                alert('Please enter an announcement title');
                return;
            }
            
            if (!messageInput.value.trim()) {
                alert('Please enter the announcement message');
                return;
            }
            
            // Get recipient options
            const recipients = [];
            document.querySelectorAll('input[name="recipient"]:checked').forEach(checkbox => {
                recipients.push(checkbox.value);
            });
            
            if (recipients.length === 0) {
                alert('Please select at least one recipient option');
                return;
            }
            
            // Collect form data
            const formData = {
                courses: selectedCourses,
                title: titleInput.value,
                message: messageInput.value,
                priority: selectedPriority,
                recipients: recipients,
                schedule: {
                    isScheduled: scheduleCheckbox.checked,
                    date: scheduleCheckbox.checked ? document.getElementById('scheduleDate').value : null,
                    time: scheduleCheckbox.checked ? document.getElementById('scheduleTime').value : null
                },
                attachments: attachedFiles.map(f => f.name),
                totalStudents: selectedCourses.reduce((sum, c) => sum + c.students, 0)
            };
            
            console.log('Announcement Data:', formData);
            
            // Post announcement
            postAnnouncement(formData);
        });
        
        // Simulate Announcement Posting
        function postAnnouncement(formData) {
            const submitBtn = announcementForm.querySelector('.btn-primary');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Posting...';
            
            // Simulate posting delay
            setTimeout(() => {
                submitBtn.textContent = '✅ Announcement Posted!';
                
                // Show notification info
                const totalStudents = formData.totalStudents;
                const sendEmail = formData.recipients.includes('send_email');
                const sendNotification = formData.recipients.includes('send_notification');
                
                let notificationMessage = `✅ Announcement Posted Successfully!\n\n`;
                notificationMessage += `📢 Posted to ${formData.courses.length} course(s)\n`;
                notificationMessage += `👥 Reaching ${totalStudents} students\n`;
                
                if (formData.schedule.isScheduled) {
                    notificationMessage += `⏰ Scheduled for: ${formData.schedule.date} at ${formData.schedule.time}\n`;
                }
                
                if (sendEmail) {
                    notificationMessage += `📧 Email notifications will be sent\n`;
                }
                
                if (sendNotification) {
                    notificationMessage += `🔔 Push notifications will be sent\n`;
                }
                
                setTimeout(() => {
                    alert(notificationMessage);
                    window.location.href = 'courseview.php';
                }, 1500);
            }, 2000);
        }
        
        // Initialize preview on page load
        window.addEventListener('load', updatePreview);
    </script>
</body>
</html>