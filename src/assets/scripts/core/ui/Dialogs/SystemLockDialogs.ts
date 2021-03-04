import AbstractDialogs          from "./AbstractDialogs";
import Loader                   from "../../../libs/loader/Loader";
import BootboxWrapper           from "../../../libs/bootbox/BootboxWrapper";
import Ajax                     from "../../ajax/Ajax";
import LockedResourceAjaxCall   from "../../locked-resource/LockedResourceAjaxCall";
import AjaxResponseDto from "../../../DTO/AjaxResponseDto";

export default class SystemLockDialogs extends AbstractDialogs {

    /**
     * @description Build dialog with confirmation about setting/removing lock for entire system
     *
     * @param callback   {function}
     * @param isUnlocked {boolean}
     */
    public buildSystemToggleLockDialog(callback = null, isUnlocked) {
        let _this = this;

        Loader.toggleMainLoader();
        $.ajax({
            method: Ajax.REQUEST_TYPE_GET,
            url: this.methods.systemLockResourcesDialogTemplate
        }).always((data) => {
            Loader.toggleMainLoader();

            let ajaxResponseDto = AjaxResponseDto.fromArray(data);

            if( ajaxResponseDto.isTemplateSet() ){
                _this.callSystemToggleLockDialog(ajaxResponseDto.template, callback, isUnlocked);
            } else if( ajaxResponseDto.isMessageSet() ) {
                _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
            }else{
                let message = 'Something went wrong while trying to load dialog template.';
                _this.bootstrapNotify.showRedNotification(message);
            }

            if( ajaxResponseDto.reloadPage ){
                if( ajaxResponseDto.isReloadMessageSet() ){
                    _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                }
                location.reload();
            }
        })
    };

    /**
     * Call dialog with confirmation about setting/removing lock for entire system
     * @param template   {string}
     * @param callback   {function}
     * @param isUnlocked {boolean}
     */
    public callSystemToggleLockDialog(template, callback = null, isUnlocked) {

        let dialog = BootboxWrapper.alert({
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
            let $systemLockPasswordInput  = $(AbstractDialogs.selectors.ids.systemLockPasswordInput);
            let $form                     = $systemLockPasswordInput.closest('form');
            let $systemLockPasswordSubmit = $form.find('button');

            setTimeout( () => {
                $systemLockPasswordInput[0].focus();
            }, 500);

            $systemLockPasswordSubmit.on('click', function (event) {
                event.preventDefault();
                let password = $systemLockPasswordInput.val();
                LockedResourceAjaxCall.ajaxToggleSystemLock(password, isUnlocked);
            })
        });
    };

    /**
     * Build dialog for creating first time lock password
     * @param callback {function}
     */
    public buildCreateLockPasswordForSystemDialog(callback = null) {
        let _this = this;

        Loader.toggleMainLoader();
        $.ajax({
            method: Ajax.REQUEST_TYPE_GET,
            url: this.methods.createSystemLockPasswordDialogTemplate
        }).always((data) => {
           _this.handleCommonAjaxCallLogicForBuildingDialog(data, callback, _this.callCreateLockPasswordForSystemDialog);
        })
    };

    /**
     * Calls dialog for creating first time lock password
     * @param callback {function}
     * @param template {string}
     */
    public callCreateLockPasswordForSystemDialog(template, callback = null) {

        let dialog = BootboxWrapper.alert({
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
            let $systemLockCreatePasswordInput  = $(AbstractDialogs.selectors.ids.systemLockPasswordInput);
            let $form                           = $systemLockCreatePasswordInput.closest('form');
            let $systemLockCreatePasswordSubmit = $form.find('button');

            setTimeout( () => {
                $systemLockCreatePasswordInput[0].focus();
            }, 500);

            $systemLockCreatePasswordSubmit.on('click', function (event) {
                event.preventDefault();
                let password = $systemLockCreatePasswordInput.val();
                LockedResourceAjaxCall.ajaxCreateLockPasswordForSystem(password);
            })
        });
    };
}