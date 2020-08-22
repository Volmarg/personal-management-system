export default class ValidationUtils {

    /**
     * @description Checks if the provided string is "true"
     *
     * @param $stringBoolean
     * @returns {boolean}
     */
    public static isTrue($stringBoolean){
        return ( $stringBoolean === 'true');
    };

    /**
     * @description Checks if the provided string is "false"
     * 
     * @param $stringBoolean
     * @returns {boolean}
     */
    public static isFalse($stringBoolean){
        return ( $stringBoolean === 'false');
    };
}