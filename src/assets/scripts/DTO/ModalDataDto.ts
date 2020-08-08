import AbstractDto from "./AbstractDto";

/**
 *  @description Data Transfer Object for data returned from ModalLoader/Definition
 */
export default class ModalDataDto extends AbstractDto{

    private _callback = ($modalWrapper?: JQuery<HTMLElement>) => {};

    get callback(): ($modalWrapper?: JQuery<HTMLElement>) => void {
        return this._callback;
    }

    set callback(value: ($modalWrapper?: JQuery<HTMLElement>) => void) {
        this._callback = value;
    }
}