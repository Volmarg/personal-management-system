import AbstractDto from "./AbstractDto";

/**
 * @description DTO for widget data
 */
export default class WidgetDataDto extends AbstractDto {

    /**
     * @type string
     */
    private _type: string = "";

    /**
     * @type string
     */
    private _url: string = "";

    /**
     * @type Object
     */
    private _ajaxData = {};

    /**
     * @type Object
     */
    private _callbackParams = {};

    /**
     * @type Function
     */
    private _callback : Function = () => {};

    get type(): string {
        return this._type;
    }

    set type(value: string) {
        this._type = value;
    }

    get url(): string {
        return this._url;
    }

    set url(value: string) {
        this._url = value;
    }

    get callback(): Function {
        return this._callback;
    }

    set callback(value: Function) {
        this._callback = value;
    }

    get ajaxData(): {} {
        return this._ajaxData;
    }

    set ajaxData(value: {}) {
        this._ajaxData = value;
    }

    get callbackParams(): {} {
        return this._callbackParams;
    }

    set callbackParams(value: {}) {
        this._callbackParams = value;
    }
}