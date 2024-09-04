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

$oldSession = session_id();

if ($oldSession == '') {
    session_id('verifone-notice');
    session_start();
}

class WC_Verifone_Notice
{
    const SESSION_KEY = 'wc-verifone-gateway-notices';

    const NOTICE_PREFIX = 'notice-';

    const NOTICE_SUCCESS = 'success';
    const NOTICE_WARNING = 'warning';
    const NOTICE_ERROR = 'error';
    const NOTICE_INFO = 'info';

    protected static $_notices = [];

    /**
     * If method wc_add_notice is available (on frontend) then use it, if not then store in session.
     *
     * @param $notice
     * @param $type
     * @param bool $isDismissible
     * @param bool $forceSession
     */
    public static function add($notice, $type, $isDismissible = false, $forceSession = false)
    {
        if (!$forceSession && function_exists('wc_add_notice')) {
            wc_add_notice($notice, $type);
        } else {

            if (!isset($_SESSION[self::SESSION_KEY])) {
                $_SESSION[self::SESSION_KEY] = [];
            }

            $hash = sha1($notice);
            if (!isset($_SESSION[self::SESSION_KEY][$hash])) {
                $_SESSION[self::SESSION_KEY][$hash] = ['type' => $type, 'content' => $notice, 'is-dismissible' => $isDismissible];
            }

        }
    }

    /**
     * Add success notice
     *
     * @param $notice
     * @param bool $isDismissible
     * @param bool $forceSession
     */
    public static function addSuccess($notice, $isDismissible = false, $forceSession = false)
    {
        self::add($notice, self::NOTICE_SUCCESS, $isDismissible, $forceSession);
    }

    /**
     * Add error notice
     *
     * @param $notice
     * @param bool $isDismissible
     * @param bool $forceSession
     */
    public static function addError($notice, $isDismissible = false, $forceSession = false)
    {
        self::add($notice, self::NOTICE_ERROR, $isDismissible, $forceSession);
    }

    /**
     * Add warning notice
     *
     * @param $notice
     * @param bool $isDismissible
     * @param bool $forceSession
     */
    public static function addWarning($notice, $isDismissible = false, $forceSession = false)
    {
        self::add($notice, self::NOTICE_WARNING, $isDismissible, $forceSession);
    }

    /**
     * Add info notice
     *
     * @param $notice
     * @param bool $isDismissible
     * @param bool $forceSession
     */
    public static function addInfo($notice, $isDismissible = false, $forceSession = false)
    {
        self::add($notice, self::NOTICE_INFO, $isDismissible, $forceSession);
    }

    /**
     * Clear notices
     */
    public static function clear()
    {
        $_SESSION[self::SESSION_KEY] = [];
    }

    /**
     * Unset session with notices
     */
    public static function unsetNotice()
    {
        unset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * Get all stored notices
     *
     * @return array
     */
    public static function get()
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return [];
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Render all notices and clear after render
     */
    public static function render()
    {
        $html = '';

        foreach (self::get() as $notice) {

            $class = 'notice ' . self::NOTICE_PREFIX . $notice['type'];
            if ($notice['is-dismissible']) {
                $class .= ' is-dismissible';
            }

            $html .= '<div class="' . $class . '">';
            $html .= '<p>' . $notice['content'] . '</p>';
            $html .= '</div>';
        }

        echo $html;

        self::clear();
    }

}

if (session_id() == 'verifone-notice') {
    session_write_close();
}