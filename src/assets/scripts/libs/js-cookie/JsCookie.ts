import * as Cookies from 'js-cookie';
import StringUtils  from "../../core/utils/StringUtils";

/**
 * @description handles the predefined cookies
 */
export default class JsCookie {

    static readonly COOKIE_KEY_HIDE_FIRST_LOGIN_INFORMATION = "HIDE_FIRST_LOGIN_INFORMATION"
    static readonly COOKIE_KEY_JS_SETTINGS_SELECTED_THEME   = "JS_SETTINGS_SELECTED_THEME";

    /**
     * @description Sets the `COOKIE_KEY_HIDE_FIRST_LOGIN_INFORMATION` cookie
     */
    public static setHideFirstLoginInformation(): void
    {
        Cookies.set(JsCookie.COOKIE_KEY_HIDE_FIRST_LOGIN_INFORMATION, true);
    }

    /**
     * @description This checks if the hide first login information cookie is set:
     *              - if not: returns false
     *              - if yes (no matter what value): returns true
     *
     * @return boolean
     */
    public static isHideFirstLoginInformation(): boolean
    {
        let cookieValue = Cookies.get(JsCookie.COOKIE_KEY_HIDE_FIRST_LOGIN_INFORMATION);
        return !StringUtils.isEmptyString(cookieValue);
    }

    /**
     * @description save the selected theme
     */
    public static setJsSettingsSelectedTheme(themName: string): void
    {
        Cookies.set(JsCookie.COOKIE_KEY_JS_SETTINGS_SELECTED_THEME, themName);
    }

    /**
     * @description get the selected theme
     *
     * @return boolean
     */
    public static getJsSettingsSelectedTheme(): string
    {
        let cookieValue = Cookies.get(JsCookie.COOKIE_KEY_JS_SETTINGS_SELECTED_THEME);
        return cookieValue;
    }

    /**
     * @description checks if the theme is selected already
     *
     * @return boolean
     */
    public static isJsSettingsSelectedTheme(): boolean
    {
        let cookieValue = Cookies.get(JsCookie.COOKIE_KEY_JS_SETTINGS_SELECTED_THEME);
        return !StringUtils.isEmptyString(cookieValue);
    }

}