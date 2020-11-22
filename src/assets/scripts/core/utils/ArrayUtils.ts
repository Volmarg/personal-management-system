
export default class ArrayUtils {

    /**
     * @description For standard array with values (strings/ints)
     *
     * @param array {object}
     * @returns {any[]}
     */
    public getUniqueValues (array): object
    {
        let uniqueValues = Array.from(new Set(array));
        return uniqueValues;
    };

    /**
     * @description This function will return true if needle is in haystack
     *
     * @param needle
     * @param haystack
     */
    public static inArray(needle: string|number|boolean, haystack: Array<any>): boolean
    {
        let inArray = ($.inArray(needle, haystack) !== -1);

        if(
                !inArray
            &&  !Number.isNaN(needle)
            &&  ("string" === typeof needle)
        ){

            let numericNeedle = parseInt(needle);
            inArray           = ($.inArray(numericNeedle, haystack) !== -1);

        }

       return inArray;
    }

    /**
     * @description Checks if array is empty
     *
     * @param array
     */
    public static isEmpty(array): boolean
    {
        return ( 0 === array.length );
    }
}