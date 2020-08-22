/**
 * @description This class handles logging data in the console, it uses standard logging mechanism for js, however
 *              it's additionally styled via css, can also be specially styled to handle stack trace etc.
 */
export default class ConsoleLogger {

    /**
     * @description Log red message
     *
     * @param message
     * @param data
     */
    public static error(message: string, data?: Array<any>)
    {
        let commonStyles = ConsoleLogger.getCommonStylesForLogger();
        console.log('%c' + message, 'background-color:red;' + commonStyles, data);
    }

    /**
     * @description Log orange message
     *
     * @param message
     * @param data
     */
    public static warning(message: string, data?: Array<any>)
    {
        let commonStyles = ConsoleLogger.getCommonStylesForLogger();
        console.log('%c' + message, 'background-color:orange;' + commonStyles, data);
    }

    /**
     * @description Log blue message
     *
     * @param message
     * @param data
     */
    public static info(message: string, data?: Array<any>)
    {
        let commonStyles = ConsoleLogger.getCommonStylesForLogger();
        console.log('%c' + message, 'background-color:blue;' + commonStyles, data);
    }

    /**
     * @description Returns styles common for all types of logs
     */
    private static getCommonStylesForLogger(): string
    {
        return `color: white; font-size:15px; font-weight:bold; padding:3px;`;
    }

}