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
use \Verifone\Core\Converter\Response\CoreResponseConverter;
use \Verifone\Core\DependencyInjection\Service\TransactionImpl;
use \Verifone\Core\DependencyInjection\CoreResponse\PaymentStatusImpl;

class WC_Verifone_Payment
{

    const RETRY_DELAY_IN_SECONDS = 2;
    const RETRY_MAX_ATTEMPTS = 5;

    /**
     * @param WC_Order $order
     * @param PaymentResponseImpl $responseBody
     */
    public static function doneOrder(WC_Order $order, $responseBody)
    {
        $trans_id = preg_replace("/[^0-9]+/", "", $responseBody->getTransactionNumber());
        $_transactionId = $responseBody->getTransactionNumber();

        $order->add_meta_data('ext_order_id', $trans_id);
        $order->add_meta_data('payment_method', $responseBody->getPaymentMethodCode());

        // save masked pan number if available
        do_action_ref_array('verifone_add_masked_pan_number', array(&$order, $responseBody));

        $amount = $responseBody->getOrderGrossAmount() / 100;
        $note = sprintf(__('Payment captured. Transaction id: %s, transaction amount: %s', WC_VERIFONE_DOMAIN), $_transactionId, wc_price($amount));
        $order->add_order_note($note);
        $order->payment_complete($_transactionId);

    }

    public static function processSuccess($status, $delayedSuccess = false)
    {

        $params = $_POST;

        $returnData = [
            'status' => $status,
            'error' => true,
            'message' => ''
        ];

        $orderNumber = WC_Verifone_Core_Payment::getOrderNumberFromResponse($params);

        if (empty($orderNumber)) {
            return self::processFail($status);
        }

        $order = new WC_Order($orderNumber);

        if ($order->is_paid()) {
            // Don't need to process paid order.
            return self::_returnPaidOrderData($order, $returnData);
        }

        $attempts = 0;
        while (!WC_Verifone_OrderLocker::lockOrder($orderNumber) && $attempts < self::RETRY_MAX_ATTEMPTS) {
            sleep(self::RETRY_DELAY_IN_SECONDS);
            ++$attempts;
        }

        if ($attempts > 0 && $attempts < self::RETRY_MAX_ATTEMPTS) {
            $orderTmp = new WC_Order($orderNumber);

            if ($orderTmp->is_paid()) {
                // Don't need to process paid order.
                return self::_returnPaidOrderData($orderTmp, $returnData);
            }

        }

        try {
            $parsedResponse = WC_Verifone_Core_Payment::validateAndParsePaymentResponse($params, $order);

            /** @var PaymentResponseImpl $body */
            $body = $parsedResponse->getBody();
            $validate = true;
        } catch (Exception $e) {
            return self::processFail($status);
        }

        if ($validate
            && $parsedResponse->getStatusCode() == CoreResponseConverter::STATUS_OK
            && empty($body->getCancelMessage())
            && !$order->is_paid()
        ) {

            self::doneOrder($order, $body);

            WC_Verifone_OrderLocker::unlockOrder($orderNumber);

            $returnData['return_url'] = $order->get_checkout_order_received_url();
            $returnData['error'] = false;

        } else {
            return self::processFail($status);
        }

        return $returnData;
    }

    /**
     * @param WC_Order $order
     * @param array $data
     * @return array
     */
    protected static function _returnPaidOrderData($order, $data)
    {

        $orderNumber = $order->get_id();

        $data['return_url'] = $order->get_checkout_order_received_url();
        $data['error'] = false;

        if (WC_Verifone_OrderLocker::isLockedOrder($orderNumber)) {
            WC_Verifone_OrderLocker::unlockOrder($orderNumber);
        }

        return $data;
    }

    /**
     * @param $status
     * @return array
     */
    public static function processFail($status)
    {
        $params = $_POST;

        $returnData = [
            'status' => $status,
            'error' => true,
            'message' => ''
        ];

        $orderNumber = WC_Verifone_Core_Payment::getOrderNumberFromResponse($params);

        if (empty($orderNumber)) {
            $returnData['return_url'] = wc_get_checkout_url();
            return $returnData;
        }

        $order = new WC_Order($orderNumber);

        if ($order->is_paid()) {
            // Don't need to process paid order.
            // Order fail in case when customer return to the webshop after long time (expired).
            // but order is confirmed by delayed success action
            return self::_returnPaidOrderData($order, $returnData);
        }

        try {
            $parsedResponse = WC_Verifone_Core_Payment::validateAndParsePaymentResponse($params, $order);

            /** @var PaymentResponseImpl $body */
            $body = $parsedResponse->getBody();
            $validate = true;
        } catch (Exception $e) {
            $validate = false;
            $parsedResponse = null;
            $body = null;

            $returnData['message'] = __('Security Error. Illegal access detected', WC_VERIFONE_DOMAIN);
        }

        if (!$validate) {
            self::_cancelOrder($order, $returnData['message']);
        } else {
            $returnData['message'] = sprintf(__('Payment was canceled. Cancel reason: %s', WC_VERIFONE_DOMAIN), $body->getCancelMessage());
            self::_cancelOrder($order, $returnData['message']);
        }

        self::_restoreCart($order);

        $returnData['return_url'] = $order->get_cancel_order_url();
        $returnData['order'] = $order;

        return $returnData;
    }

    protected static function _restoreCart(WC_Order $order)
    {
        /** @var WooCommerce $woocommerce */
        global $woocommerce;

        /** @var WC_Order_Item $item */
        foreach ($order->get_items() as $item) {
            $data = $item->get_data();

            if (isset($data['product_id'])) {

                if(isset($data['variation_id'])) {
                    $variations = [];
                    $item_meta_data = $item->get_meta_data();
                    foreach($item_meta_data as $meta_data) {
                        $variation_data = $meta_data->get_data();
                        $variations[$variation_data['key']] = $variation_data['value'];
                    }
                    $woocommerce->cart->add_to_cart($data['product_id'], $item->get_quantity(), $data['variation_id']);
                } else {
                    $woocommerce->cart->add_to_cart($data['product_id'], $item->get_quantity());
                }
            }

        }

    }

    /**
     * @param WC_Order $order
     * @param $note
     */
    protected static function _cancelOrder(WC_Order $order, $note)
    {
        $order->update_status('cancelled', $note);
    }

    public static function checkPaymentStatus(WC_Order $order, $cron = false)
    {
        $statuses = [
            'pending',
            'on-hold',
            'processing'
        ];

        if ($order->is_paid() || !in_array($order->get_status(), $statuses)) {
            return false;
        }

        if ($cron && !self::transactionsCanBeCheck($order)) {
            return false;
        }

        $response = WC_Verifone_Core_Payment::getTransactionsFromGate($order);

        if (is_null($response)) {
            return false;
        }

        $totalPaid = 0;

        /** @var TransactionImpl $item */
        foreach ($response as $item) {
            $transactionCode = $item->getMethodCode();
            $transactionNumber = $item->getNumber();

            /** @var PaymentStatusImpl $transaction */
            $transaction = WC_Verifone_Core_Payment::getPaymentStatus($transactionCode, $transactionNumber);

            if (!is_null($transaction)) {
                $transactions[] = $transaction;

                $totalPaid += $transaction->getOrderAmount();

                if (self::_confirmPayment($transaction->getCode())) {
                    self::doneOrderBackend($order, $transaction);
                    return true;
                } elseif ($transaction->getCode() == 'cancelled') {
                    return false;
                }
            }

            if ($totalPaid >= $order->get_total()) {
                return true;
            }

        }

        return null;
    }

    protected static function _confirmPayment($status)
    {
        $confirm = ['committed', 'settled', 'verified'];

        return in_array($status, $confirm);
    }

    public static function transactionsCanBeCheck(WC_Order $order)
    {

        $orderDate = new \DateTime($order->get_date_created());
        $date = new \DateTime();

        $diff = $date->diff($orderDate);

        if ($diff->days > 0 || $diff->h > 1 || $diff->i < 15 || $diff->i > 60) {
            return false;
        }

        if (self::_isNewLastNote($order)) {
            return false;
        }

        return true;
    }

    protected static function _isNewLastNote(WC_Order $order)
    {
        $args = array(
            'post_id' => $order->get_id(),
            'approve' => 'approve',
            'type' => 'order_note',
            'limit' => 1,
            'offset' => 1
        );

        remove_filter('comments_clauses', array('WC_Comments', 'exclude_order_comments'), 10);
        $notes = get_comments($args);
        add_filter('comments_clauses', array('WC_Comments', 'exclude_order_comments'), 10, 1);

        if (!count($notes)) {
            return false;
        }

        /** @var WP_Comment $note */
        $note = $notes[0];

        $noteData = new \DateTime($note->comment_date);
        $date = new \DateTime();

        $diff = $date->diff($noteData);

        if ($diff->days > 0 || $diff->h > 1 || $diff->i > 15) {
            return false;
        }

        return true;
    }

    public static function doneOrderBackend(WC_Order $order, PaymentStatusImpl $transaction)
    {
        $trans_id = preg_replace("/[^0-9]+/", "", $transaction->getTransactionNumber());
        $_transactionId = $transaction->getTransactionNumber();

        $order->add_meta_data('ext_order_id', $trans_id);
        $order->add_meta_data('payment_method', $transaction->getPaymentMethodCode());

        $amount = $transaction->getOrderAmount() / 100;
        $note = sprintf(__('Payment captured. Transaction id: %s, transaction amount: %s', WC_VERIFONE_DOMAIN), $_transactionId, wc_price($amount));
        $order->add_order_note($note);
        $order->payment_complete($_transactionId);
    }
}