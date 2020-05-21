import tinymce from "tinymce";

var bootbox = require('bootbox');
import * as selectize from "selectize";

/**
 * If possible - avoid moving logic from this script - some methods are called as plain string in twig tpls
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
        init: function () {
            this.attachRemovingEventOnTrashIcon();
            this.attachContentEditEventOnEditIcon();
            this.attachContentSaveEventOnSaveIcon();
            this.attachContentCopyEventOnCopyIcon();
            this.attachFontawesomePickEventOnEmojiIcon();
            this.attachRecordAddViaAjaxOnSubmit();
            this.attachRecordUpdateOrAddViaAjaxOnSubmitForSingleForm();


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

                    ui.widgets.loader.showLoader();

                    $.ajax({
                        url    : _this.methods.toggleBoolval.buildUrl(entityId, repositoryName, fieldName),
                        method : _this.methods.toggleBoolval.method
                    }).always(function(data){
                        ui.widgets.loader.hideLoader();

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

                        if( 200 != code ) {
                            bootstrap_notifications.showRedNotification(message);
                            return;
                        }

                        if( "undefined" !== typeof successMessage ){
                            bootstrap_notifications.showGreenNotification(successMessage);
                        }else{
                            bootstrap_notifications.showGreenNotification(message);
                        }

                        ui.ajax.loadModuleContentByUrl(TWIG_REQUEST_URI);
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
                            ui.widgets.loader.showLoader();

                            $.ajax({
                                url: url,
                                method: _this.methods.removeEntity.method,
                            }).always((data) => {

                                ui.widgets.loader.hideLoader();

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
                                        message = _this.messages.entityHasBeenRemovedFromRepository();
                                    }

                                    bootstrap_notifications.showGreenNotification(message);
                                }

                                if( "function" === typeof afterRemovalCallback ) {
                                    afterRemovalCallback();
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
                            ui.widgets.loader.showLoader();
                            $.ajax({
                                url: remove_data.url,
                                method: 'POST',
                                data: remove_data.data
                            }).always( (data) => {

                                ui.widgets.loader.hideLoader();

                                // Refactor start
                                let $twigBodySection = $('.twig-body-section');

                                try{
                                    var code     = data['code'];
                                    var message  = data['message'];
                                    var template = data['template'];
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
                                    bootstrap_notifications.showRedNotification(message);
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

                                bootstrap_notifications.showGreenNotification(message);

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
                        ui.widgets.loader.showLoader();
                        $.ajax({
                            url: copy_data.url,
                            method: 'GET',
                        }).always((data) => {
                            ui.widgets.loader.hideLoader();

                            try{
                                var message  = data['message'];
                                var password = data['password'];
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
                                bootstrap_notifications.showRedNotification(message);
                                return;
                            }

                            if( "undefined" === typeof password ){
                                bootstrap_notifications.showRedNotification(copy_data.fail_message);
                                return;
                            }

                            temporaryCopyDataInput.val(password).select();
                            document.execCommand("copy");
                            temporaryCopyDataInput.remove();

                            bootstrap_notifications.showGreenNotification(copy_data.success_message);

                        });

                    })

                });
            }

        },
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
        attachFontawesomePickEventOnEmojiIcon: function () {
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

                // todo: remove - left only case of furcan preview issue - old fix
                // if ($('.' + _this.classes["fontawesome-picker-preview"]).length === 0) {
                //     let fontawesome_preview_div = $('<div></div>');
                //     $(fontawesome_preview_div).addClass(_this.classes["fontawesome-picker-preview"]).addClass(_this.classes.hidden);
                //     $('body').append(fontawesome_preview_div);
                // }

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
         * @info this function might require refactor as I'm passing "template" but sometimes there might be code/message
         *  with this the backend should send data['template'] etc,
         *      @see this.attachRecordUpdateOrAddViaAjaxOnSubmitForSingleForm()
         * @param reloadPage
         */
        attachRecordAddViaAjaxOnSubmit: function (reloadPage = true) {
            let form  = $('.add-record-form form');

            $(form).off("submit");
            $(form).submit(function (event) {
                let form                 = $(event.target);
                let submitButton         = $(form).find('button[type="submit"]');
                let callbackParamsJson   = $(submitButton).attr('data-params');
                let dataCallbackParams   = ( "undefined" != typeof callbackParamsJson ? JSON.parse(callbackParamsJson) : null );

                // with this there is a possibility to load different template than the one from url used in ajax
                // normally the same page should be reloaded but this is helpful for widgets when we want to call
                // action from one page but load template of other
                let dataTemplateUrl = $(submitButton).attr('data-template-url');

                let method      = form.attr('method');
                let entity_name = form.attr('data-entity');
                let create_data = null;

                if( "undefined" != typeof entity_name){
                    create_data = dataProcessors.entities[entity_name].makeCreateData();
                }else{
                    let formTarget  = form.attr('data-form-target');
                    create_data     = dataProcessors.singleTargets[formTarget].makeCreateData();
                }

                ui.widgets.loader.showLoader();
                $.ajax({
                    url: create_data.url,
                    type: method,
                    data: form.serialize(),
                }).always((data) => {

                    if (create_data.callback_before) {
                        create_data.callback(dataCallbackParams);
                    }

                    try{
                        var code     = data['code'];
                        var message  = data['message'];
                        var template = data['template'];
                    } catch(Exception){
                        throw({
                            "message"   : "Could not handle ajax call",
                            "data"      : data,
                            "exception" : Exception
                        })
                    }

                    /**
                     * This reloadPage must stay like that,
                     * Somewhere in code I call this function but i pass it as string so it's not getting detected
                     */
                    if (!reloadPage) {
                        ui.widgets.loader.hideLoader();

                        if( 200 != code ){
                            bootstrap_notifications.showRedNotification(message);
                        }else{
                            bootstrap_notifications.showGreenNotification(message);
                        }

                        return;
                    }

                    if( 200 != code ){
                        ui.widgets.loader.hideLoader();
                        bootstrap_notifications.showRedNotification(message);
                        return;
                    }

                    if( "undefined" !== typeof dataTemplateUrl ){
                        let callback = () => {};
                        if(create_data.callback_for_data_template_url){
                            callback = () => {
                                create_data.callback(dataCallbackParams)
                            };
                        }

                        ui.ajax.loadModuleContentByUrl(dataTemplateUrl, callback, true);
                    }else{
                        let twigBodySection = $('.twig-body-section');
                        if( "undefined" !== template ){
                            twigBodySection.html(template);
                        }
                    }

                    if(
                            "undefined" === typeof message
                        ||  ""          === message
                    ){
                        message = create_data.success_message;
                    }

                    if (create_data.callback_after) {
                        create_data.callback(dataCallbackParams);
                    }

                    initializer.reinitialize();

                    ui.widgets.loader.hideLoader();

                    bootstrap_notifications.showGreenNotification(message);
                });

                event.preventDefault();
            });
        },
        attachRecordUpdateOrAddViaAjaxOnSubmitForSingleForm: function () {
            $('.update-record-form form').submit(function (event) {
                let form = $(event.target);
                let formTarget = form.attr('data-form-target');
                let updateData = dataProcessors.singleTargets[formTarget].makeUpdateData(form);
                ui.widgets.loader.showLoader();
                $.ajax({
                    url: updateData.url,
                    type: 'POST',
                    data: updateData.data, //In this case the data from target_action is being sent not form directly
                }).always((data) => {

                    ui.widgets.loader.hideLoader();

                    try{
                        var code     = data['code'];
                        var message  = data['message'];
                        var template = data['template'];
                    } catch(Exception){
                        throw({
                            "message"   : "Could not handle ajax call",
                            "data"      : data,
                            "exception" : Exception
                        })
                    }

                    if( 200 === code ){
                        bootstrap_notifications.showGreenNotification(message);
                    }else{
                        bootstrap_notifications.showRedNotification(message);
                        return;
                    }

                    $('.twig-body-section').html(template);
                    initializer.reinitialize();

                });

                event.preventDefault();
            });
        },
        toggleContentEditable: function (baseElement) {
            let isContentEditable = utils.domAttributes.isContentEditable(baseElement, 'td');
            let paramEntityName   = $(baseElement).attr('data-type');

            if (!isContentEditable) {
                utils.domAttributes.contentEditable(baseElement, utils.domAttributes.actions.set,  'td', 'input, select, button, img');
                $(baseElement).addClass(this.classes["table-active"]);
                this.toggleActionIconsVisibillity(baseElement, null, isContentEditable);
                this.toggleDisabledClassForTableRow(baseElement);

                bootstrap_notifications.notify(this.messages.entityEditStart(dataProcessors.entities[paramEntityName].entity_name), 'warning');
                return;
            }

            this.toggleActionIconsVisibillity(baseElement, null, isContentEditable);
            this.toggleDisabledClassForTableRow(baseElement);

            utils.domAttributes.contentEditable(baseElement, utils.domAttributes.actions.unset,'td', 'input, select, button, img');
            $(baseElement).removeClass(this.classes["table-active"]);
            bootstrap_notifications.notify(this.messages.entityEditEnd(dataProcessors.entities[paramEntityName].entity_name), 'success');
        },
        toggleActionIconsVisibillity: function ($element, toggleContentEditable = null, isContentEditable) {
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
                            _this.toggleActionIconsVisibillity(baseElement, true);
                        }
                    }
                });

            } else {
                _this.makeAjaxRecordUpdateCall(updateData);
            }

        },
        makeAjaxRecordUpdateCall: function (updateData) {
            ui.widgets.loader.showLoader();
            $.ajax({
                url: updateData.url,
                method: 'POST',
                data: updateData.data
            }).fail(() => {
                bootstrap_notifications.notify(updateData.fail_message, 'danger')
            }).always((data) => {

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

                let messageType = "success";

                if(
                        ( "undefined" !== typeof code )
                    &&  ( 200 != code )
                ){
                    messageType = "danger";
                }

                if( "undefined" === typeof message ){
                    bootstrap_notifications.notify(updateData.success_message, messageType);
                } else {
                    bootstrap_notifications.notify(message, messageType);
                }



                if (updateData.callback_after) {
                    updateData.callback();
                }

                ui.ajax.loadModuleContentByUrl(TWIG_REQUEST_URI);
            });
        },
        removeDataTableTableRow: function (table_id, tr_parent_element) {
            datatable.destroy(table_id);
            tr_parent_element.remove();
            datatable.reinit(table_id)
        },
        removeTableRow: function (tr_parent_element) {
            tr_parent_element.remove();
        },
        /**
         * Will attach logic to element so that when pressed turns the target element into tinymce
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
         * Attaches the logic after clicking on button for editing with tinymce
         */
        attachEventOnButtonForEditingViaTinyMce: function(){
            let $allActionButtons = $('.edit-record-with-tiny-mce');

            $.each($allActionButtons, function(index, button){
                let $button    = $(button);
                let $accordion = $button.closest(ui.crud.classes.accordion);

                $button.off('click'); // prevent stacking - also keep in mind that might remove other events attached before
                $button.on('click', function(event){
                    ui.crud.toggleActionIconsVisibillity($accordion);
                });
            });

        },
    };

}());

