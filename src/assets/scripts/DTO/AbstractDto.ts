/**
 * @description Common methods / data used for children DTO classes
 */
export default abstract class AbstractDto {

    /**
     * Returns found value for key in array, if non is found - returns defaultValue
     * @param array {array}
     * @param key {string}
     * @param defaultValue
     * @returns {null|string}
     */
    getFromArray(array, key, defaultValue = null){

        if( "undefined" === typeof array[key] ){
            return defaultValue;
        }

        return array[key];
    }

    /**
     * Checks if the value is non empty/null/undefined
     * @return {boolean}
     */
    isset(value){
        if(
            "undefined" === typeof value
            ||  ""          === value
            || null         === value
            ||  0           === value.length
        ){
            return false;
        }

        return true;
    }

}