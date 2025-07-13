<?php
/**
 * Plugin Name: WisChat Live Chat
 * Plugin URI: https://wischat.com
 * Description: A comprehensive live chat solution with real-time messaging, mobile app integration, and advanced customization options.
 * Version: 1.0.0
 * Author: WisChat Team
 * Author URI: https://wischat.com
 * License: Commercial License
 * License URI: https://wischat.com/license
 * Text Domain: wischat
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 *
 * WisChat Live Chat Plugin
 * Copyright (C) 2024 WisChat Team
 *
 * This is a commercial plugin. Unauthorized distribution is prohibited.
 * For licensing information, visit: https://wischat.com/license
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WISCHAT_VERSION', '1.0.0');
define('WISCHAT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WISCHAT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WISCHAT_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main WisChat Plugin Class
 */
class WisChat {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array('WisChat', 'uninstall'));
        
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_footer', array($this, 'render_chat_widget'));
        add_action('wp_ajax_wischat_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_wischat_test_connection', array($this, 'ajax_test_connection'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once WISCHAT_PLUGIN_PATH . 'includes/class-wischat-settings.php';
        require_once WISCHAT_PLUGIN_PATH . 'includes/class-wischat-api.php';
        require_once WISCHAT_PLUGIN_PATH . 'includes/class-wischat-widget.php';
        require_once WISCHAT_PLUGIN_PATH . 'includes/class-wischat-admin.php';
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create default settings
        $default_settings = array(
            'api_endpoint' => '',
            'api_key' => '',
            'widget_enabled' => true,
            'widget_position' => 'bottom-right',
            'widget_theme' => 'light',
            'primary_color' => '#007cba',
            'header_text' => __('Chat with us', 'wischat'),
            'welcome_message' => __('Hello! How can we help you today?', 'wischat'),
            'offline_message' => __('We are currently offline. Please leave a message and we\'ll get back to you.', 'wischat'),
            'show_agent_avatars' => true,
            'enable_file_upload' => true,
            'enable_emoji' => true,
            'require_name_email' => false,
            'gdpr_compliance' => true,
            'working_hours_enabled' => false,
            'working_hours' => array(),
            'custom_css' => '',
            'excluded_pages' => array(),
            'mobile_enabled' => true,
            'sound_notifications' => true,
            'language' => 'en'
        );
        
        add_option('wischat_settings', $default_settings);
        add_option('wischat_version', WISCHAT_VERSION);
        
        // Create database tables if needed
        $this->create_tables();
        
        // Set activation flag
        set_transient('wischat_activation_notice', true, 30);
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up transients
        delete_transient('wischat_activation_notice');
    }
    
    /**
     * Plugin uninstall
     */
    public static function uninstall() {
        // Remove options
        delete_option('wischat_settings');
        delete_option('wischat_version');
        
        // Remove transients
        delete_transient('wischat_activation_notice');
        
        // Drop custom tables if any
        // Note: Be careful with this in production
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize components
        WisChat_Settings::get_instance();
        WisChat_API::get_instance();
        WisChat_Widget::get_instance();
        
        // Check for updates
        $this->check_version();
    }
    
    /**
     * Admin initialization
     */
    public function admin_init() {
        WisChat_Admin::get_instance();
        
        // Show activation notice
        if (get_transient('wischat_activation_notice')) {
            add_action('admin_notices', array($this, 'activation_notice'));
            delete_transient('wischat_activation_notice');
        }
    }
    
    /**
     * Add admin menu
     */
    public function admin_menu() {
        add_menu_page(
            __('WisChat', 'wischat'),
            __('WisChat', 'wischat'),
            'manage_options',
            'wischat',
            array($this, 'admin_page'),
            'dashicons-format-chat',
            30
        );
        
        add_submenu_page(
            'wischat',
            __('Settings', 'wischat'),
            __('Settings', 'wischat'),
            'manage_options',
            'wischat',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'wischat',
            __('Analytics', 'wischat'),
            __('Analytics', 'wischat'),
            'manage_options',
            'wischat-analytics',
            array($this, 'analytics_page')
        );
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        if (!$this->should_load_widget()) {
            return;
        }
        
        wp_enqueue_script(
            'wischat-widget',
            WISCHAT_PLUGIN_URL . 'assets/js/widget.js',
            array('jquery'),
            WISCHAT_VERSION,
            true
        );
        
        wp_enqueue_style(
            'wischat-widget',
            WISCHAT_PLUGIN_URL . 'assets/css/widget.css',
            array(),
            WISCHAT_VERSION
        );
        
        // Localize script with settings
        $settings = WisChat_Settings::get_settings();
        wp_localize_script('wischat-widget', 'wischat_config', array(
            'api_endpoint' => $settings['api_endpoint'],
            'api_key' => $settings['api_key'],
            'settings' => $settings,
            'nonce' => wp_create_nonce('wischat_nonce'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'user_info' => $this->get_user_info()
        ));
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'wischat') === false) {
            return;
        }
        
        wp_enqueue_script(
            'wischat-admin',
            WISCHAT_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker'),
            WISCHAT_VERSION,
            true
        );
        
        wp_enqueue_style(
            'wischat-admin',
            WISCHAT_PLUGIN_URL . 'assets/css/admin.css',
            array('wp-color-picker'),
            WISCHAT_VERSION
        );
        
        wp_localize_script('wischat-admin', 'wischat_admin', array(
            'nonce' => wp_create_nonce('wischat_admin_nonce'),
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    }
    
    /**
     * Render chat widget
     */
    public function render_chat_widget() {
        if (!$this->should_load_widget()) {
            return;
        }
        
        WisChat_Widget::render();
    }
    
    /**
     * Check if widget should be loaded
     */
    private function should_load_widget() {
        $settings = WisChat_Settings::get_settings();
        
        if (!$settings['widget_enabled'] || empty($settings['api_endpoint']) || empty($settings['api_key'])) {
            return false;
        }
        
        // Check excluded pages
        if (!empty($settings['excluded_pages'])) {
            $current_page_id = get_the_ID();
            if (in_array($current_page_id, $settings['excluded_pages'])) {
                return false;
            }
        }
        
        // Check mobile
        if (!$settings['mobile_enabled'] && wp_is_mobile()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get user information for the widget
     */
    private function get_user_info() {
        $user_info = array(
            'is_logged_in' => is_user_logged_in(),
            'session_id' => $this->get_session_id(),
            'page_url' => get_permalink(),
            'page_title' => get_the_title(),
            'referrer' => wp_get_referer(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => $this->get_client_ip()
        );
        
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $user_info['user_id'] = $user->ID;
            $user_info['name'] = $user->display_name;
            $user_info['email'] = $user->user_email;
        }
        
        return $user_info;
    }
    
    /**
     * Get or create session ID
     */
    private function get_session_id() {
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
    private function get_client_ip() {
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
    
    /**
     * Create database tables
     */
    private function create_tables() {
        // Add custom tables if needed
        // For now, we'll rely on the backend API database
    }
    
    /**
     * Check version and run updates if needed
     */
    private function check_version() {
        $current_version = get_option('wischat_version', '0.0.0');
        
        if (version_compare($current_version, WISCHAT_VERSION, '<')) {
            // Run update procedures
            $this->update_plugin($current_version);
            update_option('wischat_version', WISCHAT_VERSION);
        }
    }
    
    /**
     * Update plugin
     */
    private function update_plugin($from_version) {
        // Handle version-specific updates
    }
    
    /**
     * Load text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('wischat', false, dirname(WISCHAT_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Activation notice
     */
    public function activation_notice() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('WisChat has been activated! Please configure your settings to get started.', 'wischat'); ?></p>
            <p><a href="<?php echo admin_url('admin.php?page=wischat'); ?>" class="button button-primary"><?php _e('Configure Settings', 'wischat'); ?></a></p>
        </div>
        <?php
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        include WISCHAT_PLUGIN_PATH . 'admin/settings.php';
    }
    
    /**
     * Analytics page
     */
    public function analytics_page() {
        include WISCHAT_PLUGIN_PATH . 'admin/analytics.php';
    }
    
    /**
     * AJAX save settings
     */
    public function ajax_save_settings() {
        check_ajax_referer('wischat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'wischat'));
        }
        
        $settings = $_POST['settings'] ?? array();
        $result = WisChat_Settings::save_settings($settings);
        
        wp_send_json($result);
    }
    
    /**
     * AJAX test connection
     */
    public function ajax_test_connection() {
        check_ajax_referer('wischat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'wischat'));
        }
        
        $api_endpoint = $_POST['api_endpoint'] ?? '';
        $api_key = $_POST['api_key'] ?? '';
        
        $result = WisChat_API::test_connection($api_endpoint, $api_key);
        
        wp_send_json($result);
    }
}

// Initialize the plugin
WisChat::get_instance();
