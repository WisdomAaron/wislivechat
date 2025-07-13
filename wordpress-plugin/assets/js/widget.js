/**
 * WisChat Widget JavaScript
 */

(function() {
    'use strict';
    
    // Widget class
    class WisChatWidget {
        constructor(widgetId) {
            this.widgetId = widgetId;
            this.widget = document.getElementById(widgetId);
            this.isOpen = false;
            this.isMinimized = false;
            this.socket = null;
            this.sessionId = null;
            this.chatId = null;
            this.config = window.wischat_config || {};
            this.translations = this.config.translations || {};
            
            this.init();
        }
        
        init() {
            if (!this.widget) {
                console.error('WisChat: Widget element not found');
                return;
            }
            
            this.bindEvents();
            this.setupWebSocket();
            this.showWidget();
            
            // Auto-open if configured
            if (this.config.settings.auto_open_delay > 0) {
                setTimeout(() => {
                    this.openWidget();
                }, this.config.settings.auto_open_delay * 1000);
            }
        }
        
        bindEvents() {
            // Trigger button
            const trigger = this.widget.querySelector('#wischat-trigger');
            if (trigger) {
                trigger.addEventListener('click', () => {
                    this.toggleWidget();
                });
            }
            
            // Close button
            const closeBtn = this.widget.querySelector('#wischat-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    this.closeWidget();
                });
            }
            
            // Minimize button
            const minimizeBtn = this.widget.querySelector('#wischat-minimize');
            if (minimizeBtn) {
                minimizeBtn.addEventListener('click', () => {
                    this.minimizeWidget();
                });
            }
            
            // Pre-chat form
            const preChatForm = this.widget.querySelector('#wischat-pre-chat-form');
            if (preChatForm) {
                preChatForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.submitPreChatForm();
                });
            }
            
            // Message input
            const messageInput = this.widget.querySelector('#wischat-message-input');
            if (messageInput) {
                messageInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        this.sendMessage();
                    }
                });
                
                messageInput.addEventListener('input', () => {
                    this.handleTyping();
                });
            }
            
            // Send button
            const sendBtn = this.widget.querySelector('#wischat-send-btn');
            if (sendBtn) {
                sendBtn.addEventListener('click', () => {
                    this.sendMessage();
                });
            }
            
            // File upload
            const fileInput = this.widget.querySelector('#wischat-file-input');
            const attachmentBtn = this.widget.querySelector('#wischat-attachment-btn');
            
            if (attachmentBtn && fileInput) {
                attachmentBtn.addEventListener('click', () => {
                    fileInput.click();
                });
                
                fileInput.addEventListener('change', (e) => {
                    this.handleFileUpload(e.target.files[0]);
                });
            }
        }
        
        setupWebSocket() {
            if (!this.config.websocketUrl || !this.config.apiKey) {
                console.warn('WisChat: WebSocket URL or API key not configured');
                return;
            }
            
            try {
                this.socket = new WebSocket(this.config.websocketUrl);
                
                this.socket.onopen = () => {
                    console.log('WisChat: WebSocket connected');
                    this.updateStatus('connected');
                    
                    // Authenticate
                    this.socket.send(JSON.stringify({
                        type: 'auth',
                        sessionId: this.getSessionId(),
                        apiKey: this.config.apiKey,
                        userInfo: this.config.userInfo
                    }));
                };
                
                this.socket.onmessage = (event) => {
                    this.handleWebSocketMessage(JSON.parse(event.data));
                };
                
                this.socket.onclose = () => {
                    console.log('WisChat: WebSocket disconnected');
                    this.updateStatus('disconnected');
                    
                    // Attempt to reconnect after 5 seconds
                    setTimeout(() => {
                        this.setupWebSocket();
                    }, 5000);
                };
                
                this.socket.onerror = (error) => {
                    console.error('WisChat: WebSocket error', error);
                    this.updateStatus('error');
                };
                
            } catch (error) {
                console.error('WisChat: Failed to setup WebSocket', error);
            }
        }
        
        handleWebSocketMessage(message) {
            switch (message.type) {
                case 'message':
                    this.displayMessage(message.data);
                    break;
                case 'typing_start':
                    this.showTypingIndicator(message.data.senderName);
                    break;
                case 'typing_stop':
                    this.hideTypingIndicator();
                    break;
                case 'agent_joined':
                    this.displaySystemMessage(this.translations.agentJoined);
                    break;
                case 'agent_left':
                    this.displaySystemMessage(this.translations.agentLeft);
                    break;
                case 'chat_ended':
                    this.displaySystemMessage(this.translations.chatEnded);
                    break;
                case 'status_update':
                    this.updateStatus(message.data.status);
                    break;
            }
        }
        
        showWidget() {
            this.widget.style.display = 'block';
        }
        
        toggleWidget() {
            if (this.isOpen) {
                this.closeWidget();
            } else {
                this.openWidget();
            }
        }
        
        openWidget() {
            const window = this.widget.querySelector('#wischat-window');
            if (window) {
                window.style.display = 'block';
                this.isOpen = true;
                this.isMinimized = false;
                
                // Focus message input if chat is active
                const messageInput = this.widget.querySelector('#wischat-message-input');
                if (messageInput && messageInput.style.display !== 'none') {
                    setTimeout(() => messageInput.focus(), 100);
                }
            }
        }
        
        closeWidget() {
            const window = this.widget.querySelector('#wischat-window');
            if (window) {
                window.style.display = 'none';
                this.isOpen = false;
                this.isMinimized = false;
            }
        }
        
        minimizeWidget() {
            this.closeWidget();
            this.isMinimized = true;
        }
        
        submitPreChatForm() {
            const form = this.widget.querySelector('#wischat-pre-chat-form');
            if (!form) return;
            
            const formData = new FormData(form);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            // Validate required fields
            if (!this.validatePreChatForm(data)) {
                return;
            }
            
            // Start chat session
            this.startChatSession(data);
        }
        
        validatePreChatForm(data) {
            const settings = this.config.settings;
            
            if (settings.pre_chat_fields.name.required && !data.name) {
                this.showError(this.translations.fillRequiredFields);
                return false;
            }
            
            if (settings.pre_chat_fields.email.required && !data.email) {
                this.showError(this.translations.fillRequiredFields);
                return false;
            }
            
            if (data.email && !this.isValidEmail(data.email)) {
                this.showError(this.translations.invalidEmail);
                return false;
            }
            
            if (settings.gdpr_compliance && !data.gdpr_consent) {
                this.showError(this.translations.fillRequiredFields);
                return false;
            }
            
            return true;
        }
        
        startChatSession(userData) {
            if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                this.socket.send(JSON.stringify({
                    type: 'start_chat',
                    data: userData
                }));
                
                // Hide pre-chat form and show chat
                this.showChatInterface();
            } else {
                this.showError(this.translations.connectionError);
            }
        }
        
        showChatInterface() {
            const preChat = this.widget.querySelector('#wischat-pre-chat');
            const messages = this.widget.querySelector('#wischat-messages');
            const inputArea = this.widget.querySelector('#wischat-input-area');
            
            if (preChat) preChat.style.display = 'none';
            if (messages) messages.style.display = 'flex';
            if (inputArea) inputArea.style.display = 'block';
        }
        
        sendMessage() {
            const messageInput = this.widget.querySelector('#wischat-message-input');
            if (!messageInput) return;
            
            const content = messageInput.value.trim();
            if (!content) {
                this.showError(this.translations.enterMessage);
                return;
            }
            
            if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                this.socket.send(JSON.stringify({
                    type: 'send_message',
                    data: {
                        content: content,
                        messageType: 'text'
                    }
                }));
                
                // Display message immediately
                this.displayMessage({
                    content: content,
                    senderType: 'visitor',
                    createdAt: new Date().toISOString()
                });
                
                messageInput.value = '';
                this.adjustTextareaHeight(messageInput);
            } else {
                this.showError(this.translations.connectionError);
            }
        }
        
        displayMessage(messageData) {
            const messagesContainer = this.widget.querySelector('#wischat-messages-container');
            if (!messagesContainer) return;
            
            const messageElement = this.createMessageElement(messageData);
            messagesContainer.appendChild(messageElement);
            
            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            // Play notification sound for agent messages
            if (messageData.senderType === 'agent' && this.config.settings.sound_notifications) {
                this.playNotificationSound();
            }
        }
        
        createMessageElement(messageData) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `wischat-message wischat-message-${messageData.senderType}`;
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'wischat-message-content';
            
            const bubbleDiv = document.createElement('div');
            bubbleDiv.className = 'wischat-message-bubble';
            bubbleDiv.textContent = messageData.content;
            
            contentDiv.appendChild(bubbleDiv);
            
            // Add timestamp
            if (messageData.createdAt) {
                const metaDiv = document.createElement('div');
                metaDiv.className = 'wischat-message-meta';
                metaDiv.textContent = this.formatTime(messageData.createdAt);
                contentDiv.appendChild(metaDiv);
            }
            
            messageDiv.appendChild(contentDiv);
            
            return messageDiv;
        }
        
        displaySystemMessage(content) {
            this.displayMessage({
                content: content,
                senderType: 'system',
                createdAt: new Date().toISOString()
            });
        }
        
        showTypingIndicator(senderName) {
            const typingIndicator = this.widget.querySelector('#wischat-typing-indicator');
            if (typingIndicator) {
                const textElement = typingIndicator.querySelector('.wischat-typing-text');
                if (textElement) {
                    textElement.textContent = `${senderName} ${this.translations.typing}`;
                }
                typingIndicator.style.display = 'flex';
            }
        }
        
        hideTypingIndicator() {
            const typingIndicator = this.widget.querySelector('#wischat-typing-indicator');
            if (typingIndicator) {
                typingIndicator.style.display = 'none';
            }
        }
        
        handleTyping() {
            if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                this.socket.send(JSON.stringify({
                    type: 'typing_start'
                }));
                
                // Stop typing after 3 seconds
                clearTimeout(this.typingTimeout);
                this.typingTimeout = setTimeout(() => {
                    this.socket.send(JSON.stringify({
                        type: 'typing_stop'
                    }));
                }, 3000);
            }
        }
        
        handleFileUpload(file) {
            if (!file) return;
            
            // Validate file
            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                this.showError(this.translations.fileTooLarge);
                return;
            }
            
            // Upload file
            this.uploadFile(file);
        }
        
        uploadFile(file) {
            const formData = new FormData();
            formData.append('file', file);
            
            fetch(`${this.config.apiEndpoint}/api/v1/upload/file`, {
                method: 'POST',
                headers: {
                    'X-API-Key': this.config.apiKey
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Send file message
                    this.sendFileMessage(data.file);
                } else {
                    this.showError(this.translations.fileUploadError);
                }
            })
            .catch(error => {
                console.error('File upload error:', error);
                this.showError(this.translations.fileUploadError);
            });
        }
        
        sendFileMessage(fileData) {
            if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                this.socket.send(JSON.stringify({
                    type: 'send_message',
                    data: {
                        content: fileData.originalName,
                        messageType: 'file',
                        fileUrl: fileData.url,
                        fileName: fileData.originalName,
                        fileSize: fileData.size
                    }
                }));
            }
        }
        
        updateStatus(status) {
            const statusElement = this.widget.querySelector('#wischat-status .wischat-status-text');
            const statusIndicator = this.widget.querySelector('.wischat-status-indicator');
            
            if (statusElement && statusIndicator) {
                switch (status) {
                    case 'connected':
                        statusElement.textContent = this.translations.online;
                        statusIndicator.style.backgroundColor = '#46b450';
                        break;
                    case 'disconnected':
                        statusElement.textContent = this.translations.offline;
                        statusIndicator.style.backgroundColor = '#dc3232';
                        break;
                    case 'connecting':
                        statusElement.textContent = this.translations.connecting;
                        statusIndicator.style.backgroundColor = '#ffb900';
                        break;
                }
            }
        }
        
        showError(message) {
            // Simple alert for now - could be enhanced with custom modal
            alert(message);
        }
        
        playNotificationSound() {
            if (this.config.settings.notification_sound_url) {
                const audio = new Audio(this.config.settings.notification_sound_url);
                audio.play().catch(e => console.log('Could not play notification sound'));
            }
        }
        
        getSessionId() {
            if (!this.sessionId) {
                this.sessionId = this.config.userInfo.sessionId || this.generateUUID();
            }
            return this.sessionId;
        }
        
        generateUUID() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                const r = Math.random() * 16 | 0;
                const v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }
        
        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
        
        adjustTextareaHeight(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 100) + 'px';
        }
    }
    
    // Global widget object
    window.WisChatWidget = {
        instances: {},
        
        init: function(widgetId) {
            if (!this.instances[widgetId]) {
                this.instances[widgetId] = new WisChatWidget(widgetId);
            }
            return this.instances[widgetId];
        }
    };
    
})();
