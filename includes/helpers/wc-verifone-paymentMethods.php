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

class WC_Verifone_PaymentMethods
{

    const TYPE_ALL = 'all';
    const TYPE_CARD = 'card';
    const TYPE_BANK = 'bank';
    const TYPE_INVOICE = 'invoice';
    const TYPE_ELECTRONIC = 'electronic';

    const PAYMENT_METHODS_OPTION_KEY = 'woocommerce_verifone_methods';

    /**
     * Get array with all possible payment methods
     *
     * @return array
     */
    public static function getPaymentMethodsArray()
    {
        return [
            'all' => ['type' => self::TYPE_ALL, 'name' => 'All in one'],
            'visa' => ['type' => self::TYPE_CARD, 'name' => 'VISA', 'display_name' => 'Visa'],
            'master-card' => ['type' => self::TYPE_CARD, 'name' => 'MASTER_CARD', 'display_name' => 'Mastercard'],
            'dankort' => ['type' => self::TYPE_CARD, 'name' => 'DANKORT', 'display_name' => 'Dankort'],
            'amex' => ['type' => self::TYPE_CARD, 'name' => 'AMEX', 'display_name' => 'American Express'],
            'diners' => ['type' => self::TYPE_CARD, 'name' => 'Diners'],
            's-pankki-verkkomaksu' => ['type' => self::TYPE_BANK, 'name' => 'S_PANKKI_VERKKOMAKSU', 'display_name' => 'S-pankki'],
            'aktia-maksu' => ['type' => self::TYPE_BANK, 'name' => 'AKTIA_MAKSU', 'display_name' => 'Aktia'],
            'op-pohjola-verkkomaksu' => ['type' => self::TYPE_BANK, 'name' => 'OP_POHJOLA_VERKKOMAKSU', 'display_name' => 'OP-Pohjola'],
            'nordea-e-payment' => ['type' => self::TYPE_BANK, 'name' => 'NORDEA_E_PAYMENT', 'display_name' => 'Nordea'],
            'sampo-web-payment' => ['type' => self::TYPE_BANK, 'name' => 'SAMPO_WEB_PAYMENT', 'display_name' => 'Danske Bank'],
            'handelsbanken-e-payment' => ['type' => self::TYPE_BANK, 'name' => 'HANDELSBANKEN_E_PAYMENT', 'display_name' => 'Handelsbanken'],
            'alandsbanken-e-payment' => ['type' => self::TYPE_BANK, 'name' => 'ALANDSBANKEN_E_PAYMENT', 'display_name' => 'Ålandsbanken'],
            'nordea-se-db' => ['type' => self::TYPE_BANK, 'name' => 'NORDEA_SE_DB', 'display_name' => ''],
            'handelsbanken-se-db' => ['type' => self::TYPE_BANK, 'name' => 'HANDELSBANKEN_SE_DB', 'display_name' => ''],
            'swedbank-se-db' => ['type' => self::TYPE_BANK, 'name' => 'SWEDBANK_SE_DB', 'display_name' => ''],
            'seb-se-db' => ['type' => self::TYPE_BANK, 'name' => 'SEB_SE_DB', 'display_name' => ''],
            'bank-axess' => ['type' => self::TYPE_BANK, 'name' => 'BANK_AXESS', 'display_name' => ''],
            'nordea-dk-db' => ['type' => self::TYPE_BANK, 'name' => 'NORDEA_DK_DB', 'display_name' => ''],
            'danske-netbetaling' => ['type' => self::TYPE_BANK, 'name' => 'DANSKE_NETBETALING', 'display_name' => ''],
            'saastopankin-verkkomaksu' => ['type' => self::TYPE_BANK, 'name' => 'SAASTOPANKIN_VERKKOMAKSU', 'display_name' => 'Säästöpankki'],
            'pop-pankin-verkkomaksu' => ['type' => self::TYPE_BANK, 'name' => 'POP_PANKIN_VERKKOMAKSU', 'display_name' => 'POP Pankki'],
            'oma-saastopankin-verkkomaksu' => ['type' => self::TYPE_BANK, 'name' => 'OMA_SAASTOPANKIN_VERKKOMAKSU', 'display_name' => 'Oma Säästöpankki'],
            'svea-webpay-installment' => ['type' => self::TYPE_INVOICE, 'name' => 'SVEA_WEBPAY_INSTALLMENT', 'display_name' => 'Svea Osamaksu'],
            'svea-webpay-invoice' => ['type' => self::TYPE_INVOICE, 'name' => 'SVEA_WEBPAY_INVOICE', 'display_name' => 'Svea Lasku'],
            'handelsbanken-se-account' => ['type' => self::TYPE_INVOICE, 'name' => 'HANDELSBANKEN_SE_ACCOUNT', 'display_name' => ''],
            'handelsbanken-se-invoice' => ['type' => self::TYPE_INVOICE, 'name' => 'HANDELSBANKEN_SE_INVOICE', 'display_name' => ''],
            'invoice-collector' => ['type' => self::TYPE_INVOICE, 'name' => 'INVOICE_COLLECTOR', 'display_name' => 'Collector Lasku'],
            'euroloan-invoice' => ['type' => self::TYPE_INVOICE, 'name' => 'EUROLOAN_INVOICE', 'display_name' => 'Euroloan Lasku'],
            'enterpay-invoice' => ['type' => self::TYPE_INVOICE, 'name' => 'ENTERPAY_INVOICE', 'display_name' => 'Enterpay Yrityslasku’'],
            'paypal' => ['type' => self::TYPE_INVOICE, 'name' => 'PAYPAL', 'display_name' => 'PayPal'],
            'swish' => ['type' => self::TYPE_ELECTRONIC, 'name' => 'SWISH', 'display_name' => 'Swish'],
            'siirto' => ['type' => self::TYPE_ELECTRONIC, 'name' => 'SIIRTO', 'display_name' => 'Siirto'],
            'afterpay-invoice' => ['type' => self::TYPE_INVOICE, 'name' => 'AFTERPAY_INVOICE', 'display_name' => 'Riverty'],
            'mobilepay' => ['type' => self::TYPE_BANK, 'name' => 'MOBILEPAY', 'display_name' => 'MobilePay'],
            'vipps' => ['type' => self::TYPE_BANK, 'name' => 'VIPPS', 'display_name' => 'VIPPS'],
            'masterpass' => ['type' => self::TYPE_BANK, 'name' => 'MASTERPASS', 'display_name' => 'MasterPass'],
        ];
    }

    /**
     * Get payment method display name (translated)
     *
     * @param $code
     * @return string
     */
    public static function getPaymentMethodDisplayName($code)
    {
        $methods = self::getPaymentMethodsArray();
        if (isset($methods[$code])) {
            if(!empty($methods[$code]['display_name'])) {
                return $methods[$code]['display_name'];
            }

            return $methods[$code]['name'];
        }

        return '';
    }

    /**
     * Get list with all payment methods configured in Verifone
     *
     * @return array
     */
    public static function getPaymentMethods()
    {
        $methods = get_option(self::PAYMENT_METHODS_OPTION_KEY);

        if (empty($methods)) {
            return ['all'];
        }

        return json_decode($methods, true);
    }

    public static function getSelectPaymentMethods()
    {
        $config = WC_Verifone_Config::getInstance();
        return $config->getPaymentMethods();
    }

    /**
     * Get all available payment methods
     *
     * @return array
     */
    public static function getAvailablePaymentMethods()
    {
        $methods = [];
        $available = self::getPaymentMethodsArray();

        foreach (self::getSelectPaymentMethods() as $code) {

            if (!array_key_exists($code, $available)) {
                continue;
            }

            $methods[$code] = $available[$code];
            $methods[$code]['code'] = $code;
            $methods[$code]['displayName'] = self::getPaymentMethodDisplayName($code);
        }

        return $methods;
    }

    /**
     * Get payment method information
     *
     * @param $code
     * @return mixed|null
     */
    public static function getPaymentMethodByCode($code)
    {
        $methods = self::getAvailablePaymentMethods();

        if (isset($methods[$code])) {
            return $methods[$code];
        }

        return null;
    }

    /**
     * Get array with saved payment methods for customer
     *
     * @param $customerId
     * @return array
     */
    public static function getSavedPaymentMethods($customerId)
    {

        if(!self::savedCardsAllowed()) {
            return [];
        }

        $tokens = WC_Payment_Tokens::get_customer_tokens($customerId, WC_VERIFONE_GATEWAY_ID);
        $default = WC_Payment_Tokens::get_customer_default_token($customerId);

        $saved = [];

        /** @var WC_Payment_Token_CC $token */
        foreach ($tokens as $token) {
            $method = [
                'code' => $token->get_token(),
                'type' => $token->get_card_type(),
                'displayName' => $token->get_display_name()
            ];

            if ($token->get_token() == $default->get_token()) {
                array_unshift($saved, $method);
            } else {
                array_push($saved, $method);
            }

        }

        return $saved;
    }

    public static function savedCardsAllowed()
    {
        $cardAvailable = false;

        foreach (self::getAvailablePaymentMethods() as $method) {
            if($method['type'] === self::TYPE_CARD) {
                $cardAvailable = true;
                break;
            }
        }

        $config = WC_Verifone_Config::getInstance();
        return $config->isAllowToSaveCC() && $cardAvailable;
    }

}