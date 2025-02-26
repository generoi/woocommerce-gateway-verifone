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
	 * Store notices in user meta.
	 * This is used to store notices that are not displayed immediately.
	 * This is useful for notices that are displayed after redirect.
	 *
	 * @param $notice
	 * @param $type
	 * @param boolean $isDismissible
	 * @return void
	 */
	public static function storeNotice( $notice, $type, $isDismissible = false ) {
		$current_user_id = get_current_user_id();
		$notices = get_user_meta( $current_user_id, self::SESSION_KEY, true );
		if ( ! is_array( $notices ) ) {
			$notices = [];
		}

		$hash = sha1( $notice );
		if ( ! isset( $notices[ $hash ] ) ) {
			$notices[ $hash ] = [ 'type' => $type, 'content' => $notice, 'is-dismissible' => $isDismissible ];
		}

		update_user_meta( $current_user_id, self::SESSION_KEY, $notices );
	}

	/**
	 * Get stored notices from user meta.
	 *
	 * @return array
	 */
	public static function getStoredNotices() {
		$current_user_id = get_current_user_id();
		$notices = get_user_meta( $current_user_id, self::SESSION_KEY, true );
		if ( ! is_array( $notices ) ) {
			$notices = [];
		}

		return $notices;
	}

	/**
	 * Clear all stored notices.
	 *
	 * @return void
	 */
	public static function clearAllNotices() {
		$user_id = get_current_user_id();
		if ($user_id) {
			update_user_meta( $user_id, self::SESSION_KEY, [] );
		}
	}

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
        if (function_exists('wc_add_notice')) {
            wc_add_notice($notice, $type);
        } else {
			if ( $forceSession ) {
				self::storeNotice( $notice, $type, $isDismissible );
			} else {
				self::$_notices[] = [
					'content' => $notice,
					'type' => $type,
					'is-dismissible' => $isDismissible
				];
			}
        }
    }

    /**
     * Add success notice.
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
     * Add error notice.
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
     * Add warning notice.
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
     * Add info notice.
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
     * Render all notices and clear after render.
     */
    public static function render()
    {
        $html = '';

		$notices = self::$_notices;
		$storedNotices = self::getStoredNotices();
		if ( ! empty( $storedNotices ) ) {
			$notices = array_merge( $notices, $storedNotices );
		}

        foreach ($notices as $notice) {

            $class = 'notice ' . self::NOTICE_PREFIX . $notice['type'];
            if ($notice['is-dismissible']) {
                $class .= ' is-dismissible';
            }

            $html .= '<div class="' . $class . '">';
            $html .= '<p>' . $notice['content'] . '</p>';
            $html .= '</div>';
        }

        echo $html;

        self::clearAllNotices();
    }
}
