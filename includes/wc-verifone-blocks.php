<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class Wc_Verifone_Blocks extends AbstractPaymentMethodType {
    private $gateway;
    protected $name = 'verifone';

    public function initialize() {
        $verifoneSettings = new WC_Verifone_Settings();
        $this->settings = $verifoneSettings->getSettings();
        $this->gateway = new WC_Gateway_Verifone();
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {
        $path = plugin_dir_url(__FILE__) . '../assets/js/verifone-block.js';

        wp_register_script(
            'wc-verifone-blocks-integration',
            $path,
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            null,
            true
        );

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('wc-verifone-blocks-integration', 'wc-verifone');
        }

        return ['wc-verifone-blocks-integration'];
    }

    public function get_payment_method_data() {
        $data = [
            'title' => $this->gateway->title,
            'description' => $this->gateway->description,
        ];

        return $data;
    }
}
?>
