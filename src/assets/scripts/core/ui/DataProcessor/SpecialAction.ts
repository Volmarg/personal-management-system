import AbstractDataProcessor    from "./AbstractDataProcessor";
import DataProcessorInterface   from "./DataProcessorInterface";
import StringUtils              from "../../utils/StringUtils";
import Navigation               from "../../Navigation";
import Ajax                     from "../../ajax/Ajax";
import DomAttributes            from "../../utils/DomAttributes";
import DataProcessorDto         from "../../../DTO/DataProcessorDto";
import BootboxWrapper           from "../../../libs/bootbox/BootboxWrapper";

/**
 * @description This class should contain definitions of actions either for special forms or certain elements on the page
 *              which not necessarily have to be forms/full forms.
 *
 *              Definition should be placed here if for example single input changes the state of given entity property
 *              or just certain entry in DB
 *
 *              Might also be used for working with special widgets actions like for example creating folder on server.
 *
 */
export default class SpecialAction extends AbstractDataProcessor {

    public static UserAvatar: DataProcessorInterface = {
        makeCopyData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeCreateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeRemoveData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        },
        makeUpdateData: function (form) {
            let avatar = $(form).find('[data-id="avatar"]').val();

            let url = '/user/profile/settings/update';

            let ajaxData = {
                'avatar': avatar,
            };

            let dataProcessorsDto      = new DataProcessorDto();
            dataProcessorsDto.url      = url;
            dataProcessorsDto.ajaxData = ajaxData;

            return dataProcessorsDto;
        },
        processorName: "User avatar"
    };

    public static UserNickname: DataProcessorInterface = {
        makeCopyData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeCreateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeRemoveData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        },
        makeUpdateData: function (form) {
            let nickname = $(form).find('[data-id="nickname"]').val();

            let url = '/user/profile/settings/update';

            let ajaxData = {
                'nickname': nickname,
            };

            let dataProcessorsDto      = new DataProcessorDto();
            dataProcessorsDto.url      = url;
            dataProcessorsDto.ajaxData = ajaxData;

            return dataProcessorsDto;
        },
        processorName: "User nickname"
    };

    public static UserPassword: DataProcessorInterface = {
        makeCopyData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeCreateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeRemoveData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        },
        makeUpdateData: function (form) {
            let password = $(form).find('[data-id="password"]').val();

            let url = '/user/profile/settings/update';

            let ajaxData = {
                'password': password,
            };

            let dataProcessorsDto      = new DataProcessorDto();
            dataProcessorsDto.url      = url;
            dataProcessorsDto.ajaxData = ajaxData;

            return dataProcessorsDto;
        },
        processorName: "User password"
    };


    public static UserLockPassword: DataProcessorInterface = {
        makeCopyData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeCreateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeRemoveData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        },
        makeUpdateData: function (form) {
            let systemLockPassword = $(form).find('[data-id="systemLockPassword"]').val();

            let url = '/api/system/system-lock-set-password';

            let ajaxData = {
                'systemLockPassword': systemLockPassword,
            };

            let dataProcessorsDto      = new DataProcessorDto();
            dataProcessorsDto.url      = url;
            dataProcessorsDto.ajaxData = ajaxData;

            return dataProcessorsDto;
        },
        processorName: "User lock password"
    };

    public static CreateFolder: DataProcessorInterface = {
        makeCopyData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeRemoveData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeUpdateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        },
        makeCreateData: function () {
            let url             = '/files/actions/create-folder';
            let successMessage  = AbstractDataProcessor.messages.entityCreatedRecordSuccess(SpecialAction.CreateFolder.processorName);
            let failMessage     = AbstractDataProcessor.messages.entityCreatedRecordFail(SpecialAction.CreateFolder.processorName);
            let callback        = (dataCallbackParams) => {
                let ajax = new Ajax();

                if(
                        null !== dataCallbackParams
                    &&  "undefined" !== typeof dataCallbackParams
                ){
                    let menuNodeModuleName           = dataCallbackParams.menuNodeModuleName;
                    let menuNodeModulesNamesToReload = dataCallbackParams.menuNodeModulesNamesToReload;

                    if( !StringUtils.isEmptyString(menuNodeModuleName)){
                        ajax.singleMenuNodeReload(menuNodeModuleName);
                    }else if( !StringUtils.isEmptyString(menuNodeModulesNamesToReload) ){
                        let arrayOfMenuNodeModuleNames = JSON.parse(menuNodeModulesNamesToReload);
                        $.each(arrayOfMenuNodeModuleNames, function(index, menuNodeModuleName){
                            ajax.singleMenuNodeReload(menuNodeModuleName);
                        })
                    }
                }

                BootboxWrapper.mainLogic.hideAll();
                ajax.loadModuleContentByUrl(Navigation.getCurrentUri());
            };

            let dataProcessorsDto            = new DataProcessorDto();
            dataProcessorsDto.url            = url;
            dataProcessorsDto.successMessage = successMessage;
            dataProcessorsDto.failMessage    = failMessage;
            dataProcessorsDto.callback       = callback;

            return dataProcessorsDto;
        },
        processorName: "Create folder"
    };

    public static settingsDashboardWidgetsVisibility: DataProcessorInterface = {
        makeCopyData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        },
        makeCreateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        },
        makeRemoveData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        },
        /**
         * data from all records must be sent at once
         * @param $baseElement {object}
         */
        makeUpdateData: function ($baseElement) {

            let table               = $($baseElement).closest('tbody');
            let modifiedSettingName = $($baseElement).find('.widget-name').text();

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
                let isChecked       = DomAttributes.isChecked(isCheckedInput);

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

            let ajaxData = {
                'all_rows_data': allRowsData
            };

            let successMessage = AbstractDataProcessor.messages.entityUpdateSuccess(SpecialAction.settingsDashboardWidgetsVisibility.processorName);
            let failMessage    = AbstractDataProcessor.messages.entityUpdateFail(SpecialAction.settingsDashboardWidgetsVisibility.processorName);

            let url = '/api/settings-dashboard/update-widgets-visibility';

            let dataProcessorsDto            = new DataProcessorDto();
            dataProcessorsDto.successMessage = successMessage;
            dataProcessorsDto.failMessage    = failMessage;
            dataProcessorsDto.url            = url;
            dataProcessorsDto.ajaxData       = ajaxData;

            return dataProcessorsDto
        },
        processorName: "Setting"
    };

    public static settingsFinancesCurrencyTable: DataProcessorInterface = {
        makeCopyData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        },
        makeCreateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        },
        makeUpdateData: function ($baseElement) {
            let name            = $($baseElement).find('.name').html();
            let symbol          = $($baseElement).find('.symbol').html();
            let multiplier      = $($baseElement).find('.multiplier').val();
            let isDefaultInput  = $($baseElement).find('.is-default').find('input');
            let isDefault       = DomAttributes.isChecked(isDefaultInput);

            let beforeUpdateState = $($baseElement).find('.before-update-state').val();

            let successMessage = AbstractDataProcessor.messages.entityUpdateSuccess(SpecialAction.settingsFinancesCurrencyTable.processorName);
            let failMessage    = AbstractDataProcessor.messages.entityUpdateFail(SpecialAction.settingsFinancesCurrencyTable.processorName);

            let url = '/api/settings-finances/update-currencies';

            let ajaxData = {
                'name'                : name,
                'symbol'              : symbol,
                'multiplier'          : multiplier,
                'is_default'          : isDefault,
                'before_update_state' : beforeUpdateState,
            };

            let dataProcessorsDto            = new DataProcessorDto();
            dataProcessorsDto.successMessage = successMessage;
            dataProcessorsDto.failMessage    = failMessage;
            dataProcessorsDto.url            = url;
            dataProcessorsDto.ajaxData       = ajaxData;

            return dataProcessorsDto;
        },
        makeRemoveData: function ($baseElement) {
            let name               = $($baseElement).find('.name').text();
            let url                = '/api/settings-finances/remove-currency/';
            let successMessage     = AbstractDataProcessor.messages.entityRemoveSuccess(SpecialAction.settingsFinancesCurrencyTable.processorName);
            let failMessage        = AbstractDataProcessor.messages.entityRemoveFail(SpecialAction.settingsFinancesCurrencyTable.processorName);

            let dataProcessorsDto                = new DataProcessorDto();
            dataProcessorsDto.successMessage     = successMessage;
            dataProcessorsDto.failMessage        = failMessage;
            dataProcessorsDto.url                = url + name;
            dataProcessorsDto.isDataTable        = false;
            dataProcessorsDto.useAjaxFailMessage = true;

            return dataProcessorsDto;
        },
        processorName: "Setting"
    };

    public static settingsFinancesCurrencyForm: DataProcessorInterface = {
        makeCopyData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        },
        makeRemoveData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        },
        makeUpdateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        },
        makeCreateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            let url                = '/page-settings';
            let successMessage     = AbstractDataProcessor.messages.entityCreatedRecordSuccess(SpecialAction.settingsFinancesCurrencyForm.processorName);
            let failMessage        = AbstractDataProcessor.messages.entityCreatedRecordFail(SpecialAction.settingsFinancesCurrencyForm.processorName);

            let dataProcessorsDto            = new DataProcessorDto();
            dataProcessorsDto.successMessage = successMessage;
            dataProcessorsDto.failMessage    = failMessage;
            dataProcessorsDto.url            = url;

            return dataProcessorsDto;
        },
        processorName: "Setting"
    };

    public static MyPaymentsBills: DataProcessorInterface =  {
        makeCopyData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeRemoveData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeUpdateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        },
        makeCreateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            let url                 = '/my-payments-bills';
            let successMessage     = AbstractDataProcessor.messages.entityCreatedRecordSuccess(SpecialAction.MyPaymentsBills.processorName);
            let failMessage        = AbstractDataProcessor.messages.entityCreatedRecordFail(SpecialAction.MyPaymentsBills.processorName);

            let dataProcessorsDto            = new DataProcessorDto();
            dataProcessorsDto.url            = url;
            dataProcessorsDto.successMessage = successMessage;
            dataProcessorsDto.failMessage    = failMessage;

            return dataProcessorsDto;
        },
        processorName: "Payment bill"
    };

    public static MyPaymentsBillsItems: DataProcessorInterface =  {
        makeCopyData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeRemoveData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeUpdateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        },
        makeCreateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            let url                = '/my-payments-bills';
            let successMessage     = AbstractDataProcessor.messages.entityCreatedRecordSuccess(SpecialAction.MyPaymentsBillsItems.processorName);
            let failMessage        = AbstractDataProcessor.messages.entityCreatedRecordFail(SpecialAction.MyPaymentsBillsItems.processorName);

            let dataProcessorsDto            = new DataProcessorDto();
            dataProcessorsDto.successMessage = successMessage;
            dataProcessorsDto.failMessage    = failMessage;
            dataProcessorsDto.url            = url;

            return dataProcessorsDto;
        },
        processorName: "Payment bill item"
    };
}
