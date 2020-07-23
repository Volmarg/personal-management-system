
var bootbox = require('bootbox');
import * as selectize from "selectize";

import tinymce          from "tinymce";
import AjaxResponseDto  from "../../DTO/AjaxResponseDto";
import FormsValidator   from '../../core/validators/FormsValidator';
import BootstrapNotify  from "../../libs/bootstrap-notify/BootstrapNotify";
import Loader           from "../../libs/loader/Loader";
import DomAttributes    from "../utils/DomAttributes";

/**
 * If possible - avoid moving logic from this script - some methods are called as plain string in twig tpls
 * @todo  When reweriting to TS - should be placed in separated file instead of adding coments to extensive logic - keep in mind usages in twig
 */
export default (function () {
    window.ui = {};
    ui.crud = {
        elements: {
            'removed-element-class': '.trash-parent',
            'edited-element-class': '.editable-parent',
            'saved-element-class': '.save-parent',
        },
        classes: {
            'hidden'                    : 'd-none',
            'disabled'                  : 'disabled',
            'table-active'              : 'table-active',
            'fontawesome-picker-preview': 'fontawesome-preview',
            'fontawesome-picker-input'  : 'fontawesome-input',
            'fontawesome-picker'        : 'fontawesome-picker',
            'entity-remove-action'      : '.entity-remove-action',
            'accordion'                 : '.ui-accordion',
            'accordionContent'          : '.ui-accordion-content',
        },
        data: {
            entityToggleBoolval                 : "data-entity-toggle-boolval",
            entityToggleBoolvalSuccessMessage   : "data-entity-toggle-success-message",
            entityId                            : "data-entity-id",
            entityRepositoryName                : "data-entity-repository-name",
            entityFieldName                     : "data-entity-field-name",
            baseParentElementSelector           : "data-base-parent-element-selector",
            tinymceElementSelector              : "data-tiny-mce-selector",
            tinymceElementInstanceSelector      : "data-tiny-mce-instance-selector", // must be id name without `#`
        },
        messages: {
            entityUpdateSuccess: function (entity_name) {
                return entity_name + " record has been succesfully updated";
            },
            entityUpdateFail: function (entity_name) {
                return "Something went wrong while updating " + entity_name + " record";
            },
            entityRemoveSuccess: function (entity_name) {
                return entity_name + " record was succesfully removed";
            },
            entityRemoveFail: function (entity_name) {
                return "Something went wrong while removing " + entity_name + " record";
            },
            entityEditStart: function (entity_name) {
                return 'You are currently editing ' + entity_name + ' record'
            },
            entityEditEnd: function (entity_name) {
                return "You've finished editing " + entity_name + ' record';
            },
            entityCreatedRecordSuccess: function (entity_name) {
                return "New " + entity_name + ' record has been created';
            },
            entityCreatedRecordFail: function (entity_name) {
                return "There was a problem while creating " + entity_name + ' record';
            },
            formTargetActionUpdateSuccess:function(form_target_action_name){
                return "Update action for " + form_target_action_name + ' has been completed';
            },
            formTargetActionUpdateFail:function(form_target_action_name){
                return "There was a problem while performing update action for " + form_target_action_name;
            },
            default_record_removal_confirmation_message: 'Are You sure that You want to remove this record?',
            default_copy_data_confirmation_message: 'Data was copied successfully',
            default_copy_data_fail_message: 'There was some problem while copying the data',
            password_copy_confirmation_message: 'Password was copied successfully',
        },
        bootstrapNotify: new BootstrapNotify(),
        init: function () {
            this.attachRemovingEventOnTrashIcon();
            this.attachContentEditEventOnEditIcon();
            this.attachContentSaveEventOnSaveIcon();
            this.attachContentCopyEventOnCopyIcon();
            this.attachFontawesomePickEventForFontawesomeAction();
            this.attachRecordAddViaAjaxOnSubmit();
            this.attachRecordUpdateViaAjaxOnSubmitForSingleForm();


            // the order is very important as in one event we block propagation to prevent accordion closing
            this.attachEventOnButtonForEditingViaTinyMce();
            this.attachEventOnButtonToTransformTargetSelectorToTinyMceInstance();

            this.general.init();
        },
        general: {
            selectors:{
                classes:{
                    entityRemoveAction        : '[data-entity-removal-action="true"]',
                    entityCallEditModalAction : '[data-entity-modal-edit-action="true"]'
                }
            },
            methods: {
                removeEntity: {
                    url: "/api/repository/remove/entity/{repository_name}/{id}",
                    method: "GET",
                    params: {
                        repositoryName: "{repository_name}",
                        id: "{id}"
                    }
                },
                buildEditEntityModalByRepositoryName: {
                    MyContactRepository: {
                        url     : "/dialog/body/edit-contact-card",
                        method  : "POST",
                        callback: function(){
                            events.general.attachFormViewAppendEvent();
                            events.general.attachRemoveParentEvent();
                            jscolorCustom.init();
                            ui.crud.attachContentSaveEventOnSaveIcon();
                        }
                    },
                    /**
                     * Each dialog method should have target repository
                     * @param entityId
                     */
                    callModal: function(entityId){

                    }
                },
                updateEntityByRepositoryName: {
                    MyContactRepository: {
                        // special submission button goes here - like sending all data at once stripping forms etc
                    }
                },
                toggleBoolval: {
                    url     : "/api/repository/toggle-boolval",
                    method  : "GET",
                    /**
                     *
                     * @param paramEntityId         {string}
                     * @param paramRepositoryName   {string}
                     * @param paramFieldName        {string}
                     * @returns {string}
                     */
                    buildUrl: function(paramEntityId, paramRepositoryName, paramFieldName){
                        if(
                                "" === paramEntityId
                            ||  "" === paramRepositoryName
                            ||  "" === paramFieldName
                        ){
                            throw{
                              "message": "At least one of the params required to build url for boolval toggle is missing",
                                paramEntityId       : paramEntityId,
                                paramRepositoryName : paramRepositoryName,
                                paramFieldName      : paramFieldName
                            };
                        }

                        let url = this.url + '/' + paramEntityId + '/' + paramRepositoryName + '/' + paramFieldName;
                        return url;
                    }
                }
            },
            messages: {
                doYouWantToRemoveThisRecord: function(){
                    return "Do You want to remove this record?";
                },
                couldNotRemoveEntityFromRepository: function(repositoryName){
                    return "Could not remove entity from " + repositoryName;
                },
                entityHasBeenRemovedFromRepository: function(){
                    return "Record has been removed successfully";
                },
            colors:{
                red     : 'danger',
                green   : 'success'
            }
            },
            init: function(){
                this.attachEntityRemovalEvent(this.selectors.classes.entityRemoveAction);
                this.attachToggleBoolvalEvent();
                this.attachEntityEditModalCallEvent(this.selectors.classes.entityCallEditModalAction);
            },
            /**
             * Will call logic for handling inverting boolval in entity via ajax
             */
            attachToggleBoolvalEvent: function(){
                let _this        = this;
                let $allElements = $("[" + ui.crud.data.entityToggleBoolval + "=true]");

                $allElements.off('click');
                $allElements.on('click', function(event){
                    let $clickedElement = $(event.currentTarget);

                    let repositoryName = $clickedElement.attr(ui.crud.data.entityRepositoryName);
                    let successMessage = $clickedElement.attr(ui.crud.data.entityToggleBoolvalSuccessMessage);
                    let fieldName      = $clickedElement.attr(ui.crud.data.entityFieldName);
                    let entityId       = $clickedElement.attr(ui.crud.data.entityId);

                    Loader.showLoader();

                    $.ajax({
                        url    : _this.methods.toggleBoolval.buildUrl(entityId, repositoryName, fieldName),
                        method : _this.methods.toggleBoolval.method
                    }).always(function(data){
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

                        if( 200 != code ) {
                            ui.crud.bootstrapNotify.showRedNotification(message);
                            return;
                        }

                        if( "undefined" !== typeof successMessage ){
                            ui.crud.bootstrapNotify.showGreenNotification(successMessage);
                        }else{
                            ui.crud.bootstrapNotify.showGreenNotification(message);
                        }

                        ui.ajax.loadModuleContentByUrl(TWIG_REQUEST_URI);

                        if( reloadPage ){
                            if( "" !== reloadMessage ){
                                ui.crud.bootstrapNotify.showBlueNotification(reloadMessage);
                            }
                            location.reload();
                        }
                    });
                });
            },
            /**
             * Removal is based on one click with aproval box
             * @param selector
             * @returns {boolean}
             */
            attachEntityRemovalEvent: function(selector){
                let element = $(selector);
                let _this   = this;

                if( !utils.validations.doElementsExists(element) ){
                    return false;
                }

                let afterRemovalCallback = function(){
                    ui.ajax.loadModuleContentByUrl(TWIG_REQUEST_URI);
                    bootbox.hideAll();
                } ;

                $(element).on('click', function(event) {
                    let clickedElement  = $(this);
                    let entityId        = $(clickedElement).attr('data-entity-id');
                    let repositoryName  = $(clickedElement).attr('data-repository-name'); // consts from Repositories class

                    let $accordionContent     = clickedElement.closest(ui.crud.classes.accordion).find(ui.crud.classes.accordionContent);
                    let isActionForAccordion  = ( 0 !== $accordionContent.length );

                    _this.removeEntityById(entityId, repositoryName, afterRemovalCallback);

                    // used to keep accordion open or closed when clicking on action
                    if( isActionForAccordion ){
                        event.stopPropagation();
                    }

                })
            },
            /**
             * Uses global repositories remove function for all repositories defined there
             * @param entityId
             * @param repositoryName
             * @param afterRemovalCallback
             */
            removeEntityById: function(entityId, repositoryName, afterRemovalCallback){

                let _this = this;
                let url   = this.methods.removeEntity.url.replace(this.methods.removeEntity.params.repositoryName, repositoryName);
                url       = url.replace(this.methods.removeEntity.params.id, entityId);

                let doYouWantToRemoveThisRecordMessage = this.messages.doYouWantToRemoveThisRecord();

                bootbox.confirm({
                    message:  doYouWantToRemoveThisRecordMessage,
                    backdrop: true,
                    callback: function (result) {
                        if (result) {
                            Loader.showLoader();

                            $.ajax({
                                url: url,
                                method: _this.methods.removeEntity.method,
                            }).always((data) => {

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
                                    ui.crud.bootstrapNotify.showRedNotification(message);
                                    return;
                                }else {

                                    if( "undefined" === typeof message ){
                                        message = _this.messages.entityHasBeenRemovedFromRepository();
                                    }

                                    ui.crud.bootstrapNotify.showGreenNotification(message);
                                }

                                if( "function" === typeof afterRemovalCallback ) {
                                    afterRemovalCallback();
                                }

                                if( reloadPage ){
                                    if( "" !== reloadMessage ){
                                        ui.crud.bootstrapNotify.showBlueNotification(reloadMessage);
                                    }
                                    location.reload();
                                }

                            });
                        }
                    }
                });
            },
            /**
             * Editing is based on modal
             * @param selector
             * @returns {boolean}
             */
            attachEntityEditModalCallEvent: function(selector){
                let element = $(selector);
                let _this   = this;

                if( !utils.validations.doElementsExists(element) ){
                    return false;
                }

                $(element).on('click', function() {
                    let clickedElement  = $(this);
                    let entityId        = $(clickedElement).attr('data-entity-id');
                    let repositoryName  = $(clickedElement).attr('data-repository-name'); // consts from Repositories class

                    _this.callModalForEntity(entityId, repositoryName);
                })
            },
            /**
             * Uses the modal building logic for calling box with prefilled data
             * @param entityId
             * @param repositoryName
             */
            callModalForEntity: function(entityId, repositoryName){
                let modalUrl = this.methods.buildEditEntityModalByRepositoryName[repositoryName].url;
                let method   = this.methods.buildEditEntityModalByRepositoryName[repositoryName].method;
                let callback = this.methods.buildEditEntityModalByRepositoryName[repositoryName].callback;


                if( "undefined" === typeof modalUrl ){
                    throw({
                       "message"        : "There is no url defined for editing modal call for given repository",
                       "repositoryName" : repositoryName
                    });
                }

                let requestData = {
                    entityId: entityId
                };

                dialogs.ui.general.buildDialogBody(modalUrl, method, requestData, callback);

            },
            /**
             * Update is done via general update method in repositories
             * @param entityId
             */
            updateEntityById: function(entityId){

                let afterEditCallback = function(){
                    ui.ajax.loadModuleContentByUrl(TWIG_REQUEST_URI);
                };

            }
        },
        /**
         * These are all for datatables
         */
        attachRemovingEventOnTrashIcon: function () {
            let _this        = this;
            let removeButton = $('.remove-record');

            $(removeButton).off('click'); // to prevent double attachement on reinit
            $(removeButton).click(function () {
                let parent_wrapper    = $(this).closest(_this.elements["removed-element-class"]);
                let param_entity_name = $(parent_wrapper).attr('data-type');
                let remove_data       = dataProcessors.entities[param_entity_name].makeRemoveData(parent_wrapper);

                let removal_message = (
                    remove_data.confirm_message !== undefined
                        ? remove_data.confirm_message
                        : _this.messages.default_record_removal_confirmation_message
                );

                bootbox.confirm({
                    message: removal_message,
                    backdrop: true,
                    callback: function (result) {
                        if (result) {
                            Loader.showLoader();
                            $.ajax({
                                url: remove_data.url,
                                method: 'POST',
                                data: remove_data.data
                            }).always( (data) => {

                                Loader.hideLoader();

                                // Refactor start
                                let $twigBodySection = $('.twig-body-section');

                                try{
                                    var code          = data['code'];
                                    var message       = data['message'];
                                    var template      = data['template'];
                                    var reloadPage    = data['reload_page'];
                                    var reloadMessage = data['reload_message'];
                                } catch(Exception){
                                    throw({
                                        "message"   : "Could not handle ajax call",
                                        "data"      : data,
                                        "exception" : Exception
                                    })
                                }

                                if( "undefined" === typeof message ){
                                    message = remove_data.success_message;
                                }

                                if (remove_data.callback_after) {
                                    remove_data.callback();
                                }

                                if( 200 != code ) {
                                    ui.crud.bootstrapNotify.showRedNotification(message);
                                    return;
                                }

                                if( "undefined" !== typeof template ){
                                    $twigBodySection.html(template);
                                    initializer.reinitialize();
                                }else if ( remove_data['is_dataTable'] ) {
                                    let table_id = $(parent_wrapper).closest('tbody').closest('table').attr('id');
                                    _this.removeDataTableTableRow(table_id, parent_wrapper);
                                }else{
                                    _this.removeTableRow(parent_wrapper);
                                }

                                ui.crud.bootstrapNotify.showGreenNotification(message);

                                if( reloadPage ){
                                    if( "" !== reloadMessage ){
                                        ui.crud.bootstrapNotify.showBlueNotification(reloadMessage);
                                    }
                                    location.reload();
                                }
                            });
                        }
                    }
                });

            });
        },
        attachContentEditEventOnEditIcon: function () {
            let _this      = this;
            let editButton = $('.edit-record');

            $(editButton).off('click'); // to prevent double attachement on reinit
            $(editButton).click(function () {
                let closest_parent = this.closest(_this.elements["edited-element-class"]);
                _this.toggleContentEditable(closest_parent);
            });
        },
        attachContentCopyEventOnCopyIcon: function () {
            let allCopyButtons = $('.copy-record');
            let _this = this;

            if ($(allCopyButtons).length > 0) {
                $(allCopyButtons).each((index, button) => {

                    $(button).on('click', (event) => {
                        let clickedElement = $(event.target);
                        let parent_wrapper = $(clickedElement).closest(_this.elements["removed-element-class"]);
                        let param_entity_name = $(parent_wrapper).attr('data-type');
                        let copy_data = dataProcessors.entities[param_entity_name].makeCopyData(parent_wrapper);

                        let temporaryCopyDataInput = $("<input>");
                        $("body").append(temporaryCopyDataInput);
                        Loader.showLoader();
                        $.ajax({
                            url: copy_data.url,
                            method: 'GET',
                        }).always((data) => {
                            Loader.hideLoader();

                            try{
                                var message       = data['message'];
                                var password      = data['password'];
                                var reloadPage    = data['reload_page'];
                                var reloadMessage = data['reload_message'];

                            } catch(Exception){
                                throw({
                                    "message"   : "Could not handle ajax call",
                                    "data"      : data,
                                    "exception" : Exception
                                })
                            }

                            if(
                                    ""          !== message
                                &&  "undefined" !== typeof  message
                            ){
                                ui.crud.bootstrapNotify.showRedNotification(message);
                                return;
                            }

                            if( "undefined" === typeof password ){
                                ui.crud.bootstrapNotify.showRedNotification(copy_data.fail_message);
                                return;
                            }

                            temporaryCopyDataInput.val(password).select();
                            document.execCommand("copy");
                            temporaryCopyDataInput.remove();

                            ui.crud.bootstrapNotify.showGreenNotification(copy_data.success_message);

                            if( reloadPage ){
                                if( "" !== reloadMessage ){
                                    ui.crud.bootstrapNotify.showBlueNotification(reloadMessage);
                                }
                                location.reload();
                            }
                        });
                    })
                });
            }
        },
        /**
         * @description attaches logic for saving record edited inline (tables)
         */
        attachContentSaveEventOnSaveIcon: function () {
            let _this      = this;
            let saveButton = $('.save-record');

            $(saveButton).off('click'); // to prevent double attachement on reinit
            $(saveButton).on('click', function (event) {
                event.preventDefault();

                let closestParent = this.closest(_this.elements["saved-element-class"]);
                _this.ajaxUpdateDatabaseRecord(closestParent);
            });
        },
        /**
         * @description will attach calling furcan fontawesome picker on certain gui elements (actions)
         */
        attachFontawesomePickEventForFontawesomeAction: function () {
            let _this = this;

            $('.' + this.classes["fontawesome-picker-input"]).each((index, input) => {
                $(input).removeClass(this.classes["fontawesome-picker-input"]);
                $(input).addClass(this.classes["fontawesome-picker-input"] + index);
            });

            $('.' + this.classes["fontawesome-picker-preview"]).each((index, input) => {
                $(input).removeClass(this.classes["fontawesome-picker-preview"]);
                $(input).addClass(this.classes["fontawesome-picker-preview"] + index);
            });

            $('.action-fontawesome').each((index, icon) => {

                $(icon).addClass('fontawesome-picker' + index);
                $(icon).attr('data-iconpicker-preview', '.' + _this.classes["fontawesome-picker-preview"] + index);
                $(icon).attr('data-iconpicker-input', '.' + _this.classes["fontawesome-picker-input"] + index);

                IconPicker.Init({
                    jsonUrl: '/assets_/static-libs/furcan-iconpicker/1.5/iconpicker-1.5.0.json',
                    searchPlaceholder: 'Search Icon',
                    showAllButton: 'Show All',
                    cancelButton: 'Cancel',
                });
                IconPicker.Run('.' + _this.classes["fontawesome-picker"] + index);
            })
        },
        /**
         * @description This is general method used for handling forms which are associated with entities or single-target forms
         *              single-target form can be any custom form not associated with entities directly
         * @info        Method is also used in twig as string which is then parsed and executed
         * @param reinitializePageLogicAfterAjaxCall {bool}
         */
        attachRecordAddViaAjaxOnSubmit: function (reinitializePageLogicAfterAjaxCall = true) {
            let form  = $('.add-record-form form');

            $(form).off("submit");
            $(form).submit(function (event) {
                event.preventDefault();

                let form                 = $(event.target);
                let submitButton         = $(form).find('button[type="submit"]');
                let callbackParamsJson   = $(submitButton).attr('data-params');
                let dataCallbackParams   = ( "undefined" != typeof callbackParamsJson ? JSON.parse(callbackParamsJson) : null );

                /**
                 * @description with this there is a possibility to load different template than the one from url used in ajax
                 *              normally the same page should be reloaded but this is helpful for widgets when we want to call
                 *              action from one page but load template of other
                 */
                let dataTemplateUrl                = $(submitButton).attr('data-template-url');
                let doReloadTemplateViaTemplateUrl = ("undefined" !== typeof dataTemplateUrl);

                let formSubmissionType                = form.attr('method');
                let processedEntityName               = form.attr('data-entity');
                let singleProcessedFormDefinitionName = form.attr('data-form-target');

                let ajaxRequestDataBag  = null;
                let isEntityBasedForm   = ("undefined" != typeof processedEntityName);

                /**
                 * @description build data bag for request using either entity based form or single-target one
                 *              and build callbacks used after receiving backend response
                 */
                try{
                    if(isEntityBasedForm ){
                        ajaxRequestDataBag = dataProcessors.entities[processedEntityName].makeCreateData();
                    }else{
                        ajaxRequestDataBag = dataProcessors.singleTargets[singleProcessedFormDefinitionName].makeCreateData();
                    }
                }catch(Exception){
                    throw({
                        "message"   : "Failed on getting data bag for creating record via form submit (ajax call)",
                        "exception" : Exception
                    })
                }

                if( null === ajaxRequestDataBag ){
                    ui.crud.bootstrapNotify.showRedNotification("Databag for creating record via form submit (ajax) is null.");
                    return;
                }

                Loader.showLoader();
                $.ajax({
                    url: ajaxRequestDataBag.url,
                    type: formSubmissionType,
                    data: form.serialize(),
                }).always((data) => {
                    let twigBodySection = $('.twig-body-section');
                    let callback        = () => {};

                    if (ajaxRequestDataBag.callback_before) {
                        ajaxRequestDataBag.callback(dataCallbackParams);
                    }

                    try{
                        var ajaxResponseDto = AjaxResponseDto.fromArray(data);
                    } catch(Exception){
                        throw({
                            "message"   : "Could not handle ajax call",
                            "data"      : data,
                            "exception" : Exception
                        })
                    }

                    if( !ajaxResponseDto.isSuccessCode() ){

                        if( ajaxResponseDto.hasInvalidFields() && !ajaxResponseDto.isInternalServerErrorCode() ){
                            let formValidator = new FormsValidator(ajaxResponseDto.validatedFormPrefix, ajaxResponseDto.invalidFormFields);
                            formValidator.handleInvalidFields();
                            Loader.hideLoader();
                            return;
                        }

                        Loader.hideLoader();
                        ui.crud.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                        return;
                    }

                    /**
                     * @info handle the way of reloading template
                     */
                    if( doReloadTemplateViaTemplateUrl ){
                        if(ajaxRequestDataBag.callback_for_data_template_url){
                            callback = () => {
                                ajaxRequestDataBag.callback(dataCallbackParams)
                            };
                        }

                        ui.ajax.loadModuleContentByUrl(dataTemplateUrl, callback, true);
                    }else if(ajaxResponseDto.isTemplateSet()){
                        twigBodySection.html(ajaxResponseDto.template);
                    }

                    if (ajaxRequestDataBag.callback_after) {
                        ajaxRequestDataBag.callback(dataCallbackParams);
                    }

                    /**
                     * @info handle logic reinitialization
                     */
                    if ( !reinitializePageLogicAfterAjaxCall ) {
                        Loader.hideLoader();

                        if( !ajaxResponseDto.isSuccessCode() ){
                            ui.crud.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                        }else{
                            ui.crud.bootstrapNotify.showGreenNotification(ajaxResponseDto.message);
                        }

                        return;
                    }

                    try{
                        initializer.reinitialize();
                        Loader.hideLoader();

                        ui.crud.bootstrapNotify.showGreenNotification( ajaxResponseDto.isMessageSet() ? ajaxResponseDto.message : ajaxRequestDataBag.success_message );

                        if( ajaxResponseDto.reloadPage ){
                            if( ajaxResponseDto.isReloadMessageSet() ){
                                ui.crud.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                            }
                            location.reload();
                        }

                        bootbox.hideAll();

                    }catch(Exception){
                        Loader.hideLoader();
                        ui.crud.bootstrapNotify.showRedNotification("Failed reinitializing logic");
                        throw Exception;
                    }

                });
            });
        },
        /**
         * @description Handles performing update for single-target forms (via ajax)
         */
        attachRecordUpdateViaAjaxOnSubmitForSingleForm: function () {
            $('.update-record-form form').submit(function (event) {

                let $form      = $(event.target);
                let formTarget = $form.attr('data-form-target');
                let updateData = dataProcessors.singleTargets[formTarget].makeUpdateData($form);

                Loader.showLoader();
                $.ajax({
                    url: updateData.url,
                    type: 'POST',
                    data: updateData.data, //In this case the data from target_action is being sent not form directly
                }).always((data) => {

                    Loader.hideLoader();

                    try{
                        var ajaxResponseDto = AjaxResponseDto.fromArray(data);
                    } catch(Exception){
                        throw({
                            "message"   : "Could not handle ajax call",
                            "data"      : data,
                            "exception" : Exception
                        })
                    }

                    if( ajaxResponseDto.isSuccessCode() ){
                        ui.crud.bootstrapNotify.showGreenNotification(ajaxResponseDto.message);
                    }else{
                        ui.crud.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                        return;
                    }

                    $('.twig-body-section').html(ajaxResponseDto.template);
                    initializer.reinitialize();

                    if( ajaxResponseDto.reloadPage ){
                        if( ajaxResponseDto.isReloadMessageSet() ){
                            ui.crud.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                        }
                        location.reload();
                    }
                });

                event.preventDefault();
            });
        },
        /**
         * @description Toggles content editable of element - mostly table
         * Todo: should be refactored
         * @param baseElement
         */
        toggleContentEditable: function (baseElement) {
            let isContentEditable = DomAttributes.isContentEditable(baseElement, 'td');
            let paramEntityName   = $(baseElement).attr('data-type');

            if (!isContentEditable) {
                DomAttributes.contentEditable(baseElement, DomAttributes.actions.set,  'td', 'input, select, button, img');
                $(baseElement).addClass(this.classes["table-active"]);
                this.toggleActionIconsVisibility(baseElement, null, isContentEditable);
                this.toggleDisabledClassForTableRow(baseElement);

                ui.crud.bootstrapNotify.notify(this.messages.entityEditStart(dataProcessors.entities[paramEntityName].entity_name), 'warning');
                return;
            }

            this.toggleActionIconsVisibility(baseElement, null, isContentEditable);
            this.toggleDisabledClassForTableRow(baseElement);

            DomAttributes.contentEditable(baseElement, DomAttributes.actions.unset,'td', 'input, select, button, img');
            $(baseElement).removeClass(this.classes["table-active"]);
            ui.crud.bootstrapNotify.notify(this.messages.entityEditEnd(dataProcessors.entities[paramEntityName].entity_name), 'success');
        },
        /**
         * @description Shows/hides actions icons (for example in tables edit/create/delete)
         * @param $element
         * @param toggleContentEditable
         * @param isContentEditable
         */
        toggleActionIconsVisibility: function ($element, toggleContentEditable = null, isContentEditable) {
            let saveIcon        = $($element).find('.save-record');
            let fontawesomeIcon = $($element).find('.action-fontawesome');

            let actionIcons = [saveIcon, fontawesomeIcon];

            $(actionIcons).each((index, icon) => {
                if ($(icon).length !== 0 && $(icon).hasClass(this.classes["hidden"]) && !isContentEditable) {
                    $(icon).removeClass(this.classes["hidden"]);
                    return;
                }

                $(icon).addClass(this.classes["hidden"]);
            });

            if (toggleContentEditable === true) {
                this.toggleContentEditable($element);
            }
        },
        /**
         * @description Toggles css `disabled` class for certain elements in table
         *              like for example after clicking on row edit certain data should be undeditable/interractable
         * @param tr_parent_element
         */
        toggleDisabledClassForTableRow: function (tr_parent_element) {
            let color_pickers   = $(tr_parent_element).find('.color-picker');
            let toggle_buttons  = $(tr_parent_element).find('.toggle-button');
            let option_pickers  = $(tr_parent_element).find('.option-picker');
            let date_pickers    = $(tr_parent_element).find('.date-picker');
            let checkbox        = $(tr_parent_element).find('.checkbox-disabled');
            let selectize       = $(tr_parent_element).find('.selectize-control');
            let dataPreview     = $(tr_parent_element).find('.data-preview');
            let elements_to_toggle = [color_pickers, option_pickers, date_pickers, checkbox, selectize, dataPreview, toggle_buttons];
            let _this = this;

            $(elements_to_toggle).each((index, element_type) => {

                if ($(element_type).length !== 0) {
                    $(element_type).each((index, element) => {

                        if ($(element).hasClass(_this.classes.disabled)) {
                            $(element).removeClass(_this.classes.disabled);
                        } else {
                            $(element).addClass(_this.classes.disabled);
                        }

                    });
                }

            })

        },
        /**
         * @description Update entry in DB
         * @param baseElement
         */
        ajaxUpdateDatabaseRecord: function (baseElement) {
            let paramEntityName = $(baseElement).attr('data-type');
            let updateData = dataProcessors.entities[paramEntityName].makeUpdateData(baseElement);
            let _this = this;

            if (updateData.edit !== undefined && updateData.edit !== null && updateData.edit.invokeAlert === true) {

                bootbox.confirm({
                    message: updateData.edit.alertMessage,
                    backdrop: true,
                    callback: function (result) {
                        if (result) {
                            _this.makeAjaxRecordUpdateCall(updateData);
                            _this.toggleActionIconsVisibility(baseElement, true);
                        }
                    }
                });

            } else {
                _this.makeAjaxRecordUpdateCall(updateData);
            }

        },
        /**
         * @description Updates record (mostly entity) via ajax call
         *              Works with Entities file to build collect data from front and send it for update on back
         * @param updateData
         */
        makeAjaxRecordUpdateCall: function (updateData) {
            Loader.showLoader();
            $.ajax({
                url: updateData.url,
                method: 'POST',
                data: updateData.data
            }).fail(() => {
                ui.crud.bootstrapNotify.notify(updateData.fail_message, 'danger')
            }).always((data) => {

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

                let messageType = "success";

                if(
                        ( "undefined" !== typeof code )
                    &&  ( 200 != code )
                ){
                    messageType = "danger";
                }

                if( "undefined" === typeof message ){
                    ui.crud.bootstrapNotify.notify(updateData.success_message, messageType);
                } else {
                    ui.crud.bootstrapNotify.notify(message, messageType);
                }



                if (updateData.callback_after) {
                    updateData.callback();
                }

                ui.ajax.loadModuleContentByUrl(TWIG_REQUEST_URI);

                if( reloadPage ){
                    if( "" !== reloadMessage ){
                        ui.crud.bootstrapNotify.showBlueNotification(reloadMessage);
                    }
                    location.reload();
                }
            });
        },
        /**
         * @description Removes table row on front (via datatable), must be also handled on back otherwise will reapper on refresh
         * @param table_id
         * @param tr_parent_element
         */
        removeDataTableTableRow: function (table_id, tr_parent_element) {
            datatable.destroy(table_id);
            tr_parent_element.remove();
            datatable.reinit(table_id)
        },
        /**
         * @description Just removes row from table, wil be shown on refresh if entry is not also removed/handled on back
         * @param tr_parent_element
         */
        removeTableRow: function (tr_parent_element) {
            tr_parent_element.remove();
        },
        /**
         * @description Will attach logic to element so that when pressed turns the target element into tinymce
         * @param preventFurtherEventPropagation {boolean}
         */
        attachEventOnButtonToTransformTargetSelectorToTinyMceInstance: function(preventFurtherEventPropagation = true){
            let $allButtons = $('.transform-to-tiny-mce');

            $.each($allButtons, function(index, button){
                let $button = $(button);

                $button.on('click', function(event){

                    let tinyMceSelector         = $button.attr(ui.crud.data.tinymceElementSelector);
                    let tinyMceInstanceSelector = $button.attr(ui.crud.data.tinymceElementInstanceSelector);
                    let tinyMceInstance         = tinymce.get(tinyMceInstanceSelector);

                    // prevent reinitializing and make it removable when closing edit
                    if( tinyMceInstance === null ){
                        let config      = tinymce.custom.config;
                        config.selector = tinyMceSelector;
                        tinymce.init(config);
                    }else{
                        tinymce.remove(tinyMceSelector);
                        prismjs.highlightCode();
                    }

                    // used for example to suppress propagating accordion open/close
                    if( preventFurtherEventPropagation ){
                        event.stopPropagation();
                    }
                });
            });
        },
        /**
         * @description Attaches the logic after clicking on button for editing with tinymce
         */
        attachEventOnButtonForEditingViaTinyMce: function(){
            let $allActionButtons = $('.edit-record-with-tiny-mce');

            $.each($allActionButtons, function(index, button){
                let $button    = $(button);
                let $accordion = $button.closest(ui.crud.classes.accordion);

                $button.off('click'); // prevent stacking - also keep in mind that might remove other events attached before
                $button.on('click', function(event){
                    ui.crud.toggleActionIconsVisibility($accordion);
                });
            });

        },
    };

}());

