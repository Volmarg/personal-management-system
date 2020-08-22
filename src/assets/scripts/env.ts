/**
 * @description contains project environmental settings for JS/TS
 */
export default class Env {

    static readonly ENV_DEV  = "DEV";
    static readonly ENV_PROD = "PROD";

    public static isDev()
    {
        return Env.APP_ENV === Env.ENV_DEV;
    }

    // ---------------------------------------------------------------------------------- ENV SETTINGS BELOW

    /**
     * @description Env configuration
     */
    static readonly APP_ENV = Env.ENV_DEV;
}