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

use \Verifone\Core\DependencyInjection\Service\TransactionImpl;
use \Verifone\Core\Service\Backend\RefundPaymentService;
use \Verifone\Core\ServiceFactory;
use \Verifone\Core\ExecutorContainer;
use \Verifone\Core\Executor\BackendServiceExecutor;
use \Verifone\Core\DependencyInjection\Transporter\CoreResponse;

class WC_Verifone_Core_Refund extends WC_Verifone_Core
{
    /**
     * Method for make refund request in Verifone
     *
     * @param WC_Order $order
     * @param $amount
     * @return bool|CoreResponse
     * @throws Exception
     */
    public static function refund(WC_Order $order, $amount)
    {
        require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

        $config = WC_Verifone_Config::getInstance();

        $configObject = self::getBackedConfigObject();

        $refundAmount = $amount * 100;

        $transaction = new TransactionImpl(
            $order->get_meta('payment_method'),
            $order->get_meta('ext_order_id'),
            (string)$refundAmount,
            WC_Verifone_System::getCurrencyNumber()
        );

        try {
            /** @var RefundPaymentService $service */
            $service = ServiceFactory::createService($configObject, 'Backend\RefundPaymentService');
            $service->insertTransaction($transaction);
            $service->insertRefundProduct($transaction);

            $container = new ExecutorContainer();

            /** @var BackendServiceExecutor $exec */

            $exec = $container->getExecutor('backend');

            /** @var CoreResponse $response */
            $keyFile = $config->getPaymentPublicKeyFile();
            $response = $exec->executeService($service, $keyFile);

            if ($response->getStatusCode()) {
                return $response;
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return false;
    }
}