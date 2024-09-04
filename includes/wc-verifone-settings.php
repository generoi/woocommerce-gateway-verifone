<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is released under commercial license by Lamia Oy.
 *
 * @copyright Copyright (c) 2017 Lamia Oy (https://lamia.fi)
 * @author    Szymon Nosal <simon@lamia.fi>
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_Verifone_Settings
{

    const DESC_TIP = false;

    public function getSettings()
    {

        $settings = array(
            'summary' => array(
                'title' => '<a href="#summary-verifone" id="verifone-summary-modal-trigger">' . __('Display configuration summary', WC_VERIFONE_DOMAIN) . '</a>',
                'type' => 'title',
                'desc_tip' => true
            ),
            'enabled' => array(
                'title' => __('Enable/Disable', WC_VERIFONE_DOMAIN),
                'type' => 'checkbox',
                'label' => __('Enable Verifone Payment', WC_VERIFONE_DOMAIN),
                'default' => 'no',
            ),
            'title' => array(
                'title' => __('Title', WC_VERIFONE_DOMAIN),
                'type' => 'text',
                'default' => __('Verifone Payment', WC_VERIFONE_DOMAIN)
            ),
            'is_live_mode' => array(
                'title' => __('Environment (Test/Production)', WC_VERIFONE_DOMAIN),
                'type' => 'select',
                'description' => __('Select environment for the payment module', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
                'default' => 0,
                'options' => array(
                    0 => __('Test', WC_VERIFONE_DOMAIN),
                    1 => __('Production', WC_VERIFONE_DOMAIN)
                )
            ),
            'merchant_agreement_code' => array(
                'title' => __('Verifone Payment production merchant agreement code', WC_VERIFONE_DOMAIN),
                'type' => 'text',
                'description' => __('Verifone Payment production merchant agreement code', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
                'default' => ''
            ),
            'merchant_agreement_code_test' => array(
                'title' => __('Verifone Payment test merchant agreement code', WC_VERIFONE_DOMAIN),
                'type' => 'text',
                'description' => __('Verifone Payment test merchant agreement code', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
                'default' => 'demo-merchant-agreement'
            ),
            'key_handling_mode' => array(
                'title' => __('Payment service key handling', WC_VERIFONE_DOMAIN),
                'type' => 'select',
                'desc_tip' => self::DESC_TIP,
                'default' => 0,
                'options' => array(
                    0 => __('Automatic (Simple)', WC_VERIFONE_DOMAIN),
                    1 => __('Manual (Advanced)', WC_VERIFONE_DOMAIN)
                )
            ),
            'generate_keys' => array(
                'title' => '',
                'type' => 'text',
                'description' => $this->_getGenerateKeysDescription(),
                'desc_tip' => false,
                'class' => 'hidden depends-key_handling_mode-0'
            ),
            'keys_directory' => array(
                'title' => __('Directory for store keys', WC_VERIFONE_DOMAIN),
                'type' => 'text',
                'description' => __('Required. A path to the directory for generated files', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
                'default' => '',
                'class' => 'depends-key_handling_mode-1'
            ),
            'shop_private_keyfile' => array(
                'title' => __('Shop private key filename', WC_VERIFONE_DOMAIN) . $this->_getTestLiveLabel('production'),
                'type' => 'text',
                'description' => __('Filename of shop secret key file generated with Verifone Payment key pair generator', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
                'default' => '',
                'class' => 'depends-key_handling_mode-1'
            ),
            'shop_private_keyfile_test' => array(
                'title' => __('Shop private key filename', WC_VERIFONE_DOMAIN) . $this->_getTestLiveLabel('test'),
                'type' => 'text',
                'description' => __('Filename of shop secret key file generated with Verifone Payment key pair generator', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
                'default' => '',
                'class' => 'depends-key_handling_mode-1'
            ),
            'pay_page_url_1' => array(
                'title' => __('Pay page URL 1', WC_VERIFONE_DOMAIN),
                'type' => 'text',
                'description' => __('Required. URL to the payment system', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
                'default' => 'https://epayment1.point.fi/pw/payment'
            ),
            'pay_page_url_2' => array(
                'title' => __('Pay page URL 2', WC_VERIFONE_DOMAIN),
                'type' => 'text',
                'description' => __('Optional. Second redundant URL to the payment system', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
                'default' => 'https://epayment2.point.fi/pw/payment'
            ),
            'pay_page_url_3' => array(
                'title' => __('Pay page URL 3', WC_VERIFONE_DOMAIN),
                'type' => 'text',
                'description' => __('Optional. Third redundant URL to the payment system', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
                'default' => ''
            ),
            'payment_page_language' => array(
                'title' => __('Payment page language', WC_VERIFONE_DOMAIN),
                'type' => 'select',
                'description' => __('Select language which will be use on payment page', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
                'default' => 'fi_FI',
                'options' => WC_Verifone_System::getLocaleOptions()
            ),
            'validate_url' => array(
                'title' => __('Check payment node availability', WC_VERIFONE_DOMAIN),
                'type' => 'select',
                'description' => __('Make a check that payment node is available', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
                'default' => 1,
                'options' => $this->_yesNoOptions()
            ),
            'skip_confirmation_page' => array(
                'title' => __('Skip confirmation page', WC_VERIFONE_DOMAIN),
                'type' => 'select',
                'description' => __('Return directly to shop after payment', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
                'default' => 1,
                'options' => $this->_yesNoOptions()
            ),
            'style_code' => array(
                'title' => __('Style code', WC_VERIFONE_DOMAIN),
                'type' => 'text',
                'description' => __('Use of custom payment page template needs first to be uploaded and to be approved by Verifone Payment', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
                'default' => ''
            ),
            'basket_item_sending' => array(
                'title' => __('Basket Item Sending', WC_VERIFONE_DOMAIN),
                'type' => 'select',
                'description' => __('Select for which type of order should send items.', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
                'default' => 1,
                'options' => WC_Verifone_System::getBasketItemsOptions()
            ),
            'combine_invoice_basket_items' => array(
                'title' => __('Combine Invoice Basket Items', WC_VERIFONE_DOMAIN),
                'type' => 'select',
                'description' => __('Currently only available for invoice payment methods', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
                'default' => 0,
                'options' => $this->_yesNoOptions()
            ),
            'external_customer_id_field' => array(
                'title' => __('Use external customer id.', WC_VERIFONE_DOMAIN),
                'type' => 'text',
                'description' => __('If you want to use other field to match user in Verifone Payment service, please set this value (for example <strong>billing_phone</strong>). If you do not know what should be, please leave empty and contact with our service.', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
                'default' => ''
            ),
            'refresh_payments' => array(
                'title' => '<a href="#refresh" id="verifone-refresh-payment-methods-trigger">' . __('Refresh Payment Methods', WC_VERIFONE_DOMAIN) . '</a>',
                'type' => 'title',
                'description' => null,
                'desc_tip' => self::DESC_TIP,
            ),
            'payment_methods' => array(
                'title' => __('Available payment methods', WC_VERIFONE_DOMAIN),
                'type' => 'multiselect',
                'description' => null,
                'desc_tip' => self::DESC_TIP,
                'options' => WC_Verifone_System::getPaymentMethodsOptions()
            ),
            'allow_to_save_cc' => array(
                'title' => __('Allow to save Credit Cards', WC_VERIFONE_DOMAIN),
                'type' => 'select',
                'description' => null,
                'desc_tip' => self::DESC_TIP,
                'default' => 0,
                'options' => $this->_yesNoOptions()
            ),
            'save_masked_pan_number' => array(
                'title' => __('Save masked PAN number', WC_VERIFONE_DOMAIN),
                'type' => 'select',
                'description' => null,
                'desc_tip' => self::DESC_TIP,
                'default' => 0,
                'options' => $this->_yesNoOptions()
            ),
            'remember_cc_info' => array(
                'title' => __('Remember me info', WC_VERIFONE_DOMAIN),
                'type' => 'textarea',
                'description' => __('Optional. Note in checkout after Remember payment method checkbox - for cards only', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
            ),
            'payment_message' => array(
                'title' => __('Payment message', WC_VERIFONE_DOMAIN),
                'type' => 'textarea',
                'description' => __('Optional. Note in the checkout below the payment method select.', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
            ),
            'min_order_total' => array(
                'title' => __('Minimum Order Total', WC_VERIFONE_DOMAIN),
                'type' => 'text',
                'description' => __('Leave empty to disable limit.', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
            ),
            'max_order_total' => array(
                'title' => __('Maximum Order Total', WC_VERIFONE_DOMAIN),
                'type' => 'text',
                'description' => __('Leave empty to disable limit.', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
            ),
            'disable_rsa_blinding' => array(
                'title' => __('Disable rsa blinding', WC_VERIFONE_DOMAIN),
                'type' => 'select',
                'description' => __('Define CRYPT_RSA_DISABLE_BLINDING as true in case of custom PHP build or PHP7 (experimental)', WC_VERIFONE_DOMAIN),
                'desc_tip' => self::DESC_TIP,
                'default' => 0,
                'options' => $this->_yesNoOptions()
            ),
//            'order_status_pending' => array(
//                'title' => __('New payment order status', WC_VERIFONE_DOMAIN),
//                'type' => 'select',
//                'description' => __('This setting cannot be changed due to WooCommerce built-in logic', WC_VERIFONE_DOMAIN),
//                'desc_tip' => self::DESC_TIP,
//                'default' => 'wc-pending',
//                'options' => $this->_getOrderStatuses('pending'),
//                'custom_attributes' => array('readonly' => 'readonly', 'disabled' => 'disabled')
//            ),
//            'order_status_processing' => array(
//                'title' => __('Completed payment order status', WC_VERIFONE_DOMAIN),
//                'type' => 'select',
//                'description' => __('This setting cannot be changed due to WooCommerce built-in logic', WC_VERIFONE_DOMAIN),
//                'desc_tip' => self::DESC_TIP,
//                'default' => 'wc-processing',
//                'options' => $this->_getOrderStatuses('processing'),
//                'custom_attributes' => array('readonly' => 'readonly', 'disabled' => 'disabled')
//            ),
        );

        return apply_filters('wc_verifone_settings', $settings);
    }

    public function getDefaultValue($key)
    {
        $settings = $this->getSettings();

        if(isset($settings[$key]) && isset($settings[$key]['default'])) {
            return $settings[$key]['default'];
        }

        return null;
    }

    protected function _getUrlForRefreshPayment()
    {
        return add_query_arg(
            array(
                'page' => 'pages-verifone-refresh'
            ),
            admin_url('admin.php')
        );
    }

    protected function _getTestLiveLabel($type)
    {
        return sprintf(' (%s)', __(ucfirst($type), WC_VERIFONE_DOMAIN));
    }

    protected function _yesNoOptions()
    {
        return [
            0 => __('No', WC_VERIFONE_DOMAIN),
            1 => __('Yes', WC_VERIFONE_DOMAIN)
        ];
    }

    protected function _getOrderStatuses($forcedStatus = null)
    {
        if($forcedStatus === null) {
            return wc_get_order_statuses();
        }

        $statuses = wc_get_order_statuses();

        if(strpos($forcedStatus, 'wc-') === false) {
            $forcedStatus = 'wc-' . $forcedStatus;
        }

        if(isset($statuses[$forcedStatus])) {
            return $statuses[$forcedStatus];
        }

        return [];
    }

    protected function _getGenerateKeysDescription()
    {

        $generateLiveLink = '<a href="#generate-verifone" id="verifone-generate-keys-trigger-live">' . __('Generate live keys', WC_VERIFONE_DOMAIN) . '</a>';
        $generateTestLink = '<a href="#generate-verifone" id="verifone-generate-keys-trigger-test">' . __('Generate test keys', WC_VERIFONE_DOMAIN) . '</a>';

        $generateLiveDesc = __('When you generate live keys, you will need to upload the new public key to Verifone Payment portal', WC_VERIFONE_DOMAIN);
        $generateTestDesc = __('Uses preset keys by default, only needed if using custom test agreements', WC_VERIFONE_DOMAIN);

        $format = '%s <br/>%s<br/><br/>%s<br/>%s <span class="confirm hidden">%s</span>';

        $msg = __("Are you sure you want to generate keys? The keys are stored in database. \n\nAfter creating a new key, remember to copy this key to payment operator configuration settings, otherwise the payment will be broken", WC_VERIFONE_DOMAIN);

        return sprintf($format, $generateLiveLink, $generateLiveDesc, $generateTestLink, $generateTestDesc, $msg);
    }

}