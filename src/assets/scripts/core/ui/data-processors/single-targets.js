import Ajax             from "../Ajax";
import Navigation       from "../../Navigation";
import StringUtils      from "../../utils/StringUtils";
import AbstractAction   from "../Actions/AbstractAction";

var bootbox = require('bootbox');

/**
 * This file contains the data passed to the ajax calls for special single targets like forms
 */
export default (function () {

    if (typeof dataProcessors === 'undefined') {
        window.dataProcessors = {}
    }

    dataProcessors.singleTargets = {
        ajax : new Ajax(),
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
        'UserLockPassword':{
            makeUpdateData: function (form) {
                let systemLockPassword = $(form).find('[data-id="systemLockPassword"]').val();

                let url = '/api/system/system-lock-set-password';

                let ajax_data = {
                    'systemLockPassword': systemLockPassword,
                };

                return {
                    'url': url,
                    'data': ajax_data
                };
            },
            form_target_action_name: "User lock password",
        },
        'CreateFolder': {
            makeCreateData: function () {
                let url                 = '/files/actions/create-folder';
                let success_message     = AbstractAction.messages.entityCreatedRecordSuccess(this.form_target_action_name);
                let fail_message        = AbstractAction.messages.entityCreatedRecordFail(this.form_target_action_name);

                return {
                    'url'               : url,
                    'success_message'   : success_message,
                    'fail_message'      : fail_message,
                    'callback': function (dataCallbackParams) {

                        if(
                                null !== dataCallbackParams
                            &&  "undefined" !== typeof dataCallbackParams
                        ){
                            let menuNodeModuleName           = dataCallbackParams.menuNodeModuleName;
                            let menuNodeModulesNamesToReload = dataCallbackParams.menuNodeModulesNamesToReload;

                            if( !StringUtils.isEmptyString(menuNodeModuleName)){
                                dataProcessors.singleTargets.ajax.singleMenuNodeReload(menuNodeModuleName);
                            }else if( !StringUtils.isEmptyString(menuNodeModulesNamesToReload) ){
                                let arrayOfMenuNodeModuleNames = JSON.parse(menuNodeModulesNamesToReload);
                                $.each(arrayOfMenuNodeModuleNames, function(index, menuNodeModuleName){
                                    dataProcessors.singleTargets.ajax.singleMenuNodeReload(menuNodeModuleName);
                                })
                            }
                        }

                        bootbox.hideAll();
                        dataProcessors.singleTargets.ajax.loadModuleContentByUrl(Navigation.getCurrentUri());
                    },
                    'callback_before': true,
                };
            },
            form_target_action_name: "Create folder",
        },
        'MyPaymentsBills': {
            makeCreateData: function () {
                let url                 = '/my-payments-bills';
                let success_message     = AbstractAction.messages.entityCreatedRecordSuccess(this.form_target_action_name);
                let fail_message        = AbstractAction.messages.entityCreatedRecordFail(this.form_target_action_name);

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
                let success_message     = AbstractAction.messages.entityCreatedRecordSuccess(this.form_target_action_name);
                let fail_message        = AbstractAction.messages.entityCreatedRecordFail(this.form_target_action_name);

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