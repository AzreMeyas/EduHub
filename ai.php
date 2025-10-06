<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Tutor - EduHub</title>
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
            height: 100vh;
            overflow: hidden;
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
        
        .chat-container {
            position: relative;
            z-index: 1;
            display: flex;
            height: 100vh;
        }
        
        /* Sidebar */
        .chat-sidebar {
            width: 320px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-header {
            padding: 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-title {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea 0%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .sidebar-subtitle {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .new-chat-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 14px;
            color: white;
            font-weight: 700;
            cursor: pointer;
            margin-top: 15px;
            transition: all 0.3s;
        }
        
        .new-chat-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .chat-history {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        
        .history-section-title {
            font-size: 12px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        
        .history-item {
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .history-item:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(5px);
        }
        
        .history-item.active {
            background: rgba(102, 126, 234, 0.15);
            border-left: 3px solid #667eea;
        }
        
        .history-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .history-preview {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .history-time {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.4);
            margin-top: 5px;
        }
        
        /* Main Chat Area */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: rgba(255, 255, 255, 0.02);
        }
        
        .chat-header {
            padding: 25px 40px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-title-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .ai-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 20px rgba(102, 126, 234, 0.5); }
            50% { box-shadow: 0 0 40px rgba(102, 126, 234, 0.8); }
        }
        
        .chat-header-info h2 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .chat-header-info p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .chat-actions {
            display: flex;
            gap: 10px;
        }
        
        .chat-action-btn {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .chat-action-btn:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        /* Messages Area */
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 30px 40px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .welcome-screen {
            text-align: center;
            margin: auto;
            max-width: 600px;
        }
        
        .welcome-icon {
            font-size: 80px;
            margin-bottom: 25px;
            animation: float-icon 3s ease-in-out infinite;
        }
        
        @keyframes float-icon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        .welcome-title {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 15px;
        }
        
        .welcome-subtitle {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 40px;
        }
        
        .suggestion-chips {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 30px;
        }
        
        .suggestion-chip {
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: left;
        }
        
        .suggestion-chip:hover {
            background: rgba(102, 126, 234, 0.15);
            border-color: rgba(102, 126, 234, 0.3);
            transform: translateY(-5px);
        }
        
        .chip-icon {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .chip-text {
            font-size: 14px;
            font-weight: 600;
        }
        
        /* Message Bubbles */
        .message {
            display: flex;
            gap: 15px;
            max-width: 80%;
        }
        
        .message.user {
            align-self: flex-end;
            flex-direction: row-reverse;
        }
        
        .message-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .message.ai .message-avatar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .message.user .message-avatar {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .message-content {
            flex: 1;
        }
        
        .message-bubble {
            padding: 16px 20px;
            border-radius: 18px;
            font-size: 15px;
            line-height: 1.6;
        }
        
        .message.ai .message-bubble {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .message.user .message-bubble {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            text-align: right;
        }
        
        .message-time {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.4);
            margin-top: 8px;
            padding: 0 5px;
        }
        
        .typing-indicator {
            display: none;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 18px;
            width: fit-content;
        }
        
        .typing-indicator.active {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        
        .typing-dot {
            width: 8px;
            height: 8px;
            background: #667eea;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }
        
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
            30% { transform: translateY(-10px); opacity: 1; }
        }
        
        /* Input Area */
        .input-container {
            padding: 25px 40px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .input-actions {
            display: flex;
            gap: 12px;
            margin-bottom: 15px;
        }
        
        .action-icon-btn {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 18px;
        }
        
        .action-icon-btn:hover {
            background: rgba(102, 126, 234, 0.15);
            border-color: rgba(102, 126, 234, 0.3);
        }
        
        .input-wrapper {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }
        
        .chat-input {
            flex: 1;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 18px;
            color: white;
            font-size: 15px;
            font-family: 'Outfit', sans-serif;
            resize: none;
            max-height: 150px;
            transition: all 0.3s;
        }
        
        .chat-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .chat-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .send-btn {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 14px;
            color: white;
            font-size: 22px;
            cursor: pointer;
            transition: all 0.3s;
            flex-shrink: 0;
        }
        
        .send-btn:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.5);
        }
        
        .send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }
        
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        @media (max-width: 1024px) {
            .chat-sidebar {
                position: absolute;
                left: -320px;
                z-index: 100;
                transition: left 0.3s;
            }
            
            .chat-sidebar.open {
                left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="bg-gradient"></div>
        <div class="bg-gradient"></div>
    </div>
    
    <div class="chat-container">
        <!-- Sidebar -->
        <div class="chat-sidebar">
            <div class="sidebar-header">
                <h1 class="sidebar-title">🤖 AI Tutor</h1>
                <p class="sidebar-subtitle">Your intelligent learning assistant</p>
                <button class="new-chat-btn" onclick="newChat()">+ New Conversation</button>
            </div>
            
            <div class="chat-history">
                <div class="history-section-title">Today</div>
                
                <div class="history-item active">
                    <div class="history-title">Algorithm Complexity Help</div>
                    <div class="history-preview">Can you explain Big O notation...</div>
                    <div class="history-time">2 hours ago</div>
                </div>
                
                <div class="history-item">
                    <div class="history-title">Data Structures Question</div>
                    <div class="history-preview">What's the difference between...</div>
                    <div class="history-time">5 hours ago</div>
                </div>
                
                <div class="history-section-title">Yesterday</div>
                
                <div class="history-item">
                    <div class="history-title">Python Programming</div>
                    <div class="history-preview">How do I implement a binary tree...</div>
                    <div class="history-time">Yesterday</div>
                </div>
                
                <div class="history-item">
                    <div class="history-title">Database Design</div>
                    <div class="history-preview">Explain normalization...</div>
                    <div class="history-time">Yesterday</div>
                </div>
                
                <div class="history-section-title">This Week</div>
                
                <div class="history-item">
                    <div class="history-title">Machine Learning Basics</div>
                    <div class="history-preview">What are neural networks...</div>
                    <div class="history-time">3 days ago</div>
                </div>
            </div>
        </div>
        
        <!-- Main Chat -->
        <div class="chat-main">
            <div class="chat-header">
                <div class="chat-title-section">
                    <div class="ai-avatar">🤖</div>
                    <div class="chat-header-info">
                        <h2>AI Study Assistant</h2>
                        <p>💚 Online • Ready to help</p>
                    </div>
                </div>
                <div class="chat-actions">
                    <button class="chat-action-btn" onclick="window.location='dashboard.html'">← Dashboard</button>
                    <button class="chat-action-btn">🗑️ Clear Chat</button>
                </div>
            </div>
            
            <div class="messages-container" id="messagesContainer">
                <!-- Welcome Screen -->
                <div class="welcome-screen" id="welcomeScreen">
                    <div class="welcome-icon">🤖✨</div>
                    <h1 class="welcome-title">Hi! I'm your AI Tutor</h1>
                    <p class="welcome-subtitle">Ask me anything about your courses, get explanations, practice problems, or study tips!</p>
                    
                    <div class="suggestion-chips">
                        <div class="suggestion-chip" onclick="sendSuggestion('Explain sorting algorithms')">
                            <div class="chip-icon">📚</div>
                            <div class="chip-text">Explain sorting algorithms</div>
                        </div>
                        <div class="suggestion-chip" onclick="sendSuggestion('Create practice quiz')">
                            <div class="chip-icon">🎯</div>
                            <div class="chip-text">Create practice quiz</div>
                        </div>
                        <div class="suggestion-chip" onclick="sendSuggestion('Help with homework')">
                            <div class="chip-icon">✏️</div>
                            <div class="chip-text">Help with homework</div>
                        </div>
                        <div class="suggestion-chip" onclick="sendSuggestion('Study tips and techniques')">
                            <div class="chip-icon">💡</div>
                            <div class="chip-text">Study tips and techniques</div>
                        </div>
                    </div>
                </div>
                
                <!-- Messages will be added here dynamically -->
                
                <!-- Typing Indicator -->
                <div class="message ai">
                    <div class="message-avatar">🤖</div>
                    <div class="message-content">
                        <div class="typing-indicator" id="typingIndicator">
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="input-container">
                <div class="input-actions">
                    <div class="action-icon-btn" title="Upload file">📎</div>
                    <div class="action-icon-btn" title="Add image">🖼️</div>
                    <div class="action-icon-btn" title="Voice input">🎤</div>
                    <div class="action-icon-btn" title="Code snippet">💻</div>
                </div>
                <div class="input-wrapper">
                    <textarea class="chat-input" id="chatInput" placeholder="Ask me anything... (Shift + Enter for new line)" rows="1"></textarea>
                    <button class="send-btn" id="sendBtn" onclick="sendMessage()">➤</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const messagesContainer = document.getElementById('messagesContainer');
        const chatInput = document.getElementById('chatInput');
        const sendBtn = document.getElementById('sendBtn');
        const typingIndicator = document.getElementById('typingIndicator');
        const welcomeScreen = document.getElementById('welcomeScreen');
        
        // Auto-resize textarea
        chatInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Send on Enter (Shift+Enter for new line)
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        
        function sendSuggestion(text) {
            chatInput.value = text;
            sendMessage();
        }
        
        function sendMessage() {
            const message = chatInput.value.trim();
            if (!message) return;
            
            // Hide welcome screen
            if (welcomeScreen) {
                welcomeScreen.style.display = 'none';
            }
            
            // Add user message
            addMessage(message, 'user');
            chatInput.value = '';
            chatInput.style.height = 'auto';
            
            // Show typing indicator
            typingIndicator.classList.add('active');
            scrollToBottom();
            
            // Simulate AI response
            setTimeout(() => {
                typingIndicator.classList.remove('active');
                const response = generateAIResponse(message);
                addMessage(response, 'ai');
            }, 1500 + Math.random() * 1000);
        }
        
        function addMessage(text, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            
            const avatar = type === 'user' ? '👤' : '🤖';
            const time = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            
            messageDiv.innerHTML = `
                <div class="message-avatar">${avatar}</div>
                <div class="message-content">
                    <div class="message-bubble">${escapeHtml(text)}</div>
                    <div class="message-time">${time}</div>
                </div>
            `;
            
            // Insert before typing indicator
            const typingMessage = messagesContainer.querySelector('.message.ai:last-child');
            messagesContainer.insertBefore(messageDiv, typingMessage);
            
            // Animate message
            messageDiv.style.opacity = '0';
            messageDiv.style.transform = 'translateY(20px)';
            setTimeout(() => {
                messageDiv.style.transition = 'all 0.4s ease-out';
                messageDiv.style.opacity = '1';
                messageDiv.style.transform = 'translateY(0)';
            }, 10);
            
            scrollToBottom();
        }
        
        function generateAIResponse(userMessage) {
            const lowerMessage = userMessage.toLowerCase();
            
            if (lowerMessage.includes('algorithm') || lowerMessage.includes('sorting')) {
                return "Great question about algorithms! Let me explain:\n\nSorting algorithms are methods for arranging elements in a specific order. The most common ones are:\n\n1. **Bubble Sort** - O(n²) - Simple but inefficient\n2. **Quick Sort** - O(n log n) - Fast and widely used\n3. **Merge Sort** - O(n log n) - Stable and predictable\n\nWould you like me to explain any specific algorithm in detail?";
            } else if (lowerMessage.includes('quiz') || lowerMessage.includes('practice')) {
                return "I'd be happy to create a practice quiz for you! What topic would you like to be quizzed on? For example:\n\n• Data Structures\n• Algorithms\n• Database Design\n• Programming Concepts\n• Web Development\n\nJust let me know and I'll generate relevant questions!";
            } else if (lowerMessage.includes('help') || lowerMessage.includes('homework')) {
                return "I'm here to help with your homework! Please share:\n\n1. The specific question or problem\n2. What you've tried so far\n3. Where you're stuck\n\nI'll guide you through the solution step by step, helping you understand the concepts rather than just giving answers.";
            } else if (lowerMessage.includes('study') || lowerMessage.includes('tips')) {
                return "Here are some effective study techniques:\n\n✅ **Pomodoro Technique** - 25 min focus, 5 min break\n✅ **Active Recall** - Test yourself regularly\n✅ **Spaced Repetition** - Review at increasing intervals\n✅ **Feynman Technique** - Explain concepts simply\n✅ **Practice Problems** - Apply what you learn\n\nWhich technique would you like to know more about?";
            } else {
                return "I understand you're asking about: \"" + userMessage + "\"\n\nLet me help you with that! This is an AI-simulated response. In a production environment, this would connect to a real AI API (like OpenAI's GPT-4) to provide intelligent, context-aware answers based on your course materials and learning history.\n\nIs there anything specific you'd like me to clarify or expand on?";
            }
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML.replace(/\n/g, '<br>');
        }
        
        function scrollToBottom() {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        function newChat() {
            if (confirm('Start a new conversation? Current chat will be saved to history.')) {
                messagesContainer.innerHTML = messagesContainer.innerHTML.split('<!-- Messages will be added here dynamically -->')[0] + 
                    '<!-- Messages will be added here dynamically -->' + 
                    messagesContainer.innerHTML.split('<!-- Messages will be added here dynamically -->')[1];
                welcomeScreen.style.display = 'block';
            }
        }
        
        // Initial animation
        window.addEventListener('load', () => {
            welcomeScreen.style.opacity = '0';
            welcomeScreen.style.transform = 'translateY(30px)';
            setTimeout(() => {
                welcomeScreen.style.transition = 'all 0.8s ease-out';
                welcomeScreen.style.opacity = '1';
                welcomeScreen.style.transform = 'translateY(0)';
            }, 300);
        });
    </script>
</body>
</html>