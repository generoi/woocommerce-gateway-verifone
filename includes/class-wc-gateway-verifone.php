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

class WC_Gateway_Verifone extends WC_Payment_Gateway
{

    const HTTP = 'http://';
    const HTTPS = 'https://';
    const WC_API = 'wc-api';
    const GATEWAY_NAME = 'WC_Gateway_Verifone';

    const ONE_CLICK_MAX_AMOUNT = '100';

    /** @var WC_Verifone_Config $_verifoneConfig */
    protected $_verifoneConfig = null;

    protected $_instance = null;

    public function __construct()
    {

        $this->version = WC_VERIFONE_VERSION;
        $this->id = WC_VERIFONE_GATEWAY_ID;
        $this->method_title = __('Verifone Payment', WC_VERIFONE_DOMAIN);
        /* translators: 1: a href link 2: closing href */
        $this->method_description = sprintf(__('Verifone payment. For more information please see %1$sVerifone page%2$s.', WC_VERIFONE_DOMAIN), '<a href="https://verifone.fi">', '</a>');
        $this->has_fields = true;

        if (file_exists(plugin_dir_path(__DIR__) . 'assets/img/verifonepayment-logo.png')) {
            $this->icon = plugins_url('assets/img/verifonepayment-logo.png', WC_VERIFONE_MAIN_FILE);
        }

        $this->init_form_fields();
        $this->init_settings();

        $this->_verifoneConfig = WC_Verifone_Config::getInstance();

        $verifoneSettings = new WC_Verifone_Settings();

        if (empty($this->settings['delayed_url'])) {
            $this->settings['delayed_url'] = WC_Verifone_Urls::getPaymentDelayedLink();
        }

        $this->title = $this->get_option('title');

        $this->supports = [
            'products',
            'refunds',
        ];

        if ($this->_verifoneConfig->isAllowToSaveCC()) {
            $this->supports[] = 'add_payment_method';
        }

        // Actions
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);

        // Payment listener/API hook
        add_action('woocommerce_api_wc_gateway_verifone', [$this, 'gateway_communication']);

        add_action('verifone_refresh_saved_methods', [WC_Verifone_Observer::class, 'refreshSavedCards'], 10, 0);
        add_action('verifone_add_masked_pan_number', [WC_Verifone_Observer::class, 'addMaskedPanNumber'], 10, 2);

        add_filter('payment_fields', [$this, 'payment_fields']);


    }

    public function get_icon()
    {

        $icon = $this->icon ? '<img src="' . WC_HTTPS::force_https_url($this->icon) . '" class="verifonepayment-logo" alt="' . esc_attr($this->get_title()) . '" />' : '';

        return apply_filters('woocommerce_gateway_icon', $icon, $this->id);

    }

    public function init_form_fields()
    {
        $verifoneSettings = new WC_Verifone_Settings();
        $this->form_fields = $verifoneSettings->getSettings();
    }

    public function payment_fields()
    {

        if (is_checkout()) {

            $saved = [];

            if (get_current_user_id()) {
                $saved = WC_Verifone_PaymentMethods::getSavedPaymentMethods(get_current_user_id());
            }

            $paymentMethods = array_merge(WC_Verifone_PaymentMethods::getAvailablePaymentMethods(), $saved);

            $messages = [
                'selectMethod' => __('Select payment method', WC_VERIFONE_DOMAIN),
                'rememberMethod' => __('Remember payment method.', WC_VERIFONE_DOMAIN),
                'rememberMeInfo' => $this->_verifoneConfig->getRememberCCInfo(),
                'redirectMessage' => __('You will be redirected to Verifone Payment service to complete your order.', WC_VERIFONE_DOMAIN)
            ];

            if(!empty($this->_verifoneConfig->getPaymentMessage())) {
                $messages['redirectMessage'] = $this->_verifoneConfig->getPaymentMessage();
            }

            $context = [
                'allowCC' => $this->_verifoneConfig->isAllowToSaveCC(),
                'messages' => $messages,
                'paymentMethods' => $paymentMethods
            ];

            WC_Verifone_Tpl::render($context, WC_Verifone_Tpl::PAYMENT_METHODS_FORM);

        } elseif (is_add_payment_method_page()) {
            echo __('You will be redirect to Verifone Payment Service to process adding payment card.', WC_VERIFONE_DOMAIN);
        }

    }

    /**
     * Sends and receives data to/from verifone server
     */
    public function gateway_communication()
    {

        $action = filter_input(INPUT_GET, 'action');


        if (filter_input(INPUT_GET, 'order_id')) {
            $data = $this->collectData();
            WC_Verifone_Core_Payment::makePaymentRequest($data);
        } elseif ($action == WC_Verifone_Urls::ACTION_DELAYED_SUCCESS) {
            $this->verifyPaymentResponse(true);
        } elseif ($action == WC_Verifone_Urls::ACTION_ADD_NEW_CARD) {
            $this->verifyCardResponse();
        } else {
            $this->verifyPaymentResponse();
        }

        //exit must be present in this function!
        exit;
    }

    public function collectData()
    {
        $orderId = filter_input(INPUT_GET, 'order_id');
        $paymentMethod = filter_input(INPUT_GET, 'payment_method');
        $savePaymentMethod = filter_input(INPUT_GET, 'save_payment_method');

        $orderData = WC_Verifone_Data::collectData($orderId, $paymentMethod, $savePaymentMethod);

        return $orderData;
    }

    public function verifyPaymentResponse($isDelayed = false)
    {
        $status = isset($_GET['status']) ? $_GET['status'] : null;

        if ($isDelayed) {
            $response = WC_Verifone_Payment::processSuccess('success', true);
            if (!$response['error']) {
                do_action('verifone_refresh_saved_methods');
            }
            // no session, i.e. late POST from the payment system. We must signal 200 OK.
            header("HTTP/1.1 200 OK");
            die('<html><head><meta http-equiv="refresh" content="0;url=' . WC_Verifone_Urls::getCheckoutUrl() . '"></head></html>');
        } elseif (is_null($status)) {
            $response = WC_Verifone_Payment::processFail('error');
        } elseif ($status == 'success') {
            $response = WC_Verifone_Payment::processSuccess($status);
            if (!$response['error']) {
                do_action('verifone_refresh_saved_methods');
            }
        } else {
            $response = WC_Verifone_Payment::processFail($status);
        }

        if (isset($response['return_url'])) {
            wp_safe_redirect(apply_filters('woocommerce_checkout_no_payment_needed_redirect', $response['return_url'], $response['order']));
        } else {
            wp_safe_redirect(WC_Verifone_Urls::getCheckoutUrl());
        }
    }

    public function process_payment($orderId)
    {
        $order = new WC_Order($orderId);

        // Reduce stock levels
//        wc_reduce_stock_levels($order->get_id());
        wc_maybe_reduce_stock_levels($order->get_id());

        // Clear cart
        /** @var WooCommerce $woocommerce */
        global $woocommerce;
        $woocommerce->cart->empty_cart();

        $paymentMethod = filter_input(INPUT_POST, 'verifone-payment-method');
        $savePaymentMethod = filter_input(INPUT_POST, 'verifone-save-payment-method');

        $params = array(
            'order_id' => $orderId,
            'payment_method' => $paymentMethod,
            'save_payment_method' => $savePaymentMethod
        );

        return array(
            'result' => 'success',
            'redirect' => add_query_arg($params, WC_Verifone_Urls::getPaymentLink())
        );

    }

    /**
     * Refund a charge
     * @param  int $order_id
     * @param  float $amount
     * @param string $reason
     * @return bool|WP_Error
     */
    public function process_refund($order_id, $amount = null, $reason = '')
    {
        $order = wc_get_order($order_id);

        if (!$order || !$order->get_transaction_id()) {
            return false;
        }

        if (is_null($amount)) {
            return true;
        }

        $error = __('Attempted refund, but rejected by Verifone.Possible reasons: payment not committed, payment has already been refunded or similar.Please try again later, or check status from Verifones TCS portal.', WC_VERIFONE_DOMAIN);

        try {

            $result = WC_Verifone_Core_Refund::refund($order, $amount);

            if ($result) {

                /** @var Verifone\Core\DependencyInjection\Service\TransactionImpl $body */
                $body = $result->getFull();

                $refundMessage = sprintf(__('Refunded %1$s - Refund ID: %2$s - Reason: %3$s', WC_VERIFONE_DOMAIN), wc_price($amount), $body->getNumber(), $reason);
                $order->add_order_note($refundMessage);

                return true;
            }


        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        return new WP_Error('error', $error);

    }

    public function add_payment_method()
    {

        if (!is_user_logged_in()) {
            wc_add_notice(__('There was a problem during adding the card.', WC_VERIFONE_DOMAIN), 'error');
            return false;
        }

        $formData = WC_Verifone_Core_SavedPaymentMethods::prepareNewCardRequestFormData();
        WC_Verifone_Core_SavedPaymentMethods::renderRequestForm($formData);

        //exit must be present in this function!
        exit;
    }

    public function verifyCardResponse()
    {
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $result = WC_Verifone_Core_SavedPaymentMethods::verifyAddNewCardResponse($status);

        if ($result['success']) {
            do_action('verifone_refresh_saved_methods');
            wc_add_notice(__($result['message'], WC_VERIFONE_DOMAIN), 'success');
        } else {
            wc_add_notice(__($result['message'], WC_VERIFONE_DOMAIN), 'error');
        }

        wp_safe_redirect(wc_get_account_endpoint_url('payment-methods'));

    }

    public function admin_options()
    {
        $data = [
            'configurationData' => WC_Verifone_Summary::getConfigurationDataForDisplay(),
            'header' => __('Configuration summary', WC_VERIFONE_DOMAIN)
        ];

        WC_Verifone_Tpl::render($data, WC_Verifone_Tpl::SUMMARY);
        parent::admin_options();
    }

}

