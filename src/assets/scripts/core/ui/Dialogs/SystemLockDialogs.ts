import AbstractDialogs          from "./AbstractDialogs";
import Loader                   from "../../../libs/loader/Loader";
import BootboxWrapper           from "../../../libs/bootbox/BootboxWrapper";
import Ajax                     from "../../ajax/Ajax";
import LockedResourceAjaxCall   from "../../locked-resource/LockedResourceAjaxCall";

export default class SystemLockDialogs extends AbstractDialogs {

    /**
     * Build dialog with confirmation about setting/removing lock for entire system
     * @param callback   {function}
     * @param isUnlocked {boolean}
     */
    public buildSystemToggleLockDialog(callback = null, isUnlocked) {
        let _this = this;

        Loader.toggleLoader();
        $.ajax({
            method: Ajax.REQUEST_TYPE_GET,
            url: this.methods.systemLockResourcesDialogTemplate
        }).always((data) => {
            Loader.toggleLoader();

            let reloadPage    = data['reload_page'];
            let reloadMessage = data['reload_message'];

            if( undefined !== data['template'] ){
                _this.callSystemToggleLockDialog(data['template'], callback, isUnlocked);
            } else if( undefined !== data['errorMessage'] ) {
                _this.bootstrapNotify.notify(data['errorMessage'], 'danger');
            }else{
                let message = 'Something went wrong while trying to load dialog template.';
                _this.bootstrapNotify.notify(message, 'danger');
            }

            if( reloadPage ){
                if( "" !== reloadMessage ){
                    _this.bootstrapNotify.showBlueNotification(reloadMessage);
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

        let _this = this;
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

        Loader.toggleLoader();
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

        let _this = this;
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