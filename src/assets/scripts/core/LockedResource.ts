
import BootstrapNotify  from "../libs/bootstrap-notify/BootstrapNotify";
import Loader           from "../libs/loader/Loader";
import Ajax             from "./ui/Ajax";
import Navigation       from "./Navigation";
import BootboxWrapper from "../libs/bootbox/BootboxWrapper";
import SystemLockDialogs from "./ui/Dialogs/SystemLockDialogs";

export default class LockedResource {

    /**
     * @type Object
     */
    private elements = {
        'saved-element-class': '.save-parent',
    };

    /**
     * @type Object
     */
    private general = {
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
     * @type Object
     */
    private attributes = {
        dataToggleResourcesLockForSystem : 'data-toggle-resources-lock-for-system',
        dataSetResourcesLockForSystem    : 'data-set-resources-lock-password-for-system',
    };

    /**
     * @type BootstrapNotify
     */
    private bootstrapNotify = new BootstrapNotify();

    /**
     * @type Ajax
     */
    private ajax = new Ajax();

    /**
     * @type SystemLockDialogs
     */
    private systemLockDialogs = new SystemLockDialogs();

    public init(){
        this.attachToggleRecordLockOnActionLockRecord();
        this.attachEventsOnToggleResourcesLockForSystem();
        this.attachEventsOnLockCreatePasswordForSystem();
    };

    /**
     * Adds click event on every lock record action icon
     */
    public attachToggleRecordLockOnActionLockRecord() {
        let _this              = this;
        let lockResourceButton = $('.action-lock-record');

        $(lockResourceButton).off('click'); // to prevent double attachement on reinit
        $(lockResourceButton).on('click', function () {
            let closest_parent = this.closest(_this.elements["saved-element-class"]);
            _this.ajaxToggleLockRecord(closest_parent);
        });
    };

    /**
     * Sends request to toggle lock for single record
     * @param tr_parent_element {object}
     */
    public ajaxToggleLockRecord(tr_parent_element) { // todo rename to element
        let _this  = this;
        let record = $(tr_parent_element).find('.action-lock-record').attr('data-lock-resource-record');
        let type   = $(tr_parent_element).find('.action-lock-record').attr('data-lock-resource-type');
        let target = $(tr_parent_element).find('.action-lock-record').attr('data-lock-resource-target');

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
                        method: _this.general.methods.lockResource.method,
                        url   : _this.general.methods.lockResource.url,
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
                                message = _this.general.messages.ajaxCallHasBeenFinishedSuccessfully;
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
     * Attaches event in the user menu Lock button
     */
    public attachEventsOnToggleResourcesLockForSystem(){
        let _this      = this;
        let $button    = $("[" + this.attributes.dataToggleResourcesLockForSystem + "= true]");
        let $i         = $button.find('i');
        let isUnlocked = $i.hasClass("text-success");

        $button.off('click');
        $button.on('click', function() {
            if( isUnlocked ){
                _this.ajaxToggleSystemLock("", isUnlocked);
                return;
            }

            _this.systemLockDialogs.buildSystemToggleLockDialog(null, isUnlocked);
        });
    };

    /**
     * Sends the request to unlock the resources for whole system
     * @param password   {string}
     * @param isUnlocked {boolean}
     */
    ajaxToggleSystemLock(password, isUnlocked){

        let _this = this;
        let data  = {
            "systemLockPassword": password,
            "isUnlocked"        : isUnlocked,
        };
        Loader.showLoader();

        $.ajax({
            method: this.general.methods.toggleLockedResourcesVisibility.method,
            url   : this.general.methods.toggleLockedResourcesVisibility.url,
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
                _this.bootstrapNotify.showRedNotification(message);
                return;
            }else {

                if( "undefined" === typeof message ){
                    message = _this.general.messages.ajaxCallHasBeenFinishedSuccessfully;
                }

                _this.bootstrapNotify.showGreenNotification(message);

                // now ajax reload for this as there is also menu to be changed etc.
                Loader.showLoader();
                window.location.reload();

                if( reloadPage ){
                    if( "" !== reloadMessage ){
                        _this.bootstrapNotify.showBlueNotification(reloadMessage);
                    }
                    location.reload();
                }
            }
        })
    };

    /**
     * Attaches event for creating the first time password for lock when user does not have any set
     *  this is pretty much like needed due to the fact that there was no such option in old version of project
     */
    public attachEventsOnLockCreatePasswordForSystem(){
        let $button = $("[" + this.attributes.dataSetResourcesLockForSystem + "= true]");

        $button.off('click');
        $button.on('click', () => {
            this.systemLockDialogs.buildCreateLockPasswordForSystemDialog();
        });
    };

    /**
     * Sends the request to create first time password for lock system
     * @param password {string}
     */
    public ajaxCreateLockPasswordForSystem(password){

        let _this = this;
        let data  = {
            "systemLockPassword": password
        };
        Loader.showLoader();

        $.ajax({
            method: this.general.methods.lockCreatePasswordType.method,
            url   : this.general.methods.lockCreatePasswordType.url,
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
                _this.bootstrapNotify.showRedNotification(message);
                return;
            }else {

                if( "undefined" === typeof message ){
                    message = _this.general.messages.ajaxCallHasBeenFinishedSuccessfully;
                }

                _this.bootstrapNotify.showGreenNotification(message);

                // no ajax reload for this as there is also menu to be changed etc.
                Loader.showLoader();
                window.location.reload();

                if( reloadPage ){
                    if( "" !== reloadMessage ){
                        _this.bootstrapNotify.showBlueNotification(reloadMessage);
                    }
                    location.reload();
                }
            }
        })
    };

}