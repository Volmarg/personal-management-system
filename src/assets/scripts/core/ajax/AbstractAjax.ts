export default class AbstractAjax {

    /**
     * @type string
     */
    static readonly REQUEST_TYPE_GET = "GET";

    /**
     * @type string`
     */
    static readonly REQUEST_TYPE_POST = "POST";

    /**
     * @type Object
     */
    static readonly API_URLS = {
        fileRemoval : "/files/action/remove-file",
        fileRename  : "/files/action/rename-file",
    };
}