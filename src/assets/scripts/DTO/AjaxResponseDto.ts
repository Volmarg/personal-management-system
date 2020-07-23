/**
 * Todo: at one point should replace all ajax responses from backend
 */
export default class AjaxResponseDto {

    /**
     * @type int
     */
    public code = 200;

    /**
     * @type string
     */
    public message = "";

    /**
     * @type string
     */
    public template = "";

    /**
     * @type string
     */
    public password = "";

    /**
     * @type boolean
     */
    public reloadPage = false;

    /**
     * @type string
     */
    public reloadMessage = "";

    /**
     * @type boolean
     */
    public success = false;

    /**
     * @type string
     */
    public formTemplate = "";

    /**
     * @type string
     */
    public validatedFormPrefix = "";

    /**
     * @type Array<string>
     */
    public invalidFormFields = [];

    /**
     * Builds DTO from data array
     * @param array
     * @returns {AjaxResponseDto}
     */
    static fromArray(array){
        let ajaxResponseDto = new AjaxResponseDto();

        ajaxResponseDto.code                = ajaxResponseDto.getFromArray(array, 'code');
        ajaxResponseDto.message             = ajaxResponseDto.getFromArray(array, 'message');
        ajaxResponseDto.template            = ajaxResponseDto.getFromArray(array, 'template');
        ajaxResponseDto.password            = ajaxResponseDto.getFromArray(array, 'password');
        ajaxResponseDto.reloadPage          = ajaxResponseDto.getFromArray(array, 'reload_page', false);
        ajaxResponseDto.reloadMessage       = ajaxResponseDto.getFromArray(array, 'reload_message');
        ajaxResponseDto.success             = ajaxResponseDto.getFromArray(array, 'success');
        ajaxResponseDto.formTemplate        = ajaxResponseDto.getFromArray(array, 'form_template');
        ajaxResponseDto.validatedFormPrefix = ajaxResponseDto.getFromArray(array, 'validated_form_prefix');
        ajaxResponseDto.invalidFormFields   = ajaxResponseDto.getFromArray(array, 'invalid_form_fields', []);

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
        return this.isset(this.code);
    }

    /**
     * @return {boolean}
     */
    isMessageSet(){
        return this.isset(this.message);
    }

    /**
     * @return {boolean}
     */
    isTemplateSet(){
        return this.isset(this.template);
    }

    /**
     * @return {boolean}
     */
    isPasswordSet(){
        return this.isset(this.password);
    }

    /**
     * @return {boolean}
     */
    isReloadMessageSet(){
        return this.isset(this.reloadPage);
    }

    /**
     * @return {boolean}
     */
    isSuccessSet(){
        return this.isset(this.success);
    }

    /**
     * @return {boolean}
     */
    isFormTemplateSet(){
        return this.isset(this.formTemplate);
    }

    /**
     * @return {boolean}
     */
    isVlidatedFormPrefixSet(){
        return this.isset(this.validatedFormPrefix);
    }

    /**
     * @return {boolean}
     */
    hasInvalidFields(){
        return ( 0 !== this.invalidFormFields.length );
    }

    /**
     * @return {boolean}
     */
    isSuccessCode(){
        if(
                200 >= this.code
            &&  300 > this.code
        ){
            return true;
        }

        return false;
    }

    /**
     * @returns {boolean}
     */
    isInternalServerErrorCode(){
        return (this.code >= 500);
    }

}