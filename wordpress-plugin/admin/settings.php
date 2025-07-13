<?php
/**
 * WisChat Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

$admin = WisChat_Admin::get_instance();
$settings = WisChat_Settings::get_settings();
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

// Handle form submission
if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'wischat_settings')) {
    $new_settings = isset($_POST['wischat_settings']) ? $_POST['wischat_settings'] : array();
    $result = WisChat_Settings::save_settings($new_settings);
    
    if ($result['success']) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
        $settings = WisChat_Settings::get_settings(); // Reload settings
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
    }
}

// Handle test connection
if (isset($_POST['test_connection']) && wp_verify_nonce($_POST['_wpnonce'], 'wischat_settings')) {
    $api_endpoint = sanitize_url($_POST['api_endpoint']);
    $api_key = sanitize_text_field($_POST['api_key']);
    $test_result = WisChat_API::test_connection($api_endpoint, $api_key);
    
    if ($test_result['success']) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($test_result['message']) . '</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($test_result['message']) . '</p></div>';
    }
}
?>

<div class="wrap">
    <h1><?php _e('WisChat Settings', 'wischat'); ?></h1>
    
    <!-- API Status -->
    <div class="wischat-status-card">
        <?php
        $api_status = WisChat_API::get_instance()->get_api_status();
        $status_class = $api_status['status'] === 'connected' ? 'success' : ($api_status['status'] === 'error' ? 'error' : 'warning');
        ?>
        <div class="wischat-status-indicator wischat-status-<?php echo esc_attr($status_class); ?>">
            <span class="wischat-status-dot"></span>
            <strong><?php echo esc_html($api_status['message']); ?></strong>
        </div>
        
        <?php if ($api_status['status'] === 'connected' && isset($api_status['data'])): ?>
        <div class="wischat-status-details">
            <p><?php printf(__('Server Version: %s', 'wischat'), esc_html($api_status['data']['version'] ?? 'Unknown')); ?></p>
            <p><?php printf(__('Uptime: %s seconds', 'wischat'), esc_html($api_status['data']['uptime'] ?? '0')); ?></p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Settings Tabs -->
    <?php $admin->render_settings_tabs($current_tab); ?>
    
    <form method="post" action="">
        <?php wp_nonce_field('wischat_settings'); ?>
        
        <table class="form-table" role="presentation">
            <?php
            $fields = $admin->get_settings_fields($current_tab);
            foreach ($fields as $field_id => $field) {
                $admin->render_settings_field($field_id, $field);
            }
            ?>
        </table>
        
        <?php if ($current_tab === 'general'): ?>
        <div class="wischat-test-connection">
            <h3><?php _e('Test API Connection', 'wischat'); ?></h3>
            <p><?php _e('Test your API endpoint and key before saving.', 'wischat'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('API Endpoint', 'wischat'); ?></th>
                    <td>
                        <input type="url" id="test_api_endpoint" name="api_endpoint" value="<?php echo esc_attr($settings['api_endpoint']); ?>" class="regular-text" placeholder="https://api.yourserver.com">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('API Key', 'wischat'); ?></th>
                    <td>
                        <input type="password" id="test_api_key" name="api_key" value="<?php echo esc_attr($settings['api_key']); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" name="test_connection" class="button button-secondary">
                    <?php _e('Test Connection', 'wischat'); ?>
                </button>
            </p>
        </div>
        <?php endif; ?>
        
        <?php submit_button(); ?>
    </form>
    
    <!-- Quick Setup Guide -->
    <?php if (empty($settings['api_endpoint']) || empty($settings['api_key'])): ?>
    <div class="wischat-setup-guide">
        <h2><?php _e('Quick Setup Guide', 'wischat'); ?></h2>
        
        <div class="wischat-setup-steps">
            <div class="wischat-setup-step">
                <div class="wischat-setup-step-number">1</div>
                <div class="wischat-setup-step-content">
                    <h3><?php _e('Deploy Backend Server', 'wischat'); ?></h3>
                    <p><?php _e('Deploy the WisChat backend server to your hosting provider or cloud platform.', 'wischat'); ?></p>
                    <a href="https://docs.wischat.com/deployment" target="_blank" class="button button-secondary">
                        <?php _e('Deployment Guide', 'wischat'); ?>
                    </a>
                </div>
            </div>
            
            <div class="wischat-setup-step">
                <div class="wischat-setup-step-number">2</div>
                <div class="wischat-setup-step-content">
                    <h3><?php _e('Get API Credentials', 'wischat'); ?></h3>
                    <p><?php _e('Create an admin account and generate API credentials for your website.', 'wischat'); ?></p>
                    <a href="https://docs.wischat.com/api-setup" target="_blank" class="button button-secondary">
                        <?php _e('API Setup Guide', 'wischat'); ?>
                    </a>
                </div>
            </div>
            
            <div class="wischat-setup-step">
                <div class="wischat-setup-step-number">3</div>
                <div class="wischat-setup-step-content">
                    <h3><?php _e('Configure Plugin', 'wischat'); ?></h3>
                    <p><?php _e('Enter your API endpoint and key in the settings above, then customize the appearance.', 'wischat'); ?></p>
                </div>
            </div>
            
            <div class="wischat-setup-step">
                <div class="wischat-setup-step-number">4</div>
                <div class="wischat-setup-step-content">
                    <h3><?php _e('Install Mobile App', 'wischat'); ?></h3>
                    <p><?php _e('Download and install the WisChat mobile app to manage chats on the go.', 'wischat'); ?></p>
                    <div class="wischat-app-links">
                        <a href="#" class="button button-secondary"><?php _e('Download for Android', 'wischat'); ?></a>
                        <a href="#" class="button button-secondary"><?php _e('Download for iOS', 'wischat'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Widget Preview -->
    <?php if (!empty($settings['api_endpoint']) && !empty($settings['api_key'])): ?>
    <div class="wischat-widget-preview">
        <h2><?php _e('Widget Preview', 'wischat'); ?></h2>
        <p><?php _e('This is how your chat widget will appear on your website.', 'wischat'); ?></p>
        
        <div class="wischat-preview-container">
            <div class="wischat-preview-widget" style="
                --wischat-primary-color: <?php echo esc_attr($settings['primary_color']); ?>;
                --wischat-secondary-color: <?php echo esc_attr($settings['secondary_color']); ?>;
                --wischat-text-color: <?php echo esc_attr($settings['text_color']); ?>;
            ">
                <div class="wischat-preview-trigger">
                    <div class="wischat-preview-icon">💬</div>
                    <div class="wischat-preview-text"><?php echo esc_html($settings['header_text']); ?></div>
                </div>
            </div>
        </div>
        
        <p class="description">
            <?php _e('The actual widget will have full functionality including real-time messaging, file uploads, and more.', 'wischat'); ?>
        </p>
    </div>
    <?php endif; ?>
    
    <!-- Export/Import Settings -->
    <div class="wischat-export-import">
        <h2><?php _e('Export/Import Settings', 'wischat'); ?></h2>
        
        <div class="wischat-export-section">
            <h3><?php _e('Export Settings', 'wischat'); ?></h3>
            <p><?php _e('Export your current settings as a JSON file for backup or migration.', 'wischat'); ?></p>
            <button type="button" id="wischat-export-settings" class="button button-secondary">
                <?php _e('Export Settings', 'wischat'); ?>
            </button>
        </div>
        
        <div class="wischat-import-section">
            <h3><?php _e('Import Settings', 'wischat'); ?></h3>
            <p><?php _e('Import settings from a previously exported JSON file.', 'wischat'); ?></p>
            <input type="file" id="wischat-import-file" accept=".json" style="display: none;">
            <button type="button" id="wischat-import-settings" class="button button-secondary">
                <?php _e('Import Settings', 'wischat'); ?>
            </button>
        </div>
        
        <div class="wischat-reset-section">
            <h3><?php _e('Reset Settings', 'wischat'); ?></h3>
            <p><?php _e('Reset all settings to their default values. This action cannot be undone.', 'wischat'); ?></p>
            <button type="button" id="wischat-reset-settings" class="button button-secondary" 
                    onclick="return confirm('<?php esc_attr_e('Are you sure you want to reset all settings? This action cannot be undone.', 'wischat'); ?>')">
                <?php _e('Reset to Defaults', 'wischat'); ?>
            </button>
        </div>
    </div>
</div>

<style>
.wischat-status-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 15px;
    margin: 20px 0;
}

.wischat-status-indicator {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.wischat-status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.wischat-status-success .wischat-status-dot {
    background-color: #46b450;
}

.wischat-status-error .wischat-status-dot {
    background-color: #dc3232;
}

.wischat-status-warning .wischat-status-dot {
    background-color: #ffb900;
}

.wischat-setup-guide {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.wischat-setup-steps {
    display: grid;
    gap: 20px;
    margin-top: 20px;
}

.wischat-setup-step {
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.wischat-setup-step-number {
    background: #0073aa;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.wischat-setup-step-content h3 {
    margin: 0 0 10px 0;
}

.wischat-app-links {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.wischat-widget-preview {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.wischat-preview-container {
    background: #f0f0f1;
    border-radius: 4px;
    padding: 40px;
    text-align: center;
    position: relative;
    min-height: 200px;
}

.wischat-preview-widget {
    position: absolute;
    bottom: 20px;
    right: 20px;
}

.wischat-preview-trigger {
    background: var(--wischat-primary-color);
    color: white;
    padding: 12px 16px;
    border-radius: 25px;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: transform 0.2s;
}

.wischat-preview-trigger:hover {
    transform: translateY(-2px);
}

.wischat-export-import {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.wischat-export-section,
.wischat-import-section,
.wischat-reset-section {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.wischat-reset-section {
    border-bottom: none;
}

.wischat-test-connection {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Export settings
    $('#wischat-export-settings').on('click', function() {
        var settings = <?php echo json_encode($settings); ?>;
        var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(settings, null, 2));
        var downloadAnchorNode = document.createElement('a');
        downloadAnchorNode.setAttribute("href", dataStr);
        downloadAnchorNode.setAttribute("download", "wischat-settings.json");
        document.body.appendChild(downloadAnchorNode);
        downloadAnchorNode.click();
        downloadAnchorNode.remove();
    });
    
    // Import settings
    $('#wischat-import-settings').on('click', function() {
        $('#wischat-import-file').click();
    });
    
    $('#wischat-import-file').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                try {
                    var settings = JSON.parse(e.target.result);
                    if (confirm('<?php esc_js_e('Are you sure you want to import these settings? Current settings will be overwritten.', 'wischat'); ?>')) {
                        // Import settings via AJAX
                        $.post(ajaxurl, {
                            action: 'wischat_import_settings',
                            settings: JSON.stringify(settings),
                            nonce: '<?php echo wp_create_nonce('wischat_admin_nonce'); ?>'
                        }, function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert('<?php esc_js_e('Import failed:', 'wischat'); ?> ' + response.data);
                            }
                        });
                    }
                } catch (error) {
                    alert('<?php esc_js_e('Invalid JSON file.', 'wischat'); ?>');
                }
            };
            reader.readAsText(file);
        }
    });
    
    // Reset settings
    $('#wischat-reset-settings').on('click', function() {
        if (confirm('<?php esc_js_e('Are you sure you want to reset all settings? This action cannot be undone.', 'wischat'); ?>')) {
            $.post(ajaxurl, {
                action: 'wischat_reset_settings',
                nonce: '<?php echo wp_create_nonce('wischat_admin_nonce'); ?>'
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('<?php esc_js_e('Reset failed:', 'wischat'); ?> ' + response.data);
                }
            });
        }
    });
});
</script>
