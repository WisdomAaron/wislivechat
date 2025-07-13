<?php
/**
 * WisChat License Management
 * 
 * Handles license validation and activation
 */

if (!defined('ABSPATH')) {
    exit;
}

class WisChat_License {
    
    private static $instance = null;
    private $license_server = 'https://api.wischat.com'; // Your license server
    private $product_id = 'wischat-wordpress';
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_init', array($this, 'check_license'));
        add_action('wp_ajax_wischat_activate_license', array($this, 'activate_license'));
        add_action('wp_ajax_wischat_deactivate_license', array($this, 'deactivate_license'));
    }
    
    /**
     * Check if license is valid
     */
    public function is_license_valid() {
        $license_data = get_option('wischat_license_data', array());
        
        if (empty($license_data['license_key']) || empty($license_data['status'])) {
            return false;
        }
        
        if ($license_data['status'] !== 'active') {
            return false;
        }
        
        // Check expiration
        if (isset($license_data['expires']) && $license_data['expires'] !== 'lifetime') {
            $expires = strtotime($license_data['expires']);
            if ($expires < time()) {
                return false;
            }
        }
        
        // Periodic validation (once per day)
        $last_check = get_option('wischat_license_last_check', 0);
        if (time() - $last_check > DAY_IN_SECONDS) {
            $this->validate_license_remote($license_data['license_key']);
        }
        
        return true;
    }
    
    /**
     * Get license status
     */
    public function get_license_status() {
        $license_data = get_option('wischat_license_data', array());
        
        if (empty($license_data)) {
            return array(
                'status' => 'inactive',
                'message' => __('No license key entered.', 'wischat')
            );
        }
        
        if (!$this->is_license_valid()) {
            return array(
                'status' => 'invalid',
                'message' => __('License is invalid or expired.', 'wischat')
            );
        }
        
        return array(
            'status' => 'active',
            'message' => __('License is active.', 'wischat'),
            'data' => $license_data
        );
    }
    
    /**
     * Activate license
     */
    public function activate_license() {
        check_ajax_referer('wischat_license_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.', 'wischat'));
        }
        
        $license_key = sanitize_text_field($_POST['license_key']);
        
        if (empty($license_key)) {
            wp_send_json_error(__('Please enter a license key.', 'wischat'));
        }
        
        $response = $this->validate_license_remote($license_key, 'activate');
        
        if ($response['success']) {
            update_option('wischat_license_data', $response['data']);
            update_option('wischat_license_last_check', time());
            wp_send_json_success(__('License activated successfully!', 'wischat'));
        } else {
            wp_send_json_error($response['message']);
        }
    }
    
    /**
     * Deactivate license
     */
    public function deactivate_license() {
        check_ajax_referer('wischat_license_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.', 'wischat'));
        }
        
        $license_data = get_option('wischat_license_data', array());
        
        if (!empty($license_data['license_key'])) {
            $this->validate_license_remote($license_data['license_key'], 'deactivate');
        }
        
        delete_option('wischat_license_data');
        delete_option('wischat_license_last_check');
        
        wp_send_json_success(__('License deactivated successfully!', 'wischat'));
    }
    
    /**
     * Validate license with remote server
     */
    private function validate_license_remote($license_key, $action = 'check') {
        $api_url = $this->license_server . '/api/v1/license/' . $action;
        
        $body = array(
            'license_key' => $license_key,
            'product_id' => $this->product_id,
            'domain' => home_url(),
            'version' => WISCHAT_VERSION
        );
        
        $response = wp_remote_post($api_url, array(
            'timeout' => 15,
            'body' => $body,
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded'
            )
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => __('Unable to connect to license server.', 'wischat')
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (empty($data)) {
            return array(
                'success' => false,
                'message' => __('Invalid response from license server.', 'wischat')
            );
        }
        
        if ($data['success']) {
            return array(
                'success' => true,
                'data' => array(
                    'license_key' => $license_key,
                    'status' => $data['license']['status'],
                    'expires' => $data['license']['expires'],
                    'sites_limit' => $data['license']['sites_limit'],
                    'activations' => $data['license']['activations']
                )
            );
        } else {
            return array(
                'success' => false,
                'message' => $data['message'] ?? __('License validation failed.', 'wischat')
            );
        }
    }
    
    /**
     * Check license periodically
     */
    public function check_license() {
        if (!$this->is_license_valid()) {
            add_action('admin_notices', array($this, 'license_notice'));
        }
    }
    
    /**
     * Show license notice
     */
    public function license_notice() {
        $license_status = $this->get_license_status();
        
        if ($license_status['status'] === 'active') {
            return;
        }
        
        $class = 'notice notice-error';
        $message = sprintf(
            __('WisChat License: %s <a href="%s">Activate your license</a> to use all features.', 'wischat'),
            $license_status['message'],
            admin_url('admin.php?page=wischat-license')
        );
        
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
    }
    
    /**
     * Render license page
     */
    public function render_license_page() {
        $license_status = $this->get_license_status();
        $license_data = get_option('wischat_license_data', array());
        ?>
        <div class="wrap">
            <h1><?php _e('WisChat License', 'wischat'); ?></h1>
            
            <div class="wischat-license-status">
                <?php if ($license_status['status'] === 'active'): ?>
                    <div class="notice notice-success">
                        <p><strong><?php _e('License Status: Active', 'wischat'); ?></strong></p>
                        <p><?php printf(__('License Key: %s', 'wischat'), esc_html(substr($license_data['license_key'], 0, 8) . '...')); ?></p>
                        <?php if (isset($license_data['expires']) && $license_data['expires'] !== 'lifetime'): ?>
                            <p><?php printf(__('Expires: %s', 'wischat'), esc_html(date('F j, Y', strtotime($license_data['expires'])))); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <form method="post" id="wischat-deactivate-license">
                        <?php wp_nonce_field('wischat_license_nonce', 'nonce'); ?>
                        <button type="submit" class="button button-secondary"><?php _e('Deactivate License', 'wischat'); ?></button>
                    </form>
                <?php else: ?>
                    <div class="notice notice-warning">
                        <p><strong><?php _e('License Status: Inactive', 'wischat'); ?></strong></p>
                        <p><?php echo esc_html($license_status['message']); ?></p>
                    </div>
                    
                    <form method="post" id="wischat-activate-license">
                        <?php wp_nonce_field('wischat_license_nonce', 'nonce'); ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('License Key', 'wischat'); ?></th>
                                <td>
                                    <input type="text" name="license_key" class="regular-text" 
                                           placeholder="<?php esc_attr_e('Enter your license key', 'wischat'); ?>" 
                                           value="<?php echo esc_attr($license_data['license_key'] ?? ''); ?>">
                                    <p class="description">
                                        <?php printf(
                                            __('Don\'t have a license? <a href="%s" target="_blank">Purchase one here</a>.', 'wischat'),
                                            'https://wischat.com/pricing'
                                        ); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                        <p class="submit">
                            <button type="submit" class="button button-primary"><?php _e('Activate License', 'wischat'); ?></button>
                        </p>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#wischat-activate-license').on('submit', function(e) {
                e.preventDefault();
                
                var $form = $(this);
                var $button = $form.find('button[type="submit"]');
                var originalText = $button.text();
                
                $button.text('<?php esc_js_e('Activating...', 'wischat'); ?>').prop('disabled', true);
                
                $.post(ajaxurl, {
                    action: 'wischat_activate_license',
                    license_key: $form.find('input[name="license_key"]').val(),
                    nonce: $form.find('input[name="nonce"]').val()
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data);
                        $button.text(originalText).prop('disabled', false);
                    }
                });
            });
            
            $('#wischat-deactivate-license').on('submit', function(e) {
                e.preventDefault();
                
                if (!confirm('<?php esc_js_e('Are you sure you want to deactivate your license?', 'wischat'); ?>')) {
                    return;
                }
                
                $.post(ajaxurl, {
                    action: 'wischat_deactivate_license',
                    nonce: $(this).find('input[name="nonce"]').val()
                }, function(response) {
                    location.reload();
                });
            });
        });
        </script>
        <?php
    }
}
