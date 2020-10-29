import * as Cookies from 'js-cookie';
import StringUtils  from "../../core/utils/StringUtils";

export default class JsCookie {

    static readonly COOKIE_KEY_HIDE_FIRST_LOGIN_INFORMATION = "HIDE_FIRST_LOGIN_INFORMATION"

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

}