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
    public static function getPaymentMethodsArray() {
        return WC_Verifone_PaymentMethodsArray::getAllMethods();
    }

	public static function getPaymentMethodsOrder() {
		$methods = self::getPaymentMethodsArray();

		// In which order different types of payment methods should be displayed
		$type_order = [
			self::TYPE_ALL,
			self::TYPE_BANK,
			self::TYPE_ELECTRONIC,
			self::TYPE_CARD,
			self::TYPE_INVOICE,
		];

		/**
		 * Have few exceptions for the order.
		 * Like mobilepay and vipps are typed as bank but should be displayed as electronic.
		 * Paypal is typed as invoice but should be displayed as electronic.
		 * Masterpass is typed as bank but should be displayed as card.
		 */
		$exceptions = [
			'mobilepay' => self::TYPE_ELECTRONIC,
			'vipps' => self::TYPE_ELECTRONIC,
			'paypal' => self::TYPE_ELECTRONIC,
			'masterpass' => self::TYPE_CARD,
		];

		// Change the type of the exceptions for ordering
		foreach($methods as $code => $method) {
			if(isset($exceptions[$code])) {
				$methods[$code]['type'] = $exceptions[$code];
			}
		}

		// Order the methods
        uasort($methods, function($a, $b) use ($type_order) {
            $typeComparison = array_search($a['type'], $type_order) - array_search($b['type'], $type_order);
            if ($typeComparison === 0) {
                return strcmp($a['name'], $b['name']);
            }
            return $typeComparison;
        });

		// We just need the keys of the methods
		return array_keys($methods);
	}

	public static function orderPaymentMethods($methods) {
		$order = self::getPaymentMethodsOrder();

		$orderedMethods = [];
		foreach($order as $code) {
			if(isset($methods[$code])) {
				$orderedMethods[$code] = $methods[$code];
			}
		}

		// Add the rest of the methods
		foreach($methods as $code => $method) {
			if(!isset($orderedMethods[$code])) {
				$orderedMethods[$code] = $method;
			}
		}

		return $orderedMethods;
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

	public static function getPaymentMethodLogo($code) {
		$methods = self::getPaymentMethodsArray();
        if (isset($methods[$code])) {
			$filename = $methods[$code]['logoname'];
			if (empty($filename)) {
				return '';
			}

			return plugins_url('assets/img/payment-methods/' . $filename . '.png', WC_VERIFONE_MAIN_FILE);
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

        $methods = json_decode($methods, true);

		// Order payment methods
		$methods = array_intersect(self::getPaymentMethodsOrder(), $methods);

		return $methods;
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

		// Order payment methods
		$methods = self::orderPaymentMethods($methods);

		// Add payment method logos
		foreach($methods as $key => $method) {
			$methods[$key]['logo'] = self::getPaymentMethodLogo($key);
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
