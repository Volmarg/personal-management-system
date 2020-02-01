var bootbox = require('bootbox');

/**
 * This file contains the data passed to the ajax calls for special single targets like forms
 */
export default (function () {

    if (typeof dataProcessors === 'undefined') {
        window.dataProcessors = {}
    }

    dataProcessors.singleTargets = {
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
            form_target_action_name: "User avatar",
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
            form_target_action_name: "User nickname",
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
            form_target_action_name: "User password",
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
                        bootbox.hideAll();
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
            form_target_action_name: "Payment bill",
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
            form_target_action_name: "Payment bill item",
        }
    };

}());