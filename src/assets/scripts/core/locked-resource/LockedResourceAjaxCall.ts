import BootboxWrapper   from "../../libs/bootbox/BootboxWrapper";
import Navigation       from "../Navigation";
import BootstrapNotify  from "../../libs/bootstrap-notify/BootstrapNotify";
import Ajax             from "../ajax/Ajax";
import Loader           from "../../libs/loader/Loader";
import AjaxResponseDto  from "../../DTO/AjaxResponseDto";
import AjaxEvents       from "../ajax/AjaxEvents";

/**
 * @description contains ajax calls methods used by LockedResource and other scripts
 *              separation of this logic is required due to circular references issues
 *              this class should only contain ajax call methods for LockedResource - EXPLICITLY
 *              other classes can/should use this methods here but none of the logic should be built for other classes
 */
export default class LockedResourceAjaxCall {

    /**
     * @type BootstrapNotify
     */
    private bootstrapNotify = new BootstrapNotify();

    /**
     * @type Ajax
     */
    private ajax = new Ajax();

    /**
     * @type AjaxEvents
     */
    private ajaxEvents = new AjaxEvents();

    /**
     * @type Object
     */
    private static general = {
        methods: {
            lockResource:{
                url: "/api/lock-resource/toggle",
                method: "POST"
            },
            toggleLockedResourcesVisibility: {
                url: "/api/system/toggle-resources-lock",
                method: "POST"
            },
            lockCreatePasswordType: {
                url: "/api/system/system-lock-set-password",
                method: "POST"
            }
        },
        messages: {
            ajaxCallHasBeenFinishedSuccessfully: function(){
                return "Ajax call has been finished successfully";
            },
        }
    };

    /**
     * Sends request to toggle lock for single record
     * @param $baseElement
     */
    public ajaxToggleLockRecord($baseElement): void
    {
        let _this  = this;
        let record = $($baseElement).find('.action-lock-record').attr('data-lock-resource-record');
        let type   = $($baseElement).find('.action-lock-record').attr('data-lock-resource-type');
        let target = $($baseElement).find('.action-lock-record').attr('data-lock-resource-target');

        let data = {
            "record" : record,
            "type"   : type,
            "target" : target
        };

        BootboxWrapper.confirm({
            message: "Do you want to toggle lock for this resource",
            backdrop: true,
            callback: function (result) {
                if (result) {

                    $.ajax({
                        method: LockedResourceAjaxCall.general.methods.lockResource.method,
                        url   : LockedResourceAjaxCall.general.methods.lockResource.url,
                        data  : data,
                    }).always(function(data){

                        let ajaxResponseDto = AjaxResponseDto.fromArray(data);

                        if( !ajaxResponseDto.isSuccessCode() ){
                            _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                            return;
                        }else {

                            let message = LockedResourceAjaxCall.general.messages.ajaxCallHasBeenFinishedSuccessfully();
                            if( ajaxResponseDto.isMessageSet()){
                                message = ajaxResponseDto.message;
                            }

                            _this.bootstrapNotify.showGreenNotification(message);
                            _this.ajaxEvents.loadModuleContentByUrl(Navigation.getCurrentUri());
                            _this.ajax.singleMenuNodeReload();

                            if( ajaxResponseDto.reloadPage){
                                if( ajaxResponseDto.isReloadMessageSet() ){
                                    _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                                }
                                location.reload();
                            }
                        }
                    });
                }
            }
        });
    };

    /**
     * @description Sends the request to unlock the resources for whole system
     *              Is static due to circular reference problems
     *
     * @param password   {string}
     * @param isUnlocked {boolean}
     */
    public static ajaxToggleSystemLock(password, isUnlocked): void
    {

        let bootstrapNotify = new BootstrapNotify();
        let data  = {
            "systemLockPassword": password,
            "isUnlocked"        : isUnlocked,
        };
        Loader.showMainLoader();

        $.ajax({
            method: LockedResourceAjaxCall.general.methods.toggleLockedResourcesVisibility.method,
            url   : LockedResourceAjaxCall.general.methods.toggleLockedResourcesVisibility.url,
            data  : data,
        }).always( function(data){
            Loader.hideMainLoader();
            let ajaxResponseDto = AjaxResponseDto.fromArray(data);

            if( !ajaxResponseDto.isSuccessCode() ){
                bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                return;
            }else {

                let message = LockedResourceAjaxCall.general.messages.ajaxCallHasBeenFinishedSuccessfully();

                if( ajaxResponseDto.isMessageSet() ){
                    message = ajaxResponseDto.message;
                }

                bootstrapNotify.showGreenNotification(message);

                // now ajax reload for this as there is also menu to be changed etc.
                Loader.showMainLoader();

                if( ajaxResponseDto.isReloadMessageSet() ){
                    bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                }

                location.reload();
            }
        })
    };

    /**
     * @description Sends the request to create first time password for lock system
     *              Is static due to circular reference problems
     * @param password {string}
     */
    public static ajaxCreateLockPasswordForSystem(password): void
    {

        let bootstrapNotify = new BootstrapNotify();
        let data  = {
            "systemLockPassword": password
        };
        Loader.showMainLoader();

        $.ajax({
            method: LockedResourceAjaxCall.general.methods.lockCreatePasswordType.method,
            url   : LockedResourceAjaxCall.general.methods.lockCreatePasswordType.url,
            data  : data,
        }).always( function(data){
            Loader.hideMainLoader();

            let ajaxResponseDto = AjaxResponseDto.fromArray(data);

            if( !ajaxResponseDto.isSuccessCode() ){
                bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                return;
            }else {

                let message = LockedResourceAjaxCall.general.messages.ajaxCallHasBeenFinishedSuccessfully();
                if( ajaxResponseDto.isMessageSet() ){
                    message = ajaxResponseDto.message;
                }

                bootstrapNotify.showGreenNotification(message);

                // no ajax reload for this as there is also menu to be changed etc.
                Loader.showMainLoader();

                if( ajaxResponseDto.isReloadMessageSet() ){
                    bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                }

                location.reload();
            }
        })
    };

}