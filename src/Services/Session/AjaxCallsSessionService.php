<?php


namespace App\Services\Session;

use App\Controller\Utils\Utils;
use Exception;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Info: this data should be invalidated upon using it, that's why getters have invalidation set to true
 *  it's important as it's stored per one ajax call, each one is handled separately, therefore
 *  this ensures that no incorrect data will be processed
 * Class SessionsService
 * @package App\Services\Session
 */
class AjaxCallsSessionService extends SessionsService {

    const SESSION_KEY_DO_PAGE_RELOAD_AFTER_AJAX_CALL      = "do_page_reload_after_ajax_call";
    const SESSION_KEY_PAGE_RELOAD_MESSAGE_AFTER_AJAX_CALL = "page_reload_message_after_ajax_call";

    /**
     * @return bool
     */
    public static function hasPageReloadAfterAjaxCall(): bool
    {
        $session = new Session();

        return $session->has(self::SESSION_KEY_DO_PAGE_RELOAD_AFTER_AJAX_CALL);
    }

    /**
     * @param bool $unset
     * @return bool|null
     * @throws Exception
     */
    public static function getPageReloadAfterAjaxCall(bool $unset = true):?bool
    {
        $session  = new Session();
        $doReload = $session->get(self::SESSION_KEY_DO_PAGE_RELOAD_AFTER_AJAX_CALL);

        if( $unset ){
            $session->remove(self::SESSION_KEY_DO_PAGE_RELOAD_AFTER_AJAX_CALL);
        }

        return Utils::getBoolRepresentationOfBoolString($doReload);
    }

    /**
     * @param bool $doReload
     */
    public static function setPageReloadAfterAjaxCall(bool $doReload): void
    {
        $session = new Session();

        $session->set(self::SESSION_KEY_DO_PAGE_RELOAD_AFTER_AJAX_CALL, $doReload);
    }

    /**
     * @return bool
     */
    public static function hasPageReloadMessageAfterAjaxCall(): bool
    {
        $session = new Session();

        return $session->has(self::SESSION_KEY_PAGE_RELOAD_MESSAGE_AFTER_AJAX_CALL);
    }

    /**
     * @param string $message
     */
    public static function setPageReloadMessageAfterAjaxCall(string $message): void
    {
        $session = new Session();

        $session->set(self::SESSION_KEY_PAGE_RELOAD_MESSAGE_AFTER_AJAX_CALL, $message);
    }

    /**
     * @param bool $unset
     * @return string
     * @throws Exception
     */
    public static function getPageReloadMessageAfterAjaxCall(bool $unset = true): string
    {
        $session = new Session();
        $message = $session->get(self::SESSION_KEY_PAGE_RELOAD_MESSAGE_AFTER_AJAX_CALL);

        if( $unset ){
            $session->remove(self::SESSION_KEY_PAGE_RELOAD_MESSAGE_AFTER_AJAX_CALL);
        }

        return $message;
    }
}