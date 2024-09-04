<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is released under commercial license by Lamia Oy.
 *
 * @copyright Copyright (c) 2018 Lamia Oy (https://lamia.fi)
 */

class WC_Verifone_Summary
{
    public static function getConfigurationDataForDisplay()
    {

        $config = WC_Verifone_Config::getInstance();

        /** Data for display */
        $display = array();

        $display['isLiveMode'] = array(
            'label' => __('Mode', WC_VERIFONE_DOMAIN),
            'value' => $config->isLiveMode() ? __('Production', WC_VERIFONE_DOMAIN) : __('Test', WC_VERIFONE_DOMAIN),
            'has_desc' => false,
            'has_desc_class' => false
        );

        $display['merchantCode'] = array(
            'label' => __('Verifone Payment merchant agreement code', WC_VERIFONE_DOMAIN),
            'value' => $config->getMerchantAgreement(),
            'has_desc' => false,
            'has_desc_class' => false
        );
        if ($config->getMerchantAgreement() === $config->getMerchantAgreementDefault()) {
            $display['merchantCode']['desc'] = __('Default test merchant agreement uses', WC_VERIFONE_DOMAIN);
            $display['merchantCode']['desc_class'] = 'info';
            $display['merchantCode']['has_desc'] = true;
            $display['merchantCode']['has_desc_class'] = true;
        }

        $display['delayedUrl'] = array(
            'label' => __('Delayed success url', WC_VERIFONE_DOMAIN),
            'value' => WC_Verifone_Urls::getPaymentDelayedLink(),
            'desc' => __('This is the url that you need to copy to payment provider settings in their portal.', WC_VERIFONE_DOMAIN),
            'desc_class' => 'success',
            'has_desc' => true,
            'has_desc_class' => true
        );

        $display['keyHandlingMode'] = array(
            'label' => __('Key handling mode', WC_VERIFONE_DOMAIN),
            'value' => $config->isKeySimpleMode() ? __('Automatic (Simple)', WC_VERIFONE_DOMAIN) : __('Manual (Advanced)', WC_VERIFONE_DOMAIN),
            'has_desc' => false,
            'has_desc_class' => false
        );

        $display['paymentServiceKey'] = array(
            'label' => __('Path and filename of Verifone Payment public key file', WC_VERIFONE_DOMAIN),
            'value' => $config->getPaymentPublicKeyPath(),
            'has_desc' => false,
            'has_desc_class' => false
        );

        if (file_exists($config->getPaymentPublicKeyPath())) {
            $display['paymentServiceKey']['desc'] = __('Key file is available', WC_VERIFONE_DOMAIN);
            $display['paymentServiceKey']['desc_class'] = 'success';
            $display['paymentServiceKey']['has_desc'] = true;
            $display['paymentServiceKey']['has_desc_class'] = true;
        } else {
            $display['paymentServiceKey']['desc'] = __('Problem with load key file. Please contact with customer service', WC_VERIFONE_DOMAIN);
            $display['paymentServiceKey']['desc_class'] = 'success';
            $display['paymentServiceKey']['has_desc'] = true;
            $display['paymentServiceKey']['has_desc_class'] = true;
        }

        if ($config->isKeyAdvancedMode()) {

            $path = $config->getKeysDirectory();

            $display['directory'] = array(
                'label' => __('Directory for store keys', WC_VERIFONE_DOMAIN),
                'value' => $path,
                'has_desc' => false,
                'has_desc_class' => false
            );
            if (file_exists($path) && is_writable($path)) {
                $display['directory']['desc'] = __('Directory configured properly', WC_VERIFONE_DOMAIN);
                $display['directory']['desc_class'] = 'success';
                $display['directory']['has_desc'] = true;
                $display['directory']['has_desc_class'] = true;
            } else {
                $display['directory']['desc'] = __('Problem with directory configuration. Please check configuration and save.', WC_VERIFONE_DOMAIN);
                $display['directory']['desc_class'] = 'error';
                $display['directory']['has_desc'] = true;
                $display['directory']['has_desc_class'] = true;
            }

            if($config->getMerchantAgreementDefault() !== $config->getMerchantAgreement()) {
                $display['shopPrivateKey'] = array(
                    'label' => __('Path and filename of shop private key file', WC_VERIFONE_DOMAIN),
                    'value' => $config->getShopPrivateKeyPath(),
                    'has_desc' => false,
                    'has_desc_class' => false
                );

                if (file_exists($display['shopPrivateKey']['value']) && !empty($config->getShopPrivateKeyFileName())) {
                    $display['shopPrivateKey']['desc'] = __('Key file is available', WC_VERIFONE_DOMAIN);
                    $display['shopPrivateKey']['desc_class'] = 'success';
                    $display['shopPrivateKey']['has_desc'] = true;
                    $display['shopPrivateKey']['has_desc_class'] = true;
                } else {
                    $display['shopPrivateKey']['desc'] = __('Key file is not available', WC_VERIFONE_DOMAIN);
                    $display['shopPrivateKey']['desc_class'] = 'error';
                    $display['shopPrivateKey']['has_desc'] = true;
                    $display['shopPrivateKey']['has_desc_class'] = true;
                }
            } else {
                $display['shopPrivateKey'] = array(
                    'label' => __('Path and filename of shop private key file', WC_VERIFONE_DOMAIN),
                    'value' => '',
                    'has_desc' => true,
                    'has_desc_class' => true,
                    'desc' => __('Default key file is used', WC_VERIFONE_DOMAIN),
                    'desc_class' => 'info'
                );
            }
        } else {

            $display['shopPrivateKey'] = array(
                'label' => __('Path and filename of shop private key file', WC_VERIFONE_DOMAIN),
                'value' => 'Key file stored in database',
                'has_desc' => false,
                'has_desc_class' => false
            );

            if ($config->getShopPrivateKey() !== null && $config->getShopPrivateKey() !== $config->getShopPrivateKeyDefault()) {
                $display['shopPrivateKey']['desc'] = __('Key file is available', WC_VERIFONE_DOMAIN);
                $display['shopPrivateKey']['desc_class'] = 'success';
                $display['shopPrivateKey']['has_desc'] = true;
                $display['shopPrivateKey']['has_desc_class'] = true;
            } elseif(!$config->isLiveMode() && $config->getMerchantAgreement() === $config->getMerchantAgreementDefault()) {
                $display['shopPrivateKey']['desc'] = __('Default key file is used', WC_VERIFONE_DOMAIN);
                $display['shopPrivateKey']['desc_class'] = 'info';
                $display['shopPrivateKey']['has_desc'] = true;
                $display['shopPrivateKey']['has_desc_class'] = true;
            } else {
                $display['shopPrivateKey']['desc'] = __('Problem with fetch shop private key file. Please check configuration and/or generate key', WC_VERIFONE_DOMAIN);
                $display['shopPrivateKey']['desc_class'] = 'error';
                $display['shopPrivateKey']['has_desc'] = true;
                $display['shopPrivateKey']['has_desc_class'] = true;
            }
        }

        if ($config->isKeySimpleMode() && $config->getShopPublicKey() === null && $config->getMerchantAgreement() !== $config->getMerchantAgreementDefault()) {
            $display['shopPublicKeyContent'] = array(
                'label' => __('Public key file'),
                'value' => '',
                'has_desc' => true,
                'has_desc_class' => true,
                'desc' => __('Problem with fetch shop public key file. Please check configuration and/or generate key', WC_VERIFONE_DOMAIN),
                'desc_class' => 'error'
            );
        } elseif ($config->getShopPublicKey() !== null) {
            $display['shopPublicKeyContent'] = array(
                'label' => __('Public key file'),
                'value' => $config->getShopPublicKey(),
                'has_desc' => true,
                'has_desc_class' => true,
                'desc' => __('Please, copy this key to payment operator configuration settings, otherwise, the payment will be broken.', WC_VERIFONE_DOMAIN),
                'desc_class' => 'success'
            );
        }

        return $display;
    }

}