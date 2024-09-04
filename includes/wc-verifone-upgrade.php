<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is released under commercial license by Lamia Oy.
 *
 * @copyright Copyright (c) 2019 Lamia Oy (https://lamia.fi)
 * @author    Szymon Nosal <simon@lamia.fi>
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_Verifone_Upgrade
{

    public static function upgrade()
    {
        global $wpdb;
        $installed_ver = get_option('verifone_payment_db_version');

        if (version_compare($installed_ver, '1.3.4', '<')) {
            self::_upgradeTo134($wpdb);
        }

        update_option( "verifone_payment_db_version", WC_VERIFONE_VERSION );
    }

    protected static function _upgradeTo134($wpdb)
    {
        $table_name = $wpdb->prefix . 'verifone_order_process_status';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            `order_id` varchar(20) NOT NULL COMMENT 'Order Increment Id',
           `under_process` tinyint(1) NOT NULL COMMENT 'Order process status',
            PRIMARY KEY  (`order_id`)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

    }
}