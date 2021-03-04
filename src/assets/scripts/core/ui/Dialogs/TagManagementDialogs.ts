import AbstractDialogs  from "./AbstractDialogs";
import Loader           from "../../../libs/loader/Loader";
import AjaxResponseDto  from "../../../DTO/AjaxResponseDto";
import Ajax             from "../../ajax/Ajax";
import BootboxWrapper   from "../../../libs/bootbox/BootboxWrapper";

export default class TagManagementDialogs extends AbstractDialogs {

    public buildTagManagementDialog(fileCurrentPath, moduleName, callback = null) {
        this.vars.fileCurrentPath = fileCurrentPath;
        let _this                 = this;

        let data = {
            'fileCurrentPath': fileCurrentPath,
            'moduleName'     : moduleName
        };
        Loader.toggleMainLoader();
        $.ajax({
            method: Ajax.REQUEST_TYPE_POST,
            url: this.methods.getTagsUpdateDialogTemplate,
            data: data
        }).always((data) => {
            _this.handleCommonAjaxCallLogicForBuildingDialog(data, callback, _this.callTagManagementDialog)
        })
    };

    private callTagManagementDialog(template, callback = null) {

        let dialog = BootboxWrapper.alert({
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
            let tagManagementDialogs = new TagManagementDialogs();
            let modalMainWrapper     = $(AbstractDialogs.selectors.classes.bootboxModalMainWrapper);
            let form                 = $(modalMainWrapper).find('form');
            let formSubmitButton     = $(form).find("[type^='submit']");

            tagManagementDialogs.attachTagsUpdateOnFormSubmit(formSubmitButton, form, callback);
            tagManagementDialogs.selectize.applyTagsSelectize();
            tagManagementDialogs.forms.init();
        });
    };

    private attachTagsUpdateOnFormSubmit(button, form, callback = null){
        let _this = this;
        $(button).on('click', (event) => {

            let formValidity = $(form)[0].checkValidity();

            if( !formValidity ){
                $(form)[0].reportValidity();
                return;
            }

            event.preventDefault();
            _this.makeAjaxCallForTagsUpdate(callback);
        });
    };

    private makeAjaxCallForTagsUpdate(callback = null){

        let fileCurrentPath = $("[" + this.data.fileCurrentLocation + "]").attr(this.data.fileCurrentLocation);
        let tagsInput       = $(AbstractDialogs.selectors.other.updateTagsInputWithTags);
        let tags            = $(tagsInput).val();
        let _this           = this;

        let data = {
            'tags'              : tags,
            'fileCurrentPath'   : fileCurrentPath,
        };
        Loader.toggleMainLoader();
        $.ajax({
            method: Ajax.REQUEST_TYPE_POST,
            url: this.methods.updateTagsForMyImages,
            data: data
        }).always( (data) => {
            Loader.toggleMainLoader();

            let ajaxResponseDto = AjaxResponseDto.fromArray(data);
            let notifyType      = '';

            if( ajaxResponseDto.isSuccessCode() ){

                if( $.isFunction(callback) ){
                    callback(tags);
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
                if( ajaxResponseDto.isReloadMessageSet() ){
                    _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                }
                location.reload();
            }
        })
    };
}