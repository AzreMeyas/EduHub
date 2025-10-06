<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - EduHub</title>
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
        
        /* Header */
        .quiz-header {
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
        
        .quiz-info h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .quiz-meta {
            display: flex;
            gap: 25px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .timer-section {
            text-align: right;
        }
        
        .timer {
            font-size: 48px;
            font-weight: 800;
            color: #667eea;
            margin-bottom: 5px;
            font-variant-numeric: tabular-nums;
        }
        
        .timer.warning {
            color: #f59e0b;
            animation: pulse-timer 1s infinite;
        }
        
        .timer.danger {
            color: #ef4444;
            animation: pulse-timer 0.5s infinite;
        }
        
        @keyframes pulse-timer {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .timer-label {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        /* Progress Bar */
        .progress-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 20px 30px;
            margin-bottom: 30px;
        }
        
        .progress-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .progress-text {
            font-weight: 600;
        }
        
        .progress-bar {
            height: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 5px;
            transition: width 0.5s ease;
        }
        
        /* Question Card */
        .question-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 40px;
            margin-bottom: 30px;
        }
        
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .question-number {
            display: inline-block;
            padding: 8px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
        }
        
        .difficulty-badge {
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .difficulty-easy {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
        }
        
        .difficulty-medium {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
        }
        
        .difficulty-hard {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }
        
        .question-text {
            font-size: 24px;
            font-weight: 700;
            line-height: 1.5;
            margin-bottom: 35px;
        }
        
        .options-container {
            display: grid;
            gap: 15px;
        }
        
        .option {
            padding: 20px 25px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 18px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .option:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(102, 126, 234, 0.3);
            transform: translateX(5px);
        }
        
        .option.selected {
            background: rgba(102, 126, 234, 0.15);
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .option.correct {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.5);
        }
        
        .option.incorrect {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.5);
        }
        
        .option-letter {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .option.selected .option-letter {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .option-text {
            flex: 1;
            font-size: 16px;
            font-weight: 500;
        }
        
        /* Navigation Buttons */
        .quiz-navigation {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }
        
        .nav-btn {
            padding: 16px 32px;
            border-radius: 16px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
        }
        
        /* Results Screen */
        .results-screen {
            display: none;
            text-align: center;
        }
        
        .results-screen.active {
            display: block;
        }
        
        .results-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 30px;
            padding: 60px 40px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .results-card::before {
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
        
        .results-content {
            position: relative;
            z-index: 1;
        }
        
        .results-icon {
            font-size: 80px;
            margin-bottom: 25px;
        }
        
        .results-title {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 15px;
        }
        
        .results-score {
            font-size: 72px;
            font-weight: 900;
            margin: 30px 0;
        }
        
        .results-message {
            font-size: 20px;
            margin-bottom: 40px;
            opacity: 0.95;
        }
        
        .results-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 13px;
            opacity: 0.8;
        }
        
        .review-section {
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
        }
        
        .review-item {
            padding: 20px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            margin-bottom: 15px;
        }
        
        .review-question {
            font-weight: 600;
            margin-bottom: 12px;
        }
        
        .review-answer {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 8px;
        }
        
        .answer-correct {
            color: #10b981;
        }
        
        .answer-incorrect {
            color: #ef4444;
        }
        
        @media (max-width: 768px) {
            .results-stats {
                grid-template-columns: repeat(2, 1fr);
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
        <!-- Quiz Screen -->
        <div id="quizScreen">
            <!-- Header -->
            <div class="quiz-header">
                <div class="quiz-info">
                    <h1>🎯 Data Structures Quiz</h1>
                    <div class="quiz-meta">
                        <span>📚 Computer Science</span>
                        <span>❓ 10 Questions</span>
                        <span>⏱️ 15 Minutes</span>
                    </div>
                </div>
                <div class="timer-section">
                    <div class="timer" id="timer">14:58</div>
                    <div class="timer-label">Time Remaining</div>
                </div>
            </div>
            
            <!-- Progress -->
            <div class="progress-container">
                <div class="progress-info">
                    <span class="progress-text">Question <span id="currentQuestion">1</span> of 10</span>
                    <span class="progress-text"><span id="progressPercent">10</span>% Complete</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill" style="width: 10%"></div>
                </div>
            </div>
            
            <!-- Question -->
            <div class="question-card">
                <div class="question-header">
                    <span class="question-number">Question 1</span>
                    <span class="difficulty-badge difficulty-medium">Medium</span>
                </div>
                
                <div class="question-text" id="questionText">
                    What is the time complexity of searching for an element in a balanced binary search tree?
                </div>
                
                <div class="options-container" id="optionsContainer">
                    <div class="option" onclick="selectOption(0)">
                        <div class="option-letter">A</div>
                        <div class="option-text">O(1) - Constant time</div>
                    </div>
                    <div class="option" onclick="selectOption(1)">
                        <div class="option-letter">B</div>
                        <div class="option-text">O(log n) - Logarithmic time</div>
                    </div>
                    <div class="option" onclick="selectOption(2)">
                        <div class="option-letter">C</div>
                        <div class="option-text">O(n) - Linear time</div>
                    </div>
                    <div class="option" onclick="selectOption(3)">
                        <div class="option-letter">D</div>
                        <div class="option-text">O(n²) - Quadratic time</div>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <div class="quiz-navigation">
                <button class="nav-btn btn-secondary" onclick="previousQuestion()" id="prevBtn">← Previous</button>
                <div style="flex: 1;"></div>
                <button class="nav-btn btn-secondary" onclick="skipQuestion()">Skip →</button>
                <button class="nav-btn btn-primary" onclick="nextQuestion()" id="nextBtn">Next →</button>
            </div>
        </div>
        
        <!-- Results Screen -->
        <div class="results-screen" id="resultsScreen">
            <div class="results-card">
                <div class="results-content">
                    <div class="results-icon">🎉</div>
                    <h1 class="results-title">Quiz Complete!</h1>
                    <div class="results-score" id="finalScore">85%</div>
                    <p class="results-message">Great job! You're making excellent progress.</p>
                </div>
            </div>
            
            <div class="results-stats">
                <div class="stat-card">
                    <div class="stat-value" id="correctCount">8</div>
                    <div class="stat-label">Correct</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="incorrectCount">2</div>
                    <div class="stat-label">Incorrect</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="timeSpent">8:42</div>
                    <div class="stat-label">Time Spent</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">85%</div>
                    <div class="stat-label">Accuracy</div>
                </div>
            </div>
            
            <div class="quiz-navigation">
                <button class="nav-btn btn-secondary" onclick="window.location='course-view.html'">Back to Course</button>
                <button class="nav-btn btn-primary" onclick="retakeQuiz()">🔄 Retake Quiz</button>
                <button class="nav-btn btn-success" onclick="window.location='dashboard.html'">✓ Done</button>
            </div>
            
            <div class="review-section" style="margin-top: 30px;">
                <h2 class="section-title">📝 Review Answers</h2>
                
                <div class="review-item">
                    <div class="review-question">1. What is the time complexity of searching in a balanced BST?</div>
                    <div class="review-answer answer-correct">✓ Your answer: O(log n) - Correct!</div>
                </div>
                
                <div class="review-item">
                    <div class="review-question">2. Which data structure uses LIFO principle?</div>
                    <div class="review-answer answer-correct">✓ Your answer: Stack - Correct!</div>
                </div>
                
                <div class="review-item">
                    <div class="review-question">3. What is the worst-case time complexity of QuickSort?</div>
                    <div class="review-answer answer-incorrect">✗ Your answer: O(n log n) - Incorrect</div>
                    <div class="review-answer answer-correct">✓ Correct answer: O(n²)</div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const questions = [
            {
                text: "What is the time complexity of searching for an element in a balanced binary search tree?",
                options: [
                    "O(1) - Constant time",
                    "O(log n) - Logarithmic time",
                    "O(n) - Linear time",
                    "O(n²) - Quadratic time"
                ],
                correct: 1,
                difficulty: "medium"
            },
            {
                text: "Which data structure follows the LIFO (Last In First Out) principle?",
                options: [
                    "Queue",
                    "Array",
                    "Stack",
                    "Linked List"
                ],
                correct: 2,
                difficulty: "easy"
            },
            {
                text: "What is the worst-case time complexity of QuickSort?",
                options: [
                    "O(n log n)",
                    "O(n)",
                    "O(n²)",
                    "O(log n)"
                ],
                correct: 2,
                difficulty: "hard"
            }
        ];
        
        let currentQ = 0;
        let selectedAnswer = -1;
        let answers = [];
        let timeRemaining = 900; // 15 minutes
        let timerInterval;
        
        // Start timer
        function startTimer() {
            timerInterval = setInterval(() => {
                timeRemaining--;
                updateTimerDisplay();
                
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    finishQuiz();
                }
            }, 1000);
        }
        
        function updateTimerDisplay() {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            const timerEl = document.getElementById('timer');
            timerEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeRemaining <= 60) {
                timerEl.classList.add('danger');
            } else if (timeRemaining <= 300) {
                timerEl.classList.add('warning');
            }
        }
        
        function selectOption(index) {
            selectedAnswer = index;
            const options = document.querySelectorAll('.option');
            options.forEach((opt, i) => {
                if (i === index) {
                    opt.classList.add('selected');
                } else {
                    opt.classList.remove('selected');
                }
            });
        }
        
        function nextQuestion() {
            if (selectedAnswer === -1) {
                alert('Please select an answer');
                return;
            }
            
            answers[currentQ] = selectedAnswer;
            selectedAnswer = -1;
            
            if (currentQ < questions.length - 1) {
                currentQ++;
                loadQuestion();
            } else {
                finishQuiz();
            }
        }
        
        function previousQuestion() {
            if (currentQ > 0) {
                currentQ--;
                selectedAnswer = answers[currentQ] || -1;
                loadQuestion();
            }
        }
        
        function skipQuestion() {
            answers[currentQ] = -1;
            selectedAnswer = -1;
            if (currentQ < questions.length - 1) {
                currentQ++;
                loadQuestion();
            }
        }
        
        function loadQuestion() {
            const question = questions[currentQ];
            document.getElementById('questionText').textContent = question.text;
            
            const optionsContainer = document.getElementById('optionsContainer');
            optionsContainer.innerHTML = '';
            
            question.options.forEach((option, index) => {
                const optionEl = document.createElement('div');
                optionEl.className = 'option';
                if (index === selectedAnswer) {
                    optionEl.classList.add('selected');
                }
                optionEl.onclick = () => selectOption(index);
                optionEl.innerHTML = `
                    <div class="option-letter">${String.fromCharCode(65 + index)}</div>
                    <div class="option-text">${option}</div>
                `;
                optionsContainer.appendChild(optionEl);
            });
            
            // Update progress
            const progress = ((currentQ + 1) / questions.length) * 100;
            document.getElementById('currentQuestion').textContent = currentQ + 1;
            document.getElementById('progressPercent').textContent = Math.round(progress);
            document.getElementById('progressFill').style.width = progress + '%';
            
            // Update buttons
            document.getElementById('prevBtn').disabled = currentQ === 0;
            document.getElementById('nextBtn').textContent = currentQ === questions.length - 1 ? 'Finish' : 'Next →';
        }
        
        function finishQuiz() {
            clearInterval(timerInterval);
            
            // Calculate score
            let correct = 0;
            answers.forEach((answer, index) => {
                if (answer === questions[index].correct) {
                    correct++;
                }
            });
            
            const percentage = Math.round((correct / questions.length) * 100);
            
            document.getElementById('finalScore').textContent = percentage + '%';
            document.getElementById('correctCount').textContent = correct;
            document.getElementById('incorrectCount').textContent = questions.length - correct;
            
            document.getElementById('quizScreen').style.display = 'none';
            document.getElementById('resultsScreen').classList.add('active');
        }
        
        function retakeQuiz() {
            currentQ = 0;
            selectedAnswer = -1;
            answers = [];
            timeRemaining = 900;
            document.getElementById('resultsScreen').classList.remove('active');
            document.getElementById('quizScreen').style.display = 'block';
            loadQuestion();
            startTimer();
        }
        
        // Initialize
        window.addEventListener('load', () => {
            loadQuestion();
            startTimer();
        });
    </script>
</body>
</html>