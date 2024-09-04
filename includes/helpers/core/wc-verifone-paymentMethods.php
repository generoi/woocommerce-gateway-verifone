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

use \Verifone\Core\ServiceFactory;
use \Verifone\Core\ExecutorContainer;
use \Verifone\Core\Service\Backend\GetAvailablePaymentMethodsService;
use \Verifone\Core\Executor\BackendServiceExecutor;
use \Verifone\Core\DependencyInjection\Transporter\CoreResponse;
use \Verifone\Core\DependencyInjection\CoreResponse\Interfaces\PaymentMethod;


class WC_Verifone_Core_PaymentMethods extends WC_Verifone_Core
{

    const PAYMENT_METHODS_OPTION_KEY = WC_Verifone_PaymentMethods::PAYMENT_METHODS_OPTION_KEY;

	const PAYMENT_METHOD_ORDER = ['all', 'aktia-maksu', 'bank-axess', 'sampo-web-payment', 'danske-netbetaling', 'nordea-e-payment', 'nordea-se-db',
      'nordea-dk-db', 'handelsbanken-e-payment', 'handelsbanken-se-db', 'oma-saastopankin-verkkomaksu', 'op-pohjola-verkkomaksu', 'pop-pankin-verkkomaksu',
      'swedbank-se-db', 's-pankki-verkkomaksu', 'seb-se-db', 'saastopankin-verkkomaksu', 'alandsbanken-e-payment', 'swish', 'siirto', 'mobilepay', 'vipps',
      'masterpass', 'dankort', 'visa', 'master-card', 'amex', 'diners', 'paypal', 'afterpay-invoice', 'invoice-collector', 'euroloan-invoice',
      'enterpay-invoice', 'handelsbanken-se-account', 'handelsbanken-se-invoice', 'svea-webpay-installment', 'svea-webpay-invoice'];
    /**
     * Refresh payment methods from Verifone
     *
     * @return array|null
     */
    public static function refreshAvailablePaymentMethods()
    {
        require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

        $config = WC_Verifone_Config::getInstance();
        $configObject = self::getAvailablePaymentMethodsConfigObject();

        /** @var GetAvailablePaymentMethodsService $service */
        $service = ServiceFactory::createService($configObject, 'Backend\GetAvailablePaymentMethodsService');
        $container = new ExecutorContainer();

        /** @var BackendServiceExecutor $exec */
        $exec = $container->getExecutor('backend');

        /** @var CoreResponse $response */
        $keyFile = $config->getPaymentPublicKeyFile();
        $response = $exec->executeService($service, $keyFile);

        if (!$response->getStatusCode()) {
            return null;
        }

        $body = $response->getBody();
        $methods = ['all'];

        /** @var PaymentMethod $item */
        foreach ($body as $item) {
            $methods[] = $item->getCode();
        }

        self::_savePaymentMethods($methods);

        return $methods;
    }

    /**
     * Save fetched payment methods properly ordered and filtered
     *
     * @param $methods
     */
    protected static function _savePaymentMethods($methods)
    {
        $encoded = json_encode(array_intersect(self::PAYMENT_METHOD_ORDER, $methods));
        update_option(self::PAYMENT_METHODS_OPTION_KEY, $encoded);
    }
}