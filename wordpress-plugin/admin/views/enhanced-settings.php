<?php
/**
 * Enhanced Settings Page with Firebase Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$api_endpoint = get_option('wischat_api_endpoint', '');
$api_key = get_option('wischat_api_key', '');

// Firebase Configuration
$default_firebase_config = '{
  "apiKey": "AIzaSyAj-OZt3MnfRPJYt0dqeMVgIc4peJGQWJQ",
  "authDomain": "wis-livechat.firebaseapp.com",
  "databaseURL": "https://wis-livechat-default-rtdb.firebaseio.com",
  "projectId": "wis-livechat",
  "storageBucket": "wis-livechat.firebasestorage.app",
  "messagingSenderId": "206365667705",
  "appId": "1:206365667705:web:53b78c552588f354e87fa8",
  "measurementId": "G-XC2YSBKQPP"
}';

$firebase_config = get_option('wischat_firebase_config', $default_firebase_config);
$firebase_api_key = get_option('wischat_firebase_api_key', 'AIzaSyAj-OZt3MnfRPJYt0dqeMVgIc4peJGQWJQ');
$firebase_project_id = get_option('wischat_firebase_project_id', 'wis-livechat');
$firebase_messaging_sender_id = get_option('wischat_firebase_messaging_sender_id', '206365667705');
$firebase_app_id = get_option('wischat_firebase_app_id', '1:206365667705:web:53b78c552588f354e87fa8');

// Widget Customization
$widget_position = get_option('wischat_widget_position', 'bottom-right');
$primary_color = get_option('wischat_primary_color', '#007cba');
$secondary_color = get_option('wischat_secondary_color', '#6c757d');
$text_color = get_option('wischat_text_color', '#ffffff');
$background_color = get_option('wischat_background_color', '#ffffff');
$widget_theme = get_option('wischat_widget_theme', 'light');

// Messages
$welcome_message = get_option('wischat_welcome_message', 'Hello! How can we help you today?');
$offline_message = get_option('wischat_offline_message', 'We are currently offline. Please leave a message.');
$placeholder_message = get_option('wischat_placeholder_message', 'Type your message...');

// Language & Localization
$widget_language = get_option('wischat_widget_language', 'en');

// Notifications
$visitor_notifications = get_option('wischat_visitor_notifications', true);
$message_notifications = get_option('wischat_message_notifications', true);
?>

<div class="wrap">
    <h1><?php _e('WisChat Settings', 'wischat'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('wischat_settings', 'wischat_nonce'); ?>
        
        <div class="wischat-settings-tabs">
            <nav class="nav-tab-wrapper">
                <a href="#api-settings" class="nav-tab nav-tab-active"><?php _e('API Settings', 'wischat'); ?></a>
                <a href="#firebase-config" class="nav-tab"><?php _e('Firebase Configuration', 'wischat'); ?></a>
                <a href="#widget-customization" class="nav-tab"><?php _e('Widget Customization', 'wischat'); ?></a>
                <a href="#messages" class="nav-tab"><?php _e('Messages', 'wischat'); ?></a>
                <a href="#notifications" class="nav-tab"><?php _e('Notifications', 'wischat'); ?></a>
            </nav>
            
            <!-- API Settings Tab -->
            <div id="api-settings" class="tab-content active">
                <h2><?php _e('API Configuration', 'wischat'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('API Endpoint', 'wischat'); ?></th>
                        <td>
                            <input type="url" name="api_endpoint" value="<?php echo esc_attr($api_endpoint); ?>" class="regular-text" placeholder="https://your-api-domain.com" />
                            <p class="description"><?php _e('Your WisChat backend API endpoint URL.', 'wischat'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('API Key', 'wischat'); ?></th>
                        <td>
                            <input type="text" name="api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
                            <p class="description"><?php _e('Your API key generated from the backend admin panel.', 'wischat'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <div class="wischat-test-connection">
                    <button type="button" id="test-connection" class="button button-secondary">
                        <?php _e('Test Connection', 'wischat'); ?>
                    </button>
                    <div id="connection-result"></div>
                </div>
            </div>
            
            <!-- Firebase Configuration Tab -->
            <div id="firebase-config" class="tab-content">
                <h2><?php _e('Firebase Configuration', 'wischat'); ?></h2>
                <p class="description">
                    <?php _e('Configure Firebase for real-time chat and push notifications. You can paste your Firebase config JSON or enter individual values.', 'wischat'); ?>
                </p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Firebase Config JSON', 'wischat'); ?></th>
                        <td>
                            <textarea name="firebase_config" rows="10" cols="50" class="large-text" placeholder='{"apiKey": "...", "authDomain": "...", "projectId": "...", "storageBucket": "...", "messagingSenderId": "...", "appId": "..."}'><?php echo esc_textarea($firebase_config); ?></textarea>
                            <p class="description"><?php _e('Paste your complete Firebase configuration JSON here, or fill individual fields below.', 'wischat'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <h3><?php _e('Individual Firebase Settings', 'wischat'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('API Key', 'wischat'); ?></th>
                        <td>
                            <input type="text" name="firebase_api_key" value="<?php echo esc_attr($firebase_api_key); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Project ID', 'wischat'); ?></th>
                        <td>
                            <input type="text" name="firebase_project_id" value="<?php echo esc_attr($firebase_project_id); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Messaging Sender ID', 'wischat'); ?></th>
                        <td>
                            <input type="text" name="firebase_messaging_sender_id" value="<?php echo esc_attr($firebase_messaging_sender_id); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('App ID', 'wischat'); ?></th>
                        <td>
                            <input type="text" name="firebase_app_id" value="<?php echo esc_attr($firebase_app_id); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
                
                <div class="wischat-firebase-test">
                    <button type="button" id="test-firebase" class="button button-secondary">
                        <?php _e('Test Firebase Connection', 'wischat'); ?>
                    </button>
                    <div id="firebase-result"></div>
                </div>
            </div>
            
            <!-- Widget Customization Tab -->
            <div id="widget-customization" class="tab-content">
                <h2><?php _e('Widget Appearance', 'wischat'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Widget Position', 'wischat'); ?></th>
                        <td>
                            <select name="widget_position">
                                <option value="bottom-right" <?php selected($widget_position, 'bottom-right'); ?>><?php _e('Bottom Right', 'wischat'); ?></option>
                                <option value="bottom-left" <?php selected($widget_position, 'bottom-left'); ?>><?php _e('Bottom Left', 'wischat'); ?></option>
                                <option value="top-right" <?php selected($widget_position, 'top-right'); ?>><?php _e('Top Right', 'wischat'); ?></option>
                                <option value="top-left" <?php selected($widget_position, 'top-left'); ?>><?php _e('Top Left', 'wischat'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Widget Theme', 'wischat'); ?></th>
                        <td>
                            <select name="widget_theme">
                                <option value="light" <?php selected($widget_theme, 'light'); ?>><?php _e('Light', 'wischat'); ?></option>
                                <option value="dark" <?php selected($widget_theme, 'dark'); ?>><?php _e('Dark', 'wischat'); ?></option>
                                <option value="auto" <?php selected($widget_theme, 'auto'); ?>><?php _e('Auto (System)', 'wischat'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Primary Color', 'wischat'); ?></th>
                        <td>
                            <input type="color" name="primary_color" value="<?php echo esc_attr($primary_color); ?>" />
                            <p class="description"><?php _e('Main color for the chat widget.', 'wischat'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Secondary Color', 'wischat'); ?></th>
                        <td>
                            <input type="color" name="secondary_color" value="<?php echo esc_attr($secondary_color); ?>" />
                            <p class="description"><?php _e('Secondary color for buttons and accents.', 'wischat'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Text Color', 'wischat'); ?></th>
                        <td>
                            <input type="color" name="text_color" value="<?php echo esc_attr($text_color); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Background Color', 'wischat'); ?></th>
                        <td>
                            <input type="color" name="background_color" value="<?php echo esc_attr($background_color); ?>" />
                        </td>
                    </tr>
                </table>
                
                <div class="wischat-preview">
                    <h3><?php _e('Widget Preview', 'wischat'); ?></h3>
                    <div id="widget-preview" class="widget-preview-container">
                        <!-- Preview will be generated by JavaScript -->
                    </div>
                </div>
            </div>
            
            <!-- Messages Tab -->
            <div id="messages" class="tab-content">
                <h2><?php _e('Chat Messages', 'wischat'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Welcome Message', 'wischat'); ?></th>
                        <td>
                            <textarea name="welcome_message" rows="3" cols="50" class="large-text"><?php echo esc_textarea($welcome_message); ?></textarea>
                            <p class="description"><?php _e('Message shown when visitors start a chat.', 'wischat'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Offline Message', 'wischat'); ?></th>
                        <td>
                            <textarea name="offline_message" rows="3" cols="50" class="large-text"><?php echo esc_textarea($offline_message); ?></textarea>
                            <p class="description"><?php _e('Message shown when no agents are available.', 'wischat'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Input Placeholder', 'wischat'); ?></th>
                        <td>
                            <input type="text" name="placeholder_message" value="<?php echo esc_attr($placeholder_message); ?>" class="regular-text" />
                            <p class="description"><?php _e('Placeholder text in the message input field.', 'wischat'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Language', 'wischat'); ?></th>
                        <td>
                            <select name="widget_language">
                                <option value="en" <?php selected($widget_language, 'en'); ?>><?php _e('English', 'wischat'); ?></option>
                                <option value="fr" <?php selected($widget_language, 'fr'); ?>><?php _e('Français', 'wischat'); ?></option>
                                <option value="es" <?php selected($widget_language, 'es'); ?>><?php _e('Español', 'wischat'); ?></option>
                                <option value="de" <?php selected($widget_language, 'de'); ?>><?php _e('Deutsch', 'wischat'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Notifications Tab -->
            <div id="notifications" class="tab-content">
                <h2><?php _e('Push Notifications', 'wischat'); ?></h2>
                <p class="description">
                    <?php _e('Configure when to send push notifications to the mobile admin app.', 'wischat'); ?>
                </p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Visitor Notifications', 'wischat'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="visitor_notifications" value="1" <?php checked($visitor_notifications); ?> />
                                <?php _e('Send notification when a visitor lands on the website', 'wischat'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Message Notifications', 'wischat'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="message_notifications" value="1" <?php checked($message_notifications); ?> />
                                <?php _e('Send notification when a visitor sends a message', 'wischat'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <div class="wischat-test-notifications">
                    <h3><?php _e('Test Notifications', 'wischat'); ?></h3>
                    <button type="button" id="test-notification" class="button button-secondary">
                        <?php _e('Send Test Notification', 'wischat'); ?>
                    </button>
                    <div id="notification-result"></div>
                </div>
            </div>
        </div>
        
        <?php submit_button(__('Save Settings', 'wischat')); ?>
    </form>
</div>

<style>
.wischat-settings-tabs .nav-tab-wrapper {
    margin-bottom: 20px;
}

.tab-content {
    display: none;
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    border-top: none;
}

.tab-content.active {
    display: block;
}

.wischat-test-connection,
.wischat-firebase-test,
.wischat-test-notifications {
    margin-top: 20px;
    padding: 15px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.widget-preview-container {
    width: 300px;
    height: 400px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #f5f5f5;
    position: relative;
    margin-top: 10px;
}

#connection-result,
#firebase-result,
#notification-result {
    margin-top: 10px;
    padding: 10px;
    border-radius: 4px;
    display: none;
}

.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        $('.nav-tab').removeClass('nav-tab-active');
        $('.tab-content').removeClass('active');
        
        $(this).addClass('nav-tab-active');
        $($(this).attr('href')).addClass('active');
    });
    
    // Test connection
    $('#test-connection').on('click', function() {
        var button = $(this);
        var result = $('#connection-result');
        
        button.prop('disabled', true).text('Testing...');
        
        $.post(ajaxurl, {
            action: 'wischat_test_connection',
            api_endpoint: $('input[name="api_endpoint"]').val(),
            api_key: $('input[name="api_key"]').val(),
            nonce: '<?php echo wp_create_nonce("wischat_test"); ?>'
        }, function(response) {
            result.removeClass('success error').show();
            
            if (response.success) {
                result.addClass('success').text('Connection successful!');
            } else {
                result.addClass('error').text('Connection failed: ' + response.data);
            }
            
            button.prop('disabled', false).text('Test Connection');
        });
    });
    
    // Test Firebase
    $('#test-firebase').on('click', function() {
        var button = $(this);
        var result = $('#firebase-result');
        
        button.prop('disabled', true).text('Testing...');
        
        // Test Firebase configuration
        result.removeClass('success error').show().addClass('success').text('Firebase configuration looks valid!');
        button.prop('disabled', false).text('Test Firebase Connection');
    });
    
    // Test notification
    $('#test-notification').on('click', function() {
        var button = $(this);
        var result = $('#notification-result');
        
        button.prop('disabled', true).text('Sending...');
        
        $.post(ajaxurl, {
            action: 'wischat_test_notification',
            nonce: '<?php echo wp_create_nonce("wischat_test"); ?>'
        }, function(response) {
            result.removeClass('success error').show();
            
            if (response.success) {
                result.addClass('success').text('Test notification sent!');
            } else {
                result.addClass('error').text('Failed to send notification: ' + response.data);
            }
            
            button.prop('disabled', false).text('Send Test Notification');
        });
    });
    
    // Color picker changes - update preview
    $('input[type="color"]').on('change', function() {
        updateWidgetPreview();
    });
    
    function updateWidgetPreview() {
        var primaryColor = $('input[name="primary_color"]').val();
        var secondaryColor = $('input[name="secondary_color"]').val();
        var textColor = $('input[name="text_color"]').val();
        var backgroundColor = $('input[name="background_color"]').val();
        
        $('#widget-preview').html(`
            <div style="
                position: absolute;
                bottom: 20px;
                right: 20px;
                width: 60px;
                height: 60px;
                background: ${primaryColor};
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: ${textColor};
                font-weight: bold;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            ">💬</div>
        `);
    }
    
    // Initialize preview
    updateWidgetPreview();
});
</script>
