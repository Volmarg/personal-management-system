
export default class ArrayUtils {

    /**
     * For standard array with values (strings/ints)
     * @param array {object}
     * @returns {any[]}
     */
    public getUniqueValues (array): object
    {
        let uniqueValues = Array.from(new Set(array));
        return uniqueValues;
    };

    /**
     * This function will return true if needle is in haystack
     * @param needle
     * @param haystack
     */
    public static inArray(needle: string, haystack: object): boolean
    {
        let isInArray = false;

        $.each(haystack, (index, value) => {
            if( needle === value ){
                isInArray = true;
                return false;
            }
        });

        return isInArray;
    }

    /**
     * Checks if array is empty
     * @param array
     */
    public static isEmpty(array): boolean
    {
        return ( 0 === array.length );
    }
}