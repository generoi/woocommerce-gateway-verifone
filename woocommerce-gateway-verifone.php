<?php
/**
 * Plugin Name: WooCommerce Verifone Payment Gateway
 * Plugin URI: https://www.verifone.com/fi
 * Description: Verifone Payment gateway for WooCommerce.
 * Version: 1.4.0
 * Author: Verifone Payment
 * Author URI: https://www.verifone.com/fi
 * Requires at least: 4.4
 * Tested up to: 5.7.1
 * WC requires at least: 3.0
 * WC tested up to: 8.3.1
 * PHP requires at least: 5.6.0
 * PHP tested up to: 7.4.16
 * Text Domain: woocommerce-gateway-verifone
 * Domain Path: /languages
 *
 * NOTICE OF LICENSE
 *
 * This source file is released under commercial license by Lamia Oy.
 *
 * @copyright Copyright (c) 2018 Lamia Oy (https://lamia.fi)
 * @author    Szymon Nosal <simon@lamia.fi>
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Required minimums and constants
 */
define('WC_VERIFONE_VERSION', '1.4.0');
define('WC_VERIFONE_MIN_PHP_VER', '5.6.0');
define('WC_VERIFONE_MIN_WC_VER', '3.0.0');
define('WC_VERIFONE_MAIN_FILE', __FILE__);
define('WC_VERIFONE_PLUGIN_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));
define('WC_VERIFONE_PLUGIN_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
define('WC_VERIFONE_DOMAIN', 'woocommerce-gateway-verifone');
define('WC_VERIFONE_GATEWAY_ID', 'verifone');

if (!class_exists('WC_Verifone')) :

    class WC_Verifone
    {
        /**
         * @var WC_Verifone $instance Singleton The reference the *Singleton* instance of this class
         */
        private static $instance;

        /**
         * @var WC_Logger $log Reference to logging class.
         */
        private static $log;

        /**
         * Returns the *Singleton* instance of this class.
         *
         * @return WC_Verifone Singleton The *Singleton* instance.
         */
        public static function getInstance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        /**
         * Protected constructor to prevent creating a new instance of the
         * *Singleton* via the `new` operator from outside of this class.
         */
        protected function __construct()
        {
            load_plugin_textdomain(WC_VERIFONE_DOMAIN, false, plugin_basename(dirname(__FILE__)) . '/languages');
            add_action('plugins_loaded', array($this, 'initMainHelpers'));
            add_action('admin_init', array($this, 'checkEnvironment'));
            add_action('plugins_loaded', array($this, 'init'));
            add_action('admin_notices', array($this, 'adminNotices'), 15);
            add_action('plugins_loaded', array($this, 'upgrade'), 15);
        }

        /**
         *
         */
        public function initMainHelpers()
        {
            require_once(plugin_basename('includes/wc-verifone-notice.php'));
            require_once(plugin_basename('includes/wc-verifone-upgrade.php'));
        }

        /**
         * Init the plugin after plugins_loaded so environment variables are set.
         */
        public function init()
        {
            // Don't hook anything else in the plugin if we're in an incompatible environment
            if (self::getEnvironmentWarning()) {
                return;
            }

            // Init the gateway itself
            $this->initGateways();

            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'pluginActionLinks'));

            add_action('admin_enqueue_scripts', array($this, 'adminScripts'));
            add_action('wp_enqueue_scripts', array($this, 'frontendScripts'));
            add_action('wp_ajax_verifone_refresh_payment_methods', array($this, 'refreshPaymentMethodsAjax'));
            add_action('wp_ajax_nopriv_verifone_refresh_payment_methods', array($this, 'refreshPaymentMethodsAjax'));

            add_action('wp_ajax_verifone_generate_keys', array($this, 'generateKeys'));
            add_action('wp_ajax_nopriv_generate_keys', array($this, 'generateKeys'));

            add_action('woocommerce_order_actions', [WC_Verifone_Observer::class, 'addCheckOrderPaymentAction']);
            add_action('woocommerce_order_action_verifone_check_order_payment', [WC_Verifone_Observer::class, 'executeCheckOrderPaymentAction']);

            add_filter('woocommerce_get_customer_payment_tokens', array($this, 'woocommerceGetCustomerPaymentTokens'), 10, 3);
            add_action('woocommerce_payment_token_deleted', array($this, 'woocommercePaymentTokenDeleted'), 10, 2);

            add_filter('cron_schedules', array($this, 'manageCustomSchedules'));

            add_action('checkPaymentStatusCronAction', array($this, 'checkPaymentStatusCron'));

            if (!wp_get_schedule('checkPaymentStatusCronAction')) {
                wp_schedule_event(time(), '15min', 'checkPaymentStatusCronAction');
            }
//            add_action('woocommerce_payment_token_set_default', array($this, 'woocommercePaymentTokenSetDefault'));

        }

        /**
         * Checks the environment for compatibility problems.  Returns a string with the first incompatibility
         * found or false if the environment has no problems.
         */
        public static function getEnvironmentWarning()
        {
            if (version_compare(phpversion(), WC_VERIFONE_MIN_PHP_VER, '<')) {
                $message = __('WooCommerce Verifone - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', WC_VERIFONE_DOMAIN);

                return sprintf($message, WC_VERIFONE_MIN_PHP_VER, phpversion());
            }

            if (!defined('WC_VERSION')) {
                return __('WooCommerce Verifone requires WooCommerce to be activated to work.', WC_VERIFONE_DOMAIN);
            }

            if (version_compare(WC_VERSION, WC_VERIFONE_MIN_WC_VER, '<')) {
                $message = __('WooCommerce Verifone - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', WC_VERIFONE_DOMAIN);

                return sprintf($message, WC_VERIFONE_MIN_WC_VER, WC_VERSION);
            }

            if (!function_exists('curl_init')) {
                return __('WooCommerce Verifone - cURL is not installed.', WC_VERIFONE_DOMAIN);
            }

            return false;
        }

        /**
         * Adds plugin action links
         *
         * @since 1.0.0
         */
        public function pluginActionLinks($links)
        {
            $settingLink = WC_Verifone_Urls::getSettingsUrl();

            $pluginLinks = array(
                '<a href="' . $settingLink . '">' . __('Settings', WC_VERIFONE_DOMAIN) . '</a>',
                '<a href="https://www.verifone.com/fi">' . __('Support', WC_VERIFONE_DOMAIN) . '</a>',);

            return array_merge($pluginLinks, $links);
        }

        /**
         * The backup sanity check, in case the plugin is activated in a weird way,
         * or the environment changes after activation. Also handles upgrade routines.
         *
         * @return bool
         */
        public function checkEnvironment()
        {
            $environmentWarning = self::getEnvironmentWarning();

            if ($environmentWarning && is_plugin_active(plugin_basename(__FILE__))) {
                WC_Verifone_Notice::addError($environmentWarning, false, true);
                return false;
            }

            return true;
        }

        /**
         * @return bool
         */
        public function checkSetupStatus()
        {

            $config = WC_Verifone_Config::getInstance();

            $merchantCode = $config->getMerchantAgreement();
            $defaultMerchantCode = $config->getMerchantAgreementDefault();
            $liveMode = $config->isLiveMode();

            $url = WC_Verifone_Urls::getSettingsUrl();
            $link = '<a href="' . $url . '">' . $url . '</a>';

            if (!is_admin()) {
                return true;
            }

            $name = '<strong>' . __('WooCommerce Verifone Payment', WC_VERIFONE_DOMAIN) . '</strong> ';

            if (empty($merchantCode) || ($merchantCode == $defaultMerchantCode && $liveMode)) {
//                $message = $name . sprintf(__('Please configure payment method: %1$s.', WC_VERIFONE_DOMAIN), $link);
//                WC_Verifone_Notice::addInfo($message, false, true);
            }

            if (!$liveMode) {
                $message = $name. sprintf(__('Is set in development mode. You can change mode on configuration page: %1$s.', WC_VERIFONE_DOMAIN), $link);
                WC_Verifone_Notice::addInfo($message, false, true);
            }

            return true;
        }

        /**
         * Display any notices we've collected thus far (e.g. for connection, disconnection)
         */
        public function adminNotices()
        {
            WC_Verifone_Notice::render();
        }

        /**
         * Initialize the gateway. Called very early - in the context of the plugins_loaded action
         *
         * @since 1.0.0
         */
        public function initGateways()
        {

            if (!class_exists('WC_Payment_Gateway')) {
                return;
            }

            // include all files
//            require_once(plugin_basename('vendor/autoload.php'));
            require_once(plugin_basename('includes/wc-verifone-settings.php'));

            $this->initHelpers();

            require_once(plugin_basename('includes/class-wc-gateway-verifone.php'));

            $this->checkSetupStatus();

            add_filter('woocommerce_payment_gateways', array($this, 'addGateway'));

        }

        /**
         * Initialize all helpers
         */
        public function initHelpers()
        {

            foreach (glob(plugin_dir_path(__FILE__) . 'includes/helpers/*.php') as $file) {
                require_once $file;
            }

            foreach (glob(plugin_dir_path(__FILE__) . 'includes/helpers/core/*.php') as $file) {
                require_once $file;
            }

        }

        /**
         * Add the gateways to WooCommerce
         *
         * @since 1.0.0
         */
        public function addGateway($methods)
        {
            $methods[] = 'WC_Gateway_Verifone';

            return $methods;
        }

        public function refreshPaymentMethodsAjax()
        {

            $success = false;
            $response = null;
            $exceptionMessage = '';
            try {
                $response = WC_Verifone_Core_PaymentMethods::refreshAvailablePaymentMethods();
                if (is_array($response)) {
                    $success = true;
                }
            } catch (Exception $e) {
                $exceptionMessage = $e->getMessage();
            }

            if ($success) {
                WC_Verifone_Notice::addSuccess(__('Payment methods have been refreshed.', WC_VERIFONE_DOMAIN), true, true);
                $return = [
                    'code' => 200,
                    'response' => $response ? $response : '',
                    'message' => __('Payment methods have been refreshed.', WC_VERIFONE_DOMAIN)
                ];
            } else {
                WC_Verifone_Notice::addError(__('Problem with retrieve payment methods. ', WC_VERIFONE_DOMAIN) . '(' . $exceptionMessage . ')', false, true);
                $return = [
                    'code' => 400,
                    'response' => $response ? $response : '',
                    'message' => __('Problem with retrieve payment methods. ', WC_VERIFONE_DOMAIN)
                ];
            }

            echo json_encode($return);
            die();
        }

        public function generateKeys()
        {

            $result = WC_Verifone_Core_Generator::generateKeys();

            if($result['success']) {
                WC_Verifone_Notice::addSuccess(implode('| ', $result['messages']));
                $return = [
                    'code' => 200,
                    'messages' => $result['messages']
                ];
            } else {
                WC_Verifone_Notice::addError(implode(' | ', $result['messages']));
                $return = [
                    'code' => 400,
                    'messages' => $result['messages']
                ];
            }

            echo json_encode($return);
            die();
        }

        /**
         * Load admin scripts.
         *
         * @since 1.0.0
         */
        public function adminScripts()
        {
            if ('woocommerce_page_wc-settings' !== get_current_screen()->id) {
                return;
            }

            $suffix = ''; //@todo add .min version

            wp_enqueue_script('woocommerce_verifone_admin', plugins_url('assets/js/verifone-admin' . $suffix . '.js', WC_VERIFONE_MAIN_FILE), array(), WC_VERIFONE_VERSION, true);
            wp_enqueue_style('verifonepayment-styles', plugins_url('assets/css/verifonepayment-styles.css', WC_VERIFONE_MAIN_FILE), array(), WC_VERIFONE_VERSION);

        }

        public function frontendScripts()
        {
            wp_enqueue_style('verifonepayment-styles_f', plugins_url('assets/css/verifonepayment-styles_f.css', WC_VERIFONE_MAIN_FILE), array(), WC_VERIFONE_VERSION);
            wp_enqueue_script('woocommerce_verifone_admin', plugins_url('assets/js/verifone.js', WC_VERIFONE_MAIN_FILE), array(), WC_VERIFONE_VERSION, true);
        }

        public function woocommerceGetCustomerPaymentTokens($tokens, $customer_id, $gateway_id)
        {

            $config = WC_Verifone_Config::getInstance();

            /**
             * @var int $key
             * @var WC_Payment_Token_CC $token
             */
            foreach ($tokens as $key => $token) {
                if ((!$config->isAllowToSaveCC() && $token->get_gateway_id() == WC_VERIFONE_GATEWAY_ID) || get_current_user_id() != $customer_id) {
                    unset($tokens[$key]);
                }
            }
            return $tokens;
        }

        /**
         * Delete token in Verifone Service
         * @param $token_id
         * @param WC_Payment_Token_CC $token
         * @return bool
         * @throws Exception
         */
        public function woocommercePaymentTokenDeleted($token_id, $token)
        {

            if (WC_VERIFONE_GATEWAY_ID === $token->get_gateway_id()) {
                try {
                    if (WC_Verifone_Core_SavedPaymentMethods::deleteCard($token->get_token())) {
                        return true;
                    } else {
                        return false;
                    }
                } catch (Exception $e) {
                    throw $e;
                }

            }

            return true;
        }

        /**
         * Set as default in Verifone Service
         * @param $token_id
         * @return bool
         */
        public function woocommercePaymentTokenSetDefault($token_id)
        {
            $token = WC_Payment_Tokens::get($token_id);

            if (WC_VERIFONE_GATEWAY_ID === $token->get_gateway_id()) {
                return true;
            }

            return true;

        }

        public static function log($message)
        {
            if (empty(self::$log)) {
                self::$log = new WC_Logger();
            }

            self::$log->info($message);
        }

        public function manageCustomSchedules($schedules)
        {
            if (!isset($schedules["15min"])) {
                $schedules["15min"] = array(
                    'interval' => 15 * 60,
                    'display' => __('Once every 15 minutes'));
            }

            return $schedules;
        }

        public function checkPaymentStatusCron()
        {

            // Get orders payed by verifone in last 2 hours.
            $args = array(
                'payment_method' => WC_VERIFONE_GATEWAY_ID,
                'date_created' => '>' . (time() - HOUR_IN_SECONDS * 2),
                'status' => 'pending'
            );

            $orders = wc_get_orders($args);

            /** @var WC_Order $order */
            foreach ($orders as $order) {
                if(WC_Verifone_Payment::transactionsCanBeCheck($order)) {
                    WC_Verifone_Observer::executeCheckOrderPaymentAction($order, true);
                }
            }

            return true;

        }

        public function upgrade()
        {
            WC_Verifone_Upgrade::upgrade();
        }

    }

//    require_once __DIR__ . '/vendor/autoload.php';

    $GLOBALS['wc_verifone'] = WC_Verifone::getInstance();

    /**
     * Declares compatibility with Woocommerce features.
     *
     * List of features:
     * - cart_checkout_blocks
     * - custom_order_tables
     *
     * @since 1.3.16
     * @return void
     */
    function woocommerce_verifone_declare_feature_compatibility() {
        if (!class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
            return;
        }

        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'cart_checkout_blocks',
            __FILE__,
            true
        );

        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            __FILE__,
            true
        );
    }

    add_action('before_woocommerce_init', 'woocommerce_verifone_declare_feature_compatibility');

    /**
     * Add the gateway to WooCommerce Blocks.
     *
     * @since 1.3.16
     * @return void
     */
    function woocommerce_verifone_woocommerce_blocks_support() {
        if (!class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
            return;
        }

        require_once plugin_dir_path(__FILE__) . 'includes/wc-verifone-blocks.php';

        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
                $payment_method_registry->register(new Wc_Verifone_Blocks);
            }
        );
    }

    add_action('woocommerce_blocks_loaded', 'woocommerce_verifone_woocommerce_blocks_support');

endif;
