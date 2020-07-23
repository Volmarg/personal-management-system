import BootstrapNotify from "../../libs/bootstrap-notify/BootstrapNotify";
import Accordion       from "../../libs/accordion/Accordion";
import Loader          from "../../libs/loader/Loader";
import DomAttributes   from "../utils/DomAttributes";

var bootbox = require('bootbox');

import 'bootstrap-toggle/css/bootstrap2-toggle.min.css';
import 'bootstrap-toggle';

// todo: when rewriting other logic to Ts
//  need to find a way to use that on static scripts or in calls in twig
export default (function () {
    if (typeof window.ui === 'undefined') {
        window.ui = {};
    }
    ui.widgets = {
        elements: {
            'accordion-element-id'          : '#accordion',
            'accordin-section-class'        : '.accordin-section',
            'call-widget-modal'             : '.call-widget-modal',
            'widgetModalClassName'          : 'widget-modal',
            'widgetRemoveFolderClassName'   : '.widget-remove-folder',
            'loader'                        : '#loader'
        },
        data: {
            folderPathInUploadDir           : 'data-folder-path-in-upload-dir',
            uploadModuleDir                 : 'data-upload-module-dir'
        },
        apiUrl: {
            removeFolderViaPost             : '/files/{upload_module_dir}/remove-subdirectory'
        },
        placeholders: {
            uploadModuleDir                 : '{upload_module_dir}'
        },
        bootstrapNotify: new BootstrapNotify(),
        accordion:       new Accordion(),
        init: function () {
            this.applyAccordion();
            this.callModalOnWidgetIcon();
            this.addMonthlyPaymentSummaryToAccordinHeader();
            this.removeFolderOnFolderRemovalIconClick();
            this.bootstrapToggle.init(); // use BootstrapToggle.ts

            $(document).ready(() => {
                this.accordion.fixAccordionsForDisplayFlex();
            });
        },
        /**
         * todo: needs to be removed - legacy support from template
         */
        applyAccordion: function(){
            this.accordion.applyAccordion();
        },
        callModalOnWidgetIcon() {
            let callModalButton = $(this.elements["call-widget-modal"]);

            if (callModalButton.length === 0) {
                return;
            }

            let _this = this;

            callModalButton.off('click'); // prevent adding multiple click events
            callModalButton.click((event) => {

                let clickedButton = $(event.target).closest('[data-widget="true"]');
                let jsonString    = $(clickedButton).attr('data-settings');
                let settings      = JSON.parse(jsonString);

                bootbox.alert({

                    message: () => {
                        if (settings.type !== undefined && settings.type !== null) {

                            let ajaxData = '';

                            if( "undefined" !== typeof settings.data ){
                                ajaxData = settings.data;
                            }

                            switch (settings.type) {
                                case 'template':
                                    Loader.toggleLoader();
                                    $.ajax({
                                        method: 'POST',
                                        data: ajaxData,
                                        url: settings.url
                                    }).done((responseData) => {

                                        if( undefined !== responseData['template'] ){
                                            responseData = responseData['template'];
                                        }

                                        let bootboxBody = $('.' + _this.elements.widgetModalClassName).find('.bootbox-body');
                                        bootboxBody.html(responseData);

                                        if (settings.callFunctions !== undefined && settings.callFunctions !== null) {
                                            let func = new Function(settings.callFunctions);
                                            func();
                                        }

                                        if( undefined !== settings.subtype ){

                                            switch(settings.subtype){
                                                case "add-note":
                                                    let formSubmitButton = $('#my_notes_submit');
                                                    formSubmitButton.attr('data-template-url', window.location.pathname);
                                                    formSubmitButton.attr('data-template-method', 'GET');
                                                    break;
                                            }

                                        }

                                        let reloadPage    = responseData['reload_page'];
                                        let reloadMessage = responseData['reload_message'];

                                        if( reloadPage ){
                                            if( "" !== reloadMessage ){
                                                ui.widgets.bootstrapNotify.showBlueNotification(reloadMessage);
                                            }
                                            location.reload();
                                        }

                                    }).fail(() => {
                                        ui.widgets.bootstrapNotify.notify('There was an error while fetching data for bootbox modal', 'danger')
                                    }).always(() => {
                                        Loader.toggleLoader();
                                    });

                                    break;
                                default:
                                    throw "Unknown type was provided: " + settings.type;
                            }

                        }
                    },
                    backdrop: true,
                    buttons: {
                        ok: {
                            label: 'Cancel'
                        }
                    },
                    className: _this.elements.widgetModalClassName,
                    size: 'large'
                });

            })
        },
        addMonthlyPaymentSummaryToAccordinHeader: function () { //TODO: refractor and make it reusable + make additional func. for payments
            let accordin_wrapper = $(this.elements["accordion-element-id"]);
            let accordin_sections = $(accordin_wrapper).find(this.elements["accordin-section-class"]);

            $(accordin_sections).each((index, element) => {
                let header = $(element).find('h3');
                let payment_summary = $(element).find('section.monthly-summary .amount').html();
                $(header).find('.payment-summary').html(' ( ' + payment_summary + ' )');
            });
        },
        removeFolderOnFolderRemovalIconClick: function () {
            let folderRemovalButton = $(this.elements.widgetRemoveFolderClassName);
            let _this               = this;

            if( $(folderRemovalButton).length > 0 ){

                $(folderRemovalButton).on('click', (event) => {

                    let clickedButton = $(event.target);

                    if( $(clickedButton).hasClass('disabled') ){
                        return;
                    }

                    // bootbox
                    bootbox.confirm({
                        message: 'Do You really want to remove this folder?',
                        backdrop: true,
                        callback: function (result) {
                            if (result) {
                                // confirmation logic
                                let subdirectoryPathInUploadDir = $(clickedButton).attr(_this.data.folderPathInUploadDir);
                                let uploadModuleDir             = $(clickedButton).attr(_this.data.uploadModuleDir);
                                let apiUrl                      = _this.apiUrl.removeFolderViaPost.replace(_this.placeholders.uploadModuleDir, uploadModuleDir);
                                let data = {
                                    'subdirectory_current_path_in_module_upload_dir': subdirectoryPathInUploadDir,
                                    'block_removal'                                 : true
                                };
                                Loader.toggleLoader();
                                $.ajax({
                                    method  : "POST",
                                    url     : apiUrl,
                                    data    : data
                                }).always((data) => {
                                    Loader.toggleLoader();
                                    // if there is code there also must be message so i dont check it
                                    let code                = data['code'];
                                    let message             = data['message'];
                                    let reloadPage          = data['reload_page'];
                                    let reloadMessage       = data['reload_message'];
                                    let notification_type   = '';

                                    if( undefined === code ){
                                        return;
                                    }

                                    if( code === 200 ){
                                        notification_type = 'success';

                                        window.setTimeout( () => {
                                            window.location.reload();
                                        }, 1000)

                                    }else{
                                        notification_type = 'danger';
                                    }

                                    ui.widgets.bootstrapNotify.notify(message, notification_type);

                                    if( reloadPage ){
                                        if( "" !== reloadMessage ){
                                            ui.widgets.bootstrapNotify.showBlueNotification(reloadMessage);
                                        }
                                        location.reload();
                                    }
                                });
                            }
                        }
                    });
                });
            }
        },
        popover: {
            init: function(){
                $('[data-toggle-popover="true"]').popover();
            },
        },
        // use BootstrapToggle.ts
        bootstrapToggle: {
            init: function(){

                let allElementsToTransform = $('[data-toggle-bootstrap-toggle="true"]');

                $.each(allElementsToTransform, function(index, element){
                    let classes  = $(this).attr('data-toggle-class');
                    let $element = $(element);
                    if( "undefined" === typeof classes){
                        classes = '';
                    }

                   $element.bootstrapToggle({
                        size    : "small",
                        onstyle : "success",
                        offstyle: "info",
                        style   : classes
                    });

                    let toggleButton = $(element).closest('.toggle');

                    $(toggleButton).on('click', () =>{
                        if( DomAttributes.isChecked($element)){
                            DomAttributes.unsetChecked($element);
                        }else{
                            DomAttributes.setChecked($element);
                        }
                    })

                });

                this.initEventsAttach();
            },
            /**
             * This function will attach:
             *   - save event for settings (normally save works with action buttons but I want it here too just for toggle with specific class);
             */
            initEventsAttach: function(){
                ui.crud.attachContentSaveEventOnSaveIcon();
            }
        },
    };
}());
