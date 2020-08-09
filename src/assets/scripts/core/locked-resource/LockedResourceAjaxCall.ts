import BootboxWrapper   from "../../libs/bootbox/BootboxWrapper";
import Navigation       from "../Navigation";
import BootstrapNotify  from "../../libs/bootstrap-notify/BootstrapNotify";
import Ajax             from "../ajax/Ajax";
import Loader           from "../../libs/loader/Loader";

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

        BootboxWrapper.mainLogic.confirm({
            message: "Do you want to toggle lock for this resource",
            backdrop: true,
            callback: function (result) {
                if (result) {

                    $.ajax({
                        method: LockedResourceAjaxCall.general.methods.lockResource.method,
                        url   : LockedResourceAjaxCall.general.methods.lockResource.url,
                        data  : data,
                    }).always(function(data){

                        try{
                            var code          = data['code'];
                            var message       = data['message'];
                            var reloadPage    = data['reload_page'];
                            var reloadMessage = data['reload_message'];
                        } catch(Exception){
                            throw({
                                "message"   : "Could not handle ajax call",
                                "data"      : data,
                                "exception" : Exception
                            })
                        }

                        if( 200 != code ){
                            _this.bootstrapNotify.showRedNotification(message);
                            return;
                        }else {

                            if( "undefined" === typeof message ){
                                message = LockedResourceAjaxCall.general.messages.ajaxCallHasBeenFinishedSuccessfully;
                            }

                            _this.bootstrapNotify.showGreenNotification(message);
                            _this.ajax.loadModuleContentByUrl(Navigation.getCurrentUri());
                            _this.ajax.singleMenuNodeReload(Navigation.getCurrentUri());

                            if( reloadPage ){
                                if( "" !== reloadMessage ){
                                    _this.bootstrapNotify.showBlueNotification(reloadMessage);
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
        Loader.showLoader();

        $.ajax({
            method: LockedResourceAjaxCall.general.methods.toggleLockedResourcesVisibility.method,
            url   : LockedResourceAjaxCall.general.methods.toggleLockedResourcesVisibility.url,
            data  : data,
        }).always( function(data){
            Loader.hideLoader();
            // todo: add AjaxDto
            try{
                var code          = data['code'];
                var message       = data['message'];
                var reloadPage    = data['reload_page'];
                var reloadMessage = data['reload_message'];
            } catch(Exception){
                throw({
                    "message"   : "Could not handle ajax call",
                    "data"      : data,
                    "exception" : Exception
                })
            }

            if( 200 != code ){
                bootstrapNotify.showRedNotification(message);
                return;
            }else {

                if( "undefined" === typeof message ){
                    message = LockedResourceAjaxCall.general.messages.ajaxCallHasBeenFinishedSuccessfully;
                }

                bootstrapNotify.showGreenNotification(message);

                // now ajax reload for this as there is also menu to be changed etc.
                Loader.showLoader();
                window.location.reload();

                if( reloadPage ){
                    if( "" !== reloadMessage ){
                        bootstrapNotify.showBlueNotification(reloadMessage);
                    }
                    location.reload();
                }
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
        Loader.showLoader();

        $.ajax({
            method: LockedResourceAjaxCall.general.methods.lockCreatePasswordType.method,
            url   : LockedResourceAjaxCall.general.methods.lockCreatePasswordType.url,
            data  : data,
        }).always( function(data){
            Loader.hideLoader();

            try{
                var code          = data['code'];
                var message       = data['message'];
                var reloadPage    = data['reload_page'];
                var reloadMessage = data['reload_message'];
            } catch(Exception){
                throw({
                    "message"   : "Could not handle ajax call",
                    "data"      : data,
                    "exception" : Exception
                })
            }

            if( 200 != code ){
                bootstrapNotify.showRedNotification(message);
                return;
            }else {

                if( "undefined" === typeof message ){
                    message = LockedResourceAjaxCall.general.messages.ajaxCallHasBeenFinishedSuccessfully;
                }

                bootstrapNotify.showGreenNotification(message);

                // no ajax reload for this as there is also menu to be changed etc.
                Loader.showLoader();
                window.location.reload();

                if( reloadPage ){
                    if( "" !== reloadMessage ){
                        bootstrapNotify.showBlueNotification(reloadMessage);
                    }
                    location.reload();
                }
            }
        })
    };

}