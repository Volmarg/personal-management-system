import AbstractDto from "./AbstractDto";

/**
 *  @description Data Transfer Object for data returned from DialogLoader/Definition
 */
export default class DialogDataDto extends AbstractDto{

    private _callback = ($dialogWrapper?: JQuery<HTMLElement>) => {};

    get callback(): ($dialogWrapper?: JQuery<HTMLElement>) => void {
        return this._callback;
    }

    set callback(value: ($dialogWrapper?: JQuery<HTMLElement>) => void) {
        this._callback = value;
    }
}