var bootbox = require('bootbox');
import * as selectize from "selectize";

/**
 * If possible - avoid moving logic from this script - some methods are called as plain string in twig tpls
 */
export default (function () {
    window.ui.lockedResource = {
        elements: {
            'saved-element-class': '.save-parent',
        },
        general: {
            methods: {
                methods: {
                    lockResource:{
                        url: "/api/lock-resource/toggle/{record}/{type}/{target}",
                        method: "GET",
                        params: {
                            record : "{record}",
                            type   : "{type}",
                            target : "{target}"
                        },
                    },
                    toggleLockedResourcesVisibility: {
                        url: "api/system/toggle-resources-lock",
                        method: "GET"
                    }
                },
            },
            messages: {
                ajaxCallHasBeenFinishedSuccessfully: function(){
                    return "Ajax call has been finished successfully";
                },
            }
        },
        attributes:{
            dataToggleResourcesLockForSystem: 'data-toggle-resources-lock-for-system'
        },
        init: function (){
            this.attachToggleRecordLockOnKeyIcon();
            this.attachEventsOnToggleResourcesLockForSystem();
        },
        attachToggleRecordLockOnKeyIcon: function () {
            let _this              = this;
            let lockResourceButton = $('.action-lock-record');

            $(lockResourceButton).off('click'); // to prevent double attachement on reinit
            $(lockResourceButton).on('click', function () {
                let closest_parent = this.closest(_this.elements["saved-element-class"]);
                _this.ajaxToggleLockRecord(closest_parent);
            });
        },
        ajaxToggleLockRecord: function (tr_parent_element) {
            let record = $(tr_parent_element).find('.action-lock-record').attr('data-lock-resource-record');
            let type   = $(tr_parent_element).find('.action-lock-record').attr('data-lock-resource-type');
            let target = $(tr_parent_element).find('.action-lock-record').attr('data-lock-resource-target');

            let url = ui.lockedResource.general.methods.removeEntity.lockResource.url.replace(ui.lockedResource.general.methods.removeEntity.lockResource.params.record, record);
            url = url.replace(ui.lockedResource.general.methods.removeEntity.lockResource.params.type, type);
            url = url.replace(ui.lockedResource.general.methods.removeEntity.lockResource.params.target, target);

            bootbox.confirm({
                message: "Do you want to toggle lock for this resource",
                backdrop: true,
                callback: function (result) {
                    if (result) {

                        $.ajax({
                            method: ui.lockedResource.general.methods.removeEntity.lockResource.method,
                            url   : url
                        }).always(function(data){

                            try{
                                var code    = data['code'];
                                var message = data['message'];
                            } catch(Exception){
                                throw({
                                    "message"   : "Could not handle ajax call",
                                    "data"      : data,
                                    "exception" : Exception
                                })
                            }

                            if( 200 != code ){
                                bootstrap_notifications.showRedNotification(message);
                                return;
                            }else {

                                if( "undefined" === typeof message ){
                                    message = window.ui.lockedResource.general.messages.ajaxCallHasBeenFinishedSuccessfully;
                                }

                                bootstrap_notifications.showGreenNotification(message);
                                ui.ajax.loadModuleContentByUrl(TWIG_REQUEST_URI);
                            }

                        });

                    }
                }
            });

        },
        attachEventsOnToggleResourcesLockForSystem: function (){
            let $button = $("[" + window.ui.lockedResource.attributes.dataToggleResourcesLockForSystem + "= true]");

            utils.spi
            $button.on('click', function() {
                $.ajax({
                    method: window.ui.lockedResource.general.methods.methods.toggleLockedResourcesVisibility.method,
                    url   : window.ui.lockedResource.general.methods.methods.toggleLockedResourcesVisibility.url,
                }).always( function(data){
                    try{
                        var code    = data['code'];
                        var message = data['message'];
                    } catch(Exception){
                        throw({
                            "message"   : "Could not handle ajax call",
                            "data"      : data,
                            "exception" : Exception
                        })
                    }

                    if( 200 != code ){
                        bootstrap_notifications.showRedNotification(message);
                        return;
                    }else {

                        if( "undefined" === typeof message ){
                            message = window.ui.lockedResource.general.messages.ajaxCallHasBeenFinishedSuccessfully;
                        }

                        bootstrap_notifications.showGreenNotification(message);

                        // no ajax reload for this as there is also menu to be changed etc.
                        ui.widgets.loader.showLoader();
                        window.location.reload();
                    }
                })

            });
        }
    };


}());