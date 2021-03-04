import AbstractDialogs  from "./AbstractDialogs";
import Loader           from "../../../libs/loader/Loader";
import AjaxResponseDto  from "../../../DTO/AjaxResponseDto";
import Ajax             from "../../ajax/Ajax";
import BootboxWrapper   from "../../../libs/bootbox/BootboxWrapper";
import BootstrapSelect  from "../../../libs/bootstrap-select/BootstrapSelect";

/**
 * @description This is an old solution used to handle mass transfer of files, used for example in datatables
 */
export default class DataTransferDialogs extends AbstractDialogs {

    /**
     *
     * @param filesCurrentPaths array
     * @param moduleName string
     * @param callback function
     */
    public buildDataTransferDialog(filesCurrentPaths, moduleName, callback = null) {
        let _this = this;

        let data = {
            'files_current_locations' : filesCurrentPaths,
            'moduleName'              : moduleName
        };

        Loader.toggleMainLoader();
        $.ajax({
            method: Ajax.REQUEST_TYPE_POST,
            url: this.methods.getDataTransferDialogTemplate,
            data: data
        }).always((data) => {
            _this.handleCommonAjaxCallLogicForBuildingDialog(data, callback, _this.callDataTransferDialog)
        })
    };

    private callDataTransferDialog(template, callback = null) {

        let dialog = BootboxWrapper.alert({
            size: "lg",
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
            let dataTransferDialogs = new DataTransferDialogs();
            let modalMainWrapper    = $(AbstractDialogs.selectors.classes.bootboxModalMainWrapper);
            let form                = $(modalMainWrapper).find('form');
            let formSubmitButton    = $(form).find("[type^='submit']");

            let $jsonTransferredFilesListDomElement = $(modalMainWrapper).find("[" + dataTransferDialogs.data.transferredFilesJson + "]");
            let jsonTransferredFilesList            = JSON.parse($jsonTransferredFilesListDomElement.attr(dataTransferDialogs.data.transferredFilesJson));

            BootstrapSelect.init();

            dataTransferDialogs.attachDataTransferToDialogFormSubmit(formSubmitButton, jsonTransferredFilesList, callback);
            dataTransferDialogs.forms.init();
        });
    };

    public attachDataTransferToDialogFormSubmit(button, transferredFilesPaths: Array<string>, callback = null){
        let _this = this;
        $(button).on('click', (event) => {
            event.preventDefault();
            _this.makeAjaxCallForDataTransfer(transferredFilesPaths, callback);
        });
    };

    private makeAjaxCallForDataTransfer(transferredFilesPaths: Array<string>, callback = null){
        let _this                       = this;
        let targetUploadModuleDirInput  = $(AbstractDialogs.selectors.ids.targetUploadModuleDirInput).val();
        let targetSubdirectoryPath      = $(AbstractDialogs.selectors.ids.targetSubdirectoryTypeInput).val();

        let data = {
            'files_current_locations'                       : transferredFilesPaths,
            'target_upload_module_dir'                      : targetUploadModuleDirInput,
            'subdirectory_target_path_in_module_upload_dir' : targetSubdirectoryPath
        };
        Loader.toggleMainLoader();
        $.ajax({
            method: Ajax.REQUEST_TYPE_POST,
            url: this.methods.moveMultipleFiles,
            data: data
        }).always( (data) => {
            Loader.toggleMainLoader();

            let ajaxResponseDto = AjaxResponseDto.fromArray(data);
            let notifyType      = '';

            if( ajaxResponseDto.isSuccessCode() ){

                if( $.isFunction(callback) ){
                    callback();
                    BootboxWrapper.hideAll();
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