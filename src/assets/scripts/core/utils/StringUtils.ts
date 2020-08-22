
export default class StringUtils {

    /**
     * @description Will check if given string is empty
     *
     * @param string
     * @return boolean
     */
    public static isEmptyString(string: string): boolean
    {
        return (
                ""          == string
            ||  null        == string
            ||  "null"      == string
            ||  "undefined" == undefined
            ||  "undefined" == typeof string
        );
    }

    /**
     * @description Check if both strings are the same
     * @param firstString
     * @param secondString
     */
    public static areTheSame(firstString: string, secondString: string): boolean
    {
        return firstString === secondString;
    }

}