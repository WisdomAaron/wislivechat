<?php
/**
 * WisChat Widget Class
 * 
 * Handles the frontend chat widget rendering and functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class WisChat_Widget {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Widget is initialized when needed
    }
    
    /**
     * Render the chat widget
     */
    public static function render() {
        $settings = WisChat_Settings::get_settings();
        
        if (!$settings['widget_enabled']) {
            return;
        }
        
        $widget_id = 'wischat-widget-' . uniqid();
        $position_class = 'wischat-' . $settings['widget_position'];
        $theme_class = 'wischat-theme-' . $settings['widget_theme'];
        
        ?>
        <div id="<?php echo esc_attr($widget_id); ?>" class="wischat-widget <?php echo esc_attr($position_class); ?> <?php echo esc_attr($theme_class); ?>" style="display: none;">
            <!-- Widget Trigger Button -->
            <div class="wischat-trigger" id="wischat-trigger">
                <div class="wischat-trigger-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2ZM20 16H5.17L4 17.17V4H20V16Z" fill="currentColor"/>
                        <path d="M7 9H17V11H7V9ZM7 12H15V14H7V12Z" fill="currentColor"/>
                    </svg>
                </div>
                <div class="wischat-trigger-text"><?php echo esc_html($settings['header_text']); ?></div>
                <?php if ($settings['show_agent_avatars']): ?>
                <div class="wischat-trigger-avatars">
                    <!-- Agent avatars will be populated by JavaScript -->
                </div>
                <?php endif; ?>
                <div class="wischat-unread-count" style="display: none;">0</div>
            </div>
            
            <!-- Widget Window -->
            <div class="wischat-window" id="wischat-window" style="display: none;">
                <!-- Header -->
                <div class="wischat-header">
                    <div class="wischat-header-content">
                        <?php if ($settings['company_logo']): ?>
                        <div class="wischat-header-logo">
                            <img src="<?php echo esc_url($settings['company_logo']); ?>" alt="<?php echo esc_attr($settings['company_name']); ?>">
                        </div>
                        <?php endif; ?>
                        <div class="wischat-header-text">
                            <div class="wischat-header-title"><?php echo esc_html($settings['header_text']); ?></div>
                            <div class="wischat-header-status" id="wischat-status">
                                <span class="wischat-status-indicator"></span>
                                <span class="wischat-status-text"><?php _e('Online', 'wischat'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="wischat-header-actions">
                        <button class="wischat-minimize-btn" id="wischat-minimize" title="<?php echo esc_attr($settings['minimize_text']); ?>">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M4 8H12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <button class="wischat-close-btn" id="wischat-close" title="<?php echo esc_attr($settings['close_text']); ?>">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Pre-chat Form -->
                <?php if ($settings['pre_chat_form']): ?>
                <div class="wischat-pre-chat" id="wischat-pre-chat">
                    <div class="wischat-pre-chat-content">
                        <h3><?php _e('Start a conversation', 'wischat'); ?></h3>
                        <p><?php echo esc_html($settings['welcome_message']); ?></p>
                        
                        <form id="wischat-pre-chat-form">
                            <?php if ($settings['pre_chat_fields']['name']['enabled']): ?>
                            <div class="wischat-form-group">
                                <label for="wischat-name"><?php echo esc_html($settings['name_field_label']); ?></label>
                                <input type="text" id="wischat-name" name="name" <?php echo $settings['pre_chat_fields']['name']['required'] ? 'required' : ''; ?>>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($settings['pre_chat_fields']['email']['enabled']): ?>
                            <div class="wischat-form-group">
                                <label for="wischat-email"><?php echo esc_html($settings['email_field_label']); ?></label>
                                <input type="email" id="wischat-email" name="email" <?php echo $settings['pre_chat_fields']['email']['required'] ? 'required' : ''; ?>>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($settings['pre_chat_fields']['phone']['enabled']): ?>
                            <div class="wischat-form-group">
                                <label for="wischat-phone"><?php _e('Phone Number', 'wischat'); ?></label>
                                <input type="tel" id="wischat-phone" name="phone" <?php echo $settings['pre_chat_fields']['phone']['required'] ? 'required' : ''; ?>>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($settings['department_enabled'] && $settings['pre_chat_fields']['department']['enabled']): ?>
                            <div class="wischat-form-group">
                                <label for="wischat-department"><?php _e('Department', 'wischat'); ?></label>
                                <select id="wischat-department" name="department" <?php echo $settings['pre_chat_fields']['department']['required'] ? 'required' : ''; ?>>
                                    <option value=""><?php _e('Select Department', 'wischat'); ?></option>
                                    <?php foreach ($settings['departments'] as $dept): ?>
                                        <?php if ($dept['enabled']): ?>
                                        <option value="<?php echo esc_attr($dept['id']); ?>"><?php echo esc_html($dept['name']); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($settings['pre_chat_fields']['message']['enabled']): ?>
                            <div class="wischat-form-group">
                                <label for="wischat-initial-message"><?php _e('How can we help you?', 'wischat'); ?></label>
                                <textarea id="wischat-initial-message" name="message" rows="3" <?php echo $settings['pre_chat_fields']['message']['required'] ? 'required' : ''; ?>></textarea>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($settings['gdpr_compliance']): ?>
                            <div class="wischat-form-group wischat-gdpr">
                                <label class="wischat-checkbox-label">
                                    <input type="checkbox" id="wischat-gdpr" name="gdpr_consent" required>
                                    <span class="wischat-checkbox"></span>
                                    <?php echo esc_html($settings['gdpr_message']); ?>
                                </label>
                            </div>
                            <?php endif; ?>
                            
                            <button type="submit" class="wischat-btn wischat-btn-primary">
                                <?php _e('Start Chat', 'wischat'); ?>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Chat Messages -->
                <div class="wischat-messages" id="wischat-messages" <?php echo $settings['pre_chat_form'] ? 'style="display: none;"' : ''; ?>>
                    <div class="wischat-messages-container" id="wischat-messages-container">
                        <!-- Welcome message -->
                        <div class="wischat-message wischat-message-agent wischat-message-system">
                            <div class="wischat-message-content">
                                <div class="wischat-message-text"><?php echo esc_html($settings['welcome_message']); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Typing indicator -->
                    <?php if ($settings['enable_typing_indicator']): ?>
                    <div class="wischat-typing-indicator" id="wischat-typing-indicator" style="display: none;">
                        <div class="wischat-typing-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <span class="wischat-typing-text"><?php _e('Agent is typing...', 'wischat'); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Message Input -->
                <div class="wischat-input-area" id="wischat-input-area" <?php echo $settings['pre_chat_form'] ? 'style="display: none;"' : ''; ?>>
                    <?php if ($settings['enable_file_upload']): ?>
                    <div class="wischat-file-upload" style="display: none;">
                        <input type="file" id="wischat-file-input" accept="image/*,.pdf,.doc,.docx,.txt">
                        <div class="wischat-file-preview" id="wischat-file-preview"></div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="wischat-input-container">
                        <?php if ($settings['enable_file_upload']): ?>
                        <button class="wischat-attachment-btn" id="wischat-attachment-btn" title="<?php _e('Attach file', 'wischat'); ?>">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2L3 9L10 16L17 9L10 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <?php endif; ?>
                        
                        <div class="wischat-input-wrapper">
                            <textarea 
                                id="wischat-message-input" 
                                placeholder="<?php echo esc_attr($settings['placeholder_text']); ?>"
                                rows="1"
                                maxlength="<?php echo esc_attr(apply_filters('wischat_max_message_length', 1000)); ?>"
                            ></textarea>
                            
                            <?php if ($settings['enable_emoji']): ?>
                            <button class="wischat-emoji-btn" id="wischat-emoji-btn" title="<?php _e('Add emoji', 'wischat'); ?>">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2"/>
                                    <path d="M7 13C7 13 8.5 15 10 15S13 13 13 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <circle cx="7.5" cy="7.5" r="0.5" fill="currentColor"/>
                                    <circle cx="12.5" cy="7.5" r="0.5" fill="currentColor"/>
                                </svg>
                            </button>
                            <?php endif; ?>
                        </div>
                        
                        <button class="wischat-send-btn" id="wischat-send-btn" title="<?php echo esc_attr($settings['send_button_text']); ?>">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M18 2L9 11L4 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Powered by -->
                <?php if ($settings['branding_enabled'] && !$settings['hide_powered_by']): ?>
                <div class="wischat-powered-by">
                    <a href="https://wischat.com" target="_blank" rel="noopener">
                        <?php echo esc_html($settings['powered_by_text']); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Custom CSS -->
        <?php if (!empty($settings['custom_css'])): ?>
        <style type="text/css">
            <?php echo wp_strip_all_tags($settings['custom_css']); ?>
        </style>
        <?php endif; ?>
        
        <!-- Widget Styles -->
        <style type="text/css">
            :root {
                --wischat-primary-color: <?php echo esc_html($settings['primary_color']); ?>;
                --wischat-secondary-color: <?php echo esc_html($settings['secondary_color']); ?>;
                --wischat-text-color: <?php echo esc_html($settings['text_color']); ?>;
                --wischat-widget-width: <?php echo esc_html($settings['widget_width']); ?>px;
                --wischat-widget-height: <?php echo esc_html($settings['widget_height']); ?>px;
                --wischat-border-radius: <?php echo esc_html($settings['widget_border_radius']); ?>px;
            }
            
            <?php if ($settings['rtl_support'] && is_rtl()): ?>
            .wischat-widget {
                direction: rtl;
            }
            <?php endif; ?>
            
            @media (max-width: <?php echo esc_html($settings['mobile_breakpoint']); ?>px) {
                .wischat-widget {
                    --wischat-widget-width: 100%;
                    --wischat-widget-height: 100%;
                }
                
                .wischat-window {
                    position: fixed !important;
                    top: 0 !important;
                    left: 0 !important;
                    right: 0 !important;
                    bottom: 0 !important;
                    width: 100% !important;
                    height: 100% !important;
                    border-radius: 0 !important;
                }
            }
        </style>
        
        <script type="text/javascript">
            // Initialize widget when DOM is ready
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof WisChatWidget !== 'undefined') {
                    WisChatWidget.init('<?php echo esc_js($widget_id); ?>');
                }
            });
        </script>
        <?php
    }
    
    /**
     * Get widget configuration for JavaScript
     */
    public static function get_widget_config() {
        $settings = WisChat_Settings::get_settings();
        
        return array(
            'apiEndpoint' => $settings['api_endpoint'],
            'apiKey' => $settings['api_key'],
            'websocketUrl' => WisChat_API::get_instance()->get_websocket_url(),
            'settings' => $settings,
            'translations' => self::get_translations(),
            'userInfo' => self::get_user_info()
        );
    }
    
    /**
     * Get translations for JavaScript
     */
    private static function get_translations() {
        return array(
            'online' => __('Online', 'wischat'),
            'offline' => __('Offline', 'wischat'),
            'connecting' => __('Connecting...', 'wischat'),
            'connected' => __('Connected', 'wischat'),
            'disconnected' => __('Disconnected', 'wischat'),
            'typing' => __('is typing...', 'wischat'),
            'messageSent' => __('Message sent', 'wischat'),
            'messageDelivered' => __('Message delivered', 'wischat'),
            'messageRead' => __('Message read', 'wischat'),
            'fileUploading' => __('Uploading file...', 'wischat'),
            'fileUploaded' => __('File uploaded', 'wischat'),
            'fileUploadError' => __('File upload failed', 'wischat'),
            'connectionError' => __('Connection error', 'wischat'),
            'chatEnded' => __('Chat ended', 'wischat'),
            'agentJoined' => __('Agent joined the chat', 'wischat'),
            'agentLeft' => __('Agent left the chat', 'wischat'),
            'newMessage' => __('New message', 'wischat'),
            'chatStarted' => __('Chat started', 'wischat'),
            'enterMessage' => __('Please enter a message', 'wischat'),
            'fillRequiredFields' => __('Please fill in all required fields', 'wischat'),
            'invalidEmail' => __('Please enter a valid email address', 'wischat'),
            'fileTooLarge' => __('File is too large', 'wischat'),
            'fileTypeNotAllowed' => __('File type not allowed', 'wischat'),
            'chatUnavailable' => __('Chat is currently unavailable', 'wischat'),
            'tryAgainLater' => __('Please try again later', 'wischat')
        );
    }
    
    /**
     * Get user information
     */
    private static function get_user_info() {
        $user_info = array(
            'isLoggedIn' => is_user_logged_in(),
            'sessionId' => self::get_session_id(),
            'pageUrl' => get_permalink(),
            'pageTitle' => get_the_title(),
            'referrer' => wp_get_referer(),
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ipAddress' => self::get_client_ip(),
            'language' => get_locale(),
            'timezone' => wp_timezone_string()
        );
        
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $user_info['userId'] = $user->ID;
            $user_info['name'] = $user->display_name;
            $user_info['email'] = $user->user_email;
        }
        
        return $user_info;
    }
    
    /**
     * Get or create session ID
     */
    private static function get_session_id() {
        if (!session_id()) {
            session_start();
        }
        
        if (!isset($_SESSION['wischat_session_id'])) {
            $_SESSION['wischat_session_id'] = wp_generate_uuid4();
        }
        
        return $_SESSION['wischat_session_id'];
    }
    
    /**
     * Get client IP address
     */
    private static function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
