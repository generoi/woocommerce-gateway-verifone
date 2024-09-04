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

class WC_Verifone_Core_Generator extends WC_Verifone_Core
{
    const STORAGE_KEY = 'woocommerce_verifone_keys';


    public static function generateKeys()
    {
        require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

        $success = true;
        $messages = array();

        $type = $_POST['type'];

        if(empty($type)) {
            $success = false;
            $messages[] = __('Problem with generating new keys.', WC_VERIFONE_DOMAIN) . __('Please refresh the page and try again.', WC_VERIFONE_DOMAIN);
        }

        if (!$success) {
            return self::_returnJson($success, $messages);
        }

        $generator = new \Verifone\Core\DependencyInjection\CryptUtils\RsaKeyGenerator();
        $resultGenerate = $generator->generate();

        if ($resultGenerate) {
            $resultStoreKey = WC_Verifone_Keys::storeKeys($type, $generator->getPublicKey(), $generator->getPrivateKey());

            if($resultStoreKey === true) {
                $messages[] = __('Keys are generated correctly. Please refresh or save the configuration.', WC_VERIFONE_DOMAIN);
                return self::_returnJson($success, $messages);
            }

            $success = false;
            $messages[] = $resultStoreKey;

        } else {
            $success = false;
            $messages[] = __('Problem with generating new keys.', WC_VERIFONE_DOMAIN);
        }

        return self::_returnJson($success, $messages);

    }

    protected static function _returnJson($success, $messages)
    {
        return array('success' => $success, 'messages' => $messages);
    }
}