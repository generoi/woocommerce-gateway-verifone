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

class WC_Verifone_Urls
{

    const PAY_PAGE_URL_DEMO = 'https://epayment.test.point.fi/pw/payment';
    const SERVER_URL_DEMO = 'https://epayment.test.point.fi/pw/serverinterface';

    const SERVER_URL_1 = 'https://epayment1.point.fi/pw/serverinterface';
    const SERVER_URL_2 = 'https://epayment2.point.fi/pw/serverinterface';
    const SERVER_URL_3 = 'https://epayment3.point.fi/pw/serverinterface';

    const WC_API = 'wc-api';
    const GATEWAY_NAME = 'WC_Gateway_Verifone';
    const HTTPS = 'https';
    const HTTP = 'http';

    const ACTION_DELAYED_SUCCESS = 'delayedSuccess';
    const ACTION_ADD_NEW_CARD = 'verifoneCardResponse';

    /**
     * Get urls to payment and server service for test and live environments.
     *
     * @param $type [server, page]
     * @return array
     */
    public static function getUrls($type)
    {
        $config = WC_Verifone_Config::getInstance();
        $isLive = $config->isLiveMode();

        if ($isLive) {

            if ($type === 'server') {
                return [self::SERVER_URL_1, self::SERVER_URL_2, self::SERVER_URL_3];
            } elseif ($type === 'page') {
                $urls = [];
                for ($i = 1; $i <= 3; $i++) {
                    $url = $config->getPayPageUrl($i);
                    if (isset($url) && !empty($url)) {
                        $urls[] = $url;
                    }
                }
                return $urls;
            }

        } else {

            if ($type === 'server') {
                $urls = [self::SERVER_URL_DEMO];
                return apply_filters( 'woocommerce_verifone_server_url_demo', $urls );
            } elseif ($type === 'page') {
                return [self::PAY_PAGE_URL_DEMO];
            }

        }

        return [];
    }

    /**
     * Get url to settings page
     *
     * @return string
     */
    public static function getSettingsUrl()
    {
        $settingsUrl = add_query_arg(
            array(
                'page' => 'wc-settings',
                'tab' => 'checkout',
                'section' => 'wc_gateway_verifone',
            ),
            admin_url('admin.php')
        );

        return esc_url($settingsUrl);
    }

    /**
     * Check if shop is with ssl
     *
     * @return bool
     */
    protected static function _isSsl()
    {
        return (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || (is_ssl());
    }

    /**
     *  Get url for redirect to send payment request form page.
     *  Get url for set to payment request for fetch response.
     *
     * @return mixed
     */
    public static function getPaymentLink()
    {
        $query = add_query_arg(static::WC_API, static::GATEWAY_NAME, home_url('/'));

        if (self::_isSsl()) {
            if(strpos($query, static::HTTPS) !== false) {
                return $query;
            }
            return str_replace(static::HTTP, static::HTTPS, $query);
        } else {
            return str_replace(static::HTTPS, static::HTTP, $query);
        }
    }

    /**
     * Get url for set to card request for fetch response.
     *
     * @return string
     */
    public static function getCardLink()
    {
        return add_query_arg('action', self::ACTION_ADD_NEW_CARD, self::getPaymentLink());
    }

    /**
     * Get url for set in Verifone Payment system for fetch delayed success response.
     *
     * @return string
     */
    public static function getPaymentDelayedLink()
    {
        return add_query_arg('action', self::ACTION_DELAYED_SUCCESS, self::getPaymentLink());
    }

    public static function getCheckoutUrl()
    {
        global $woocommerce;

        if($woocommerce->cart && !empty($woocommerce->cart->get_checkout_url())) {
            $checkoutUrl = $woocommerce->cart->get_checkout_url();
        } else {
            $checkoutUrl = get_home_url();
        }

        return $checkoutUrl;
    }

}
