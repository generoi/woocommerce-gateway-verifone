<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class Wc_Verifone_Blocks extends AbstractPaymentMethodType {
    private $gateway;
    protected $name = 'verifone';

    public function initialize() {
        // $verifoneSettings = new WC_Verifone_Settings();
        // $this->settings = $verifoneSettings->getSettings();
        // $this->gateway = new WC_Gateway_Verifone();
    }

    public function is_active() {
		$gateway = new WC_Gateway_Verifone();
        return $gateway->is_available();
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
		$gateway = new WC_Gateway_Verifone();

		// Bypass is_checkout check in payment_fields function
		$gateway->is_checkout_block = true;

		/**
		 * Output the payment fields for the gateway.
		 * We need buffering because the payment_fields function echoes the fields in order for the
		 * legacy payment gateway selection to work correctly, and we want to use the same function
		 * for the block for the sake of consistency.
		 */
		ob_start();
		$gateway->payment_fields();
		$html = ob_get_clean();

		// Reset the bypass
		$gateway->is_checkout_block = false;

        $data = [
            'title'	=> $gateway->title,
			'logo'	=> $gateway->get_icon(),
			'html'	=> $html,
        ];

        return $data;
    }
}
?>
