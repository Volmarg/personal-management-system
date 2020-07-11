/**
 * Todo: at one point should replace all ajax responses from backend
 */
export default class AjaxResponseDto {


    constructor(){
        /**
         * @type {string}
         */
        this._code = "";

        /**
         * @type {string}
         */
        this._message = "";

        /**
         * @type {string}
         */
        this._template = "";

        /**
         * @type {string}
         */
        this._password = "";

        /**
         * @type {boolean}
         */
        this._reloadPage = false;

        /**
         * @type {string}
         */
        this._reloadMessage = "";

        /**
         * @type {boolean}
         */
        this._success = false;

        /**
         * @type {string}
         */
        this._formTemplate = "";
    }

    get code() {
        return this._code;
    }

    get message() {
        return this._message;
    }

    get template() {
        return this._template;
    }

    get password() {
        return this._password;
    }

    get reloadPage() {
        return this._reloadPage;
    }

    get reloadMessage() {
        return this._reloadMessage;
    }

    get success() {
        return this._success;
    }

    get formTemplate() {
        return this._formTemplate;
    }

    /**
     * Builds DTO from data array
     * @param array
     * @returns {AjaxResponseDto}
     */
    static fromArray(array){
        let ajaxResponseDto = new AjaxResponseDto();

        ajaxResponseDto._code          = ajaxResponseDto.getFromArray(array, 'code');
        ajaxResponseDto._message       = ajaxResponseDto.getFromArray(array, 'message');
        ajaxResponseDto._template      = ajaxResponseDto.getFromArray(array, 'template');
        ajaxResponseDto._password      = ajaxResponseDto.getFromArray(array, 'password');
        ajaxResponseDto._reloadPage    = ajaxResponseDto.getFromArray(array, 'reload_page', false);
        ajaxResponseDto._reloadMessage = ajaxResponseDto.getFromArray(array, 'reload_message');
        ajaxResponseDto._success       = ajaxResponseDto.getFromArray(array, 'success');
        ajaxResponseDto._formTemplate  = ajaxResponseDto.getFromArray(array, 'form_template');

        return ajaxResponseDto;
    }

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

    /**
     * @return {boolean}
     */
    isCodeSet(){
        return this.isset(this._code);
    }

    /**
     * @return {boolean}
     */
    isMessageSet(){
        return this.isset(this._message);
    }

    /**
     * @return {boolean}
     */
    isTemplateSet(){
        return this.isset(this._template);
    }

    /**
     * @return {boolean}
     */
    isPasswordSet(){
        return this.isset(this._password);
    }

    /**
     * @return {boolean}
     */
    isReloadMessageSet(){
        return this.isset(this._reloadPage);
    }

    /**
     * @return {boolean}
     */
    isSuccessSet(){
        return this.isset(this._success);
    }

    /**
     * @return {boolean}
     */
    isFormTemplateSet(){
        return this.isset(this._formTemplate);
    }

    /**
     * @return {boolean}
     */
    isSuccessCode(){
        if(
                200 >= this._code
            &&  300 > this._code
        ){
            return true;
        }

        return false;
    }

}