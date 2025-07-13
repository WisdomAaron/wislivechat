<?php
/**
 * WisChat Subscription Management Page
 * 
 * Handles the subscription management interface in WordPress admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class WisChat_Subscription_Page {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Add subscription page to admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'wischat-settings',
            __('Subscription', 'wischat'),
            __('Subscription', 'wischat'),
            'manage_options',
            'wischat-subscription',
            array($this, 'render_page')
        );
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        if ($hook !== 'wischat_page_wischat-subscription') {
            return;
        }
        
        wp_enqueue_script(
            'wischat-subscription-manager',
            WISCHAT_PLUGIN_URL . 'admin/js/subscription-manager.js',
            array('jquery'),
            WISCHAT_VERSION,
            true
        );
        
        wp_enqueue_style(
            'wischat-subscription-styles',
            WISCHAT_PLUGIN_URL . 'admin/css/subscription.css',
            array(),
            WISCHAT_VERSION
        );
        
        // Localize script with admin data
        wp_localize_script('wischat-subscription-manager', 'wischat_admin', array(
            'api_endpoint' => get_option('wischat_api_endpoint', ''),
            'api_key' => get_option('wischat_api_key', ''),
            'nonce' => wp_create_nonce('wischat_admin_nonce'),
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    }
    
    /**
     * Render subscription management page
     */
    public function render_page() {
        $api_endpoint = get_option('wischat_api_endpoint', '');
        $api_key = get_option('wischat_api_key', '');
        
        if (empty($api_endpoint) || empty($api_key)) {
            $this->render_setup_required();
            return;
        }
        
        ?>
        <div class="wrap">
            <h1><?php _e('WisChat Subscription', 'wischat'); ?></h1>
            
            <div class="wischat-subscription-container">
                
                <!-- Current Subscription Status -->
                <div class="wischat-card">
                    <h2><?php _e('Current Subscription', 'wischat'); ?></h2>
                    <div id="wischat-current-subscription">
                        <div class="loading">
                            <p><?php _e('Loading subscription information...', 'wischat'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Available Plans -->
                <div class="wischat-card">
                    <h2><?php _e('Available Plans', 'wischat'); ?></h2>
                    <p class="description">
                        <?php _e('Choose the plan that best fits your needs. All plans include our core live chat features.', 'wischat'); ?>
                    </p>
                    
                    <div id="wischat-subscription-plans">
                        <div class="loading">
                            <p><?php _e('Loading available plans...', 'wischat'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Information -->
                <div class="wischat-card">
                    <h2><?php _e('Payment Information', 'wischat'); ?></h2>
                    
                    <div class="payment-info-grid">
                        <div class="payment-method">
                            <h3><?php _e('MTN Mobile Money Cameroun', 'wischat'); ?></h3>
                            <p><?php _e('Payez en toute sécurité avec votre compte MTN Mobile Money. Disponible partout au Cameroun avec MTN.', 'wischat'); ?></p>

                            <div class="supported-countries">
                                <h4><?php _e('Informations de Paiement:', 'wischat'); ?></h4>
                                <ul>
                                    <li><strong>Pays:</strong> Cameroun 🇨🇲</li>
                                    <li><strong>Devise:</strong> XAF (Francs CFA)</li>
                                    <li><strong>Opérateur:</strong> MTN Cameroun</li>
                                    <li><strong>Format numéro:</strong> 237XXXXXXXX</li>
                                    <li><strong>Exemple:</strong> 237671234567</li>
                                    <li><strong>Langues:</strong> Français, Anglais</li>
                                </ul>
                            </div>

                            <div class="payment-benefits">
                                <h4><?php _e('Avantages MTN MoMo:', 'wischat'); ?></h4>
                                <ul>
                                    <li>✅ Paiement instantané et sécurisé</li>
                                    <li>✅ Pas besoin de carte bancaire</li>
                                    <li>✅ Disponible 24h/24, 7j/7</li>
                                    <li>✅ Confirmation par SMS</li>
                                    <li>✅ Support client en français</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="payment-process">
                            <h3><?php _e('How Payment Works', 'wischat'); ?></h3>
                            <ol>
                                <li><?php _e('Select your desired plan', 'wischat'); ?></li>
                                <li><?php _e('Enter your MTN Mobile Money number', 'wischat'); ?></li>
                                <li><?php _e('Click "Pay Now" to initiate payment', 'wischat'); ?></li>
                                <li><?php _e('You\'ll receive an SMS prompt on your phone', 'wischat'); ?></li>
                                <li><?php _e('Enter your MTN Mobile Money PIN to complete', 'wischat'); ?></li>
                                <li><?php _e('Your subscription activates automatically', 'wischat'); ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <!-- Usage & Limits -->
                <div class="wischat-card">
                    <h2><?php _e('Usage & Limits', 'wischat'); ?></h2>
                    <div id="wischat-usage-info">
                        <div class="loading">
                            <p><?php _e('Loading usage information...', 'wischat'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Payment History -->
                <div class="wischat-card">
                    <h2><?php _e('Payment History', 'wischat'); ?></h2>
                    <div id="wischat-payment-history">
                        <div class="loading">
                            <p><?php _e('Loading payment history...', 'wischat'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Support Information -->
                <div class="wischat-card">
                    <h2><?php _e('Need Help?', 'wischat'); ?></h2>
                    <div class="support-info">
                        <p><?php _e('If you have any questions about subscriptions or payments, we\'re here to help:', 'wischat'); ?></p>
                        
                        <div class="support-options">
                            <div class="support-option">
                                <h4><?php _e('Email Support', 'wischat'); ?></h4>
                                <p><a href="mailto:support@wischat.com">support@wischat.com</a></p>
                            </div>
                            
                            <div class="support-option">
                                <h4><?php _e('Documentation', 'wischat'); ?></h4>
                                <p><a href="https://docs.wischat.com" target="_blank"><?php _e('View Documentation', 'wischat'); ?></a></p>
                            </div>
                            
                            <div class="support-option">
                                <h4><?php _e('Live Chat', 'wischat'); ?></h4>
                                <p><?php _e('Use the chat widget on our website for instant support', 'wischat'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
        <style>
        .wischat-subscription-container {
            max-width: 1200px;
        }
        
        .wischat-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        
        .wischat-card h2 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
        }
        
        .wischat-plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .wischat-plan-card {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .wischat-plan-card:hover {
            border-color: #0073aa;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .wischat-plan-card.popular {
            border-color: #0073aa;
            transform: scale(1.05);
        }
        
        .wischat-plan-card.current {
            border-color: #46b450;
            background-color: #f7fcf0;
        }
        
        .plan-badge {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: #0073aa;
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .plan-badge.current {
            background: #46b450;
        }
        
        .plan-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .plan-header h3 {
            margin: 0 0 10px 0;
            font-size: 20px;
            font-weight: 600;
        }
        
        .plan-price .price {
            font-size: 28px;
            font-weight: 700;
            color: #0073aa;
        }
        
        .plan-price .period {
            font-size: 14px;
            color: #666;
        }
        
        .plan-features ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .plan-features li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .plan-features li:last-child {
            border-bottom: none;
        }
        
        .plan-features .dashicons {
            color: #46b450;
            margin-right: 8px;
        }
        
        .plan-action {
            text-align: center;
            margin-top: 20px;
        }
        
        .plan-action .button {
            width: 100%;
            padding: 10px;
            font-weight: 600;
        }
        
        .payment-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .payment-info-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .supported-countries ul {
            list-style: none;
            padding: 0;
        }
        
        .supported-countries li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        
        .payment-process ol {
            padding-left: 20px;
        }
        
        .payment-process li {
            margin-bottom: 8px;
        }
        
        .support-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        
        .support-option {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }
        
        .support-option h4 {
            margin: 0 0 10px 0;
            color: #0073aa;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .wischat-subscription-status {
            padding: 20px;
            border-radius: 4px;
            border-left: 4px solid #46b450;
            background: #f7fcf0;
        }
        
        .wischat-subscription-status.expired {
            border-left-color: #dc3232;
            background: #fef7f1;
        }
        
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-badge.active {
            background: #46b450;
            color: white;
        }
        
        .status-badge.expired {
            background: #dc3232;
            color: white;
        }
        </style>
        <?php
    }
    
    /**
     * Render setup required message
     */
    private function render_setup_required() {
        ?>
        <div class="wrap">
            <h1><?php _e('WisChat Subscription', 'wischat'); ?></h1>
            
            <div class="notice notice-warning">
                <p>
                    <strong><?php _e('Setup Required', 'wischat'); ?></strong><br>
                    <?php 
                    printf(
                        __('Please configure your API settings first. Go to <a href="%s">WisChat Settings</a> to set up your API endpoint and key.', 'wischat'),
                        admin_url('admin.php?page=wischat-settings')
                    ); 
                    ?>
                </p>
            </div>
            
            <div class="wischat-card">
                <h2><?php _e('Getting Started', 'wischat'); ?></h2>
                <p><?php _e('To manage your subscription, you need to:', 'wischat'); ?></p>
                <ol>
                    <li><?php _e('Deploy the WisChat backend server', 'wischat'); ?></li>
                    <li><?php _e('Generate an API key from the admin panel', 'wischat'); ?></li>
                    <li><?php _e('Configure the API settings in WisChat Settings', 'wischat'); ?></li>
                    <li><?php _e('Return to this page to manage your subscription', 'wischat'); ?></li>
                </ol>
                
                <p>
                    <a href="<?php echo admin_url('admin.php?page=wischat-settings'); ?>" class="button button-primary">
                        <?php _e('Go to Settings', 'wischat'); ?>
                    </a>
                </p>
            </div>
        </div>
        <?php
    }
}

// Initialize the subscription page
WisChat_Subscription_Page::get_instance();
