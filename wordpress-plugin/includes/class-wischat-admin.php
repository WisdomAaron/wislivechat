<?php
/**
 * WisChat Admin Class
 * 
 * Handles WordPress admin functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class WisChat_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize admin hooks
     */
    private function init_hooks() {
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('admin_init', array($this, 'register_settings'));
        add_filter('plugin_action_links_' . WISCHAT_PLUGIN_BASENAME, array($this, 'plugin_action_links'));
        add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2);
    }
    
    /**
     * Display admin notices
     */
    public function admin_notices() {
        $settings = WisChat_Settings::get_settings();
        
        // Configuration notice
        if (empty($settings['api_endpoint']) || empty($settings['api_key'])) {
            $this->show_notice(
                sprintf(
                    __('WisChat is not configured yet. <a href="%s">Configure it now</a> to start using live chat.', 'wischat'),
                    admin_url('admin.php?page=wischat')
                ),
                'warning'
            );
        }
        
        // API connection notice
        if (!empty($settings['api_endpoint']) && !empty($settings['api_key'])) {
            $api_status = WisChat_API::get_instance()->get_api_status();
            
            if ($api_status['status'] === 'error') {
                $this->show_notice(
                    sprintf(
                        __('WisChat API connection failed: %s. <a href="%s">Check your settings</a>.', 'wischat'),
                        $api_status['message'],
                        admin_url('admin.php?page=wischat')
                    ),
                    'error'
                );
            }
        }
    }
    
    /**
     * Show admin notice
     */
    private function show_notice($message, $type = 'info') {
        $class = 'notice notice-' . $type;
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('wischat_settings', 'wischat_settings', array(
            'sanitize_callback' => array($this, 'sanitize_settings')
        ));
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        // This will be handled by WisChat_Settings::save_settings()
        return $input;
    }
    
    /**
     * Add plugin action links
     */
    public function plugin_action_links($links) {
        $action_links = array(
            'settings' => '<a href="' . admin_url('admin.php?page=wischat') . '">' . __('Settings', 'wischat') . '</a>',
            'analytics' => '<a href="' . admin_url('admin.php?page=wischat-analytics') . '">' . __('Analytics', 'wischat') . '</a>',
        );
        
        return array_merge($action_links, $links);
    }
    
    /**
     * Add plugin row meta
     */
    public function plugin_row_meta($links, $file) {
        if ($file === WISCHAT_PLUGIN_BASENAME) {
            $row_meta = array(
                'docs' => '<a href="https://docs.wischat.com" target="_blank">' . __('Documentation', 'wischat') . '</a>',
                'support' => '<a href="https://support.wischat.com" target="_blank">' . __('Support', 'wischat') . '</a>',
                'rate' => '<a href="https://wordpress.org/plugins/wischat/#reviews" target="_blank">' . __('Rate Plugin', 'wischat') . '</a>',
            );
            
            return array_merge($links, $row_meta);
        }
        
        return $links;
    }
    
    /**
     * Get dashboard widget data
     */
    public function get_dashboard_data() {
        $api = WisChat_API::get_instance();
        
        // Get analytics data
        $analytics = $api->get_analytics();
        $active_chats = $api->get_active_chats();
        
        return array(
            'analytics' => $analytics['success'] ? $analytics['data'] : null,
            'active_chats' => $active_chats['success'] ? $active_chats['data'] : null,
            'api_status' => $api->get_api_status()
        );
    }
    
    /**
     * Render settings tabs
     */
    public function render_settings_tabs($current_tab = 'general') {
        $tabs = array(
            'general' => __('General', 'wischat'),
            'appearance' => __('Appearance', 'wischat'),
            'behavior' => __('Behavior', 'wischat'),
            'notifications' => __('Notifications', 'wischat'),
            'advanced' => __('Advanced', 'wischat')
        );
        
        echo '<nav class="nav-tab-wrapper woo-nav-tab-wrapper">';
        foreach ($tabs as $tab_key => $tab_name) {
            $active = $current_tab === $tab_key ? 'nav-tab-active' : '';
            echo '<a href="?page=wischat&tab=' . esc_attr($tab_key) . '" class="nav-tab ' . esc_attr($active) . '">' . esc_html($tab_name) . '</a>';
        }
        echo '</nav>';
    }
    
    /**
     * Get settings fields for a tab
     */
    public function get_settings_fields($tab) {
        $fields = array();
        
        switch ($tab) {
            case 'general':
                $fields = array(
                    'api_endpoint' => array(
                        'title' => __('API Endpoint', 'wischat'),
                        'type' => 'url',
                        'description' => __('The URL of your WisChat backend server.', 'wischat'),
                        'placeholder' => 'https://api.yourserver.com'
                    ),
                    'api_key' => array(
                        'title' => __('API Key', 'wischat'),
                        'type' => 'password',
                        'description' => __('Your unique API key for authentication.', 'wischat')
                    ),
                    'widget_enabled' => array(
                        'title' => __('Enable Widget', 'wischat'),
                        'type' => 'checkbox',
                        'description' => __('Enable the chat widget on your website.', 'wischat')
                    ),
                    'header_text' => array(
                        'title' => __('Header Text', 'wischat'),
                        'type' => 'text',
                        'description' => __('Text displayed in the chat widget header.', 'wischat')
                    ),
                    'welcome_message' => array(
                        'title' => __('Welcome Message', 'wischat'),
                        'type' => 'textarea',
                        'description' => __('Initial message shown to visitors.', 'wischat')
                    )
                );
                break;
                
            case 'appearance':
                $fields = array(
                    'widget_position' => array(
                        'title' => __('Widget Position', 'wischat'),
                        'type' => 'select',
                        'options' => array(
                            'bottom-right' => __('Bottom Right', 'wischat'),
                            'bottom-left' => __('Bottom Left', 'wischat'),
                            'top-right' => __('Top Right', 'wischat'),
                            'top-left' => __('Top Left', 'wischat')
                        ),
                        'description' => __('Position of the chat widget on the page.', 'wischat')
                    ),
                    'widget_theme' => array(
                        'title' => __('Widget Theme', 'wischat'),
                        'type' => 'select',
                        'options' => array(
                            'light' => __('Light', 'wischat'),
                            'dark' => __('Dark', 'wischat'),
                            'auto' => __('Auto (follows system)', 'wischat')
                        ),
                        'description' => __('Color theme for the chat widget.', 'wischat')
                    ),
                    'primary_color' => array(
                        'title' => __('Primary Color', 'wischat'),
                        'type' => 'color',
                        'description' => __('Main color used in the chat widget.', 'wischat')
                    ),
                    'secondary_color' => array(
                        'title' => __('Secondary Color', 'wischat'),
                        'type' => 'color',
                        'description' => __('Secondary color used in the chat widget.', 'wischat')
                    ),
                    'widget_width' => array(
                        'title' => __('Widget Width', 'wischat'),
                        'type' => 'number',
                        'description' => __('Width of the chat widget in pixels.', 'wischat'),
                        'min' => 300,
                        'max' => 600
                    ),
                    'widget_height' => array(
                        'title' => __('Widget Height', 'wischat'),
                        'type' => 'number',
                        'description' => __('Height of the chat widget in pixels.', 'wischat'),
                        'min' => 400,
                        'max' => 800
                    )
                );
                break;
                
            case 'behavior':
                $fields = array(
                    'require_name_email' => array(
                        'title' => __('Require Name & Email', 'wischat'),
                        'type' => 'checkbox',
                        'description' => __('Require visitors to provide name and email before chatting.', 'wischat')
                    ),
                    'enable_file_upload' => array(
                        'title' => __('Enable File Upload', 'wischat'),
                        'type' => 'checkbox',
                        'description' => __('Allow visitors to upload files in chat.', 'wischat')
                    ),
                    'enable_emoji' => array(
                        'title' => __('Enable Emoji', 'wischat'),
                        'type' => 'checkbox',
                        'description' => __('Enable emoji picker in chat.', 'wischat')
                    ),
                    'mobile_enabled' => array(
                        'title' => __('Enable on Mobile', 'wischat'),
                        'type' => 'checkbox',
                        'description' => __('Show chat widget on mobile devices.', 'wischat')
                    ),
                    'auto_open_delay' => array(
                        'title' => __('Auto Open Delay', 'wischat'),
                        'type' => 'number',
                        'description' => __('Automatically open chat after X seconds (0 = disabled).', 'wischat'),
                        'min' => 0,
                        'max' => 300
                    )
                );
                break;
                
            case 'notifications':
                $fields = array(
                    'sound_notifications' => array(
                        'title' => __('Sound Notifications', 'wischat'),
                        'type' => 'checkbox',
                        'description' => __('Play sound when new messages arrive.', 'wischat')
                    ),
                    'notification_sound_url' => array(
                        'title' => __('Custom Sound URL', 'wischat'),
                        'type' => 'url',
                        'description' => __('URL to custom notification sound file.', 'wischat')
                    )
                );
                break;
                
            case 'advanced':
                $fields = array(
                    'custom_css' => array(
                        'title' => __('Custom CSS', 'wischat'),
                        'type' => 'textarea',
                        'description' => __('Add custom CSS to style the chat widget.', 'wischat'),
                        'rows' => 10
                    ),
                    'excluded_pages' => array(
                        'title' => __('Excluded Pages', 'wischat'),
                        'type' => 'multiselect',
                        'description' => __('Pages where the chat widget should not appear.', 'wischat'),
                        'options' => $this->get_pages_options()
                    ),
                    'gdpr_compliance' => array(
                        'title' => __('GDPR Compliance', 'wischat'),
                        'type' => 'checkbox',
                        'description' => __('Show GDPR consent checkbox.', 'wischat')
                    ),
                    'gdpr_message' => array(
                        'title' => __('GDPR Message', 'wischat'),
                        'type' => 'textarea',
                        'description' => __('GDPR consent message text.', 'wischat')
                    )
                );
                break;
        }
        
        return apply_filters('wischat_settings_fields_' . $tab, $fields);
    }
    
    /**
     * Get pages options for select fields
     */
    private function get_pages_options() {
        $pages = get_pages();
        $options = array();
        
        foreach ($pages as $page) {
            $options[$page->ID] = $page->post_title;
        }
        
        return $options;
    }
    
    /**
     * Render settings field
     */
    public function render_settings_field($field_id, $field) {
        $settings = WisChat_Settings::get_settings();
        $value = isset($settings[$field_id]) ? $settings[$field_id] : '';
        
        echo '<tr>';
        echo '<th scope="row"><label for="' . esc_attr($field_id) . '">' . esc_html($field['title']) . '</label></th>';
        echo '<td>';
        
        switch ($field['type']) {
            case 'text':
            case 'url':
            case 'email':
                echo '<input type="' . esc_attr($field['type']) . '" id="' . esc_attr($field_id) . '" name="wischat_settings[' . esc_attr($field_id) . ']" value="' . esc_attr($value) . '" class="regular-text"';
                if (isset($field['placeholder'])) {
                    echo ' placeholder="' . esc_attr($field['placeholder']) . '"';
                }
                echo '>';
                break;
                
            case 'password':
                echo '<input type="password" id="' . esc_attr($field_id) . '" name="wischat_settings[' . esc_attr($field_id) . ']" value="' . esc_attr($value) . '" class="regular-text">';
                break;
                
            case 'number':
                echo '<input type="number" id="' . esc_attr($field_id) . '" name="wischat_settings[' . esc_attr($field_id) . ']" value="' . esc_attr($value) . '" class="small-text"';
                if (isset($field['min'])) {
                    echo ' min="' . esc_attr($field['min']) . '"';
                }
                if (isset($field['max'])) {
                    echo ' max="' . esc_attr($field['max']) . '"';
                }
                echo '>';
                break;
                
            case 'textarea':
                $rows = isset($field['rows']) ? $field['rows'] : 3;
                echo '<textarea id="' . esc_attr($field_id) . '" name="wischat_settings[' . esc_attr($field_id) . ']" rows="' . esc_attr($rows) . '" class="large-text">' . esc_textarea($value) . '</textarea>';
                break;
                
            case 'checkbox':
                echo '<input type="checkbox" id="' . esc_attr($field_id) . '" name="wischat_settings[' . esc_attr($field_id) . ']" value="1"' . checked($value, true, false) . '>';
                echo '<label for="' . esc_attr($field_id) . '">' . esc_html($field['description']) . '</label>';
                break;
                
            case 'select':
                echo '<select id="' . esc_attr($field_id) . '" name="wischat_settings[' . esc_attr($field_id) . ']">';
                foreach ($field['options'] as $option_value => $option_label) {
                    echo '<option value="' . esc_attr($option_value) . '"' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
                }
                echo '</select>';
                break;
                
            case 'color':
                echo '<input type="color" id="' . esc_attr($field_id) . '" name="wischat_settings[' . esc_attr($field_id) . ']" value="' . esc_attr($value) . '" class="color-picker">';
                break;
        }
        
        if (isset($field['description']) && $field['type'] !== 'checkbox') {
            echo '<p class="description">' . esc_html($field['description']) . '</p>';
        }
        
        echo '</td>';
        echo '</tr>';
    }
}
