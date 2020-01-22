/**
 * @info: This class contains the methods and representations used to:
 *  - handle formView fetching from backed
 */

export default (function () {

    if (typeof dataProcessors === 'undefined') {
        window.dataProcessors = {}
    }

    dataProcessors.entities = {
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
            entity_name: "Schedule",
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
                    'fail_message'      : fail_message,
                    'callback'          : function () {
                        ui.ajax.entireMenuReload();
                    },
                    'callback_after': true,
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
                    'fail_message'   : fail_message,
                    'is_dataTable'   : false, //temporary
                    'confirm_message': message,
                    'callback'       : function () {
                        ui.ajax.entireMenuReload();
                    },
                    'callback_after': true,
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
                    'callback'       : function (dataCallbackParams) {
                        let menuNodeModuleName = dataCallbackParams.menuNodeModuleName;

                        if( "undefined" == typeof menuNodeModuleName){
                            throw ("menuNodeModuleName param is missing in ScheduleType::makeCreateData");
                        }

                        ui.ajax.singleMenuNodeReload(menuNodeModuleName);
                    },
                    'callback_after': true,
                };
            },
            entity_name: "Schedule type",
        },
        "MyPaymentsProduct": {
            makeUpdateData: function (tr_parent_element) {
                let id = $(tr_parent_element).find('.id').html();
                let name = $(tr_parent_element).find('.name').html();
                let price = $(tr_parent_element).find('.price input').val();
                let market = $(tr_parent_element).find('.market').html();
                let products = $(tr_parent_element).find('.products').html();
                let information = $(tr_parent_element).find('.information').html();
                let rejected = $(tr_parent_element).find('.rejected input').prop("checked");

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
            entity_name: "Payment product",
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
            entity_name: "Payment monthly",
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
            entity_name: "Recurring payment monthly",
        },
        "MyPaymentsOwed": {
            makeUpdateData: function (tr_parent_element) {
                let id          = $(tr_parent_element).find('.id').html();
                let date        = $(tr_parent_element).find('.date input').val();
                let target      = $(tr_parent_element).find('.target').html();
                let amount      = $(tr_parent_element).find('.amount').html();
                let information = $(tr_parent_element).find('.information').html();
                let currency    = $(tr_parent_element).find('.currency').find("select").val();

                let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                let fail_message = ui.crud.messages.entityUpdateFail(this.entity_name);

                let url = '/my-payments-owed/update/';
                let ajax_data = {
                    'id'         : id,
                    'date'       : date,
                    'target'     : target,
                    'amount'     : amount,
                    'currency'   : currency,
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
            entity_name: "Payment owed",
        },
        "MyJobAfterhours": {
            makeUpdateData: function (tr_parent_element) {
                let id          = $(tr_parent_element).find('.id').html();
                let date        = $(tr_parent_element).find('.date input').val();
                let minutes     = $(tr_parent_element).find('.minutes input').val();
                let description = $(tr_parent_element).find('.description').html();
                let type        = $(tr_parent_element).find('.type').html();
                let goal        = $(tr_parent_element).find('.goal').html();

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
            entity_name: "Job afterhour",
        },
        "MyJobHolidays": {
            makeUpdateData: function (tr_parent_element) {
                let id          = $(tr_parent_element).find('.id').html();
                let year        = $(tr_parent_element).find('.year').html();
                let daysSpent   = $(tr_parent_element).find('.daysSpent').find("input").val();
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
            entity_name: "Job holiday",
        },
        "MyJobHolidaysPool": {
            makeUpdateData: function (tr_parent_element) {
                let id          = $(tr_parent_element).find('.id').html();
                let year        = $(tr_parent_element).find('.year input').val();
                let daysLeft    = $(tr_parent_element).find('.daysLeft input').val();
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
            entity_name: "Job holiday pool",
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
            entity_name: "My shopping plan",
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
            entity_name: "My travel idea",
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
            entity_name: "achievement",
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
            entity_name: "Note category",
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
            entity_name: "Note",
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
            entity_name: "Payment setting",
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
            entity_name: "Contact type",
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
            entity_name: "Contact group",
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
            entity_name: "Password",
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
            entity_name: "Password group",
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
            entity_name: "Goal",
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
            entity_name: "Subgoal",
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
            entity_name: "Goal payment",
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
            entity_name: "File"
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
            entity_name: "Bill item"
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
            entity_name: "Bill"
        },
        'settingsDashboardWidgetsVisibility':{
            /**
             * data from all records must be sent at once
             * @param tr_parent_element {object}
             */
            makeUpdateData: function (tr_parent_element) {

                let table               = $(tr_parent_element).closest('tbody');
                let modifiedSettingName = $(tr_parent_element).find('.widget-name').text();

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

                    // we make update before the state is changed so we take opposite state for modified setting
                    if( modifiedSettingName === name ){
                        isChecked = !isChecked;
                    }

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
            entity_name: "Setting",
        },
        'settingsFinancesCurrencyTable':{
            makeUpdateData: function (tr_parent_element) {
                let name            = $(tr_parent_element).find('.name').html();
                let symbol          = $(tr_parent_element).find('.symbol').html();
                let multiplier      = $(tr_parent_element).find('.multiplier').val();
                let isDefaultInput  = $(tr_parent_element).find('.is-default').find('input');
                let isDefault       = utils.domAttributes.isChecked(isDefaultInput);

                let beforeUpdateState = $(tr_parent_element).find('.before-update-state').val();

                let success_message = ui.crud.messages.entityUpdateSuccess(this.entity_name);
                let fail_message    = ui.crud.messages.entityUpdateFail(this.entity_name);

                let url = '/api/settings-finances/update-currencies';
                let ajax_data = {
                    'name'                : name,
                    'symbol'              : symbol,
                    'multiplier'          : multiplier,
                    'is_default'          : isDefault,
                    'before_update_state' : beforeUpdateState,
                };

                return {
                    'url': url,
                    'data': ajax_data,
                    'success_message': success_message,
                    'fail_message': fail_message,
                };
            },
            makeRemoveData: function (parent_element) {
                let name                = $(parent_element).find('.name').text();
                let url                 = '/api/settings-finances/remove-currency/';
                let success_message     = ui.crud.messages.entityRemoveSuccess(this.entity_name);
                let fail_message        = ui.crud.messages.entityRemoveFail(this.entity_name);

                return {
                    'url'                   : url + name,
                    'data'                  : {},
                    'success_message'       : success_message,
                    'fail_message'          : fail_message,
                    'is_dataTable'          : false,
                    "use_ajax_fail_message" : true
                };
            },
            entity_name: "Setting",
        },
        'settingsFinancesCurrencyForm':{
            makeCreateData: function () {
                let url                 = '/page-settings';
                let success_message     = ui.crud.messages.entityCreatedRecordSuccess(this.entity_name);
                let fail_message        = ui.crud.messages.entityCreatedRecordFail(this.entity_name);

                return {
                    'url'               : url,
                    'success_message'   : success_message,
                    'fail_message'      : fail_message,
                };
            },
            entity_name: "Setting",
        },
    };

}());