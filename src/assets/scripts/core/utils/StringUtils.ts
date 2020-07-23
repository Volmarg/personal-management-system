
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

}