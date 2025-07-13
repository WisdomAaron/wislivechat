<?php
/**
 * WisChat Settings Class
 * 
 * Handles plugin settings and configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

class WisChat_Settings {
    
    private static $instance = null;
    private $settings = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_settings();
    }
    
    /**
     * Load settings from database
     */
    private function load_settings() {
        $default_settings = $this->get_default_settings();
        $saved_settings = get_option('wischat_settings', array());
        
        $this->settings = wp_parse_args($saved_settings, $default_settings);
    }
    
    /**
     * Get default settings
     */
    private function get_default_settings() {
        return array(
            'api_endpoint' => '',
            'api_key' => '',
            'widget_enabled' => true,
            'widget_position' => 'bottom-right',
            'widget_theme' => 'light',
            'primary_color' => '#007cba',
            'secondary_color' => '#ffffff',
            'text_color' => '#333333',
            'header_text' => __('Chat with us', 'wischat'),
            'welcome_message' => __('Hello! How can we help you today?', 'wischat'),
            'offline_message' => __('We are currently offline. Please leave a message and we\'ll get back to you.', 'wischat'),
            'placeholder_text' => __('Type your message...', 'wischat'),
            'send_button_text' => __('Send', 'wischat'),
            'minimize_text' => __('Minimize', 'wischat'),
            'close_text' => __('Close', 'wischat'),
            'show_agent_avatars' => true,
            'show_agent_names' => true,
            'enable_file_upload' => true,
            'enable_emoji' => true,
            'enable_typing_indicator' => true,
            'enable_read_receipts' => true,
            'require_name_email' => false,
            'name_field_label' => __('Your Name', 'wischat'),
            'email_field_label' => __('Your Email', 'wischat'),
            'gdpr_compliance' => true,
            'gdpr_message' => __('By using this chat, you agree to our privacy policy.', 'wischat'),
            'working_hours_enabled' => false,
            'working_hours' => array(
                'monday' => array('enabled' => true, 'start' => '09:00', 'end' => '17:00'),
                'tuesday' => array('enabled' => true, 'start' => '09:00', 'end' => '17:00'),
                'wednesday' => array('enabled' => true, 'start' => '09:00', 'end' => '17:00'),
                'thursday' => array('enabled' => true, 'start' => '09:00', 'end' => '17:00'),
                'friday' => array('enabled' => true, 'start' => '09:00', 'end' => '17:00'),
                'saturday' => array('enabled' => false, 'start' => '09:00', 'end' => '17:00'),
                'sunday' => array('enabled' => false, 'start' => '09:00', 'end' => '17:00')
            ),
            'timezone' => 'UTC',
            'custom_css' => '',
            'excluded_pages' => array(),
            'mobile_enabled' => true,
            'mobile_breakpoint' => 768,
            'sound_notifications' => true,
            'notification_sound_url' => '',
            'language' => 'en',
            'rtl_support' => false,
            'widget_width' => 350,
            'widget_height' => 500,
            'widget_border_radius' => 10,
            'widget_shadow' => true,
            'animation_enabled' => true,
            'auto_open_delay' => 0,
            'auto_open_pages' => array(),
            'trigger_rules' => array(),
            'department_enabled' => false,
            'departments' => array(),
            'pre_chat_form' => false,
            'pre_chat_fields' => array(
                'name' => array('enabled' => true, 'required' => true),
                'email' => array('enabled' => true, 'required' => true),
                'phone' => array('enabled' => false, 'required' => false),
                'department' => array('enabled' => false, 'required' => false),
                'message' => array('enabled' => true, 'required' => false)
            ),
            'post_chat_survey' => false,
            'survey_questions' => array(),
            'branding_enabled' => true,
            'company_logo' => '',
            'company_name' => get_bloginfo('name'),
            'powered_by_text' => __('Powered by WisChat', 'wischat'),
            'hide_powered_by' => false
        );
    }
    
    /**
     * Get all settings
     */
    public static function get_settings() {
        $instance = self::get_instance();
        return $instance->settings;
    }
    
    /**
     * Get specific setting
     */
    public static function get_setting($key, $default = null) {
        $instance = self::get_instance();
        return isset($instance->settings[$key]) ? $instance->settings[$key] : $default;
    }
    
    /**
     * Save settings
     */
    public static function save_settings($new_settings) {
        $instance = self::get_instance();
        
        // Sanitize settings
        $sanitized_settings = $instance->sanitize_settings($new_settings);
        
        // Merge with existing settings
        $instance->settings = wp_parse_args($sanitized_settings, $instance->settings);
        
        // Save to database
        $result = update_option('wischat_settings', $instance->settings);
        
        if ($result) {
            return array(
                'success' => true,
                'message' => __('Settings saved successfully.', 'wischat')
            );
        } else {
            return array(
                'success' => false,
                'message' => __('Failed to save settings.', 'wischat')
            );
        }
    }
    
    /**
     * Sanitize settings
     */
    private function sanitize_settings($settings) {
        $sanitized = array();
        
        // Text fields
        $text_fields = array(
            'api_endpoint', 'api_key', 'header_text', 'welcome_message', 
            'offline_message', 'placeholder_text', 'send_button_text',
            'minimize_text', 'close_text', 'name_field_label', 'email_field_label',
            'gdpr_message', 'timezone', 'custom_css', 'notification_sound_url',
            'language', 'company_name', 'powered_by_text', 'company_logo'
        );
        
        foreach ($text_fields as $field) {
            if (isset($settings[$field])) {
                $sanitized[$field] = sanitize_text_field($settings[$field]);
            }
        }
        
        // Textarea fields
        $textarea_fields = array('custom_css');
        foreach ($textarea_fields as $field) {
            if (isset($settings[$field])) {
                $sanitized[$field] = sanitize_textarea_field($settings[$field]);
            }
        }
        
        // URL fields
        $url_fields = array('api_endpoint', 'notification_sound_url', 'company_logo');
        foreach ($url_fields as $field) {
            if (isset($settings[$field])) {
                $sanitized[$field] = esc_url_raw($settings[$field]);
            }
        }
        
        // Color fields
        $color_fields = array('primary_color', 'secondary_color', 'text_color');
        foreach ($color_fields as $field) {
            if (isset($settings[$field])) {
                $sanitized[$field] = sanitize_hex_color($settings[$field]);
            }
        }
        
        // Boolean fields
        $boolean_fields = array(
            'widget_enabled', 'show_agent_avatars', 'show_agent_names',
            'enable_file_upload', 'enable_emoji', 'enable_typing_indicator',
            'enable_read_receipts', 'require_name_email', 'gdpr_compliance',
            'working_hours_enabled', 'mobile_enabled', 'sound_notifications',
            'rtl_support', 'widget_shadow', 'animation_enabled',
            'department_enabled', 'pre_chat_form', 'post_chat_survey',
            'branding_enabled', 'hide_powered_by'
        );
        
        foreach ($boolean_fields as $field) {
            if (isset($settings[$field])) {
                $sanitized[$field] = (bool) $settings[$field];
            }
        }
        
        // Integer fields
        $integer_fields = array(
            'mobile_breakpoint', 'widget_width', 'widget_height',
            'widget_border_radius', 'auto_open_delay'
        );
        
        foreach ($integer_fields as $field) {
            if (isset($settings[$field])) {
                $sanitized[$field] = absint($settings[$field]);
            }
        }
        
        // Select fields with predefined options
        if (isset($settings['widget_position'])) {
            $allowed_positions = array('bottom-right', 'bottom-left', 'top-right', 'top-left');
            if (in_array($settings['widget_position'], $allowed_positions)) {
                $sanitized['widget_position'] = $settings['widget_position'];
            }
        }
        
        if (isset($settings['widget_theme'])) {
            $allowed_themes = array('light', 'dark', 'auto');
            if (in_array($settings['widget_theme'], $allowed_themes)) {
                $sanitized['widget_theme'] = $settings['widget_theme'];
            }
        }
        
        // Array fields
        if (isset($settings['excluded_pages']) && is_array($settings['excluded_pages'])) {
            $sanitized['excluded_pages'] = array_map('absint', $settings['excluded_pages']);
        }
        
        if (isset($settings['auto_open_pages']) && is_array($settings['auto_open_pages'])) {
            $sanitized['auto_open_pages'] = array_map('absint', $settings['auto_open_pages']);
        }
        
        // Complex fields (working hours, departments, etc.)
        if (isset($settings['working_hours']) && is_array($settings['working_hours'])) {
            $sanitized['working_hours'] = $this->sanitize_working_hours($settings['working_hours']);
        }
        
        if (isset($settings['departments']) && is_array($settings['departments'])) {
            $sanitized['departments'] = $this->sanitize_departments($settings['departments']);
        }
        
        if (isset($settings['pre_chat_fields']) && is_array($settings['pre_chat_fields'])) {
            $sanitized['pre_chat_fields'] = $this->sanitize_pre_chat_fields($settings['pre_chat_fields']);
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize working hours
     */
    private function sanitize_working_hours($working_hours) {
        $sanitized = array();
        $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        
        foreach ($days as $day) {
            if (isset($working_hours[$day])) {
                $sanitized[$day] = array(
                    'enabled' => (bool) ($working_hours[$day]['enabled'] ?? false),
                    'start' => sanitize_text_field($working_hours[$day]['start'] ?? '09:00'),
                    'end' => sanitize_text_field($working_hours[$day]['end'] ?? '17:00')
                );
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize departments
     */
    private function sanitize_departments($departments) {
        $sanitized = array();
        
        foreach ($departments as $department) {
            if (isset($department['name'])) {
                $sanitized[] = array(
                    'id' => sanitize_key($department['id'] ?? ''),
                    'name' => sanitize_text_field($department['name']),
                    'description' => sanitize_text_field($department['description'] ?? ''),
                    'enabled' => (bool) ($department['enabled'] ?? true)
                );
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize pre-chat fields
     */
    private function sanitize_pre_chat_fields($fields) {
        $sanitized = array();
        $allowed_fields = array('name', 'email', 'phone', 'department', 'message');
        
        foreach ($allowed_fields as $field) {
            if (isset($fields[$field])) {
                $sanitized[$field] = array(
                    'enabled' => (bool) ($fields[$field]['enabled'] ?? false),
                    'required' => (bool) ($fields[$field]['required'] ?? false),
                    'label' => sanitize_text_field($fields[$field]['label'] ?? '')
                );
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Reset settings to defaults
     */
    public static function reset_settings() {
        $instance = self::get_instance();
        $instance->settings = $instance->get_default_settings();
        
        $result = update_option('wischat_settings', $instance->settings);
        
        if ($result) {
            return array(
                'success' => true,
                'message' => __('Settings reset to defaults successfully.', 'wischat')
            );
        } else {
            return array(
                'success' => false,
                'message' => __('Failed to reset settings.', 'wischat')
            );
        }
    }
    
    /**
     * Export settings
     */
    public static function export_settings() {
        $instance = self::get_instance();
        return json_encode($instance->settings, JSON_PRETTY_PRINT);
    }
    
    /**
     * Import settings
     */
    public static function import_settings($json_data) {
        $settings = json_decode($json_data, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array(
                'success' => false,
                'message' => __('Invalid JSON data.', 'wischat')
            );
        }
        
        return self::save_settings($settings);
    }
}
