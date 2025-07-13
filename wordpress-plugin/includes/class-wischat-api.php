<?php
/**
 * WisChat API Class
 * 
 * Handles communication with the WisChat backend API
 */

if (!defined('ABSPATH')) {
    exit;
}

class WisChat_API {
    
    private static $instance = null;
    private $api_endpoint;
    private $api_key;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $settings = WisChat_Settings::get_settings();
        $this->api_endpoint = rtrim($settings['api_endpoint'], '/');
        $this->api_key = $settings['api_key'];
    }
    
    /**
     * Test API connection
     */
    public static function test_connection($api_endpoint = null, $api_key = null) {
        $instance = self::get_instance();
        
        $endpoint = $api_endpoint ?: $instance->api_endpoint;
        $key = $api_key ?: $instance->api_key;
        
        if (empty($endpoint) || empty($key)) {
            return array(
                'success' => false,
                'message' => __('API endpoint and key are required.', 'wischat')
            );
        }
        
        $response = $instance->make_request('GET', '/health', array(), $endpoint, $key);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => sprintf(__('Connection failed: %s', 'wischat'), $response->get_error_message())
            );
        }
        
        if ($response['status'] === 'OK') {
            return array(
                'success' => true,
                'message' => __('Connection successful!', 'wischat'),
                'data' => $response
            );
        } else {
            return array(
                'success' => false,
                'message' => __('Invalid response from server.', 'wischat')
            );
        }
    }
    
    /**
     * Register website with the API
     */
    public function register_website() {
        $website_data = array(
            'website_domain' => home_url(),
            'website_name' => get_bloginfo('name'),
            'website_description' => get_bloginfo('description'),
            'admin_email' => get_option('admin_email'),
            'wordpress_version' => get_bloginfo('version'),
            'plugin_version' => WISCHAT_VERSION
        );
        
        $response = $this->make_request('POST', '/api/v1/settings', $website_data);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => sprintf(__('Registration failed: %s', 'wischat'), $response->get_error_message())
            );
        }
        
        return array(
            'success' => true,
            'message' => __('Website registered successfully!', 'wischat'),
            'data' => $response
        );
    }
    
    /**
     * Get chat analytics
     */
    public function get_analytics($start_date = null, $end_date = null) {
        $params = array();
        
        if ($start_date) {
            $params['startDate'] = $start_date;
        }
        
        if ($end_date) {
            $params['endDate'] = $end_date;
        }
        
        $response = $this->make_request('GET', '/api/v1/analytics/dashboard', $params);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        return array(
            'success' => true,
            'data' => $response
        );
    }
    
    /**
     * Get active chat sessions
     */
    public function get_active_chats() {
        $response = $this->make_request('GET', '/api/v1/chat/sessions', array('status' => 'active'));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        return array(
            'success' => true,
            'data' => $response
        );
    }
    
    /**
     * Send a message via API
     */
    public function send_message($session_id, $message, $sender_type = 'visitor') {
        $message_data = array(
            'content' => $message,
            'messageType' => 'text',
            'senderType' => $sender_type
        );
        
        $response = $this->make_request('POST', "/api/v1/chat/sessions/{$session_id}/messages", $message_data);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        return array(
            'success' => true,
            'data' => $response
        );
    }
    
    /**
     * Create a new chat session
     */
    public function create_chat_session($visitor_data) {
        $response = $this->make_request('POST', '/api/v1/chat/sessions', $visitor_data);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        return array(
            'success' => true,
            'data' => $response
        );
    }
    
    /**
     * Upload file
     */
    public function upload_file($file_path, $file_name) {
        if (!file_exists($file_path)) {
            return array(
                'success' => false,
                'message' => __('File not found.', 'wischat')
            );
        }
        
        $boundary = wp_generate_uuid4();
        $file_content = file_get_contents($file_path);
        $mime_type = mime_content_type($file_path);
        
        $body = "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"file\"; filename=\"{$file_name}\"\r\n";
        $body .= "Content-Type: {$mime_type}\r\n\r\n";
        $body .= $file_content . "\r\n";
        $body .= "--{$boundary}--\r\n";
        
        $args = array(
            'method' => 'POST',
            'timeout' => 60,
            'headers' => array(
                'X-API-Key' => $this->api_key,
                'Content-Type' => "multipart/form-data; boundary={$boundary}"
            ),
            'body' => $body
        );
        
        $response = wp_remote_request($this->api_endpoint . '/api/v1/upload/file', $args);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (wp_remote_retrieve_response_code($response) !== 200) {
            return array(
                'success' => false,
                'message' => $data['message'] ?? __('Upload failed.', 'wischat')
            );
        }
        
        return array(
            'success' => true,
            'data' => $data
        );
    }
    
    /**
     * Make HTTP request to API
     */
    private function make_request($method, $endpoint, $data = array(), $custom_endpoint = null, $custom_key = null) {
        $api_endpoint = $custom_endpoint ?: $this->api_endpoint;
        $api_key = $custom_key ?: $this->api_key;
        
        if (empty($api_endpoint)) {
            return new WP_Error('no_endpoint', __('API endpoint not configured.', 'wischat'));
        }
        
        $url = $api_endpoint . $endpoint;
        
        $args = array(
            'method' => $method,
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'User-Agent' => 'WisChat-WordPress/' . WISCHAT_VERSION
            )
        );
        
        // Add API key if available
        if (!empty($api_key)) {
            $args['headers']['X-API-Key'] = $api_key;
        }
        
        // Add data based on method
        if ($method === 'GET' && !empty($data)) {
            $url = add_query_arg($data, $url);
        } elseif (in_array($method, array('POST', 'PUT', 'PATCH')) && !empty($data)) {
            $args['body'] = json_encode($data);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        // Try to decode JSON response
        $decoded_body = json_decode($body, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            $body = $decoded_body;
        }
        
        // Handle error responses
        if ($response_code >= 400) {
            $error_message = __('API request failed.', 'wischat');
            
            if (is_array($body) && isset($body['message'])) {
                $error_message = $body['message'];
            } elseif (is_string($body)) {
                $error_message = $body;
            }
            
            return new WP_Error('api_error', $error_message, array('status' => $response_code));
        }
        
        return $body;
    }
    
    /**
     * Get WebSocket connection URL
     */
    public function get_websocket_url() {
        $endpoint = $this->api_endpoint;
        
        // Convert HTTP to WebSocket URL
        $websocket_url = str_replace(array('http://', 'https://'), array('ws://', 'wss://'), $endpoint);
        
        return $websocket_url;
    }
    
    /**
     * Validate API configuration
     */
    public function is_configured() {
        return !empty($this->api_endpoint) && !empty($this->api_key);
    }
    
    /**
     * Get API status
     */
    public function get_api_status() {
        if (!$this->is_configured()) {
            return array(
                'status' => 'not_configured',
                'message' => __('API not configured.', 'wischat')
            );
        }
        
        $response = $this->make_request('GET', '/health');
        
        if (is_wp_error($response)) {
            return array(
                'status' => 'error',
                'message' => $response->get_error_message()
            );
        }
        
        return array(
            'status' => 'connected',
            'message' => __('API connected successfully.', 'wischat'),
            'data' => $response
        );
    }
    
    /**
     * Log API request for debugging
     */
    private function log_request($method, $endpoint, $data, $response) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                'WisChat API Request: %s %s | Data: %s | Response: %s',
                $method,
                $endpoint,
                json_encode($data),
                is_wp_error($response) ? $response->get_error_message() : json_encode($response)
            ));
        }
    }
}
