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

class WC_Verifone_System
{

    /** SYSTEM */
    const DEFAULT_TIMEZONE = 'Europe/Helsinki';
    const DEFAULT_LOCALE_COUNTRY = 'FI';

    /** BASKET ITEMS */
    const BASKET_ITEMS_NO_SEND = 0;
    const BASKET_ITEMS_SEND_FOR_ALL = 1;
    const BASKET_ITEMS_SEND_FOR_INVOICE = 2;

    /** SYSTEM */

    /**
     * Get shop name
     *
     * @return string
     */
    public static function getSystemName()
    {
        return 'WooCommerce';
    }

    /**
     * Get shop version.
     *
     * @return string
     */
    public static function getVersion()
    {
        /** @var WooCommerce $woocommerce */
        global $woocommerce;

        return $woocommerce->version;

    }

    /**
     * Get shop timezone
     *
     * @return DateTimeZone
     */
    public static function getTimezone()
    {

        $country = self::getLocaleCountry();
        $timezoneCode = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country);

        if (isset($timezoneCode[0])) {
            $timezone = new DateTimeZone($timezoneCode[0]);
        } else {
            // default
            $timezone = new DateTimeZone(self::DEFAULT_TIMEZONE);
        }

        return $timezone;
    }

    /**
     * Get shop locale country
     *
     * @return string
     */
    public static function getLocaleCountry()
    {
        $locationArray = wc_get_base_location();

        if (isset($locationArray['country'])) {
            $country = $locationArray['country'];
        } else {
            $country = self::DEFAULT_LOCALE_COUNTRY;
        }

        return $country;
    }

    public static function getCustomerLocale()
    {
        $locale = get_locale();

        if(array_key_exists($locale, self::getLocaleOptions())) {
            return $locale;
        }

        $config = WC_Verifone_Config::getInstance();
        return $config->getPaymentPageLanguage();
    }

    /**
     * Get available locales in Verifone service
     *
     * @return array
     */
    public static function getLocaleOptions()
    {
        return [
            'fi_FI' => __('Finnish', WC_VERIFONE_DOMAIN),
            'sv_SE' => __('Swedish', WC_VERIFONE_DOMAIN),
            'no_NO' => __('Norwegian', WC_VERIFONE_DOMAIN),
            'dk_DK' => __('Danish', WC_VERIFONE_DOMAIN),
            'sv_FI' => __('Swedish (Finland)', WC_VERIFONE_DOMAIN),
            'en_GB' => __('English', WC_VERIFONE_DOMAIN),
        ];
    }

    /** CURRENCY */
    /**
     * Get shop currency
     *
     * @return string
     */
    public static function getCurrencyCode()
    {
        $shopCurrency = get_option('woocommerce_currency');

        return $shopCurrency;
    }

    /**
     * Get shop currency number (ISO 4217)
     *
     * @return string
     */
    public static function getCurrencyNumber()
    {
        return self::convertCountryToISO4217(self::getCurrencyCode());
    }

    /**
     * Convert currency code to ISO 4217 number
     *
     * @param string $shopCurrency
     * @return string
     */
    public static function convertCountryToISO4217($shopCurrency = 'EUR')
    {
        // http://en.wikipedia.org/wiki/ISO_4217
        $currency = array(
            'AFA' => array('Afghan Afghani', '971'),
            'AWG' => array('Aruban Florin', '533'),
            'AUD' => array('Australian Dollars', '036'),
            'ARS' => array('Argentine Pes', '032'),
            'AZN' => array('Azerbaijanian Manat', '944'),
            'BSD' => array('Bahamian Dollar', '044'),
            'BDT' => array('Bangladeshi Taka', '050'),
            'BBD' => array('Barbados Dollar', '052'),
            'BYR' => array('Belarussian Rouble', '974'),
            'BOB' => array('Bolivian Boliviano', '068'),
            'BRL' => array('Brazilian Real', '986'),
            'GBP' => array('British Pounds Sterling', '826'),
            'BGN' => array('Bulgarian Lev', '975'),
            'KHR' => array('Cambodia Riel', '116'),
            'CAD' => array('Canadian Dollars', '124'),
            'KYD' => array('Cayman Islands Dollar', '136'),
            'CLP' => array('Chilean Peso', '152'),
            'CNY' => array('Chinese Renminbi Yuan', '156'),
            'COP' => array('Colombian Peso', '170'),
            'CRC' => array('Costa Rican Colon', '188'),
            'HRK' => array('Croatia Kuna', '191'),
            'CPY' => array('Cypriot Pounds', '196'),
            'CZK' => array('Czech Koruna', '203'),
            'DKK' => array('Danish Krone', '208'),
            'DOP' => array('Dominican Republic Peso', '214'),
            'XCD' => array('East Caribbean Dollar', '951'),
            'EGP' => array('Egyptian Pound', '818'),
            'ERN' => array('Eritrean Nakfa', '232'),
            'EEK' => array('Estonia Kroon', '233'),
            'EUR' => array('Euro', '978'),
            'GEL' => array('Georgian Lari', '981'),
            'GHC' => array('Ghana Cedi', '288'),
            'GIP' => array('Gibraltar Pound', '292'),
            'GTQ' => array('Guatemala Quetzal', '320'),
            'HNL' => array('Honduras Lempira', '340'),
            'HKD' => array('Hong Kong Dollars', '344'),
            'HUF' => array('Hungary Forint', '348'),
            'ISK' => array('Icelandic Krona', '352'),
            'INR' => array('Indian Rupee', '356'),
            'IDR' => array('Indonesia Rupiah', '360'),
            'ILS' => array('Israel Shekel', '376'),
            'JMD' => array('Jamaican Dollar', '388'),
            'JPY' => array('Japanese yen', '392'),
            'KZT' => array('Kazakhstan Tenge', '368'),
            'KES' => array('Kenyan Shilling', '404'),
            'KWD' => array('Kuwaiti Dinar', '414'),
            'LVL' => array('Latvia Lat', '428'),
            'LBP' => array('Lebanese Pound', '422'),
            'LTL' => array('Lithuania Litas', '440'),
            'MOP' => array('Macau Pataca', '446'),
            'MKD' => array('Macedonian Denar', '807'),
            'MGA' => array('Malagascy Ariary', '969'),
            'MYR' => array('Malaysian Ringgit', '458'),
            'MTL' => array('Maltese Lira', '470'),
            'BAM' => array('Marka', '977'),
            'MUR' => array('Mauritius Rupee', '480'),
            'MXN' => array('Mexican Pesos', '484'),
            'MZM' => array('Mozambique Metical', '508'),
            'NPR' => array('Nepalese Rupee', '524'),
            'ANG' => array('Netherlands Antilles Guilder', '532'),
            'TWD' => array('New Taiwanese Dollars', '901'),
            'NZD' => array('New Zealand Dollars', '554'),
            'NIO' => array('Nicaragua Cordoba', '558'),
            'NGN' => array('Nigeria Naira', '566'),
            'KPW' => array('North Korean Won', '408'),
            'NOK' => array('Norwegian Krone', '578'),
            'OMR' => array('Omani Riyal', '512'),
            'PKR' => array('Pakistani Rupee', '586'),
            'PYG' => array('Paraguay Guarani', '600'),
            'PEN' => array('Peru New Sol', '604'),
            'PHP' => array('Philippine Pesos', '608'),
            'PLN' => array('Polish złoty', '985'),
            'QAR' => array('Qatari Riyal', '634'),
            'RON' => array('Romanian New Leu', '946'),
            'RUB' => array('Russian Federation Ruble', '643'),
            'SAR' => array('Saudi Riyal', '682'),
            'CSD' => array('Serbian Dinar', '891'),
            'SCR' => array('Seychelles Rupee', '690'),
            'SGD' => array('Singapore Dollars', '702'),
            'SKK' => array('Slovak Koruna', '703'),
            'SIT' => array('Slovenia Tolar', '705'),
            'ZAR' => array('South African Rand', '710'),
            'KRW' => array('South Korean Won', '410'),
            'LKR' => array('Sri Lankan Rupee', '144'),
            'SRD' => array('Surinam Dollar', '968'),
            'SEK' => array('Swedish Krona', '752'),
            'CHF' => array('Swiss Francs', '756'),
            'TZS' => array('Tanzanian Shilling', '834'),
            'THB' => array('Thai Baht', '764'),
            'TTD' => array('Trinidad and Tobago Dollar', '780'),
            'TRY' => array('Turkish New Lira', '949'),
            'AED' => array('UAE Dirham', '784'),
            'USD' => array('US Dollars', '840'),
            'UGX' => array('Ugandian Shilling', '800'),
            'UAH' => array('Ukraine Hryvna', '980'),
            'UYU' => array('Uruguayan Peso', '858'),
            'UZS' => array('Uzbekistani Som', '860'),
            'VEB' => array('Venezuela Bolivar', '862'),
            'VND' => array('Vietnam Dong', '704'),
            'AMK' => array('Zambian Kwacha', '894'),
            'ZWD' => array('Zimbabwe Dollar', '716'),
        );

        if (isset($currency[$shopCurrency][1])) {
            return $currency[$shopCurrency][1];
        } else {
            return $currency['EUR'][1];  // default to EUR
        }
    }

    /** BASKET ITEMS */
    /**
     * Get possible options for sending basket items
     *
     * @return array
     */
    public static function getBasketItemsOptions()
    {
        return [
            self::BASKET_ITEMS_NO_SEND => __('Do not send basket items', WC_VERIFONE_DOMAIN),
            self::BASKET_ITEMS_SEND_FOR_ALL => __('Send for all payment methods', WC_VERIFONE_DOMAIN),
            self::BASKET_ITEMS_SEND_FOR_INVOICE => __('Send only for invoice payment methods', WC_VERIFONE_DOMAIN)
        ];
    }

    /**
     * Get all available payment methods for display in configuration
     *
     * @return array
     */
    public static function getPaymentMethodsOptions()
    {
        $methods = [];
        $available = WC_Verifone_PaymentMethods::getPaymentMethods();

        foreach ($available as $code) {
            $methods[$code] = WC_Verifone_PaymentMethods::getPaymentMethodDisplayName($code);
        }

        return $methods;
    }

    /**
     * Convert country code to number
     *
     * @param string $cc
     * @return string
     */
    public static function convertCountryCode2Numeric($cc)
    {

        $cc = strtoupper($cc);

        $codes = array(
            'AF' => 4, 'AL' => 8, 'DZ' => 12, 'AS' => 16, 'AD' => 20, 'AO' => 24, 'AI' => 660, 'AQ' => 10,
            'AG' => 28, 'AR' => 32, 'AM' => 51, 'AW' => 533, 'AU' => 36, 'AT' => 40, 'AZ' => 31, 'BS' => 44,
            'BH' => 48, 'BD' => 50, 'BB' => 52, 'BY' => 112, 'BE' => 56, 'BZ' => 84, 'BJ' => 204, 'BM' => 60,
            'BT' => 64, 'BO' => 68, 'BA' => 70, 'BW' => 72, 'BV' => 74, 'BR' => 76, 'IO' => 86, 'BN' => 96,
            'BG' => 100, 'BF' => 854, 'BI' => 108, 'KH' => 116, 'CM' => 120, 'CA' => 124, 'CV' => 132, 'KY' => 136,
            'CF' => 140, 'TD' => 148, 'CL' => 152, 'CN' => 156, 'CX' => 162, 'CC' => 166, 'CO' => 170, 'KM' => 174,
            'CG' => 178, 'CK' => 184, 'CR' => 188, 'CI' => 384, 'HR' => 191, 'CU' => 192, 'CY' => 196, 'CZ' => 203,
            'DK' => 208, 'DJ' => 262, 'DM' => 212, 'DO' => 214, 'TP' => 626, 'EC' => 218, 'EG' => 818, 'SV' => 222,
            'GQ' => 226, 'ER' => 232, 'EE' => 233, 'ET' => 231, 'FK' => 238, 'FO' => 234, 'FJ' => 242, 'FI' => 246,
            'FR' => 250, 'FX' => 249, 'GF' => 254, 'PF' => 258, 'TF' => 260, 'GA' => 266, 'GM' => 270, 'GE' => 268,
            'DE' => 276, 'GH' => 288, 'GI' => 292, 'GR' => 300, 'GL' => 304, 'GD' => 308, 'GP' => 312, 'GU' => 316,
            'GT' => 320, 'GN' => 324, 'GW' => 624, 'GY' => 328, 'HT' => 332, 'HM' => 334, 'VA' => 336, 'HN' => 340,
            'HK' => 344, 'HU' => 348, 'IS' => 352, 'IN' => 356, 'ID' => 360, 'IR' => 364, 'IQ' => 368, 'IE' => 372,
            'IL' => 376, 'IT' => 380, 'JM' => 388, 'JP' => 392, 'JO' => 400, 'KZ' => 398, 'KE' => 404, 'KI' => 296,
            'KP' => 408, 'KR' => 410, 'KW' => 414, 'KG' => 417, 'LA' => 418, 'LV' => 428, 'LB' => 422, 'LS' => 426,
            'LR' => 430, 'LY' => 434, 'LI' => 438, 'LT' => 440, 'LU' => 442, 'MO' => 446, 'MK' => 807, 'MG' => 450,
            'MW' => 454, 'MY' => 458, 'MV' => 462, 'ML' => 466, 'MT' => 470, 'MH' => 584, 'MQ' => 474, 'MR' => 478,
            'MU' => 480, 'YT' => 175, 'MX' => 484, 'FM' => 583, 'MD' => 498, 'MC' => 492, 'MN' => 496, 'MS' => 500,
            'MA' => 504, 'MZ' => 508, 'MM' => 104, 'NA' => 516, 'NR' => 520, 'NP' => 524, 'NL' => 528, 'AN' => 530,
            'NC' => 540, 'NZ' => 554, 'NI' => 558, 'NE' => 562, 'NG' => 566, 'NU' => 570, 'NF' => 574, 'MP' => 580,
            'NO' => 578, 'OM' => 512, 'PK' => 586, 'PW' => 585, 'PA' => 591, 'PG' => 598, 'PY' => 600, 'PE' => 604,
            'PH' => 608, 'PN' => 612, 'PL' => 616, 'PT' => 620, 'PR' => 630, 'QA' => 634, 'RE' => 638, 'RO' => 642,
            'RU' => 643, 'RW' => 646, 'KN' => 659, 'LC' => 662, 'VC' => 670, 'WS' => 882, 'SM' => 674, 'ST' => 678,
            'SA' => 682, 'SN' => 686, 'SC' => 690, 'SL' => 694, 'SG' => 702, 'SK' => 703, 'SI' => 705, 'SB' => 90,
            'SO' => 706, 'ZA' => 710, 'GS' => 239, 'ES' => 724, 'LK' => 144, 'SH' => 654, 'PM' => 666, 'SD' => 736,
            'SR' => 740, 'SJ' => 744, 'SZ' => 748, 'SE' => 752, 'CH' => 756, 'SY' => 760, 'TW' => 158, 'TJ' => 762,
            'TZ' => 834, 'TH' => 764, 'TG' => 768, 'TK' => 772, 'TO' => 776, 'TT' => 780, 'TN' => 788, 'TR' => 792,
            'TM' => 795, 'TC' => 796, 'TV' => 798, 'UG' => 800, 'UA' => 804, 'AE' => 784, 'GB' => 826, 'US' => 840,
            'UM' => 581, 'UY' => 858, 'UZ' => 860, 'VU' => 548, 'VE' => 862, 'VN' => 704, 'VG' => 92, 'VI' => 850,
            'WF' => 876, 'EH' => 732, 'YE' => 887, 'YU' => 891, 'ZR' => 180, 'ZM' => 894, 'ZW' => 716);

        if (isset($codes[$cc])) {
            return $codes[$cc];
        } else {
            return $codes['FI'];  // default to Finland
        }
    }

}