<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is released under commercial license by Lamia Oy.
 *
 * @copyright Copyright (c) 2019 Lamia Oy (https://lamia.fi)
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WC_Verifone_OrderLocker
{

    const TABLE_NAME = 'verifone_order_process_status';

    public static function lockOrder($orderId)
    {
        global $wpdb;
        $result = false;

        try {
            $wpdb->query('START TRANSACTION');

            $query = 'SELECT * from `' . self::_getTableName() . '` WHERE order_id = ' . $orderId;
            if($wpdb->get_row($query)) {
                $result = (bool)$wpdb->update(
                    self::_getTableName(),
                    ['under_process' => 1],
                    ['under_process' => 0, 'order_id' => $orderId]
                );
            } else {
                $result = (bool)$wpdb->insert(self::_getTableName(), ['order_id' => $orderId, 'under_process' => 1]);
            }

            $wpdb->query('COMMIT');
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');

        }

        return $result;
    }

    public static function unlockOrder($orderId)
    {
        global $wpdb;
        $result = false;

        try {
            $wpdb->query('START TRANSACTION');

            $result = (bool)$wpdb->update(
                self::_getTableName(),
                ['under_process' => 0],
                ['under_process' => 1, 'order_id' => $orderId]
            );

            $wpdb->query('COMMIT');
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
        }

        return $result;
    }

    public static function isLockedOrder($orderId)
    {
        global $wpdb;

        $query = 'SELECT * from `' . self::_getTableName() . '` WHERE under_process = 1 AND order_id = ' . $orderId;

        if($wpdb->get_row($query)) {
            return true;
        }

        return false;

    }

    protected static function _getTableName()
    {
        global $wpdb;

        return $wpdb->prefix . self::TABLE_NAME;
    }
}