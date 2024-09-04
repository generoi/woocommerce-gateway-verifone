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

use \Verifone\Core\DependencyInjection\Service\ProductImpl;
use \Verifone\Core\DependencyInjection\Service\PaymentInfoImpl;
use \Verifone\Core\DependencyInjection\Service\TransactionImpl;
use \Verifone\Core\Service\Frontend\CreateNewOrderService;
use \Verifone\Core\ExecutorContainer;
use \Verifone\Core\Service\Backend\ProcessPaymentService;
use \Verifone\Core\DependencyInjection\Transporter\CoreResponse;
use \Verifone\Core\Executor\BackendServiceExecutor;
use \Verifone\Core\Service\Backend\GetPaymentStatusService;
use \Verifone\Core\Service\FrontendResponse\FrontendResponseServiceImpl;
use \Verifone\Core\ServiceFactory;
use \Verifone\Core\DependencyInjection\Service\OrderImpl;
use \Verifone\Core\Service\Backend\ListTransactionNumbersService;
use \Verifone\Core\DependencyInjection\Service\AddressImpl;

class WC_Verifone_Core_Payment extends WC_Verifone_Core
{
    public static function preparePaymentRequestFormData(array $data)
    {
        require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

        $config = WC_Verifone_Config::getInstance();

        $urls = self::getRedirectUrlsObject(false);

        $customerData = $data['customer'];

        $address = new AddressImpl(
            $data['address']['address_1'],
            $data['address']['address_2'],
            '',
            $data['address']['city'],
            $data['address']['postcode'],
            $data['address']['country_code'],
            $data['address']['firstname'],
            $data['address']['lastname'],
            $data['address']['telephone'],
            $data['address']['email']
        );

        if($data['shipping_address'] !== null) {
            $shippingAddress = new AddressImpl(
                $data['shipping_address']['address_1'],
                $data['shipping_address']['address_2'],
                '',
                $data['shipping_address']['city'],
                $data['shipping_address']['postcode'],
                $data['shipping_address']['country_code'],
                $data['shipping_address']['firstname'],
                $data['shipping_address']['lastname']
            );
        } else {
            $shippingAddress = null;
        }


        $customer = self::createCustomerObject($customerData, $address);
        $order = self::createOrderObject($data);

        $products = [];

        foreach ($data['products'] as $product) {
            $products[] = new ProductImpl(
                self::sanitize($product['name']),
                (string)$product['unit_cost'],
                (string)$product['net_amount'],
                (string)$product['gross_amount'],
                (string)$product['unit_count'],
                (string)$product['discount_percentage']
            );
        }

        $savePaymentMethod = PaymentInfoImpl::SAVE_METHOD_AUTO_NO_SAVE;
        if (isset($data['save_payment_method']) && $data['save_payment_method'] == true) {
            $savePaymentMethod = PaymentInfoImpl::SAVE_METHOD_AUTO_SAVE;
        }

        $paymentMethodId = '';
        if (isset($data['payment_method_id'])) {
            $paymentMethodId = $data['payment_method_id'];
        }

        $paymentInfo = new PaymentInfoImpl(
            $data['locale'],
            $savePaymentMethod,
            $paymentMethodId,
            '',
            (bool)$config->isSaveMaskedPanNumber()
        );

        $paymentMethod = '';
        if (isset($data['payment_method'])) {
            $paymentMethod = $data['payment_method'];
        }

        $transactionInfo = new TransactionImpl(
            $paymentMethod,
            !is_null($data['ext_order_id']) ? $data['ext_order_id'] : '');

        /** @var CreateNewOrderService $service */
        $service = ServiceFactory::createService(self::getFrontendConfigObject($urls), 'Frontend\CreateNewOrderService');
        $service->insertCustomer($customer);
        $service->insertOrder($order);
        $service->insertPaymentInfo($paymentInfo);
        $service->insertTransaction($transactionInfo);

        if($shippingAddress !== null) {
            $service->insertDeliveryAddress($shippingAddress);
        }

        foreach ($products as $product) {
            $service->insertProduct($product);
        }

        // for json: new ExecutorContainer(array('requestConversion.class' => ExecutorContainer::REQUEST_CONVERTER_TYPE_JSON));
        $container = new ExecutorContainer();
        $exec = $container->getExecutor(ExecutorContainer::EXECUTOR_TYPE_FRONTEND);

        $form = $exec->executeService($service, WC_Verifone_Urls::getUrls('page'), $config->isValidateUrl());

        return $form;
    }

    public static function makeOneClickPaymentRequest(array $data)
    {
        require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

        if ($data['total_incl_amount'] > WC_Gateway_Verifone::ONE_CLICK_MAX_AMOUNT * 100) {
            return null;
        }

        $config = WC_Verifone_Config::getInstance();

        $customerData = $data['customer'];
        $customer = self::createCustomerObject($customerData);

        $order = self::createOrderObject($data);

        $paymentMethodId = '';
        if (isset($data['payment_method_id'])) {
            $paymentMethodId = $data['payment_method_id'];
        }

        $paymentInfo = new PaymentInfoImpl(
            $data['locale'],
            '',
            $paymentMethodId,
            '',
            (bool)$config->isSaveMaskedPanNumber()
        );

        $paymentMethod = '';
        if (isset($data['payment_method'])) {
            $paymentMethod = $data['payment_method'];
        }

        $transactionInfo = new TransactionImpl(
            $paymentMethod,
            !is_null($data['ext_order_id']) ? $data['ext_order_id'] : '');

        /** @var ProcessPaymentService $service */
        $service = ServiceFactory::createService(self::getBackedConfigObject(), 'Backend\ProcessPaymentService');
        $service->insertCustomer($customer);
        $service->insertOrder($order);
        $service->insertPaymentInfo($paymentInfo);
        $service->insertTransaction($transactionInfo);

        // for json: new ExecutorContainer(array('requestConversion.class' => ExecutorContainer::REQUEST_CONVERTER_TYPE_JSON));
        $container = new ExecutorContainer();

        /** @var BackendServiceExecutor $exec */
        $exec = $container->getExecutor(ExecutorContainer::EXECUTOR_TYPE_BACKEND);

        /** @var CoreResponse $response */
        $keyFile = $config->getPaymentPublicKeyFile();
        try {
            $response = $exec->executeService($service, $keyFile);
        } catch (Exception $e) {
            return null;
        }

        if ($response->getStatusCode()) {
            return $response->getBody();
        } else {
            return null;
        }
    }

    public static function makePaymentRequest(array $data)
    {
        require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

        // Due to PSD/2 regulation payment must be redirect to the payment provider page.
        // Is not possible to make payment request by S2S integration

        $formData = WC_Verifone_Core_Payment::preparePaymentRequestFormData($data);
        WC_Verifone_Core_Payment::renderRequestForm($formData);
        return true;
    }

    public static function getPaymentStatus($paymentMethod, $transactionNumber)
    {
        require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

        $config = WC_Verifone_Config::getInstance();

        $transaction = new TransactionImpl($paymentMethod, $transactionNumber);

        /** @var GetPaymentStatusService $service */
        $service = ServiceFactory::createService(self::getBackedConfigObject(), 'Backend\GetPaymentStatusService');
        $service->insertTransaction($transaction);

        $container = new ExecutorContainer();

        /** @var BackendServiceExecutor $exec */
        $exec = $container->getExecutor('backend');

        /** @var CoreResponse $response */
        $keyFile = $config->getPaymentPublicKeyFile();
        $response = $exec->executeService($service, $keyFile);

        if ($response->getStatusCode()) {
            return $response->getBody();
        } else {
            return null;
        }
    }

    /**
     * Fetch order number from payment response
     *
     * @param $params
     * @return mixed
     */
    public static function getOrderNumberFromResponse($params)
    {
        require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

        /** @var FrontendResponseServiceImpl $service */
        $service = ServiceFactory::createResponseService($params);

        return $service->getOrderNumber();
    }

    /**
     * Validate payment response and parse to CoreResponse object
     *
     * @param $requestData
     * @param WC_Order $order
     * @return CoreResponse
     * @internal param $orderId
     */
    public static function validateAndParsePaymentResponse($requestData, WC_Order $order)
    {
        require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

        $config = WC_Verifone_Config::getInstance();

        $orderData = WC_Verifone_Data::collectOrderData($order, '');
        $orderImpl = self::createOrderObject($orderData);

        /** @var FrontendResponseServiceImpl $service */
        $service = ServiceFactory::createResponseService($requestData);
        $service->insertOrder($orderImpl);
        $container = new ExecutorContainer(array('responseConversion.class' => 'Converter\Response\FrontendServiceResponseConverter'));
        $exec = $container->getExecutor(ExecutorContainer::EXECUTOR_TYPE_FRONTEND_RESPONSE);

        /** @var CoreResponse $parseResponse */
        $keyFile = $config->getPaymentPublicKeyFile();
        $parseResponse = $exec->executeService($service, $keyFile);

        return $parseResponse;

    }

    public static function getTransactionsFromGate(WC_Order $order)
    {
        require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

        $config = WC_Verifone_Config::getInstance();

        $orderImpl = new OrderImpl((string)$order->get_id(), '', '', '', '', '');

        /** @var ListTransactionNumbersService $service */
        $service = ServiceFactory::createService(self::getBackedConfigObject(), 'Backend\ListTransactionNumbersService');
        $service->insertOrder($orderImpl);

        $container = new ExecutorContainer();

        /** @var BackendServiceExecutor $exec */
        $exec = $container->getExecutor('backend');

        /** @var CoreResponse $response */
        $keyFile = $config->getPaymentPublicKeyFile();
        $response = $exec->executeService($service, $keyFile);

        if ($response->getStatusCode()) {
            return $response->getBody();
        } else {
            return null;
        }

    }
}
