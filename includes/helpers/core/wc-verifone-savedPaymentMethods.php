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

use \Verifone\Core\DependencyInjection\Service\OrderImpl;
use \Verifone\Core\DependencyInjection\Service\PaymentInfoImpl;
use \Verifone\Core\Service\Frontend\AddNewCardService;
use \Verifone\Core\ServiceFactory;
use \Verifone\Core\ExecutorContainer;
use \Verifone\Core\DependencyInjection\Transporter\CoreResponse;
use \Verifone\Core\Service\Backend\GetSavedCreditCardsService;
use \Verifone\Core\Executor\BackendServiceExecutor;
use \Verifone\Core\DependencyInjection\CoreResponse\CardImpl;
use Verifone\Core\Service\Backend\RemoveSavedCreditCardsService;

class WC_Verifone_Core_SavedPaymentMethods extends WC_Verifone_Core
{
    public static function prepareNewCardRequestFormData()
    {
        require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

        $config = WC_Verifone_Config::getInstance();

        $urls = self::getRedirectUrlsObject(true);

        $customerData = WC_Verifone_Data::collectCustomerData();

        if($customerData['address'] !== null) {
            $address = new \Verifone\Core\DependencyInjection\Service\AddressImpl(
                $customerData['address']['address_1'],
                $customerData['address']['address_2'],
                '',
                $customerData['address']['city'],
                $customerData['address']['postcode'],
                $customerData['address']['country_code'],
                $customerData['address']['firstname'],
                $customerData['address']['lastname']
            );
            $customer = self::createCustomerObject($customerData, $address);
        } else {
            $customer = self::createCustomerObject($customerData);
        }

        $order = new OrderImpl(
            'addNewCard',
            gmdate('Y-m-d H:i:s'),
            WC_Verifone_System::getCurrencyNumber(),
            '1',
            '1',
            '0'
        );

        $payment = new PaymentInfoImpl(
            $config->getPaymentPageLanguage(),
            PaymentInfoImpl::SAVE_METHOD_SAVE_ONLY,
            '',
            (string)time(),
            (bool)$config->isSaveMaskedPanNumber()
        );

        /** @var AddNewCardService $service */
        $service = ServiceFactory::createService(self::getFrontendConfigObject($urls), 'Frontend\AddNewCardService');
        $service->insertCustomer($customer);
        $service->insertOrder($order);
        $service->insertPaymentInfo($payment);

        // for json: new ExecutorContainer(array('requestConversion.class' => ExecutorContainer::REQUEST_CONVERTER_TYPE_JSON));
        $container = new ExecutorContainer();
        $exec = $container->getExecutor(ExecutorContainer::EXECUTOR_TYPE_FRONTEND);

        $form = $exec->executeService($service, WC_Verifone_Urls::getUrls('page'), $config->isValidateUrl());

        return $form;

    }

    public static function verifyAddNewCardResponse($status)
    {
        $result = [
            'success' => $status == 'success',
            'message' => self::_getResponseMessage($status)
        ];

        return $result;

    }

    protected static function _getResponseMessage($status)
    {
        $messages = [
            'cancel' => __('Card adding canceled.', WC_VERIFONE_DOMAIN),
            'error' => __('Server error, please try again later.', WC_VERIFONE_DOMAIN),
            'expired' => __('Your card has expired.', WC_VERIFONE_DOMAIN),
            'rejected' => __('Your card has been rejected.', WC_VERIFONE_DOMAIN),
            'success' => __('Your card has been successfully added.', WC_VERIFONE_DOMAIN)
        ];

        if (isset($messages[$status])) {
            return $messages[$status];
        }

        return $messages['error'];

    }

    /**
     * @return CoreResponse|null
     * @throws Exception
     */
    public static function getListSavedPaymentMethods()
    {
        require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

        $config = WC_Verifone_Config::getInstance();

        $customerData = WC_Verifone_Data::collectCustomerData();
        if ($customerData === null) {
            return null;
        }

        $customer = self::createCustomerObject($customerData);

        try {
            /**
             * @var GetSavedCreditCardsService $service
             */
            $service = ServiceFactory::createService(self::getBackedConfigObject(), 'Backend\GetSavedCreditCardsService');
            $service->insertCustomer($customer);

            $container = new ExecutorContainer();

            /** @var BackendServiceExecutor $exec */
            $exec = $container->getExecutor('backend');

            /** @var CoreResponse $response */
            $keyFile = $config->getPaymentPublicKeyFile();
            $response = $exec->executeService($service, $keyFile);

            return $response;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Add card fetched from Verifone System
     *
     * @param CardImpl $card
     * @return bool
     */
    public static function addCard(CardImpl $card)
    {
        require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

        // Add token to WooCommerce
        if (get_current_user_id() && class_exists('WC_Payment_Token_CC')) {

            $tokenId = $card->getId();

            try {
                global $wpdb;
                if ($data = $wpdb->get_row($wpdb->prepare("SELECT token, user_id, gateway_id, is_default FROM {$wpdb->prefix}woocommerce_payment_tokens WHERE token LIKE %d LIMIT 1;", $tokenId))) {
                    return true;
                }

            } catch (Exception $e) {
                // add new
            }

            $token = new WC_Payment_Token_CC();
            $token->set_token($tokenId);
            $token->set_gateway_id(WC_VERIFONE_GATEWAY_ID);
            $token->set_card_type(strtolower($card->getCode()));

            $number = $card->getTitle();
            $token->set_last4(substr($number, -4, 4));

            $date = $card->getValidity();
            if (strlen($date) == 5) {
                $date = '0' . $date;
            }
            $month = substr($date, 0, 2);
            $year = substr($date, 2, 4);

            $token->set_expiry_month($month);
            $token->set_expiry_year($year);
            $token->set_user_id(get_current_user_id());
            $token->save();
        }

        return true;
    }

    /**
     * Delete card in Verifone System
     *
     * @param $cardId
     * @return array|null
     * @throws Exception
     */
    public static function deleteCard($cardId)
    {
        require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

        $config = WC_Verifone_Config::getInstance();

        $customerData = WC_Verifone_Data::collectCustomerData();
        if ($customerData === null) {
            return null;
        }

        $customer = self::createCustomerObject($customerData);

        $payment = new PaymentInfoImpl('', '', $cardId);

        try {
            /**
             * @var RemoveSavedCreditCardsService $service
             */
            $service = ServiceFactory::createService(self::getBackedConfigObject(), 'Backend\RemoveSavedCreditCardsService');
            $service->insertCustomer($customer);
            $service->insertPaymentInfo($payment);

            $container = new ExecutorContainer();

            /** @var BackendServiceExecutor $exec */
            $exec = $container->getExecutor('backend');
            $keyFile = $config->getPaymentPublicKeyFile();
            $response = $exec->executeService($service, $keyFile);

            return $response;
        } catch (\Exception $e) {
            throw $e;
        }
    }


}