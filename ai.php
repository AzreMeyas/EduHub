<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Tutor - EduHub</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap');
        
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
        
        .container {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        
        .header {
            padding: 25px 40px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .upload-area {
            padding: 20px 40px;
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .upload-box {
            border: 2px dashed rgba(102, 126, 234, 0.4);
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .upload-box:hover,
        .upload-box.dragover {
            background: rgba(102, 126, 234, 0.1);
            border-color: #667eea;
        }
        
        #fileInput {
            display: none;
        }
        
        .uploaded-files {
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .file-chip {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .file-chip button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 16px;
            line-height: 1;
        }
        
        .messages {
            flex: 1;
            overflow-y: auto;
            padding: 30px 40px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .welcome {
            text-align: center;
            margin: auto;
        }
        
        .welcome-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        .welcome h2 {
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .welcome p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 16px;
            margin-bottom: 8px;
        }
        
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
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .message.ai .message-avatar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .message.user .message-avatar {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .message-bubble {
            padding: 16px 20px;
            border-radius: 16px;
            font-size: 15px;
            line-height: 1.6;
        }
        
        .message.ai .message-bubble {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .message.user .message-bubble {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .message.info .message-bubble {
            background: rgba(102, 126, 234, 0.15);
            border: 1px solid rgba(102, 126, 234, 0.3);
        }
        
        .message.error .message-bubble {
            background: rgba(245, 87, 108, 0.15);
            border: 1px solid rgba(245, 87, 108, 0.3);
        }
        
        .typing {
            display: flex;
            gap: 6px;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            width: fit-content;
        }
        
        .typing span {
            width: 8px;
            height: 8px;
            background: #667eea;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }
        
        .typing span:nth-child(2) { animation-delay: 0.2s; }
        .typing span:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
            30% { transform: translateY(-10px); opacity: 1; }
        }
        
        .input-area {
            padding: 25px 40px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .input-wrapper {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }
        
        #userInput {
            flex: 1;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-size: 15px;
            font-family: 'Outfit', sans-serif;
            resize: none;
            max-height: 150px;
        }
        
        #userInput:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        #userInput::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        #sendBtn {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 14px;
            color: white;
            font-size: 22px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        #sendBtn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        #sendBtn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
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
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="bg-gradient"></div>
        <div class="bg-gradient"></div>
    </div>
    
    <div class="container">
        <div class="header">
            <h1>🤖 AI Tutor - Document Chat</h1>
        </div>
        
        <div class="upload-area">
            <div class="upload-box" id="uploadBox">
                <div style="font-size: 40px; margin-bottom: 10px;">📄</div>
                <div style="font-size: 16px; font-weight: 600; margin-bottom: 5px;">
                    Upload Your Documents
                </div>
                <div style="font-size: 14px; color: rgba(255, 255, 255, 0.6);">
                    PDF, TXT, DOC (drag & drop or click)
                </div>
                <input type="file" id="fileInput" multiple accept=".pdf,.txt,.doc,.docx">
            </div>
            <div class="uploaded-files" id="uploadedFiles"></div>
        </div>
        
        <div class="messages" id="messages">
            <div class="welcome">
                <div class="welcome-icon">🤖✨</div>
                <h2>Welcome to AI Tutor</h2>
                <p>Chat with me about anything!</p>
                <p>Or upload documents for specific help 📚</p>
                <p>Powered by Groq AI 🚀</p>
            </div>
        </div>
        
        <div class="input-area">
            <div class="input-wrapper">
                <textarea id="userInput" rows="1" placeholder="Type your message..." disabled></textarea>
                <button id="sendBtn" disabled>➤</button>
            </div>
        </div>
    </div>

    <script>
        // 🔑 Your Groq API Key
        const GROQ_API_KEY = '';
        
        // Check if API key looks valid (starts with gsk_)
        const isValidKey = GROQ_API_KEY.startsWith('gsk_') && GROQ_API_KEY.length > 20;
        
        const fileInput = document.getElementById('fileInput');
        const uploadBox = document.getElementById('uploadBox');
        const uploadedFilesDiv = document.getElementById('uploadedFiles');
        const messagesDiv = document.getElementById('messages');
        const userInput = document.getElementById('userInput');
        const sendBtn = document.getElementById('sendBtn');
        
        let uploadedFiles = [];
        let fileContents = [];
        
        // Check if API key is set
        if (!isValidKey) {
            addMessage('⚠️ Please add your Groq API key in the code! Get one free at: https://console.groq.com/keys', 'error');
        } else {
            // Enable chat immediately if API key is valid
            userInput.disabled = false;
            sendBtn.disabled = false;
            userInput.placeholder = "Ask me anything or upload documents...";
        }
        
        uploadBox.addEventListener('click', () => fileInput.click());
        
        uploadBox.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadBox.classList.add('dragover');
        });
        
        uploadBox.addEventListener('dragleave', () => {
            uploadBox.classList.remove('dragover');
        });
        
        uploadBox.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadBox.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });
        
        fileInput.addEventListener('change', (e) => handleFiles(e.target.files));
        
        async function handleFiles(files) {
            for (let file of files) {
                try {
                    uploadedFiles.push(file);
                    const content = await readFile(file);
                    
                    // Check if content is readable
                    if (!content || content.trim().length === 0) {
                        addMessage(`⚠️ Could not read "${file.name}". File might be empty or in unsupported format.`, 'error');
                        uploadedFiles.pop();
                        continue;
                    }
                    
                    fileContents.push({ name: file.name, content });
                    
                    const chip = document.createElement('div');
                    chip.className = 'file-chip';
                    const fileIndex = uploadedFiles.length - 1;
                    chip.innerHTML = `${file.name} <button onclick="removeFile(${fileIndex})">✕</button>`;
                    uploadedFilesDiv.appendChild(chip);
                } catch (error) {
                    addMessage(`❌ Error reading "${file.name}": ${error.message}`, 'error');
                }
            }
            
            if (uploadedFiles.length > 0) {
                // Remove welcome message
                const welcome = messagesDiv.querySelector('.welcome');
                if (welcome) welcome.remove();
                
                addMessage(`✅ ${uploadedFiles.length} file(s) uploaded! I can now answer questions about your documents.`, 'info');
                userInput.focus();
            }
        }
        
        async function readFile(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    let text = e.target.result;
                    // Clean up any problematic characters
                    text = text
                        .replace(/�/g, '') // Remove replacement characters
                        .replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, '') // Remove control chars
                        .replace(/[^\x20-\x7E\n\r\t\u00A0-\uFFFF]/g, ''); // Keep printable chars only
                    resolve(text);
                };
                reader.onerror = () => reject(new Error('Failed to read file'));
                
                // Try UTF-8 first
                try {
                    reader.readAsText(file, 'UTF-8');
                } catch (e) {
                    // Fallback to default encoding
                    reader.readAsText(file);
                }
            });
        }
        
        window.removeFile = function(index) {
            uploadedFiles.splice(index, 1);
            fileContents.splice(index, 1);
            
            // Rebuild file chips
            uploadedFilesDiv.innerHTML = '';
            uploadedFiles.forEach((file, i) => {
                const chip = document.createElement('div');
                chip.className = 'file-chip';
                chip.innerHTML = `${file.name} <button onclick="removeFile(${i})">✕</button>`;
                uploadedFilesDiv.appendChild(chip);
            });
            
            if (uploadedFiles.length === 0) {
                addMessage('💡 Tip: You can upload documents for me to reference, or just chat normally!', 'info');
            }
        };
        
        userInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
        
        userInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        
        sendBtn.addEventListener('click', sendMessage);
        
        async function sendMessage() {
            const message = userInput.value.trim();
            
            if (!message) return;
            
            if (!isValidKey) {
                addMessage('⚠️ Please add your Groq API key in the code first!', 'error');
                return;
            }
            
            userInput.value = '';
            userInput.style.height = 'auto';
            
            // Remove welcome message on first interaction
            const welcome = messagesDiv.querySelector('.welcome');
            if (welcome) welcome.remove();
            
            addMessage(message, 'user');
            
            const typing = document.createElement('div');
            typing.className = 'message ai';
            typing.innerHTML = '<div class="message-avatar">🤖</div><div class="typing"><span></span><span></span><span></span></div>';
            messagesDiv.appendChild(typing);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
            
            userInput.disabled = true;
            sendBtn.disabled = true;
            
            try {
                let context;
                
                // If documents are uploaded, include them in context
                if (fileContents.length > 0) {
                    context = "You are a helpful AI tutor. Answer based on the following documents:\n\n";
                    fileContents.forEach(f => {
                        // Clean content before sending
                        const cleanContent = f.content
                            .replace(/�/g, '')
                            .substring(0, 5000);
                        context += `--- ${f.name} ---\n${cleanContent}\n\n`;
                    });
                    context += `\nStudent's Question: ${message}\n\nProvide a clear, helpful answer:`;
                } else {
                    // No documents - normal conversation
                    context = message;
                }
                
                const response = await fetch('https://api.groq.com/openai/v1/chat/completions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${GROQ_API_KEY}`
                    },
                    body: JSON.stringify({
                        model: 'llama-3.3-70b-versatile',
                        messages: [{ 
                            role: 'user', 
                            content: context 
                        }],
                        temperature: 0.7,
                        max_tokens: 2048
                    })
                });
                
                const data = await response.json();
                
                typing.remove();
                
                if (data.error) {
                    addMessage(`❌ Error: ${data.error.message}`, 'error');
                } else {
                    addMessage(data.choices[0].message.content, 'ai');
                }
                
            } catch (error) {
                typing.remove();
                addMessage(`❌ Error: ${error.message}. Check your API key and internet connection.`, 'error');
            } finally {
                userInput.disabled = false;
                sendBtn.disabled = false;
                userInput.focus();
            }
        }
        
        function addMessage(text, type) {
            const msg = document.createElement('div');
            msg.className = `message ${type}`;
            
            // Clean and sanitize text
            const cleanText = text
                .replace(/[\u0000-\u001F\u007F-\u009F]/g, '') // Remove control characters
                .replace(/\r\n/g, '\n')
                .replace(/\r/g, '\n');
            
            if (type === 'user' || type === 'ai') {
                const avatar = type === 'user' ? '👤' : '🤖';
                msg.innerHTML = `
                    <div class="message-avatar">${avatar}</div>
                    <div class="message-bubble">${cleanText.replace(/\n/g, '<br>')}</div>
                `;
            } else {
                const icon = type === 'error' ? '❌' : 'ℹ️';
                msg.innerHTML = `
                    <div class="message-avatar">${icon}</div>
                    <div class="message-bubble">${cleanText}</div>
                `;
            }
            
            messagesDiv.appendChild(msg);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
    </script>
</body>
</html>
