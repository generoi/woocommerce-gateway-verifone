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
    exit; // Exit if accessed directly.
}

use \Verifone\Core\DependencyInjection\Configuration\Backend\BackendConfigurationImpl;
use \Verifone\Core\DependencyInjection\Configuration\Backend\GetAvailablePaymentMethodsConfigurationImpl;
use \Verifone\Core\DependencyInjection\Configuration\Frontend\RedirectUrlsImpl;
use \Verifone\Core\DependencyInjection\Configuration\Frontend\FrontendConfigurationImpl;
use \Verifone\Core\DependencyInjection\Service\CustomerImpl;
use \Verifone\Core\DependencyInjection\Service\OrderImpl;

class WC_Verifone_Core
{
    /**
     * @return BackendConfigurationImpl
     */
    public static function getBackedConfigObject()
    {

        $config = WC_Verifone_Config::getInstance();

        $merchant = $config->getMerchantAgreement();
        $keyFile = $config->getShopPrivateKeyFile();

        $configObject = new BackendConfigurationImpl(
            $keyFile,
            $merchant,
            WC_Verifone_System::getSystemName(),
            WC_Verifone_System::getVersion(),
            WC_Verifone_Urls::getUrls('server'),
            $config->isDisableRsaBlinding()
        );

        return $configObject;
    }

    /**
     * @return GetAvailablePaymentMethodsConfigurationImpl
     */
    public static function getAvailablePaymentMethodsConfigObject()
    {
        $config = WC_Verifone_Config::getInstance();

        $merchant = $config->getMerchantAgreement();
        $keyFile = $config->getShopPrivateKeyFile();

        $configObject = new GetAvailablePaymentMethodsConfigurationImpl(
            $keyFile,
            $merchant,
            WC_Verifone_System::getSystemName(),
            WC_Verifone_System::getVersion(),
            WC_Verifone_Urls::getUrls('server'),
            WC_Verifone_System::getCurrencyNumber(),
            $config->isDisableRsaBlinding()
        );

        return $configObject;
    }

    /**
     * @param RedirectUrlsImpl $urls
     * @return FrontendConfigurationImpl
     */
    public static function getFrontendConfigObject(RedirectUrlsImpl $urls)
    {
        $config = WC_Verifone_Config::getInstance();

        $merchant = $config->getMerchantAgreement();
        $keyFile = $config->getShopPrivateKeyFile();

        $configObject = new FrontendConfigurationImpl(
            $urls,
            $keyFile,
            $merchant,
            WC_Verifone_System::getSystemName(),
            WC_Verifone_System::getVersion(),
            (string)$config->isSkipConfirmationPage(),
            $config->isDisableRsaBlinding(),
            $config->getStyleCode()
        );

        return $configObject;
    }

    /**
     * @param $customerData
     * @param null $address
     * @return CustomerImpl
     */
    public static function createCustomerObject($customerData, $address = null)
    {
        $customer = new CustomerImpl(
            self::sanitize($customerData['firstname']),
            self::sanitize($customerData['lastname']),
            self::sanitize($customerData['phone']),
            self::sanitize($customerData['email']),
            $address,
            isset($customerData['external_id']) && $customerData['external_id'] ? (string)$customerData['external_id'] : ''
        );

        return $customer;
    }

    public static function createOrderObject($orderData)
    {
        $order = new OrderImpl(
            (string)$orderData['order_id'],
            $orderData['time'],
            (string)$orderData['currency_code'],
            (string)$orderData['total_incl_amount'],
            (string)$orderData['total_excl_amount'],
            (string)$orderData['total_tax']
        );

        return $order;
    }

    public static function getRedirectUrlsObject($isCard = false)
    {

        if(!$isCard) {
            $link = WC_Verifone_Urls::getPaymentLink();
        } else {
            $link = WC_Verifone_Urls::getCardLink();
        }

        $urls = new RedirectUrlsImpl(
            add_query_arg('status', 'success', $link),
            add_query_arg('status', 'rejected', $link),
            add_query_arg('status', 'cancel', $link),
            add_query_arg('status', 'expired', $link),
            add_query_arg('status', 'error', $link)
        );

        return $urls;
    }

    public static function renderRequestForm($formData)
    {
        $action = $formData['action'];
        unset($formData['action']);

        $message = __('Redirecting to Verifone Payment', WC_VERIFONE_DOMAIN);

        $context = [
            'action' => $action,
            'redirectMessage' => $message,
            'formData' => $formData
        ];

        WC_Verifone_Tpl::render($context, WC_Verifone_Tpl::REQUEST_FORM);

        return true;
    }

    /**
     * Remove unexpected characters
     *
     * @param $string
     * @return mixed
     */
    public static function sanitize($string)
    {
        return str_replace('"', '', str_replace('\\', '', str_replace('-', ' ', $string)));
    }

}