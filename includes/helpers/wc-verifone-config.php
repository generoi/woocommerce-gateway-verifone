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


class WC_Verifone_Config extends WC_Settings_API
{

    protected $_objectData = [];

    protected $_settingsObject = null;

    /**
     * @var WC_Verifone_Config $instance Singleton The reference the *Singleton* instance of this class
     */
    private static $instance;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return WC_Verifone_Config Singleton The *Singleton* instance.
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct()
    {
        $this->id = WC_VERIFONE_GATEWAY_ID;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function __get($key)
    {
        if (isset($this->_objectData[$key])) {
            return $this->_objectData[$key];
        } else {
            return null;
        }
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function __set($key, $value)
    {
        $this->_objectData[$key] = $value;
        return $this;
    }

    /**
     * Method for get data from settings. if exist get from current object, if not fetch from settings.
     *
     * @param $key
     * @return mixed|null
     */
    public function getData($key)
    {
        if ($this->$key === null) {
            $this->$key = $this->get_option($key);
        }

        return $this->$key;
    }

    protected function _getTestLiveOption($key, $force = false)
    {

        if ($force) {
            return $this->getData($key);
        }

        if ($this->isLiveMode()) {
            return $this->getData($key);
        } elseif (!empty($this->getData($key . '_test'))) {
            return $this->getData($key . '_test');
        } else {
            return $this->getDefault($key);
        }

    }

    public function getDefault($key)
    {
        $settingsObject = new WC_Verifone_Settings();
        return $settingsObject->getDefaultValue($key);
    }

    // FIELDS
    public function getMerchantAgreement()
    {

        if ($this->isLiveMode()) {
            return $this->getData('merchant_agreement_code');
        }

        if (!empty($this->getData('merchant_agreement_code_test'))) {
            return $this->getData('merchant_agreement_code_test');
        }

        return $this->getDefault('merchant_agreement_code_test');

    }

    public function getMerchantAgreementDefault()
    {
        return $this->getDefault('merchant_agreement_code_test');
    }

    public function isLiveMode()
    {
        return (bool)$this->getData('is_live_mode');
    }

    public function getKeyMode()
    {
        return $this->getData('key_handling_mode');
    }

    public function isKeySimpleMode()
    {
        return (int)$this->getKeyMode() == 0;
    }

    public function isKeyAdvancedMode()
    {
        return (int)$this->getKeyMode() == 1;
    }

    public function getPayPageUrl($index)
    {
        return $this->getData('pay_page_url_' . $index);
    }

    public function getPaymentPageLanguage()
    {
        return $this->getData('payment_page_language');
    }

    public function isValidateUrl()
    {
        return (bool)$this->getData('validate_url');
    }

    public function isSkipConfirmationPage()
    {
        return (bool)$this->getData('skip_confirmation_page');
    }

    public function getBasketItemSending()
    {
        return $this->getData('basket_item_sending');
    }

    public function isCombineInvoiceBasketItems()
    {
        return (bool)$this->getData('combine_invoice_basket_items');
    }

    public function getExternalCustomerIdField()
    {
        return $this->getData('external_customer_id_field');
    }

    public function getPaymentMethods()
    {
        return $this->getData('payment_methods');
    }

    public function isAllowToSaveCC()
    {
        return (bool)$this->getData('allow_to_save_cc');
    }

    public function isSaveMaskedPanNumber()
    {
        return (bool)$this->getData('save_masked_pan_number');
    }

    public function getRememberCCInfo()
    {
        return $this->getData('remember_cc_info');
    }

    public function getPaymentMessage()
    {
        return $this->getData('payment_message');
    }

    public function getMinOrderTotal()
    {
        return $this->getData('min_order_total');
    }

    public function getMaxOrderTotal()
    {
        return $this->getData('max_order_total');
    }

    public function isDisableRsaBlinding()
    {
        return (bool)$this->getData('disable_rsa_blinding');
    }

    public function getStyleCode()
    {
        return $this->getData('style_code');
    }

    /** KEYS FILES */
    public function getKeysDirectory()
    {
        return $this->getData('keys_directory');
    }

    public function getShopPrivateKeyFileName()
    {
        if($this->isLiveMode()) {
            return $this->getData('shop_private_keyfile');
        }

        return $this->getData('shop_private_keyfile_test');
    }

    public function getLiveShopPrivateKeyPath()
    {

        if ($this->isKeySimpleMode()) {
            return null;
        }

        return $this->getKeysDirectory() . DIRECTORY_SEPARATOR . $this->getData('shop_private_keyfile');

    }

    public function getTestShopPrivateKeyPath()
    {

        if ($this->isKeySimpleMode()) {
            return null;
        }

        return $this->getKeysDirectory() . DIRECTORY_SEPARATOR . $this->getData('shop_private_keyfile_test');
    }

    public function getShopPrivateKeyPath()
    {
        if($this->isLiveMode()) {
            return $this->getLiveShopPrivateKeyPath();
        }

        return $this->getTestShopPrivateKeyPath();
    }

    public function getShopPrivateKeyFile()
    {
        return $this->getShopPrivateKey();
    }

    public function getShopPrivateKey()
    {

        // If TEST mode is set
        if (!$this->isLiveMode()) {

            if ($this->getMerchantAgreement() === $this->getMerchantAgreementDefault()) {
                // If DEFAULT test merchant is set, return default key
                return $this->getShopPrivateKeyDefault();
            }

            if ($this->isKeySimpleMode()) {
                // If CUSTOM test merchant is set, and SIMPLE mode is set, return generated key stored in DB
                return WC_Verifone_Keys::getTestPrivateKey();
            }

            $path = $this->getTestShopPrivateKeyPath();
            if (file_exists($path)) {
                // If CUSTOM test merchant is set, and ADVANCED mode is set, return old key stored in files
                return file_get_contents($path);
            }

            // return default key file
            return $this->getShopPrivateKeyDefault();
        }

        // If LIVE mode is set

        if ($this->isKeySimpleMode()) {
            // If CUSTOM test merchant is set, and SIMPLE mode is set, return generated key stored in DB
            return WC_Verifone_Keys::getLivePrivateKey();
        }

        $path = $this->getLiveShopPrivateKeyPath();
        if (file_exists($path)) {
            // If CUSTOM test merchant is set, and ADVANCED mode is set, return old key stored in files
            return file_get_contents($path);
        }

        // return nothing
        return null;
    }

    public function getShopPrivateKeyPathDefault()
    {
        return WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'keys' . DIRECTORY_SEPARATOR . 'demo-merchant-agreement-private.pem';
    }

    public function getShopPrivateKeyDefault()
    {
        return file_get_contents($this->getShopPrivateKeyPathDefault());
    }

    public function getShopPublicKey()
    {
        // If TEST mode is set
        if (!$this->isLiveMode()) {

            if ($this->getMerchantAgreement() === $this->getMerchantAgreementDefault()) {
                // If DEFAULT test merchant is set, return default key
                // For default simple key is not require to configure in payment service.
                return null;
            }

            if ($this->isKeySimpleMode()) {
                // If CUSTOM test merchant is set, and SIMPLE mode is set, return generated key stored in DB
                return WC_Verifone_Keys::getTestPublicKey();
            }


            // If CUSTOM test merchant is set, and ADVANCED mode is set, return old key stored in files
            // This is not required, because if this mode is set, it means that payment service is configured,
            // and it does not require to configure again.
            return null;
        }

        // If LIVE mode is set
        if ($this->isKeySimpleMode()) {
            // If CUSTOM test merchant is set, and SIMPLE mode is set, return generated key stored in DB
            return WC_Verifone_Keys::getLivePublicKey();
        }

        // When advanced mode is set, or is default merchant agreement then in not require simple key to display.
        return null;
    }

    public function getShopPublicKeyPathDefault()
    {
        return WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'keys' . DIRECTORY_SEPARATOR . 'demo-merchant-agreement-public.pem';
    }

    public function getShopPublicKeyDefault()
    {
        return file_get_contents($this->getShopPublicKeyPathDefault());
    }

    public function getPaymentPublicKeyPath()
    {
        if($this->isLiveMode()) {
            return WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'keys' . DIRECTORY_SEPARATOR . WC_Verifone_Keys::STORAGE_KEY_GATEWAY_LIVE;
        }

        return WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WC_VERIFONE_DOMAIN . DIRECTORY_SEPARATOR . 'keys' . DIRECTORY_SEPARATOR . WC_Verifone_Keys::STORAGE_KEY_GATEWAY_TEST;
    }

    public function getPaymentPublicKeyFile()
    {
        return file_get_contents($this->getPaymentPublicKeyPath());
    }

}
