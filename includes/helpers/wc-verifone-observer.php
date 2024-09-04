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

use \Verifone\Core\DependencyInjection\CoreResponse\PaymentResponseImpl;
use \Verifone\Core\DependencyInjection\CoreResponse\Interfaces\Card;

class WC_Verifone_Observer
{

    /**
     *
     */
    public static function refreshSavedCards()
    {
        if (WC_Verifone_Config::getInstance()->isAllowToSaveCC()) {
            try {
                $response = WC_Verifone_Core_SavedPaymentMethods::getListSavedPaymentMethods();

                if($response === null) {
                    return false;
                }

                $cards = $response->getBody();
                if (count($cards)) {
                    foreach ($cards as $card) {
                        WC_Verifone_Core_SavedPaymentMethods::addCard($card);
                    }
                }
            } catch (Exception $e) {
                // no cards
            }
        }
    }

    /**
     * @param WC_Order $order
     * @param PaymentResponseImpl $body
     * @return array
     */
    public static function addMaskedPanNumber($order, $body)
    {
        $config = WC_Verifone_Config::getInstance();
        if (!$config->isSaveMaskedPanNumber()) {
            return [$order, $body];
        }

        /** @var Card $card */
        $card = $body->getCard();

        if (
            strlen($card->getFirst6()) &&
            $card->getFirst6() &&
            strlen($card->getLast2()) &&
            $card->getLast2()
        ) {

            if (empty($order->get_meta('masked_pan_number'))) {
                $maskedPanNumber = $card->getFirst6() . '********' . $card->getLast2();
                $order->add_meta_data('masked_pan_number', $maskedPanNumber);
            }

            return [$order, $body];
        }

        return [$order, $body];
    }

    /**
     * Add a custom action to order actions select box on edit order page
     * Only added for paid orders that haven't fired this action yet
     *
     * @param array $actions order actions array to display
     * @return array - updated actions
     */
    public static function addCheckOrderPaymentAction($actions)
    {
        /** @var WC_Order $theorder */
        global $theorder;

        if ($theorder->get_payment_method() != WC_VERIFONE_GATEWAY_ID) {
            return $actions;
        }

        if ($theorder->is_paid()) {
            return $actions;
        }

        $actions['verifone_check_order_payment'] = __('Verifone: Check payment status', WC_VERIFONE_DOMAIN);
        return $actions;

    }

    public static function executeCheckOrderPaymentAction(WC_Order $order, $cron = false)
    {
        if ($order->is_paid()) {
            return true;
        }

        $result = WC_Verifone_Payment::checkPaymentStatus($order, $cron);

        if ($result) {
            $status = $order->needs_processing() ? 'processing' : 'completed';
            $message = sprintf(__('Order status is now [%s] because payment for this order was found.', WC_VERIFONE_DOMAIN), $status);
            if (!$cron) {
                WC_Verifone_Notice::addSuccess($message, false, true);
            }
        } elseif ($result === null) {
            $message = __('Order status is now [pending] because payment for this order was NOT found.', WC_VERIFONE_DOMAIN);
            if (!$cron) {
                WC_Verifone_Notice::addInfo($message, false, true);
            }
        } else {
            $message = __('Order status is now [cancel] because payment for this order was cancelled', WC_VERIFONE_DOMAIN);
            if (!$cron) {
                WC_Verifone_Notice::addError($message, false, true);
            }
        }

        if (!$cron) {
            $note = __('Manually check order payment status result: ', WC_VERIFONE_DOMAIN);
        } else {
            $note = __('Cron check order payment status result: ', WC_VERIFONE_DOMAIN);
        }
        $order->add_order_note($note . '<br>' . $message);
    }


}