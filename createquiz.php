<?php
// create-quiz.php - Create Quiz Page for Teachers
session_start();
require_once 'config.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get teacher's courses
$courses_query = "SELECT course_id, course_code, title, icon 
                  FROM courses 
                  WHERE instructor_id = ? 
                  ORDER BY title";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$courses_result = $stmt->get_result();

$courses = [];
while ($row = $courses_result->fetch_assoc()) {
    $courses[] = $row;
}

$conn->close();

if (empty($courses)) {
    die("You need to create a course first before creating a quiz.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz - EduHub</title>
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
            max-width: 1000px;
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
        
        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .help-text {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 8px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        
        .form-grid.full {
            grid-template-columns: 1fr;
        }
        
        .course-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
        
        .course-icon {
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .course-code {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 8px;
        }
        
        .course-option.selected .course-code {
            color: rgba(255, 255, 255, 0.9);
        }
        
        .course-name {
            font-weight: 600;
            font-size: 14px;
        }
        
        .questions-list {
            display: grid;
            gap: 20px;
        }
        
        .question-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 25px;
            transition: all 0.3s;
        }
        
        .question-item:hover {
            background: rgba(255, 255, 255, 0.06);
        }
        
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .question-num {
            font-weight: 700;
            color: #667eea;
        }
        
        .question-actions {
            display: flex;
            gap: 10px;
        }
        
        .icon-btn {
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .icon-btn:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .icon-btn.delete:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.3);
        }
        
        .question-text-input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-size: 14px;
            margin-bottom: 15px;
            font-family: 'Outfit', sans-serif;
        }
        
        .question-type-badge {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(102, 126, 234, 0.15);
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #667eea;
        }
        
        .options-grid {
            display: grid;
            gap: 12px;
            margin-bottom: 15px;
        }
        
        .option-input-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .option-letter {
            width: 40px;
            height: 40px;
            background: rgba(102, 126, 234, 0.15);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #667eea;
            flex-shrink: 0;
        }
        
        .option-input {
            flex: 1;
            padding: 10px 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: white;
            font-size: 13px;
            font-family: 'Outfit', sans-serif;
        }
        
        .correct-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .radio-input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .difficulty-select {
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: white;
            font-size: 13px;
            font-family: 'Outfit', sans-serif;
        }
        
        .add-question-btn {
            display: inline-block;
            padding: 12px 24px;
            background: rgba(102, 126, 234, 0.15);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 14px;
            color: #667eea;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
        }
        
        .add-question-btn:hover {
            background: rgba(102, 126, 234, 0.25);
        }
        
        .preview-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
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
        }
        
        .preview-value {
            font-weight: 600;
        }
        
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
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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
            .form-grid {
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
            <a href="tdashboard.php" class="back-btn">
                ← Back to Dashboard
            </a>
            <h1 class="page-title">🎯 Create Quiz</h1>
        </div>
        
        <div class="form-container">
            <form id="createQuizForm">
                <div class="info-box">
                    🎯 Create an interactive quiz to assess student understanding. Set time limits, difficulty levels, and automatic grading.
                </div>
                
                <div class="form-section">
                    <div class="section-title">🎓 Select Course</div>
                    <div class="course-selector" id="courseSelector">
                        <?php foreach ($courses as $index => $course): ?>
                        <div class="course-option <?php echo $index === 0 ? 'selected' : ''; ?>" 
                             data-course-id="<?php echo $course['course_id']; ?>" 
                             data-course-name="<?php echo htmlspecialchars($course['title']); ?>">
                            <div class="course-icon"><?php echo $course['icon'] ?? '📚'; ?></div>
                            <div class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></div>
                            <div class="course-name"><?php echo htmlspecialchars(substr($course['title'], 0, 20)); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="selectedCourse" value="<?php echo $courses[0]['course_id']; ?>" required>
                </div>
                
                <div class="form-section">
                    <div class="section-title">⚙️ Quiz Settings</div>
                    
                    <div class="form-group">
                        <label class="form-label">Quiz Title <span class="required">*</span></label>
                        <input type="text" class="form-input" id="quizTitle" placeholder="e.g., Data Structures Quiz - Week 3" required>
                        <p class="help-text">Give your quiz a clear, descriptive title</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-textarea" id="quizDescription" placeholder="Brief description of what this quiz covers..."></textarea>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Time Limit (minutes) <span class="required">*</span></label>
                            <input type="number" class="form-input" id="timeLimit" value="15" min="5" max="180" required>
                            <p class="help-text">Time students have to complete the quiz</p>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Passing Score (%) <span class="required">*</span></label>
                            <input type="number" class="form-input" id="passingScore" value="70" min="0" max="100" required>
                            <p class="help-text">Minimum score to pass</p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Max Attempts</label>
                        <input type="number" class="form-input" id="maxAttempts" value="3" min="1" max="10">
                        <p class="help-text">Maximum number of times a student can take this quiz</p>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">❓ Questions</div>
                    
                    <div class="questions-list" id="questionsList"></div>
                    
                    <button type="button" class="add-question-btn" onclick="addQuestion()">+ Add Question</button>
                </div>
                
                <div class="form-section">
                    <div class="section-title">⚡ Quiz Options</div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 15px;">
                            <input type="checkbox" id="randomizeQuestions">
                            <span>Randomize questions for each student</span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 15px;">
                            <input type="checkbox" id="randomizeOptions">
                            <span>Randomize answer options</span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 15px;">
                            <input type="checkbox" id="showCorrectAnswers" checked>
                            <span>Show correct answers after completion</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">👁️ Preview</div>
                    <div class="preview-card">
                        <div class="preview-item">
                            <span class="preview-label">Quiz Title:</span>
                            <span class="preview-value" id="previewTitle">-</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Course:</span>
                            <span class="preview-value" id="previewCourse"><?php echo htmlspecialchars($courses[0]['title']); ?></span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Total Questions:</span>
                            <span class="preview-value" id="previewQuestions">0</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Time Limit:</span>
                            <span class="preview-value" id="previewTime">15 minutes</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Passing Score:</span>
                            <span class="preview-value" id="previewPassing">70%</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
                    <button type="submit" class="btn btn-primary">🎯 Create Quiz</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const courseOptions = document.querySelectorAll('.course-option');
        const quizForm = document.getElementById('createQuizForm');
        let questionCount = 0;
        
        courseOptions.forEach(option => {
            option.addEventListener('click', function() {
                courseOptions.forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('selectedCourse').value = this.dataset.courseId;
                document.getElementById('previewCourse').textContent = this.dataset.courseName;
            });
        });
        
        document.getElementById('quizTitle').addEventListener('input', updatePreview);
        document.getElementById('timeLimit').addEventListener('change', updatePreview);
        document.getElementById('passingScore').addEventListener('change', updatePreview);
        
        function updatePreview() {
            const title = document.getElementById('quizTitle').value || '-';
            const timeLimit = document.getElementById('timeLimit').value;
            const passingScore = document.getElementById('passingScore').value;
            
            document.getElementById('previewTitle').textContent = title;
            document.getElementById('previewTime').textContent = timeLimit + ' minutes';
            document.getElementById('previewPassing').textContent = passingScore + '%';
            document.getElementById('previewQuestions').textContent = document.querySelectorAll('.question-item').length;
        }
        
        function addQuestion() {
            questionCount++;
            const questionsList = document.getElementById('questionsList');
            
            const newQuestion = document.createElement('div');
            newQuestion.className = 'question-item';
            newQuestion.dataset.questionId = questionCount;
            newQuestion.innerHTML = `
                <div class="question-header">
                    <div>
                        <span class="question-num">Question ${questionCount}</span>
                        <span class="question-type-badge">Multiple Choice</span>
                    </div>
                    <div class="question-actions">
                        <button type="button" class="icon-btn delete" onclick="deleteQuestion(${questionCount})">🗑️ Delete</button>
                    </div>
                </div>
                <textarea class="question-text-input" placeholder="Enter your question here..." required></textarea>
                
                <div class="form-group">
                    <label class="form-label">Difficulty Level</label>
                    <select class="difficulty-select">
                        <option value="easy">Easy</option>
                        <option value="medium" selected>Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
                
                <div class="options-grid">
                    <div class="option-input-wrapper">
                        <div class="option-letter">A</div>
                        <input type="text" class="option-input" placeholder="Option A" required>
                        <div class="correct-indicator">
                            <input type="radio" name="correct_answer_${questionCount}" value="0" class="radio-input" required>
                            <span style="font-size: 12px;">Correct</span>
                        </div>
                    </div>
                    <div class="option-input-wrapper">
                        <div class="option-letter">B</div>
                        <input type="text" class="option-input" placeholder="Option B" required>
                        <div class="correct-indicator">
                            <input type="radio" name="correct_answer_${questionCount}" value="1" class="radio-input" required>
                            <span style="font-size: 12px;">Correct</span>
                        </div>
                    </div>
                    <div class="option-input-wrapper">
                        <div class="option-letter">C</div>
                        <input type="text" class="option-input" placeholder="Option C" required>
                        <div class="correct-indicator">
                            <input type="radio" name="correct_answer_${questionCount}" value="2" class="radio-input" required>
                            <span style="font-size: 12px;">Correct</span>
                        </div>
                    </div>
                    <div class="option-input-wrapper">
                        <div class="option-letter">D</div>
                        <input type="text" class="option-input" placeholder="Option D" required>
                        <div class="correct-indicator">
                            <input type="radio" name="correct_answer_${questionCount}" value="3" class="radio-input" required>
                            <span style="font-size: 12px;">Correct</span>
                        </div>
                    </div>
                </div>
            `;
            
            questionsList.appendChild(newQuestion);
            updatePreview();
        }
        
        function deleteQuestion(id) {
            const question = document.querySelector(`.question-item[data-question-id="${id}"]`);
            if (question) {
                question.remove();
                updatePreview();
            }
        }
        
        quizForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const questions = document.querySelectorAll('.question-item');
            if (questions.length === 0) {
                alert('Please add at least one question');
                return;
            }
            
            let allQuestionsValid = true;
            const questionsData = [];
            
            questions.forEach((q, index) => {
                const questionText = q.querySelector('.question-text-input').value;
                const difficulty = q.querySelector('.difficulty-select').value;
                const optionInputs = q.querySelectorAll('.option-input');
                const radioButtons = q.querySelectorAll('input[type="radio"]');
                
                let correctIndex = -1;
                radioButtons.forEach((radio, idx) => {
                    if (radio.checked) correctIndex = idx;
                });
                
                if (correctIndex === -1) {
                    alert(`Question ${index + 1}: Please select a correct answer`);
                    allQuestionsValid = false;
                    return;
                }
                
                const options = [];
                optionInputs.forEach((input, idx) => {
                    if (!input.value.trim()) {
                        alert(`Question ${index + 1}: All options must be filled`);
                        allQuestionsValid = false;
                        return;
                    }
                    options.push({
                        text: input.value,
                        is_correct: idx === correctIndex
                    });
                });
                
                questionsData.push({
                    text: questionText,
                    difficulty: difficulty,
                    options: options,
                    order_number: index + 1
                });
            });
            
            if (!allQuestionsValid) return;
            
            const quizData = {
                course_id: document.getElementById('selectedCourse').value,
                title: document.getElementById('quizTitle').value,
                description: document.getElementById('quizDescription').value,
                duration_minutes: document.getElementById('timeLimit').value,
                passing_score: document.getElementById('passingScore').value,
                max_attempts: document.getElementById('maxAttempts').value,
                randomize_questions: document.getElementById('randomizeQuestions').checked,
                randomize_options: document.getElementById('randomizeOptions').checked,
                show_correct_answers: document.getElementById('showCorrectAnswers').checked,
                questions: questionsData
            };
            
            const submitBtn = quizForm.querySelector('.btn-primary');
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Creating Quiz...';
            
            // Log the data being sent
            console.log('Sending quiz data:', quizData);
            
            try {
                const response = await fetch('save_quiz.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(quizData)
                });
                
                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Get response text first
                const responseText = await response.text();
                console.log('Response:', responseText);
                
                // Try to parse as JSON
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.error('Response was:', responseText);
                    throw new Error('Server returned invalid JSON. Check browser console for details.');
                }
                
                if (result.success) {
                    submitBtn.textContent = '✅ Quiz Created!';
                    alert(`Quiz "${quizData.title}" created successfully with ${quizData.questions.length} questions!`);
                    window.location.href = 'tdashboard.php';
                } else {
                    throw new Error(result.error || 'Failed to create quiz');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error creating quiz: ' + error.message);
                submitBtn.disabled = false;
                submitBtn.textContent = '🎯 Create Quiz';
            }
        });
        
        // Add first question on load
        window.addEventListener('load', () => {
            addQuestion();
        });
    </script>
</body>
</html>