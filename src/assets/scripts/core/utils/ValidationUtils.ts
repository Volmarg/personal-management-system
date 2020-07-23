export default class ValidationUtils {

    /**
     * Checks if the provided string is "true"
     * @param $stringBoolean
     * @returns {boolean}
     */
    public static isTrue($stringBoolean){
        return ( $stringBoolean === 'true');
    };

    /**
     * Checks if the provided string is "false"
     * @param $stringBoolean
     * @returns {boolean}
     */
    public static isFalse($stringBoolean){
        return ( $stringBoolean === 'false');
    };
}