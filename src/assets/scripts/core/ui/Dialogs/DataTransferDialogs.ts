import AbstractDialogs  from "./AbstractDialogs";
import Loader           from "../../../libs/loader/Loader";
import AjaxResponseDto  from "../../../DTO/AjaxResponseDto";
import Ajax             from "../../ajax/Ajax";
import BootboxWrapper   from "../../../libs/bootbox/BootboxWrapper";

export default class DataTransferDialogs extends AbstractDialogs {

    /**
     *
     * @param filesCurrentPaths array
     * @param moduleName string
     * @param callback function
     */
    public buildDataTransferDialog(filesCurrentPaths, moduleName, callback = null) {
        this.vars.filesCurrentPaths = filesCurrentPaths;
        let _this                   = this;

        let data = {
            'files_current_locations' : filesCurrentPaths,
            'moduleName'              : moduleName
        };

        Loader.toggleLoader();
        $.ajax({
            method: Ajax.REQUEST_TYPE_POST,
            url: this.methods.getDataTransferDialogTemplate,
            data: data
        }).always((data) => {
            _this.handleCommonAjaxCallLogicForBuildingDialog(data, callback, _this.callDataTransferDialog)
        })
    };

    private callDataTransferDialog(template, callback = null) {

        let dialog = BootboxWrapper.mainLogic.alert({
            size: "sm",
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
            let modalMainWrapper = $(this.selectors.classes.bootboxModalMainWrapper);
            let form             = $(modalMainWrapper).find('form');
            let formSubmitButton = $(form).find("[type^='submit']");

            this.attachDataTransferToDialogFormSubmit(formSubmitButton, callback);
            this.forms.init();
        });
    };

    private attachDataTransferToDialogFormSubmit(button, callback = null){
        let _this = this;
        $(button).on('click', (event) => {
            event.preventDefault();
            _this.makeAjaxCallForDataTransfer(callback);
        });
    };

    private makeAjaxCallForDataTransfer(callback = null){
        let _this                       = this;
        let filesCurrentPaths           = this.vars.filesCurrentPaths;
        let targetUploadModuleDirInput  = $(this.selectors.ids.targetUploadModuleDirInput).val();
        let targetSubdirectoryPath      = $(this.selectors.ids.targetSubdirectoryTypeInput).val();

        let data = {
            'files_current_locations'                       : filesCurrentPaths,
            'target_upload_module_dir'                      : targetUploadModuleDirInput,
            'subdirectory_target_path_in_module_upload_dir' : targetSubdirectoryPath
        };
        Loader.toggleLoader();
        $.ajax({
            method: Ajax.REQUEST_TYPE_POST,
            url: this.methods.moveMultipleFiles,
            data: data
        }).always( (data) => {
            Loader.toggleLoader();

            let ajaxResponseDto = AjaxResponseDto.fromArray(data);
            let notifyType      = '';

            if( ajaxResponseDto.isSuccessCode() ){

                if( 'function' === typeof(callback) ){
                    callback();
                    bootbox.hideAll()
                }

                notifyType = 'success'
            }else{
                notifyType = 'danger';
            }

            // not checking if code is set because if message is then code must be also
            if( ajaxResponseDto.isMessageSet() ){
                _this.bootstrapNotify.notify(ajaxResponseDto.message, notifyType);
            }

            if( ajaxResponseDto.reloadPage ){
                if( !ajaxResponseDto.isReloadMessageSet() ){
                    _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                }
                location.reload();
            }
        })
    };
}