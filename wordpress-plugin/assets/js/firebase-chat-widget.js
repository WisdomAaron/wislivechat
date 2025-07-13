/**
 * WisChat Firebase-Enabled Chat Widget
 * 
 * Real-time chat widget with Firebase integration
 */

(function() {
    'use strict';

    class WisChatWidget {
        constructor(config) {
            this.config = config;
            this.sessionId = this.generateSessionId();
            this.isOpen = false;
            this.firebase = null;
            this.database = null;
            this.messagesRef = null;
            this.unsubscribe = null;
            
            this.init();
        }

        async init() {
            try {
                // Initialize Firebase
                await this.initFirebase();
                
                // Create widget HTML
                this.createWidget();
                
                // Bind events
                this.bindEvents();
                
                // Create chat session
                await this.createChatSession();
                
                // Listen for messages
                this.listenForMessages();
                
                console.log('WisChat widget initialized successfully');
            } catch (error) {
                console.error('Failed to initialize WisChat widget:', error);
            }
        }

        async initFirebase() {
            if (typeof firebase === 'undefined') {
                throw new Error('Firebase SDK not loaded');
            }

            // Parse Firebase config
            let firebaseConfig;
            if (this.config.firebase_config) {
                try {
                    firebaseConfig = JSON.parse(this.config.firebase_config);
                } catch (e) {
                    // Fallback to individual config values
                    firebaseConfig = {
                        apiKey: this.config.firebase_api_key || 'AIzaSyAj-OZt3MnfRPJYt0dqeMVgIc4peJGQWJQ',
                        authDomain: this.config.firebase_auth_domain || 'wis-livechat.firebaseapp.com',
                        databaseURL: this.config.firebase_database_url || 'https://wis-livechat-default-rtdb.firebaseio.com',
                        projectId: this.config.firebase_project_id || 'wis-livechat',
                        storageBucket: this.config.firebase_storage_bucket || 'wis-livechat.firebasestorage.app',
                        messagingSenderId: this.config.firebase_messaging_sender_id || '206365667705',
                        appId: this.config.firebase_app_id || '1:206365667705:web:53b78c552588f354e87fa8'
                    };
                }
            }

            if (!firebaseConfig || !firebaseConfig.projectId) {
                throw new Error('Firebase configuration is incomplete');
            }

            // Initialize Firebase app
            if (!firebase.apps.length) {
                firebase.initializeApp(firebaseConfig);
            }

            this.database = firebase.database();
            this.messagesRef = this.database.ref(`chats/${this.sessionId}/messages`);
        }

        generateSessionId() {
            // Try to get existing session ID from localStorage
            let sessionId = localStorage.getItem('wischat_session_id');
            
            if (!sessionId) {
                // Generate new session ID
                sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                localStorage.setItem('wischat_session_id', sessionId);
            }
            
            return sessionId;
        }

        createWidget() {
            const widgetHTML = `
                <div id="wischat-widget" class="wischat-widget ${this.config.widget_position} ${this.config.widget_theme}">
                    <!-- Chat Button -->
                    <div id="wischat-button" class="wischat-button">
                        <div class="wischat-button-icon">💬</div>
                        <div class="wischat-unread-count" id="wischat-unread" style="display: none;">0</div>
                    </div>
                    
                    <!-- Chat Window -->
                    <div id="wischat-window" class="wischat-window" style="display: none;">
                        <div class="wischat-header">
                            <div class="wischat-header-info">
                                <div class="wischat-title">${this.config.company_name || 'Live Chat'}</div>
                                <div class="wischat-status">Online</div>
                            </div>
                            <div class="wischat-header-actions">
                                <button id="wischat-minimize" class="wischat-minimize">−</button>
                                <button id="wischat-close" class="wischat-close">×</button>
                            </div>
                        </div>
                        
                        <div class="wischat-messages" id="wischat-messages">
                            <div class="wischat-welcome-message">
                                ${this.config.welcome_message}
                            </div>
                        </div>
                        
                        <div class="wischat-input-container">
                            <input type="text" id="wischat-input" placeholder="${this.config.placeholder_message}" />
                            <button id="wischat-send" class="wischat-send-button">Send</button>
                        </div>
                    </div>
                </div>
            `;

            // Add widget to page
            document.body.insertAdjacentHTML('beforeend', widgetHTML);
            
            // Add styles
            this.addStyles();
        }

        addStyles() {
            const styles = `
                <style id="wischat-styles">
                .wischat-widget {
                    position: fixed;
                    z-index: 999999;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                }
                
                .wischat-widget.bottom-right {
                    bottom: 20px;
                    right: 20px;
                }
                
                .wischat-widget.bottom-left {
                    bottom: 20px;
                    left: 20px;
                }
                
                .wischat-widget.top-right {
                    top: 20px;
                    right: 20px;
                }
                
                .wischat-widget.top-left {
                    top: 20px;
                    left: 20px;
                }
                
                .wischat-button {
                    width: 60px;
                    height: 60px;
                    background: ${this.config.primary_color};
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    transition: all 0.3s ease;
                    position: relative;
                }
                
                .wischat-button:hover {
                    transform: scale(1.1);
                    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
                }
                
                .wischat-button-icon {
                    font-size: 24px;
                    color: ${this.config.text_color};
                }
                
                .wischat-unread-count {
                    position: absolute;
                    top: -5px;
                    right: -5px;
                    background: #ff4444;
                    color: white;
                    border-radius: 50%;
                    width: 20px;
                    height: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 12px;
                    font-weight: bold;
                }
                
                .wischat-window {
                    width: 350px;
                    height: 500px;
                    background: ${this.config.background_color};
                    border-radius: 12px;
                    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
                    display: flex;
                    flex-direction: column;
                    overflow: hidden;
                    margin-bottom: 10px;
                }
                
                .wischat-header {
                    background: ${this.config.primary_color};
                    color: ${this.config.text_color};
                    padding: 15px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .wischat-title {
                    font-weight: 600;
                    font-size: 16px;
                }
                
                .wischat-status {
                    font-size: 12px;
                    opacity: 0.8;
                }
                
                .wischat-header-actions {
                    display: flex;
                    gap: 5px;
                }
                
                .wischat-minimize,
                .wischat-close {
                    background: none;
                    border: none;
                    color: ${this.config.text_color};
                    font-size: 18px;
                    cursor: pointer;
                    padding: 5px;
                    border-radius: 4px;
                    opacity: 0.8;
                }
                
                .wischat-minimize:hover,
                .wischat-close:hover {
                    opacity: 1;
                    background: rgba(255,255,255,0.1);
                }
                
                .wischat-messages {
                    flex: 1;
                    padding: 15px;
                    overflow-y: auto;
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                }
                
                .wischat-welcome-message {
                    background: #f0f0f0;
                    padding: 10px;
                    border-radius: 8px;
                    font-size: 14px;
                    color: #666;
                }
                
                .wischat-message {
                    max-width: 80%;
                    padding: 10px 12px;
                    border-radius: 12px;
                    font-size: 14px;
                    line-height: 1.4;
                }
                
                .wischat-message.visitor {
                    background: ${this.config.primary_color};
                    color: ${this.config.text_color};
                    align-self: flex-end;
                    border-bottom-right-radius: 4px;
                }
                
                .wischat-message.admin {
                    background: #f0f0f0;
                    color: #333;
                    align-self: flex-start;
                    border-bottom-left-radius: 4px;
                }
                
                .wischat-message-time {
                    font-size: 11px;
                    opacity: 0.7;
                    margin-top: 4px;
                }
                
                .wischat-input-container {
                    padding: 15px;
                    border-top: 1px solid #eee;
                    display: flex;
                    gap: 10px;
                }
                
                .wischat-input-container input {
                    flex: 1;
                    padding: 10px 12px;
                    border: 1px solid #ddd;
                    border-radius: 20px;
                    outline: none;
                    font-size: 14px;
                }
                
                .wischat-input-container input:focus {
                    border-color: ${this.config.primary_color};
                }
                
                .wischat-send-button {
                    background: ${this.config.primary_color};
                    color: ${this.config.text_color};
                    border: none;
                    padding: 10px 16px;
                    border-radius: 20px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 500;
                }
                
                .wischat-send-button:hover {
                    opacity: 0.9;
                }
                
                .wischat-send-button:disabled {
                    opacity: 0.5;
                    cursor: not-allowed;
                }
                
                @media (max-width: 768px) {
                    .wischat-window {
                        width: calc(100vw - 40px);
                        height: calc(100vh - 100px);
                        max-width: 350px;
                        max-height: 500px;
                    }
                }
                </style>
            `;
            
            document.head.insertAdjacentHTML('beforeend', styles);
        }

        bindEvents() {
            const button = document.getElementById('wischat-button');
            const window = document.getElementById('wischat-window');
            const minimize = document.getElementById('wischat-minimize');
            const close = document.getElementById('wischat-close');
            const input = document.getElementById('wischat-input');
            const send = document.getElementById('wischat-send');

            button.addEventListener('click', () => this.toggleWidget());
            minimize.addEventListener('click', () => this.minimizeWidget());
            close.addEventListener('click', () => this.closeWidget());
            send.addEventListener('click', () => this.sendMessage());
            
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.sendMessage();
                }
            });
        }

        toggleWidget() {
            const window = document.getElementById('wischat-window');
            
            if (this.isOpen) {
                this.minimizeWidget();
            } else {
                window.style.display = 'flex';
                this.isOpen = true;
                
                // Reset unread count
                this.updateUnreadCount(0);
            }
        }

        minimizeWidget() {
            const window = document.getElementById('wischat-window');
            window.style.display = 'none';
            this.isOpen = false;
        }

        closeWidget() {
            this.minimizeWidget();
        }

        async createChatSession() {
            try {
                const response = await fetch(`${this.config.api_endpoint}/api/v1/firebase/chat/session`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${this.config.api_key}`
                    },
                    body: JSON.stringify({
                        sessionId: this.sessionId,
                        visitorId: this.sessionId,
                        websiteUrl: window.location.href,
                        visitorInfo: {
                            userAgent: navigator.userAgent,
                            referrer: document.referrer,
                            timestamp: Date.now()
                        }
                    })
                });

                if (!response.ok) {
                    throw new Error('Failed to create chat session');
                }

                console.log('Chat session created successfully');
            } catch (error) {
                console.error('Error creating chat session:', error);
            }
        }

        listenForMessages() {
            if (!this.messagesRef) return;

            this.messagesRef.on('child_added', (snapshot) => {
                const message = snapshot.val();
                if (message && message.senderType === 'admin') {
                    this.displayMessage(message);
                    
                    // Update unread count if widget is closed
                    if (!this.isOpen) {
                        this.incrementUnreadCount();
                    }
                }
            });
        }

        displayMessage(message) {
            const messagesContainer = document.getElementById('wischat-messages');
            const messageElement = document.createElement('div');
            messageElement.className = `wischat-message ${message.senderType}`;
            
            const time = new Date(message.timestamp).toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
            
            messageElement.innerHTML = `
                <div class="wischat-message-content">${this.escapeHtml(message.message)}</div>
                <div class="wischat-message-time">${time}</div>
            `;
            
            messagesContainer.appendChild(messageElement);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        async sendMessage() {
            const input = document.getElementById('wischat-input');
            const send = document.getElementById('wischat-send');
            const message = input.value.trim();
            
            if (!message) return;
            
            // Disable input while sending
            input.disabled = true;
            send.disabled = true;
            
            try {
                // Display message immediately
                this.displayMessage({
                    message: message,
                    senderType: 'visitor',
                    timestamp: Date.now()
                });
                
                // Send to Firebase via API
                const response = await fetch(`${this.config.api_endpoint}/api/v1/firebase/chat/message`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${this.config.api_key}`
                    },
                    body: JSON.stringify({
                        sessionId: this.sessionId,
                        senderId: this.sessionId,
                        senderType: 'visitor',
                        message: message,
                        senderName: 'Visitor'
                    })
                });

                if (!response.ok) {
                    throw new Error('Failed to send message');
                }
                
                // Clear input
                input.value = '';
                
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Failed to send message. Please try again.');
            } finally {
                // Re-enable input
                input.disabled = false;
                send.disabled = false;
                input.focus();
            }
        }

        updateUnreadCount(count) {
            const unreadElement = document.getElementById('wischat-unread');
            if (count > 0) {
                unreadElement.textContent = count;
                unreadElement.style.display = 'flex';
            } else {
                unreadElement.style.display = 'none';
            }
        }

        incrementUnreadCount() {
            const unreadElement = document.getElementById('wischat-unread');
            const current = parseInt(unreadElement.textContent) || 0;
            this.updateUnreadCount(current + 1);
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    // Initialize widget when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWidget);
    } else {
        initWidget();
    }

    function initWidget() {
        if (typeof wischatConfig !== 'undefined') {
            new WisChatWidget(wischatConfig);
        } else {
            console.error('WisChat configuration not found');
        }
    }

})();
