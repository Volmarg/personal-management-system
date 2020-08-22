import AbstractDialogs  from "./AbstractDialogs";
import Loader           from "../../../libs/loader/Loader";
import Ajax             from "../../ajax/Ajax";
import DialogDataDto    from "../../../DTO/DialogDataDto";
import BootboxWrapper   from "../../../libs/bootbox/BootboxWrapper";

export default class CallableViaDataAttrsDialogs extends AbstractDialogs {

    public init(){
        this.attachCallDialogOnClickEvent();
    };

    private attachCallDialogOnClickEvent(){
        let elements = $("[" + this.data.callDialogOnClick + "=true]");
        let _this    = this;

        elements.on('click', function(event){
            let $clickedElement = $(event.currentTarget);

            let requestMethod  = $clickedElement.attr(_this.data.requestMethod);
            let requestUrl     = $clickedElement.attr(_this.data.requestUrl);
            let postParameters = $clickedElement.attr(_this.data.postParameters);
            let dialogName     = $clickedElement.attr(_this.data.dialogName);

            let dialogDataDto  = _this.dialogLogicLoader.getDialogDataDto(dialogName);
            let callback       = ( !(dialogDataDto instanceof DialogDataDto) ? () => {} : dialogDataDto.callback );

            let usedParameters = null;
            let url            = null;
            let data           = null;

            switch( requestMethod ){
                case Ajax.REQUEST_TYPE_POST:
                {
                    if(
                        ""              === postParameters
                        ||  "undefined" === typeof postParameters
                    ){
                        throw{
                            "message": "Post parameters are missing for dialog call"
                        }
                    }

                    usedParameters = postParameters;
                    url            = requestUrl;
                    data           = JSON.parse(postParameters);
                }
                break;

                default:
                    throw {
                        "message" : "Unsupported method",
                        "method"  : requestMethod
                    }
            }

            Loader.toggleLoader();
            $.ajax({
                method : requestMethod,
                url    : url,
                data   : data
            }).always((data) => {
                _this.handleCommonAjaxCallLogicForBuildingDialog(data, callback, _this.callDialog);
            })
        });
    };

    /**
     * General function for calling the modal
     * @param url {string}
     * @param method {string}
     * @param requestData {object}
     * @param callback {function}
     */
    public buildDialogBody(url, method, requestData, callback) {

        if( "undefined" === typeof callback ){
            callback = null;
        }

        let _this = this;

        Loader.toggleLoader();
        $.ajax({
            method: method,
            url: url,
            data: requestData
        }).always((data) => {
            _this.handleCommonAjaxCallLogicForBuildingDialog(data, callback, _this.callDialog);
        })
    };

    /**
     * Call the dialog and insert template in it's body
     * @param template {string}
     * @param callback {function}
     */
    private callDialog(template, callback = null) {

        let dialog = BootboxWrapper.mainLogic.alert({
            size: "large",
            backdrop: true,
            closeButton: false,
            message: template,
            buttons: {
                ok: {
                    label: 'Cancel',
                    className: 'btn-primary dialog-ok-button',
                    callback: () => {}
                },
            },
            callback: function () {
            }
        });

        //@ts-ignore
        dialog.init( () => {
            let callableViaDataAttrsDialogs = new CallableViaDataAttrsDialogs();

            if( $.isFunction(callback) ){
                callback();
            }

            callableViaDataAttrsDialogs.forms.init();
        });

    };

}