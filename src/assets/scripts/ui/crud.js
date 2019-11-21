var bootbox = require('bootbox');
import * as selectize from "selectize";

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

                let message = this.messages.doYouWantToRemoveThisRecord();

                bootbox.confirm({
                    message:  message,
                    backdrop: true,
                    callback: function (result) {
                        if (result) {
                            ui.widgets.loader.toggleLoader();

                            $.ajax({
                                url: url,
                                method: _this.methods.removeEntity.method,
                            }).fail((data) => {
                                let message = _this.messages.couldNotRemoveEntityFromRepository(repositoryName);
                                let type    = _this.messages.colors.red;
                                bootstrap_notifications.notify(message, type)
                            }).done((data) => {
                                if( "function" === typeof afterRemovalCallback ){
                                    afterRemovalCallback();
                                }

                                let message = _this.messages.entityHasBeenRemovedFromRepository();
                                let type    = _this.messages.colors.green;
                                bootstrap_notifications.notify(message, type)
                            }).always((data) => {
                                ui.widgets.loader.toggleLoader();
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
         * Todo: make section for them
         */
        attachRemovingEventOnTrashIcon: function () {
            let _this = this;
            $('.fa-trash').click(function () { //todo: change selector for action...something
                let parent_wrapper    = $(this).closest(_this.elements["removed-element-class"]);
                let param_entity_name = $(parent_wrapper).attr('data-type');
                let remove_data       = _this.entity_actions[param_entity_name].makeRemoveData(parent_wrapper);

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
                            ui.widgets.loader.toggleLoader();
                            $.ajax({
                                url: remove_data.url,
                                method: 'POST',
                                data: remove_data.data,
                                success: (template) => {
                                    bootstrap_notifications.notify(remove_data.success_message, 'success');
                                    let table_id = $(parent_wrapper).closest('tbody').closest('table').attr('id');
                                    if (remove_data['is_dataTable']) {
                                        _this.removeDataTableTableRow(table_id, parent_wrapper);
                                        return;
                                    }
                                    _this.removeTableRow(parent_wrapper);

                                    $('.twig-body-section').html(template);
                                    initializer.reinitialize();
                                },
                            }).fail((data) => {

                                let message  = remove_data.fail_message;
                                let response = data.responseJSON;

                                if(
                                        "object" === typeof response
                                    &&  remove_data.use_ajax_fail_message
                                    &&  "undefined" !== typeof response.message
                                )
                                {
                                    message = response.message;
                                }

                                bootstrap_notifications.notify(message, 'danger')
                            }).always(() => {
                                ui.widgets.loader.toggleLoader();
                            });
                        }
                    }
                });

            });
        },
        attachContentEditEventOnEditIcon: function () {
            let _this = this;
            $('.fa-edit').click(function () { //todo: change selector for action...something
                let closest_parent = this.closest(_this.elements["edited-element-class"]);
                _this.toggleContentEditable(closest_parent);
            });
        },
        attachContentCopyEventOnCopyIcon: function () {
            let allCopyButtons = $('.fa-copy'); //todo: change selector for action...something
            let _this = this;

            if ($(allCopyButtons).length > 0) {
                $(allCopyButtons).each((index, button) => {

                    $(button).on('click', (event) => {
                        let clickedElement = $(event.target);
                        let parent_wrapper = $(clickedElement).closest(_this.elements["removed-element-class"]);
                        let param_entity_name = $(parent_wrapper).attr('data-type');
                        let copy_data = _this.entity_actions[param_entity_name].makeCopyData(parent_wrapper);

                        let temporaryCopyDataInput = $("<input>");
                        $("body").append(temporaryCopyDataInput);
                        /* Or use this to get directly content by html attributes
                            let selectorOfTargetElement = $(clickedElement).attr('data-copy-from-selector');
                            let targetElement = $(selectorOfTargetElement);
                         */
                        ui.widgets.loader.toggleLoader();
                        $.ajax({
                            url: copy_data.url,
                            method: 'GET',
                            success: (data) => {
                                temporaryCopyDataInput.val(data).select();
                                document.execCommand("copy");
                                temporaryCopyDataInput.remove();

                                bootstrap_notifications.notify(copy_data.success_message, 'success')
                            },
                        }).fail(() => {
                            bootstrap_notifications.notify(update_data.fail_message, 'danger')
                        }).always(() => {
                            ui.widgets.loader.toggleLoader();
                        });

                    })

                });
            }

        },
        attachContentSaveEventOnSaveIcon: function () {
            let _this      = this;
            let saveButton = $('.fa-save');

            $(saveButton).off('click'); // to prevent double attachement on reinit
            $(saveButton).on('click', function () { //todo: change selector for action...something
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

            $('.fa-smile').each((index, icon) => {

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
            let _this = this;
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
                let dataTemplateUrl      = $(submitButton).attr('data-template-url');
                let dataMethod           = $(submitButton).attr('data-template-method');

                let method      = form.attr('method');
                let entity_name = form.attr('data-entity');
                let create_data = null;

                if( "undefined" != typeof entity_name){
                    create_data = _this.entity_actions[entity_name].makeCreateData();
                }else{
                    let formTarget  = form.attr('data-form-target');
                    create_data     = _this.form_target_actions[formTarget].makeCreateData();
                }

                ui.widgets.loader.showLoader();
                $.ajax({
                    url: create_data.url,
                    type: method,
                    data: form.serialize(),
                }).done((template) => {

                    if (create_data.callback_before) {
                        create_data.callback(dataCallbackParams);
                    }

                    /**
                     * This reloadPage must stay like that,
                     * Somewhere in code I call this function but i pass it as string so it's not getting detected
                     */

                    if (!reloadPage) {
                        return;
                    }

                    if( "undefined" !== typeof dataTemplateUrl ){

                        $.ajax({
                            url: dataTemplateUrl,
                            type: dataMethod,
                        }).always(() => {
                            ui.widgets.loader.hideLoader();
                        }).fail((data) => {
                            bootstrap_notifications.notify(data.responseText, 'danger');
                        }).done((template) => {
                            $('.twig-body-section').html(template);

                            if(create_data.callback_for_data_template_url){
                                create_data.callback(dataCallbackParams);
                            }

                            initializer.reinitialize();
                        });

                    }else {

                        // do not attempt to reload template if this is not a template
                        if( "undefined" !== typeof template['code'] ){
                            return;
                        }

                        $('.twig-body-section').html(template);
                        initializer.reinitialize();
                    }

                }).fail((data) => {
                    bootstrap_notifications.notify(data.responseText, 'danger');
                }).always((data) => {
                    // hide loader only when there is no other ajax executed inside
                    if( "undefined" === typeof dataTemplateUrl ){
                        ui.widgets.loader.hideLoader();
                    }

                    // if there is code there also must be message so i dont check it
                    let code                = data['code'];
                    let message             = data['message'];
                    let notification_type   = '';

                    if( undefined === code ){
                        bootstrap_notifications.notify(create_data.success_message, 'success');
                        return;
                    }

                    if( code === 200 ){
                        notification_type = 'success';
                    }else{
                        notification_type = 'danger';
                    }

                    bootstrap_notifications.notify(message, notification_type);

                });

                event.preventDefault();
            });
        },
        attachRecordUpdateOrAddViaAjaxOnSubmitForSingleForm: function () {
            let _this = this;
            $('.update-record-form form').submit(function (event) {
                let form = $(event.target);
                let formTarget = form.attr('data-form-target');
                let updateData = _this.form_target_actions[formTarget].makeUpdateData(form);
                ui.widgets.loader.toggleLoader();
                $.ajax({
                    url: updateData.url,
                    type: 'POST',
                    data: updateData.data, //In this case the data from target_action is being sent not form directly
                }).done((data) => {

                    if( undefined !== data['template'] ){
                        $('.twig-body-section').html(data['template']);
                        initializer.reinitialize();
                    }

                }).fail((data) => {
                    bootstrap_notifications.notify(data.responseText, 'danger');
                }).always((data) => {

                    // if there is code there also must be message so i dont check it
                    let code                = data['code'];
                    let message             = data['message'];
                    let notification_type   = '';

                    if( undefined === code ){
                        return;
                    }

                    if( code === 200 ){
                        notification_type = 'success';
                    }else{
                        notification_type = 'danger';
                    }

                    bootstrap_notifications.notify(message, notification_type);

                    ui.widgets.loader.toggleLoader();
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

                bootstrap_notifications.notify(this.messages.entityEditStart(this.entity_actions[param_entity_name].entity_name), 'warning');
                return;
            }

            this.toggleActionIconsVisibillity(tr_closest_parent, null, is_content_editable);
            this.toggleDisabledClassForTableRow(tr_closest_parent);

            utils.domAttributes.contentEditable(tr_closest_parent, utils.domAttributes.actions.unset,'td', 'input, select, button, img');
            $(tr_closest_parent).removeClass(this.classes["table-active"]);
            bootstrap_notifications.notify(this.messages.entityEditEnd(this.entity_actions[param_entity_name].entity_name), 'success');
        },
        toggleActionIconsVisibillity: function (tr_parent_element, toggle_content_editable = null, is_content_editable) {
            let save_icon = $(tr_parent_element).find('.fa-save');
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
            let option_pickers  = $(tr_parent_element).find('.option-picker');
            let date_pickers    = $(tr_parent_element).find('.date-picker');
            let checkbox        = $(tr_parent_element).find('.checkbox-disabled');
            let selectize       = $(tr_parent_element).find('.selectize-control');
            let dataPreview     = $(tr_parent_element).find('.data-preview');
            let elements_to_toggle = [color_pickers, option_pickers, date_pickers, checkbox, selectize, dataPreview];
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
            let update_data = this.entity_actions[param_entity_name].makeUpdateData(tr_parent_element);
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
                data: update_data.data,
                success: (data) => {
                    bootstrap_notifications.notify(update_data.success_message, 'success');
                },
            }).fail(() => {
                bootstrap_notifications.notify(update_data.fail_message, 'danger')
            }).always(() => {
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
        entity_actions: {
            "MySchedules": {
                makeUpdateData: function (tr_parent_element) {
                    let id              = $(tr_parent_element).find('.id').html();
                    let name            = $(tr_parent_element).find('.name').html();
                    let scheduleType    = $(tr_parent_element).find('.type :selected');
                    let date            = $(tr_parent_element).find('.date input').val();
                    let information     = $(tr_parent_element).find('.information').html();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/my-schedule/update/';
                    let ajax_data = {
                        'name': name,
                        'date': date,
                        'information': information,
                        'id': id,
                        'scheduleType': {
                            "type": "entity",
                            'namespace': 'App\\Entity\\Modules\\Schedules\\MyScheduleType',
                            'id': $(scheduleType).val(),
                        },
                    };

                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id = $(parent_element).find('.id').html();
                    let url = '/my-schedule/remove/';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityRemoveFail(this.entity_name);

                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': true,
                    };

                },
                makeCreateData: function () {
                    let schedulesType = JSON.parse(TWIG_GET_ATTRS).schedules_type;

                    let url = '/my-schedules/' + schedulesType;
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "My schedule",
            },
            "MySchedulesTypes": {
                makeUpdateData: function (tr_parent_element) {
                    let id   = $(tr_parent_element).find('.id').html();
                    let name = $(tr_parent_element).find('.name').html();
                    let icon = $(tr_parent_element).find('.icon').html();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message    = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/my-schedule-settings/schedule-type/update';
                    let ajax_data = {
                        'name': name,
                        'icon': icon,
                        'id'  : id
                    };

                    return {
                        'url'               : url,
                        'data'              : ajax_data,
                        'success_message'   : success_message,
                        'fail_message'      : fail_message
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id              = $(parent_element).find('.id').html();
                    let name            = $(parent_element).find('.name').html();
                    let url             = '/my-schedule-settings/schedule-type/remove';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message    = ui.crud.messages.entityRemoveFail(this.entity_name);

                    let message = 'You are about to remove schedule type named <b>' + name + ' </b>. There might be schedule connected with it. Are You 100% sure? This might break something...';
                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': false, //temporary
                        'confirm_message': message
                    };
                },
                makeCreateData: function () {
                    let url = '/my-schedules-settings';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "My schedule type",
            },
            "IntegrationResource": {
                makeUpdateData: function (tr_parent_element) {
                    let id = $(tr_parent_element).find('.db-id').html();
                    let name = $(tr_parent_element).find('.resource-name').html();
                    let data = $(tr_parent_element).find('.resource-data').html();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/integrations-resource/update/';
                    let ajax_data = {
                        'id': id,
                        'name': name,
                        'data': data,
                    };

                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id = $(parent_element).find('.db-id').html();
                    let url = '/integrations-resource/remove/';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityRemoveFail(this.entity_name);

                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': false,
                    };

                },
                makeCreateData: function () {
                    let url = '/integrations';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "Integration Resource",
            },
            "MyPaymentsProduct": {
                makeUpdateData: function (tr_parent_element) {
                    let id = $(tr_parent_element).find('.id').html();
                    let name = $(tr_parent_element).find('.name').html();
                    let price = $(tr_parent_element).find('.price').html();
                    let market = $(tr_parent_element).find('.market').html();
                    let products = $(tr_parent_element).find('.products').html();
                    let information = $(tr_parent_element).find('.information').html();
                    let rejected = $(tr_parent_element).find('.rejected').html();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/my-payments-products/update/';
                    let ajax_data = {
                        'id': id,
                        'name': name,
                        'price': price,
                        'market': market,
                        'products': products,
                        'information': information,
                        'rejected': rejected
                    };
                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id = $(parent_element).find('.id').html();
                    let url = '/my-payments-products/remove/';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityRemoveFail(this.entity_name);

                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': true,
                    };

                },
                makeCreateData: function () {
                    let url = '/my-payments-products';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "My Payments Product",
            },
            "MyPaymentsMonthly": {
                makeUpdateData: function (tr_parent_element) {
                    let id = $(tr_parent_element).find('.id').html();
                    let date = $(tr_parent_element).find('.date input').val();
                    let money = $(tr_parent_element).find('.money').html();
                    let description = $(tr_parent_element).find('.description').html();
                    let paymentType = $(tr_parent_element).find('.type :selected');

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/my-payments-monthly/update/';
                    let ajax_data = {
                        'id': id,
                        'date': date,
                        'money': money,
                        'description': description,
                        'type': {
                            "type": "entity",
                            'namespace': 'App\\Entity\\Modules\\Payments\\MyPaymentsSettings',
                            'id': $(paymentType).val(),
                        },
                    };

                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id = $(parent_element).find('.id').html();
                    let url = '/my-payments-monthly/remove/';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityRemoveFail(this.entity_name);

                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': false, //temporary
                    };

                },
                makeCreateData: function () {
                    let url = '/my-payments-monthly';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "My Payments Monthly",
            },
            "MyRecurringPaymentsMonthly": {
                makeUpdateData: function (tr_parent_element) {
                    let id          = $(tr_parent_element).find('.id').html();
                    let date        = $(tr_parent_element).find('.date input').val();
                    let money       = $(tr_parent_element).find('.money').html();
                    let description = $(tr_parent_element).find('.description').html();
                    let paymentType = $(tr_parent_element).find('.type :selected');

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = 'my-recurring-payments-monthly/update/';
                    let ajax_data = {
                        'id': id,
                        'date': date,
                        'money': money,
                        'description': description,
                        'type': {
                            "type": "entity",
                            'namespace': 'App\\Entity\\Modules\\Payments\\MyPaymentsSettings',
                            'id': $(paymentType).val(),
                        },
                    };

                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id = $(parent_element).find('.id').html();
                    let url = '/my-recurring-payments-monthly/remove/';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityRemoveFail(this.entity_name);

                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': false, //temporary
                    };

                },
                makeCreateData: function () {
                    let url = '/my-recurring-payments-monthly-settings';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "My Recurring Payments Monthly",
            },
            "MyPaymentsOwed": {
                makeUpdateData: function (tr_parent_element) {
                    let id          = $(tr_parent_element).find('.id').html();
                    let date        = $(tr_parent_element).find('.date input').val();
                    let target      = $(tr_parent_element).find('.target').html();
                    let amount      = $(tr_parent_element).find('.amount').html();
                    let information = $(tr_parent_element).find('.information').html();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/my-payments-owed/update/';
                    let ajax_data = {
                        'id'         : id,
                        'date'       : date,
                        'target'     : target,
                        'amount'     : amount,
                        'information': information,
                    };

                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id              = $(parent_element).find('.id').html();
                    let url             = '/my-payments-owed/remove/';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message    = ui.crud.messages.entityRemoveFail(this.entity_name);

                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message'   : fail_message,
                        'is_dataTable'   : false, //temporary
                    };

                },
                makeCreateData: function () {
                    let url             = '/my-payments-owed';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message    = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url'            : url,
                        'success_message': success_message,
                        'fail_message'   : fail_message,
                    };
                },
                entity_name: "My Payments Owed",
            },
            "MyJobAfterhours": {
                makeUpdateData: function (tr_parent_element) {
                    let id = $(tr_parent_element).find('.id').html();
                    let date = $(tr_parent_element).find('.date input').val();
                    let minutes = $(tr_parent_element).find('.minutes').html();
                    let description = $(tr_parent_element).find('.description').html();
                    let type = $(tr_parent_element).find('.type').html();
                    let goal = $(tr_parent_element).find('.goal').html();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/my-job/afterhours/update/';
                    let ajax_data = {
                        'date': date,
                        'description': description,
                        'minutes': minutes,
                        'type': type,
                        'id': id,
                        'goal': goal,
                    };
                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id = $(parent_element).find('.id').html();
                    let url = '/my-job/afterhours/remove/';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityRemoveFail(this.entity_name);

                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': false, //temporary
                    };

                },
                makeCreateData: function () {
                    let url = '/my-job/afterhours';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "My Job Afterhours",
            },
            "MyJobHolidays": {
                makeUpdateData: function (tr_parent_element) {
                    let id          = $(tr_parent_element).find('.id').html();
                    let year        = $(tr_parent_element).find('.year').html();
                    let daysSpent   = $(tr_parent_element).find('.daysSpent').html();
                    let information = $(tr_parent_element).find('.information').html();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message    = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/my-job/holidays/update/';
                    let ajax_data = {
                        'year'          : year,
                        'daysSpent'     : daysSpent,
                        'information'   : information,
                        'id'            : id,
                    };
                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id              = $(parent_element).find('.id').html();
                    let url             = '/my-job/holidays/remove/';
                    let fail_message    = ui.crud.messages.entityRemoveFail(this.entity_name);
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);

                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': false, //temporary
                    };

                },
                makeCreateData: function () {
                    let url             = '/my-job/holidays';
                    let fail_message    = ui.crud.messages.entityCreatedRecordFail(this.entity_name);
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "My Job Holidays",
            },
            "MyJobHolidaysPool": {
                makeUpdateData: function (tr_parent_element) {
                    let id          = $(tr_parent_element).find('.id').html();
                    let year        = $(tr_parent_element).find('.year').html();
                    let daysLeft    = $(tr_parent_element).find('.daysLeft').html();
                    let companyName = $(tr_parent_element).find('.companyName').html();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message    = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/my-job/holidays-pool/update/';
                    let ajax_data = {
                        'year'          : year,
                        'daysLeft'      : daysLeft,
                        'companyName'   : companyName,
                        'id'            : id,
                    };
                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id              = $(parent_element).find('.id').html();
                    let url             = '/my-job/holidays-pool/remove/';
                    let fail_message    = ui.crud.messages.entityRemoveFail(this.entity_name);
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);

                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': false, //temporary
                    };

                },
                makeCreateData: function () {
                    let url             = '/my-job/settings';
                    let fail_message    = ui.crud.messages.entityCreatedRecordFail(this.entity_name);
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "My Job Holidays Pool",
            },
            "MyShoppingPlans": {
                makeUpdateData: function (tr_parent_element) {
                    let id = $(tr_parent_element).find('.id').html();
                    let information = $(tr_parent_element).find('.information').html();
                    let example = $(tr_parent_element).find('.example').html();
                    let name = $(tr_parent_element).find('.name').html();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/my-shopping/plans/update/';
                    let ajax_data = {
                        'id': id,
                        'information': information,
                        'example': example,
                        'name': name
                    };

                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id = $(parent_element).find('.id').html();
                    let url = '/my-shopping/plans/remove/';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityRemoveFail(this.entity_name);

                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': false, //temporary
                    };

                },
                makeCreateData: function () {
                    let url = '/my-shopping/plans';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "My Shopping Plans",
            },
            "MyTravelsIdeas": {
                makeUpdateData: function (tr_parent_element) {
                    let id = $(tr_parent_element).find('.id').html();
                    let location = $(tr_parent_element).find('.location span').html();
                    let country = $(tr_parent_element).find('.country span').html();
                    let image = $(tr_parent_element).find('.image img').attr('src');
                    let map = $(tr_parent_element).find('.map a').attr('href');
                    let category = $(tr_parent_element).find('.category i').html();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/my-travels/ideas/update/';
                    let ajax_data = {
                        'location': location,
                        'country': country,
                        'image': image,
                        'map': map,
                        'category': category,
                        'id': id
                    };

                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id = $(parent_element).find('.id').html();
                    let url = '/my-travels/ideas/remove/';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityRemoveFail(this.entity_name);

                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': false, //temporary
                    };

                },
                makeCreateData: function () {
                    let url = '/my/travels/ideas';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "My Travels Ideas",
            },
            "Achievements": {
                makeUpdateData: function (tr_parent_element) {
                    let id = $(tr_parent_element).find('.id').html();
                    let type = $(tr_parent_element).find('.type').html();
                    let description = $(tr_parent_element).find('.description').html();
                    let name = $(tr_parent_element).find('.name').html();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/achievement/update/';
                    let ajax_data = {
                        'id': id,
                        'name': name,
                        'description': description,
                        'type': type
                    };

                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id = $(parent_element).find('.id').html();
                    let url = '/achievement/remove/';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityRemoveFail(this.entity_name);

                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': false, //temporary
                    };

                },
                makeCreateData: function () {
                    let url = '/achievement';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "Achievements",
            },
            "MyNotesCategories": {
                makeUpdateData: function (tr_parent_element) {
                    let id = $(tr_parent_element).find('.id').html();
                    let name = $(tr_parent_element).find('.name').html();
                    let icon = $(tr_parent_element).find('.icon').html();
                    let color = $(tr_parent_element).find('.color').text();
                    let parent = $(tr_parent_element).find('.parent').find(':selected').val();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/my-notes/settings/update/';
                    let ajax_data = {
                        'name': name,
                        'icon': icon,
                        'color': color,
                        'parent_id': parent,
                        'id': id
                    };

                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id = $(parent_element).find('.id').html();
                    let url = '/my-notes/settings/remove/';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityRemoveFail(this.entity_name);

                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': false, //temporary
                        'confirm_message': 'This category might contain notes or be parent of other category. Do You really want to remove it?'
                    };

                },
                makeCreateData: function () {
                    let url = '/my-notes/settings';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "My Notes Categories",
            },
            "MyNotes": {
                makeCreateData: function () {
                    let url = '/my-notes/create';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'callback': function () {
                            tinymce.remove(".tiny-mce"); //tinymce must be removed or won't be reinitialized.
                        },
                        'callback_for_data_template_url': true,
                    };
                },
                entity_name: "My Notes",
            },
            "MyPaymentsSettings": {
                /**
                 * @info Important! At this moment settings panel has only option to add currency and types
                 * while currency will be rarely changed if changed at all, I've prepared this to work only with types
                 */
                makeUpdateData: function (tr_parent_element) {
                    let id = $(tr_parent_element).find('.id').html();
                    let value = $(tr_parent_element).find('.value').html();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/my-payments-settings/update';
                    let ajax_data = {
                        'value': value,
                        'id': id
                    };

                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id = $(parent_element).find('.id').html();
                    let url = '/my-payments-settings/remove/';
                    let value = $(parent_element).find('.value').html();
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityRemoveFail(this.entity_name);
                    let message = 'You are about to remove type named <b>' + value + ' </b>. There might be payment connected with it. Are You 100% sure? This might break something...';

                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': false, //temporary
                        'confirm_message': message
                    };

                },
                makeCreateData: function () {
                    let url = '/my-payments-settings';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "My Payments Settings",
            },
            "MyContactType": {
                makeUpdateData: function (tr_parent_element) {
                    let id          = $(tr_parent_element).find('.id').html();
                    let name        = $(tr_parent_element).find('.name').html();
                    let imagePath   = $(tr_parent_element).find('.image_path').html();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message    = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/my-contacts-types/update';
                    let ajax_data = {
                        'imagePath': imagePath,
                        'name'      : name,
                        'id'        : id
                    };

                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id   = $(parent_element).find('.id').html();
                    let name = $(parent_element).find('.name').html();
                    let url  = '/my-contacts-types/remove';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message    = ui.crud.messages.entityRemoveFail(this.entity_name);

                    let message = 'You are about to remove type named <b>' + name + ' </b>. There might be contact connected with it. Are You 100% sure? This might break something...';
                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'use_ajax_fail_message': true,
                        'is_dataTable': false, //temporary
                        'confirm_message': message
                    };
                },
                makeCreateData: function () {
                    let url = '/my-contacts-settings';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message    = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "My contact type",
            },
            "MyContactGroup": {
                makeUpdateData: function (tr_parent_element) {
                    let id     = $(tr_parent_element).find('.id').html();
                    let name   = $(tr_parent_element).find('.name').html();
                    let icon   = $(tr_parent_element).find('.icon').html();
                    let color  = $(tr_parent_element).find('.color').text();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message    = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/my-contacts-groups/update';
                    let ajax_data = {
                        'name'      : name,
                        'color'     : color,
                        'icon'      : icon,
                        'id'        : id
                    };

                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id   = $(parent_element).find('.id').html();
                    let name = $(parent_element).find('.name').html();
                    let url  = '/my-contacts-groups/remove';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message    = ui.crud.messages.entityRemoveFail(this.entity_name);

                    let message = 'You are about to remove group named <b>' + name + ' </b>. There might be contact connected with it. Are You 100% sure? This might break something...';
                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': false, //temporary
                        'confirm_message': message,
                        'use_ajax_fail_message': true
                    };
                },
                makeCreateData: function () {
                    let url = '/my-contacts-settings';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message    = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "My contact group",
            },
            "MyPasswords": {
                makeUpdateData: function (tr_parent_element) {
                    let id = $(tr_parent_element).find('.id').html().trim();
                    let login = $(tr_parent_element).find('.login').html().trim();
                    let password = $(tr_parent_element).find('.password').html().trim();
                    let url = $(tr_parent_element).find('.url').html().trim();
                    let description = $(tr_parent_element).find('.description').html().trim();
                    let groupId = $(tr_parent_element).find('.group :selected').val().trim();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let ajax_url = '/my-passwords/update/';
                    let ajax_data = {
                        'id': id,
                        'password': password,
                        'login': login,
                        'url': url,
                        'description': description,
                        'group': {
                            "type": "entity",
                            'namespace': 'App\\Entity\\Modules\\Passwords\\MyPasswordsGroups',
                            'id': groupId,
                        },
                    };

                    return {
                        'url': ajax_url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'edit': {
                            'invokeAlert': true,
                            'alertMessage': '<b>WARNING</b>! You are about to save Your password. There is NO comming back. If You click save now with all stars **** in the password field then stars will be Your new password!'
                        }
                    }
                },
                makeRemoveData: function (parent_element) {
                    let id = $(parent_element).find('.id').html();
                    let url = '/my-passwords/remove/';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityRemoveFail(this.entity_name);

                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': false, //temporary
                    };

                },
                makeCreateData: function () {
                    let url = '/my-passwords';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                makeCopyData: function (parent_element) {
                    let url = '/my-passwords/get-password/';
                    let id = $(parent_element).find('.id').html();

                    return {
                        'url': url + id,
                        'success_message': ui.crud.messages.password_copy_confirmation_message,
                        'fail_message': ui.crud.messages.default_copy_data_fail_message,
                    };
                },
                entity_name: "My Passwords",
            },
            "MyPasswordsGroups": {
                makeUpdateData: function (tr_parent_element) {
                    let id = $(tr_parent_element).find('.id').html();
                    let name = $(tr_parent_element).find('.name').html();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/my-passwords-groups/update';
                    let ajax_data = {
                        'name': name,
                        'id': id
                    };

                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id              = $(parent_element).find('.id').html();
                    let name            = $(parent_element).find('.name').html();
                    let url             = '/my-passwords-groups/remove';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message    = ui.crud.messages.entityRemoveFail(this.entity_name);

                    let message = 'You are about to remove group named <b>' + name + ' </b>. There might be password connected with it. Are You 100% sure? This might break something...';
                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': false, //temporary
                        'confirm_message': message
                    };
                },
                makeCreateData: function () {
                    let url = '/my-passwords-settings';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "My Passwords Groups",
            },
            "MyGoals": {
                makeUpdateData: function (tr_parent_element) {
                    let id                          = $(tr_parent_element).find('.id').html();
                    let name                        = $(tr_parent_element).find('.name').html();
                    let description                 = $(tr_parent_element).find('.description').html();
                    let displayOnDashboardCheckbox  = $(tr_parent_element).find('.displayOnDashboard');
                    let displayOnDashboard          = $(displayOnDashboardCheckbox).prop("checked");

                    let success_message     = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message        = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/admin/goals/settings/update';
                    let ajax_data = {
                        'name'               : name,
                        'description'        : description,
                        'id'                 : id,
                        'displayOnDashboard' : displayOnDashboard,
                    };

                    return {
                        'url'                : url,
                        'data'               : ajax_data,
                        'success_message'    : success_message,
                        'fail_message'       : fail_message
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id = $(parent_element).find('.id').html();
                    let name = $(parent_element).find('.name').html();
                    let url = '/admin/goals/settings/remove';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityRemoveFail(this.entity_name);

                    let message = 'You are about to remove goal named <b>' + name + ' </b>. There might be subgoal connected with it. Are You 100% sure? This might break something...';
                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': false, //temporary
                        'confirm_message': message
                    };
                },
                makeCreateData: function () {
                    let url = '/admin/goals/settings/MyGoals';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "My Goals",
            },
            "MySubgoals": {
                makeUpdateData: function (tr_parent_element) {
                    let id = $(tr_parent_element).find('.id').html();
                    let name = $(tr_parent_element).find('.name').html();
                    let goalId = $(tr_parent_element).find('.goal :selected').val().trim();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/admin/subgoals/settings/update';
                    let ajax_data = {
                        'id': id,
                        'name': name,
                        'myGoal': {
                            "type": "entity",
                            'namespace': 'App\\Entity\\Modules\\Goals\\MyGoals',
                            'id': goalId,
                        },
                    };

                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id = $(parent_element).find('.id').html();
                    let url = '/admin/subgoals/settings/remove';
                    let success_message = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityRemoveFail(this.entity_name);

                    return {
                        'url': url,
                        'data': {
                            'id': id
                        },
                        'success_message': success_message,
                        'fail_message': fail_message,
                        'is_dataTable': false, //temporary
                    };
                },
                makeCreateData: function () {
                    let url = '/admin/goals/settings/MySubgoals';
                    let success_message = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url': url,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                entity_name: "My Subgoals",
            },
            "MyGoalsPayments": {
                makeUpdateData: function (tr_parent_element) {
                    let id                          = $(tr_parent_element).find('.id').html();
                    let name                        = $(tr_parent_element).find('.name').html();
                    let deadline                    = $(tr_parent_element).find('.deadline input').val();
                    let collectionStartDate         = $(tr_parent_element).find('.collectionStartDate input').val();
                    let moneyGoal                   = $(tr_parent_element).find('.moneyGoal').html();
                    let moneyCollected              = $(tr_parent_element).find('.moneyCollected').html();
                    let displayOnDashboardCheckbox  = $(tr_parent_element).find('.displayOnDashboard');
                    let displayOnDashboard          = $(displayOnDashboardCheckbox).prop("checked");

                    let success_message             = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message                = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/admin/goals/payments/settings/update';
                    let ajax_data = {
                        'id'                        : id,
                        'name'                      : name,
                        'deadline'                  : deadline,
                        'collectionStartDate'       : collectionStartDate,
                        'moneyGoal'                 : moneyGoal,
                        'moneyCollected'            : moneyCollected,
                        'displayOnDashboard'        : displayOnDashboard,
                    };

                    return {
                        'url'                       : url,
                        'data'                      : ajax_data,
                        'success_message'           : success_message,
                        'fail_message'              : fail_message
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id                  = $(parent_element).find('.id').html();
                    let url                 = '/admin/goals/payments/settings/remove';
                    let success_message     = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message        = ui.crud.messages.entityRemoveFail(this.entity_name);

                    return {
                        'url'               : url,
                        'data'              : {
                            'id'            : id
                        },
                        'success_message'   : success_message,
                        'fail_message'      : fail_message,
                        'is_dataTable'      : false, //temporary
                    };
                },
                makeCreateData: function () {
                    let url                 = '/admin/goals/settings/MyGoalsPayments';
                    let success_message     = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                    let fail_message        = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                    return {
                        'url'               : url,
                        'success_message'   : success_message,
                        'fail_message'      : fail_message,
                    };
                },
                entity_name: "My Goals Payments",
            },
            "MyFiles": {
                makeUpdateData: function (tr_parent_element) {
                    let subdirectory        = $(tr_parent_element).find('input[name^="file_full_path"]').attr('data-subdirectory');
                    let file_full_path      = $(tr_parent_element).find('input[name^="file_full_path"]').val();
                    let file_new_name       = $(tr_parent_element).find('.file_name').text();

                    let selectizeSelect     = $(tr_parent_element).find('.tags');
                    let tags                = $(selectizeSelect)[0].selectize.getValue();

                    let url                 = '/api/my-files/update';

                    let success_message     = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message        = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let ajax_data = {
                        'file_full_path'    : file_full_path,
                        'file_new_name'     : file_new_name,
                        'subdirectory'      : subdirectory,
                        'tags'              : tags,
                    };

                    return {
                        'url'                       : url,
                        'data'                      : ajax_data,
                        'success_message'           : success_message,
                        'fail_message'              : fail_message,
                        'update_template'           : true
                    };
                },
                makeRemoveData: function (parent_element) {
                    let subdirectory        = $(parent_element).find('input[name^="file_full_path"]').attr('data-subdirectory');
                    let file_full_path      = $(parent_element).find('input[name^="file_full_path"]').val();
                    let url                 = '/my-files/remove-file';

                    let success_message     = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message        = ui.crud.messages.entityRemoveFail(this.entity_name);

                    return {
                        'url'               : url,
                        'data'              : {
                            'file_full_path'    : file_full_path,
                            'subdirectory'      : subdirectory
                        },
                        'success_message'   : success_message,
                        'fail_message'      : fail_message,
                        'is_dataTable'      : false, //temporary
                    };
                },
                entity_name: "My files"
            },
            "MyPaymentsBillsItems": {
                makeUpdateData: function (tr_parent_element) {
                    let id      = $(tr_parent_element).find('.id').html();
                    let amount  = $(tr_parent_element).find('.amount').html();
                    let name    = $(tr_parent_element).find('.name').html();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message    = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/my-payments-bills/update-bill-item/';
                    let ajax_data = {
                        'id'    : id,
                        'amount': amount,
                        'name'  : name
                    };

                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id                  = $(parent_element).find('.id').html();
                    let url                 = '/my-payments-bills/remove-bill-item/';
                    let success_message     = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message        = ui.crud.messages.entityRemoveFail(this.entity_name);

                    return {
                        'url'               : url,
                        'data'              : {
                            'id'            : id
                        },
                        'success_message'   : success_message,
                        'fail_message'      : fail_message,
                        'is_dataTable'      : false, //temporary
                    };
                },
                entity_name: "My bill items"
            },
            "MyPaymentsBills": {
                makeUpdateData: function (tr_parent_element) {
                    let id              = $(tr_parent_element).find('.id').html();
                    let name            = $(tr_parent_element).find('.name').html();
                    let information     = $(tr_parent_element).find('.information').html();
                    let startDate       = $(tr_parent_element).find('.startDate').val();
                    let endDate         = $(tr_parent_element).find('.endDate').val();
                    let plannedAmount   = $(tr_parent_element).find('.plannedAmount').html();

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message    = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/my-payments-bills/update-bill/';
                    let ajax_data = {
                        'id'            : id,
                        'plannedAmount' : plannedAmount,
                        'startDate'     : startDate,
                        'endDate'       : endDate,
                        'name'          : name,
                        'information'   : information
                    };

                    return {
                        'url': url,
                        'data': ajax_data,
                        'success_message': success_message,
                        'fail_message': fail_message,
                    };
                },
                makeRemoveData: function (parent_element) {
                    let id                  = $(parent_element).find('.id').html();
                    let url                 = '/my-payments-bills/remove-bill/';
                    let success_message     = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                    let fail_message        = ui.crud.messages.entityRemoveFail(this.entity_name);

                    return {
                        'url'               : url,
                        'data'              : {
                            'id'            : id
                        },
                        'success_message'   : success_message,
                        'fail_message'      : fail_message,
                        'is_dataTable'      : false, //temporary
                    };
                },
                entity_name: "My bill"
            },
            'settingsDashboardWidgetsVisibility':{
                /**
                 * data from all records must be sent at once
                 * @param tr_parent_element {object}
                 */
                makeUpdateData: function (tr_parent_element) {

                    let table         = $(tr_parent_element).closest('tbody');
                    let allRows       = $(table).find('tr');
                    let allRowsData   = [];

                    if( 0 === table.length || 0 === allRows.length ){
                        throw({
                           "message": "Either no form or rows were found for entity update",
                           "entity" : "Settings",
                           "method" : "settingsDashboardWidgetsVisibility::makeUpdateData"
                        });
                    }

                    $.each(allRows, (index, row) => {

                        let name            = $(row).find('.widget-name').text();
                        let isCheckedInput  = $(row).find('.is-checked').find('input');
                        let isChecked       = utils.domAttributes.isChecked(isCheckedInput);

                        let rowData = {
                            'name'          : name,
                            'is_visible'    : isChecked,
                        };

                        allRowsData.push(rowData);
                    });

                    let ajax_data = {
                        'all_rows_data': allRowsData
                    };

                    let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                    let fail_message    = ui.crud.messages.entityUpdateFail(this.entity_name);

                    let url = '/api/settings-dashboard/update-widgets-visibility';

                    return {
                        'url'             : url,
                        'data'            : ajax_data,
                        'success_message' : success_message,
                        'fail_message'    : fail_message,
                    };
                },
            entity_name: "Settings",
        },
        },
        form_target_actions: {
            "UserAvatar": {
                makeUpdateData: function (form) {
                    let avatar = $(form).find('[data-id="avatar"]').val();

                    let url = '/user/profile/settings/update';

                    let ajax_data = {
                        'avatar': avatar,
                    };

                    return {
                        'url': url,
                        'data': ajax_data
                    };
                },
                form_target_action_name: "User Avatar",
            },
            'UserNickname':{
                makeUpdateData: function (form) {
                    let nickname = $(form).find('[data-id="nickname"]').val();

                    let url = '/user/profile/settings/update';

                    let ajax_data = {
                        'nickname': nickname,
                    };

                    return {
                        'url': url,
                        'data': ajax_data
                    };
                },
                form_target_action_name: "User Nickname",
            },
            'UserPassword':{
                makeUpdateData: function (form) {
                    let password = $(form).find('[data-id="password"]').val();

                    let url = '/user/profile/settings/update';

                    let ajax_data = {
                        'password': password,
                    };

                    return {
                        'url': url,
                        'data': ajax_data
                    };
                },
                form_target_action_name: "User Password",
            },
            'CreateFolder': {
                makeCreateData: function () {
                    let url                 = '/files/actions/create-folder';
                    let success_message     = ui.crud.messages.entityCreatedRecordSuccess(this.form_target_action_name);
                    let fail_message        = ui.crud.messages.entityCreatedRecordFail(this.form_target_action_name);

                    return {
                        'url'               : url,
                        'success_message'   : success_message,
                        'fail_message'      : fail_message,
                        'callback': function (dataCallbackParams) {
                            let menuNodeModuleName = dataCallbackParams.menuNodeModuleName;

                            if( "undefined" == typeof menuNodeModuleName){
                                throw ("menuNodeModuleName param is missing in CreateFolder::makeCreateData");
                            }

                            ui.ajax.singleMenuNodeReload(menuNodeModuleName);
                        },
                        'callback_before': true,
                    };
                },
                form_target_action_name: "Create folder",
            },
            'MyPaymentsBills': {
                makeCreateData: function () {
                    let url                 = '/my-payments-bills';
                    let success_message     = ui.crud.messages.entityCreatedRecordSuccess(this.form_target_action_name);
                    let fail_message        = ui.crud.messages.entityCreatedRecordFail(this.form_target_action_name);

                    return {
                        'url'               : url,
                        'success_message'   : success_message,
                        'fail_message'      : fail_message,
                    };
                },
                form_target_action_name: "My Payments Bills",
            },
            'MyPaymentsBillsItems': {
                makeCreateData: function () {
                    let url                 = '/my-payments-bills';
                    let success_message     = ui.crud.messages.entityCreatedRecordSuccess(this.form_target_action_name);
                    let fail_message        = ui.crud.messages.entityCreatedRecordFail(this.form_target_action_name);

                    return {
                        'url'               : url,
                        'success_message'   : success_message,
                        'fail_message'      : fail_message,
                    };
                },
                form_target_action_name: "My Payments Bills Items",
            }
        }
    };

}());

