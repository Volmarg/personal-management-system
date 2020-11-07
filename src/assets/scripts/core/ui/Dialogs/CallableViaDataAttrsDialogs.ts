import AbstractDialogs  from "./AbstractDialogs";
import Loader           from "../../../libs/loader/Loader";
import Ajax             from "../../ajax/Ajax";
import DialogDataDto    from "../../../DTO/DialogDataDto";
import BootboxWrapper   from "../../../libs/bootbox/BootboxWrapper";
import Dialog           from "./Dialog";
import StringUtils     from "../../utils/StringUtils";

export default class CallableViaDataAttrsDialogs extends AbstractDialogs {

    static readonly ALERT_CANCEL_BUTTON_STRING = "Cancel";

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
                    if( StringUtils.isEmptyString(postParameters) ){
                        data = dialogDataDto.ajaxData;
                    }else{
                        data = JSON.parse(postParameters);
                    }

                    usedParameters = postParameters;
                    url            = requestUrl;
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
     * @param center {boolean}
     * @param dialogButtonLabel {string}
     */
    public buildDialogBody(url, method, requestData, callback, center :boolean = false, dialogButtonLabel :string = null) {

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
            _this.handleCommonAjaxCallLogicForBuildingDialog(data, callback, _this.callDialog, center, dialogButtonLabel);
        })
    };

    /**
     * Call the dialog and insert template in it's body
     * @param template {string}
     * @param callback {function}
     * @param center {boolean}
     * @param dialogButtonLabel {string}
     */
    private callDialog(template, callback = null, center :boolean = false, dialogButtonLabel :string = CallableViaDataAttrsDialogs.ALERT_CANCEL_BUTTON_STRING) {

        if( StringUtils.isEmptyString(dialogButtonLabel) )
        {
            dialogButtonLabel = CallableViaDataAttrsDialogs.ALERT_CANCEL_BUTTON_STRING;
        }

        let dialog = BootboxWrapper.alert({
            size: "large",
            backdrop: true,
            closeButton: false,
            message: template,
            centerVertical: center,
            buttons: {
                ok: {
                    label: dialogButtonLabel,
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
                callback(dialog);
            }

            dialog.addClass("." + Dialog.classesNames.modalMovedBackdrop);
            callableViaDataAttrsDialogs.forms.init();
        });

        if( center )
        {
            BootboxWrapper.centerDialog(dialog);
        }
    };

}