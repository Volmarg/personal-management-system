var bootbox   = require('bootbox');
import * as selectize from "selectize";
import 'selectize/dist/css/selectize.css';
import 'selectize/dist/css/selectize.bootstrap3.css';


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
        init: function () {
            this.applyAccordion();
            this.selectize.applyTagsSelectize();
            this.selectize.disableTagsInputsForSelectorsOnPage();
            this.callModalOnWidgetIcon();
            this.addMonthlyPaymentSummaryToAccordinHeader();
            this.removeFolderOnFolderRemovalIconClick();
            this.popover.init();

            $(document).ready(function(){
                ui.widgets.fixAccordions();
            });
        },
        applyAccordion: function () {
            $(this.elements["accordion-element-id"]).accordion({
                header: "h3",
                collapsible: true,
                active: false,
                autoHeight: false
            });
        },
        fixAccordions: function () {
            /**
             * In some cases accordion won't work if display is set to flex
             *  accordion needs to be initialized first and then css must be applied so it will work fine
             */
            let accordionSectionSelectorForMyTravels = '.ui-accordion-content';
            let cssFlex = {
                "display": "flex",
                "flex-wrap": "wrap",
                "padding": "5px"
            };
            let allSelectorsToFix = [
                accordionSectionSelectorForMyTravels
            ];

            $.each(allSelectorsToFix, function (index, selector) {
                if ($(selector).length > 0) {
                    $(selector)
                        .css(cssFlex)
                        .collapse('hide')
                        .hide();
                }

            });


        },
        callModalOnWidgetIcon() {
            let callModalButton = $(this.elements["call-widget-modal"]);

            if (callModalButton.length === 0) {
                return;
            }

            let _this = this;

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
                                    ui.widgets.loader.toggleLoader();
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
                                                default:
                                                    throw "Unknow subtype was provided: " + settings.subtype;
                                            }

                                        }

                                    }).fail(() => {
                                        bootstrap_notifications.notify('There was an error while fetching data for bootbox modal', 'danger')
                                    }).always(() => {
                                        ui.widgets.loader.toggleLoader();
                                    });

                                    break;
                                default:
                                    throw "Unknow type was provided: " + settings.type;
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
                                ui.widgets.loader.toggleLoader();
                                $.ajax({
                                    method  : "POST",
                                    url     : apiUrl,
                                    data    : data
                                }).always((data) => {
                                    ui.widgets.loader.toggleLoader();
                                    // if there is code there also must be message so i dont check it
                                    let code                = data['code'];
                                    let message             = data['message'];
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

                                    bootstrap_notifications.notify(message, notification_type);
                                });

                            }
                        }
                    });

                });

            }

        },
        selectize: {
            applyTagsSelectize: function(){

                let allTagsInputsFields = $('input.tags');
                let _this               = ui.widgets.selectize;

                // init tags with data from server
                $.each(allTagsInputsFields, (index, input) => {

                    let jsonValues   = $(input).attr('data-value');
                    let objectValues = [];
                    if( "" !== jsonValues ){
                        objectValues = JSON.parse(jsonValues);
                    }
                    let selectize = $(input).selectize({
                        persist     : false,
                        createOnBlur: true,
                        create      : true,
                    });

                    _this.addTagsToSelectize(selectize, objectValues);

                });

            },
            addTagsToSelectize(selectize, arrayOfTags){

                var selectize_element = selectize[0].selectize;

                $.each(arrayOfTags, (index, value) => {
                    selectize_element.addOption({
                        text    : value,
                        value   : value
                    });
                    selectize_element.refreshOptions() ;
                    selectize_element.addItem(value);
                });

            },
            disableTagsInputsForSelectorsOnPage: function (){

                let disableForSelectorsOnPage = ['#MyFiles .selectize-control'];

                // search for selectors on page and if found disable tags
                $.each(disableForSelectorsOnPage, (index, selector) => {
                    if ( $(selector).length > 0 )
                    {
                        let allSelectizeRenderdInputWrappers = $(selector);
                        $(allSelectizeRenderdInputWrappers).addClass('disabled');

                        return false;
                    }
                });

            }
        },
        popover: {
            init: function(){
                $('[data-toggle-popover="true"]').popover();
            }
        },
        loader: {
            toggleLoader: function(soft = true){
                let loader = $(ui.widgets.elements.loader);

                if( loader.hasClass('fadeOut') ){
                    this.showLoader();
                }else{
                    this.hideLoader();
                }
            },
            showLoader: (soft = true) => {
                let loader = $(ui.widgets.elements.loader);

                if( loader.hasClass('fadeOut') ){
                    if( soft ){
                        loader.attr('style', 'background: rgba(255,255,255,0.5) !important');
                    }
                    loader.removeClass('fadeOut');
                }

            },
            hideLoader: () => {
                let loader = $(ui.widgets.elements.loader);

                loader.removeAttr('style');
                loader.addClass('fadeOut');
            }
        }

    };
}());
