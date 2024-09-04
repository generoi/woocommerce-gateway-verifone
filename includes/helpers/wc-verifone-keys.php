<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is released under commercial license by Lamia Oy.
 *
 * @copyright Copyright (c) 2018 Lamia Oy (https://lamia.fi)
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WC_Verifone_Keys
{
    const STORAGE_KEY_LIVE_PUBLIC = 'woocommerce_verifone_keys_live_public';
    const STORAGE_KEY_LIVE_PRIVATE = 'woocommerce_verifone_keys_live_private';
    const STORAGE_KEY_TEST_PUBLIC = 'woocommerce_verifone_keys_test_public';
    const STORAGE_KEY_TEST_PRIVATE = 'woocommerce_verifone_keys_test_private';

    const STORAGE_KEY_GATEWAY_LIVE = 'verifone-e-commerce-live-public-key.pem';
    const STORAGE_KEY_GATEWAY_TEST = 'verifone-e-commerce-test-public-key.pem';

    public static function getLivePrivateKey()
    {
        return get_option(self::STORAGE_KEY_LIVE_PRIVATE, null);
    }

    public static function getLivePublicKey()
    {
        return get_option(self::STORAGE_KEY_LIVE_PUBLIC, null);
    }

    public static function getTestPrivateKey()
    {
        return get_option(self::STORAGE_KEY_TEST_PRIVATE, null);
    }

    public static function getTestPublicKey()
    {
        return get_option(self::STORAGE_KEY_TEST_PUBLIC, null);
    }

    public static function storeKeys($type, $publicKey, $privateKey)
    {

        $updated = true;

        if ($type === 'live') {
            $updated = $updated && update_option(self::STORAGE_KEY_LIVE_PRIVATE, $privateKey);
            $updated = $updated && update_option(self::STORAGE_KEY_LIVE_PUBLIC, $publicKey);
        } else {
            $updated = $updated && update_option(self::STORAGE_KEY_TEST_PRIVATE, $privateKey);
            $updated = $updated && update_option(self::STORAGE_KEY_TEST_PUBLIC, $publicKey);
        }

        return $updated;
    }
}