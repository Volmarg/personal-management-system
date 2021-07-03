
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

    /**
     * @description will return given string with first letter being capitalized
     */
    public static capitalizeFirstLetter(string): string
    {
        return string[0].toUpperCase() + string.slice(1);
    }

    /**
     * @description will check if target string contains searched string
     */
    public static stringContain(targetString: string, searchedString: string): boolean
    {
        return (targetString.indexOf(searchedString) !== -1);
    }

}