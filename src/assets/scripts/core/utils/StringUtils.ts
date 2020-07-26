
export default class StringUtils {

    /**
     * Will check if given string is empty
     * @param string
     * @return boolean
     */
    public static isEmptyString(string: string): boolean
    {
        return (
                ""        == string
            ||  null      == string
            ||  undefined == typeof string
        );
    }

    /**
     * Check if both strings are the same
     * @param firstString
     * @param secondString
     */
    public static areTheSame(firstString: string, secondString: string): boolean
    {
        return firstString === secondString;
    }

}