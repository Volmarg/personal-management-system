import AbstractDto from "./AbstractDto";

/**
 *  @description Data Transfer Object for data returned from DialogLoader/Definition
 */
export default class DialogDataDto extends AbstractDto{

    private _callback      = ($dialogWrapper?: JQuery<HTMLElement>) => {};
    private _callbackAfter = ($dialogWrapper?: JQuery<HTMLElement>) => {};

    /**
     * @type Object
     * @private
     */
    private _ajaxData: Object = {};

    get callback(): ($dialogWrapper?: JQuery<HTMLElement>) => void {
        return this._callback;
    }

    set callback(value: ($dialogWrapper?: JQuery<HTMLElement>) => void) {
        this._callback = value;
    }

    get ajaxData(): Object {
        return this._ajaxData;
    }

    set ajaxData(value: Object) {
        this._ajaxData = value;
    }

    get callbackAfter(): ($dialogWrapper?: JQuery<HTMLElement>) => void {
        return this._callbackAfter;
    }

    set callbackAfter(value: ($dialogWrapper?: JQuery<HTMLElement>) => void) {
        this._callbackAfter = value;
    }
}