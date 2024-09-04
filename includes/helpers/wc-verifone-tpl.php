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

require_once plugin_dir_path(__FILE__) . 'tpl/sviews.class.php';

class WC_Verifone_Tpl
{

    const REQUEST_FORM = 'request-form.thtml';
    const PAYMENT_METHODS_FORM = 'payment-methods-form.thtml';
    const SUMMARY = 'summary.thtml';

    public static function render($context, $filename)
    {
        $tpl = new SViewsVerifone();

        $tpl->render($filename, $context);
    }
}