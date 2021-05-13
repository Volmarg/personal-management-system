import AbstractDto from "./AbstractDto";
import StringUtils from "../core/utils/StringUtils";

/**
 * @description DTO used for DataProcessors
 */
export default class DataProcessorDto extends AbstractDto {

    /**
     * @type string
     */
    private _url: string = "";

    /**
     * @type Object
     */
    private _ajaxData: Object = {};

    /**
     * @type string
     */
    private _successMessage: string = "";

    /**
     * @type string
     */
    private _failMessage: string = "";

    /**
     * @type boolean
     */
    private _isDataTable: boolean = false;

    /**
     * @type string
     */
    private _confirmMessage: string = "";

    /**
     * @type boolean
     */
    private _invokeAlert: boolean = false;

    /**
     * @type string
     */
    private _invokedAlertBody: string = "";

    /**
     * @type boolean
     */
    private _reloadModuleContent: boolean = true;

    /**
     * @type string
     */
    private _callback: Function = () => {};

    /**
     * @type Function
     */
    private _callbackAfter: Function = () => {};

    /**
     * @type boolean
     */
    private _useAjaxFailMessage: boolean = false;

    /**
     * @type boolean
     */
    private _updateTemplate: boolean = false;

    /**
     * @type string
     */
    private _processorName: string = "";

    /**
     * @type Function
     */
    private _callbackForLoadingModuleContentByUrl: Function = () => {};

    get url(): string {
        return this._url;
    }

    set url(value: string) {
        this._url = value;
    }

    get ajaxData(): Object {
        return this._ajaxData;
    }

    set ajaxData(value: Object) {
        this._ajaxData = value;
    }

    get successMessage(): string {
        return this._successMessage;
    }

    set successMessage(value: string) {
        this._successMessage = value;
    }

    get failMessage(): string {
        return this._failMessage;
    }

    set failMessage(value: string) {
        this._failMessage = value;
    }

    get isDataTable(): boolean {
        return this._isDataTable;
    }

    set isDataTable(value: boolean) {
        this._isDataTable = value;
    }

    get confirmMessage(): string {
        return this._confirmMessage;
    }

    set confirmMessage(value: string) {
        this._confirmMessage = value;
    }

    get callback(): Function {
        return this._callback;
    }

    set callback(value: Function) {
        this._callback = value;
    }

    get callbackAfter(): Function {
        return this._callbackAfter;
    }

    set callbackAfter(value: Function) {
        this._callbackAfter = value;
    }

    get useAjaxFailMessage(): boolean {
        return this._useAjaxFailMessage;
    }

    set useAjaxFailMessage(value: boolean) {
        this._useAjaxFailMessage = value;
    }

    get updateTemplate(): boolean {
        return this._updateTemplate;
    }

    set updateTemplate(value: boolean) {
        this._updateTemplate = value;
    }

    get invokeAlert(): boolean {
        return this._invokeAlert;
    }

    set invokeAlert(value: boolean) {
        this._invokeAlert = value;
    }

    get invokedAlertBody(): string {
        return this._invokedAlertBody;
    }

    set invokedAlertBody(value: string) {
        this._invokedAlertBody = value;
    }

    public isSuccessMessageSet(): boolean {
        return !StringUtils.isEmptyString(this._successMessage);
    }

    public isFailMessageSet(): boolean {
        return !StringUtils.isEmptyString(this._failMessage);
    }

    public isConfirmMessageSet(): boolean {
        return !StringUtils.isEmptyString(this._confirmMessage);
    }

    public isInvokedAlertBodySet(): boolean {
        return !StringUtils.isEmptyString(this._invokedAlertBody);
    }

    get callbackForLoadingModuleContentByUrl(): Function {
        return this._callbackForLoadingModuleContentByUrl;
    }

    set callbackForLoadingModuleContentByUrl(value: Function) {
        this._callbackForLoadingModuleContentByUrl = value;
    }

    get processorName(): string {
        return this._processorName;
    }

    set processorName(value: string) {
        this._processorName = value;
    }

    get reloadModuleContent(): boolean {
        return this._reloadModuleContent;
    }

    set reloadModuleContent(value: boolean) {
        this._reloadModuleContent = value;
    }

    /**
     * Builds DTO from data array
     * @param array
     * @returns {DataProcessorDto}
     */
    static fromArray(array: Array<any>): DataProcessorDto
    {
        let dto = new DataProcessorDto();

        let url                 = dto.getFromArray(array, 'url');
        let ajaxData            = dto.getFromArray(array, 'ajaxData');
        let successMessage      = dto.getFromArray(array, 'successMessage');
        let failMessage         = dto.getFromArray(array, 'failMessage');
        let isDataTable         = dto.getFromArray(array, 'isDataTable');
        let confirmMessage      = dto.getFromArray(array, 'confirmMessage');
        let callback            = dto.getFromArray(array, 'callback');
        let callbackAfter       = dto.getFromArray(array, 'callbackAfter');
        let useAjaxFailMessage  = dto.getFromArray(array, 'useAjaxFailMessage');
        let updateTemplate      = dto.getFromArray(array, 'updateTemplate');
        let invokeAlert         = dto.getFromArray(array, 'invokeAlert');
        let invokedAlertBody    = dto.getFromArray(array, 'invokedAlertBody');
        let processorName       = dto.getFromArray(array, 'processorName');

        let callbackForLoadingModuleContentByUrl = dto.getFromArray(array, 'callbackForLoadingModuleContentByUrl');

        dto._url                = url;
        dto._ajaxData           = ajaxData;
        dto._successMessage     = successMessage;
        dto._failMessage        = failMessage;
        dto._isDataTable        = isDataTable;
        dto._confirmMessage     = confirmMessage;
        dto._callback           = callback;
        dto._callbackAfter      = callbackAfter;
        dto._useAjaxFailMessage = useAjaxFailMessage;
        dto._updateTemplate     = updateTemplate;
        dto._invokeAlert        = invokeAlert;
        dto._invokedAlertBody   = invokedAlertBody;
        dto._processorName      = processorName;

        dto._callbackForLoadingModuleContentByUrl = callbackForLoadingModuleContentByUrl;

        return dto;
    }
}