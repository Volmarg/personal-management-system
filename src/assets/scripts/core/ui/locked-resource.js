import BootstrapNotify from "../../libs/bootstrap-notify/BootstrapNotify";
import Loader          from "../../libs/loader/Loader";

var bootbox = require('bootbox');
import * as selectize from "selectize";

export default (function () {
    window.ui.lockedResource = {
        elements: {
            'saved-element-class': '.save-parent',
        },
        general: {
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
        },
        attributes:{
            dataToggleResourcesLockForSystem : 'data-toggle-resources-lock-for-system',
            dataSetResourcesLockForSystem    : 'data-set-resources-lock-password-for-system',
        },
        bootstrapNotify: null,
        init: function (){
            this.bootstrapNotify = new BootstrapNotify();

            this.attachToggleRecordLockOnActionLockRecord();
            this.attachEventsOnToggleResourcesLockForSystem();
            this.attachEventsOnLockCreatePasswordForSystem();
        },
        /**
         * Adds click event on every lock record action icon
         */
        attachToggleRecordLockOnActionLockRecord: function () {
            let _this              = this;
            let lockResourceButton = $('.action-lock-record');

            $(lockResourceButton).off('click'); // to prevent double attachement on reinit
            $(lockResourceButton).on('click', function () {
                let closest_parent = this.closest(_this.elements["saved-element-class"]);
                _this.ajaxToggleLockRecord(closest_parent);
            });
        },
        /**
         * Sends request to toggle lock for single record
         * @param tr_parent_element {object}
         */
        ajaxToggleLockRecord: function (tr_parent_element) {
            let _this  = this;
            let record = $(tr_parent_element).find('.action-lock-record').attr('data-lock-resource-record');
            let type   = $(tr_parent_element).find('.action-lock-record').attr('data-lock-resource-type');
            let target = $(tr_parent_element).find('.action-lock-record').attr('data-lock-resource-target');

            let data = {
                "record" : record,
                "type"   : type,
                "target" : target
            };

            bootbox.confirm({
                message: "Do you want to toggle lock for this resource",
                backdrop: true,
                callback: function (result) {
                    if (result) {

                        $.ajax({
                            method: ui.lockedResource.general.methods.lockResource.method,
                            url   : ui.lockedResource.general.methods.lockResource.url,
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
                                    message = window.ui.lockedResource.general.messages.ajaxCallHasBeenFinishedSuccessfully;
                                }

                                _this.bootstrapNotify.showGreenNotification(message);
                                ui.ajax.loadModuleContentByUrl(TWIG_REQUEST_URI);
                                ui.ajax.singleMenuNodeReload();

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

        },
        /**
         * Attaches event in the user menu Lock button
         */
        attachEventsOnToggleResourcesLockForSystem: function (){
            let $button    = $("[" + window.ui.lockedResource.attributes.dataToggleResourcesLockForSystem + "= true]");
            let $i         = $button.find('i');
            let isUnlocked = $i.hasClass("text-success");

            $button.off('click');
            $button.on('click', function() {
                if( isUnlocked ){
                    ui.lockedResource.ajaxToggleSystemLock("", isUnlocked);
                    return;
                }

                dialogs.ui.systemLock.buildSystemToggleLockDialog(null, isUnlocked);
            });
        },
        /**
         * Sends the request to unlock the resources for whole system
         * @param password   {string}
         * @param isUnlocked {boolean}
         */
        ajaxToggleSystemLock: function(password, isUnlocked){

            let _this = this;
            let data  = {
                "systemLockPassword": password,
                "isUnlocked"        : isUnlocked,
            };
            Loader.showLoader();

            $.ajax({
                method: window.ui.lockedResource.general.methods.toggleLockedResourcesVisibility.method,
                url   : window.ui.lockedResource.general.methods.toggleLockedResourcesVisibility.url,
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
                        message = window.ui.lockedResource.general.messages.ajaxCallHasBeenFinishedSuccessfully;
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
        },
        /**
         * Attaches event for creating the first time password for lock when user does not have any set
         *  this is pretty much like needed due to the fact that there was no such option in old version of project
         */
        attachEventsOnLockCreatePasswordForSystem: function (){
            let $button = $("[" + window.ui.lockedResource.attributes.dataSetResourcesLockForSystem + "= true]");

            $button.off('click');
            $button.on('click', function() {
                dialogs.ui.systemLock.buildCreateLockPasswordForSystemDialog();
            });
        },
        /**
         * Sends the request to create first time password for lock system
         * @param password {string}
         */
        ajaxCreateLockPasswordForSystem: function(password){

            let _this = this;
            let data  = {
                "systemLockPassword": password
            };
            Loader.showLoader();

            $.ajax({
                method: window.ui.lockedResource.general.methods.lockCreatePasswordType.method,
                url   : window.ui.lockedResource.general.methods.lockCreatePasswordType.url,
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
                        message = window.ui.lockedResource.general.messages.ajaxCallHasBeenFinishedSuccessfully;
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
        },
    };


}());