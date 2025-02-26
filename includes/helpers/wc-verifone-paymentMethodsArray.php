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

class WC_Verifone_PaymentMethodsArray
{

    const TYPE_ALL = WC_Verifone_PaymentMethods::TYPE_ALL;
    const TYPE_CARD = WC_Verifone_PaymentMethods::TYPE_CARD;
    const TYPE_BANK = WC_Verifone_PaymentMethods::TYPE_BANK;
    const TYPE_INVOICE = WC_Verifone_PaymentMethods::TYPE_INVOICE;
    const TYPE_ELECTRONIC = WC_Verifone_PaymentMethods::TYPE_ELECTRONIC;

	public static function getAllMethods() {
		$methods = self::getActiveMethods();

		if ( apply_filters( 'woocommerce_verifone_enable_sunsetting_methods', false ) ) {
			$methods = array_merge($methods, self::getSunsettingMethods());
		}

		if ( apply_filters( 'woocommerce_verifone_enable_legacy_methods', false ) ) {
			$methods = array_merge($methods, self::getLegacyMethods());
		}

		return $methods;
	}

    public static function getActiveMethods() {
        $methods = [
            'all' => [
				'type'		=> self::TYPE_ALL,
				'name'		=> 'All in ones',
				'logoname'	=> 	'',
			],
            'visa' => [
				'type'		 	=> self::TYPE_CARD,
				'name'			=> 'VISA',
				'display_name' 	=> 'Visa',
				'logoname'		=> 'acquirer_visa',
			],
            'master-card' => [
				'type'		 	=> self::TYPE_CARD,
				'name'			=> 'MASTER_CARD',
				'display_name' 	=> 'Mastercard',
				'logoname'		=> 'acquirer_master_card',
			],
            'dankort' => [
				'type'		 	=> self::TYPE_CARD,
				'name'			=> 'DANKORT',
				'display_name' 	=> 'Dankort',
				'logoname'		=> 'acquirer_dankort',
			],
            'amex' => [
				'type'		 	=> self::TYPE_CARD,
				'name'			=> 'AMEX',
				'display_name' 	=> 'American Express',
				'logoname'		=> 'acquirer_amex',
			],
            'diners' => [
				'type'		 	=> self::TYPE_CARD,
				'name'			=> 'Diners',
				'logoname'		=> 'acquirer_diners_club',
			],
            's-pankki-verkkomaksu' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'S_PANKKI_VERKKOMAKSU',
				'display_name' 	=> 'S-pankki',
				'logoname'		=> 'acquirer_spankki',
			],
            'aktia-maksu' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'AKTIA_MAKSU',
				'display_name' 	=> 'Aktia',
				'logoname'		=> 'acquirer_aktia',
			],
            'op-pohjola-verkkomaksu' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'OP_POHJOLA_VERKKOMAKSU',
				'display_name' 	=> 'OP',
				'logoname'		=> 'acquirer_op',
			],
            'nordea-e-payment' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'NORDEA_E_PAYMENT',
				'display_name' 	=> 'Nordea',
				'logoname'		=> 'acquirer_nordea',
			],
            'sampo-web-payment' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'SAMPO_WEB_PAYMENT',
				'display_name' 	=> 'Danske Bank',
				'logoname'		=> 'acquirer_danskebank',
			],
            'handelsbanken-e-payment' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'HANDELSBANKEN_E_PAYMENT',
				'display_name' 	=> 'Handelsbanken',
				'logoname'		=> 'acquirer_handelsbanken',
			],
            'alandsbanken-e-payment' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'ALANDSBANKEN_E_PAYMENT',
				'display_name' 	=> 'Ålandsbanken',
				'logoname'		=> 'acquirer_alands',
			],
            'saastopankin-verkkomaksu' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'SAASTOPANKIN_VERKKOMAKSU',
				'display_name' 	=> 'Säästöpankki',
				'logoname'		=> 'acquirer_saastopankki',
			],
            'pop-pankin-verkkomaksu' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'POP_PANKIN_VERKKOMAKSU',
				'display_name' 	=> 'POP Pankki',
				'logoname'		=> 'acquirer_pop_maksunappi',
			],
            'oma-saastopankin-verkkomaksu' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'OMA_SAASTOPANKIN_VERKKOMAKSU',
				'display_name' 	=> 'Oma Säästöpankki',
				'logoname'		=> 'acquirer_oma_saastopankki',
			],
            'paypal' => [
				'type'		 	=> self::TYPE_INVOICE,
				'name'			=> 'PAYPAL',
				'display_name' 	=> 'PayPal',
				'logoname'		=> 'acquirer_paypal',
			],
            'swish' => [
				'type'		 	=> self::TYPE_ELECTRONIC,
				'name'			=> 'SWISH',
				'display_name' 	=> 'Swish',
				'logoname'		=> 'swish_payment_method',
			],
            'siirto' => [
				'type'		 	=> self::TYPE_ELECTRONIC,
				'name'			=> 'SIIRTO',
				'display_name' 	=> 'Siirto',
				'logoname'		=> 'siirto_payment_method',
			],
            'afterpay-invoice' => [
				'type'		 	=> self::TYPE_INVOICE,
				'name'			=> 'AFTERPAY_INVOICE',
				'display_name' 	=> 'Riverty',
				'logoname'		=> 'acquirer_arvato',
			],
            'mobilepay' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'MOBILEPAY',
				'display_name' 	=> 'MobilePay',
				'logoname'		=> 'mobilepay_payment_method',
			],
            'vipps' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'VIPPS',
				'display_name' 	=> 'VIPPS',
				'logoname'		=> 'vipps_payment_method',
			],
			'applepay' => [
				'type'		 	=> self::TYPE_ELECTRONIC,
				'name'			=> 'APPLEPAY',
				'display_name' 	=> 'Apple Pay',
				'logoname'		=> '',
			],
			'svea-webpay-invoice-new' => [
				'type'		 	=> self::TYPE_INVOICE,
				'name'			=> 'SVEA_WEBPAY_INVOICE_NEW',
				'display_name' 	=> 'Svea Lasku',
				'logoname'		=> '',
			],
			'svea-webpay-installment-new' => [
				'type'		 	=> self::TYPE_INVOICE,
				'name'			=> 'SVEA_WEBPAY_INSTALLMENT_NEW',
				'display_name' 	=> 'Svea Osamaksu',
				'logoname'		=> '',
			],
			'svea-webpay-invoice-b2b' => [
				'type'		 	=> self::TYPE_INVOICE,
				'name'			=> 'SVEA_WEBPAY_INVOICE_B2B',
				'display_name' 	=> 'Svea Yrityslasku',
				'logoname'		=> '',
			],
        ];

		return $methods;
    }

	public static function getSunsettingMethods() {
		$methods = [
			'svea-webpay-installment' => [
				'type'		 	=> self::TYPE_INVOICE,
				'name'			=> 'SVEA_WEBPAY_INSTALLMENT',
				'display_name' 	=> 'Svea Osamaksu',
				'logoname'		=> 'acquirer_svea_installment_new',
			],
            'svea-webpay-invoice' => [
				'type'		 	=> self::TYPE_INVOICE,
				'name'			=> 'SVEA_WEBPAY_INVOICE',
				'display_name' 	=> 'Svea Lasku',
				'logoname'		=> 'acquirer_svea_new',
			],
			'handelsbanken-se-account' => [
				'type'		 	=> self::TYPE_INVOICE,
				'name'			=> 'HANDELSBANKEN_SE_ACCOUNT',
				'display_name' 	=> '',
				'logoname'		=> 'acquirer_handelsbanken_account',
			],
            'handelsbanken-se-invoice' => [
				'type'		 	=> self::TYPE_INVOICE,
				'name'			=> 'HANDELSBANKEN_SE_INVOICE',
				'display_name' 	=> '',
				'logoname'		=> 'acquirer_handelsbanken_invoice',
			],
			'enterpay-invoice' => [
				'type'		 	=> self::TYPE_INVOICE,
				'name'			=> 'ENTERPAY_INVOICE',
				'display_name' 	=> 'Enterpay Yrityslasku',
				'logoname'		=> 'lasku_yritykselle_square',
			],
		];

		return $methods;
	}

	public static function getLegacyMethods() {
		$methods = [
			'nordea-se-db' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'NORDEA_SE_DB',
				'display_name' 	=> '',
				'logoname'		=> 'acquirer_nordea',
			],
            'handelsbanken-se-db' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'HANDELSBANKEN_SE_DB',
				'display_name' 	=> '',
				'logoname'		=> 'acquirer_handelsbanken',
			],
            'swedbank-se-db' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'SWEDBANK_SE_DB',
				'display_name' 	=> '',
				'logoname'		=> 'acquirer_swedbank_se',
			],
            'seb-se-db' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'SEB_SE_DB',
				'display_name' 	=> '',
				'logoname'		=> 'acquirer_seb',
			],
            'bank-axess' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'BANK_AXESS',
				'display_name' 	=> '',
				'logoname'		=> 'acquirer_bankaxess',
			],
            'nordea-dk-db' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'NORDEA_DK_DB',
				'display_name' 	=> '',
				'logoname'		=> 'acquirer_nordea',
			],
            'danske-netbetaling' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'DANSKE_NETBETALING',
				'display_name' 	=> '',
				'logoname'		=> 'acquirer_danskebank',
			],
			'invoice-collector' => [
				'type'		 	=> self::TYPE_INVOICE,
				'name'			=> 'INVOICE_COLLECTOR',
				'display_name' 	=> 'Collector Lasku',
				'logoname'		=> '',
			],
            'euroloan-invoice' => [
				'type'		 	=> self::TYPE_INVOICE,
				'name'			=> 'EUROLOAN_INVOICE',
				'display_name' 	=> 'Euroloan Lasku',
				'logoname'		=> '',
			],
			'masterpass' => [
				'type'		 	=> self::TYPE_BANK,
				'name'			=> 'MASTERPASS',
				'display_name' 	=> 'MasterPass',
				'logoname'		=> '',
			],
		];

		return $methods;
	}
}
