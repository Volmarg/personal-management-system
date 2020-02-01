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
            'entity-remove-action'      : '.entity-remove-action'
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
                this.attachEntityEditModalCallEvent(this.selectors.classes.entityCallEditModalAction);
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
                } ;

                $(element).on('click', function() {
                    let clickedElement  = $(this);
                    let entityId        = $(clickedElement).attr('data-entity-id');
                    let repositoryName  = $(clickedElement).attr('data-repository-name'); // consts from Repositories class

                    _this.removeEntityById(entityId, repositoryName, afterRemovalCallback);

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
            $(saveButton).on('click', function () {
                let closest_parent = this.closest(_this.elements["saved-element-class"]);
                _this.ajaxUpdateDatabaseRecord(closest_parent);
            });
        },
        attachFontawesomePickEventOnEmojiIcon: function () {
            let _this = this;

            $('.' + this.classes["fontawesome-picker-input"]).each((index, input) => {
                $(input).removeClass(this.classes["fontawesome-picker-input"]);
                $(input).addClass(this.classes["fontawesome-picker-input"] + index);
            });

            $('.action-fontawesome').each((index, icon) => {

                if ($('.' + _this.classes["fontawesome-picker-preview"]).length === 0) {
                    let fontawesome_preview_div = $('<div></div>');
                    $(fontawesome_preview_div).addClass(_this.classes["fontawesome-picker-preview"]).addClass(_this.classes.hidden);
                    $('body').append(fontawesome_preview_div);
                }

                $(icon).addClass('fontawesome-picker' + index);
                $(icon).attr('data-iconpicker-preview', '.' + _this.classes["fontawesome-picker-preview"]);
                $(icon).attr('data-iconpicker-input', '.' + _this.classes["fontawesome-picker-input"] + index);

                IconPicker.Init({
                    jsonUrl: '/assets_/static-libs/furcan-iconpicker/iconpicker-1.0.0.json',
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
        toggleContentEditable: function (tr_closest_parent) {
            let is_content_editable = utils.domAttributes.isContentEditable(tr_closest_parent, 'td');
            let param_entity_name   = $(tr_closest_parent).attr('data-type');

            if (!is_content_editable) {
                utils.domAttributes.contentEditable(tr_closest_parent, utils.domAttributes.actions.set,  'td', 'input, select, button, img');
                $(tr_closest_parent).addClass(this.classes["table-active"]);
                this.toggleActionIconsVisibillity(tr_closest_parent, null, is_content_editable);
                this.toggleDisabledClassForTableRow(tr_closest_parent);

                bootstrap_notifications.notify(this.messages.entityEditStart(dataProcessors.entities[param_entity_name].entity_name), 'warning');
                return;
            }

            this.toggleActionIconsVisibillity(tr_closest_parent, null, is_content_editable);
            this.toggleDisabledClassForTableRow(tr_closest_parent);

            utils.domAttributes.contentEditable(tr_closest_parent, utils.domAttributes.actions.unset,'td', 'input, select, button, img');
            $(tr_closest_parent).removeClass(this.classes["table-active"]);
            bootstrap_notifications.notify(this.messages.entityEditEnd(dataProcessors.entities[param_entity_name].entity_name), 'success');
        },
        toggleActionIconsVisibillity: function (tr_parent_element, toggle_content_editable = null, is_content_editable) {
            let save_icon = $(tr_parent_element).find('.save-record');
            let fontawesome_icon = $(tr_parent_element).find('.action-fontawesome');

            let action_icons = [save_icon, fontawesome_icon];

            $(action_icons).each((index, icon) => {
                if ($(icon).length !== 0 && $(icon).hasClass(this.classes["hidden"]) && !is_content_editable) {
                    $(icon).removeClass(this.classes["hidden"]);
                    return;
                }

                $(icon).addClass(this.classes["hidden"]);
            });

            if (toggle_content_editable === true) {
                this.toggleContentEditable(tr_parent_element);
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
        ajaxUpdateDatabaseRecord: function (tr_parent_element) {
            let param_entity_name = $(tr_parent_element).attr('data-type');
            let update_data = dataProcessors.entities[param_entity_name].makeUpdateData(tr_parent_element);
            let _this = this;

            if (update_data.edit !== undefined && update_data.edit !== null && update_data.edit.invokeAlert === true) {

                bootbox.confirm({
                    message: update_data.edit.alertMessage,
                    backdrop: true,
                    callback: function (result) {
                        if (result) {
                            _this.makeAjaxRecordUpdateCall(update_data);
                            _this.toggleActionIconsVisibillity(tr_parent_element, true);
                        }
                    }
                });

            } else {
                _this.makeAjaxRecordUpdateCall(update_data);
            }

        },
        makeAjaxRecordUpdateCall: function (update_data) {
            ui.widgets.loader.showLoader();
            $.ajax({
                url: update_data.url,
                method: 'POST',
                data: update_data.data
            }).fail(() => {
                bootstrap_notifications.notify(update_data.fail_message, 'danger')
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
                    bootstrap_notifications.notify(update_data.success_message, messageType);
                } else {
                    bootstrap_notifications.notify(message, messageType);
                }



                if (update_data.callback_after) {
                    update_data.callback();
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
        }
    };

}());

