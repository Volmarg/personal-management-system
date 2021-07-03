import AbstractDataProcessor    from "./AbstractDataProcessor";
import DataProcessorInterface   from "./DataProcessorInterface";
import StringUtils              from "../../utils/StringUtils";
import Navigation               from "../../Navigation";
import Ajax                     from "../../ajax/Ajax";
import DomAttributes            from "../../utils/DomAttributes";
import DataProcessorDto         from "../../../DTO/DataProcessorDto";
import BootboxWrapper           from "../../../libs/bootbox/BootboxWrapper";
import AjaxEvents               from "../../ajax/AjaxEvents";
import Sidebars                 from "../../sidebar/Sidebars";
import SystemInformationReader  from "../../SystemInformationReader";
import UiUtils                  from "../../utils/UiUtils";

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
                let ajax       = new Ajax();
                let ajaxEvents = new AjaxEvents();

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

                BootboxWrapper.hideAll();
                ajaxEvents.loadModuleContentByUrl(Navigation.getCurrentUri());
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

    public static RenameFolder: DataProcessorInterface = {
        makeCopyData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeRemoveData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeUpdateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            let url             = '/files/actions/rename-folder';
            let successMessage  = AbstractDataProcessor.messages.entityCreatedRecordSuccess(SpecialAction.RenameFolder.processorName);
            let failMessage     = AbstractDataProcessor.messages.entityCreatedRecordFail(SpecialAction.RenameFolder.processorName);

            let uploadModuleDir              = $baseElement.find('.upload-module-dir select').val();
            let currentPathInUploadModuleDir = $baseElement.find('.current-path-in-module-upload-dir select').val();
            let subdirectoryNewName          = $baseElement.find('.subdirectory-new-name input').val();

            let ajaxData = {
                subdirectory_current_path_in_module_upload_dir  : currentPathInUploadModuleDir,
                upload_module_dir                               : uploadModuleDir,
                subdirectory_new_name                           : subdirectoryNewName
            };

            let callback = (dataCallbackParams) => {
                let ajax       = new Ajax();
                let ajaxEvents = new AjaxEvents();

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

                BootboxWrapper.hideAll();
                ajaxEvents.loadModuleContentByUrl(Navigation.getCurrentUri());
            };

            let dataProcessorsDto            = new DataProcessorDto();
            dataProcessorsDto.url            = url;
            dataProcessorsDto.successMessage = successMessage;
            dataProcessorsDto.failMessage    = failMessage;
            dataProcessorsDto.callback       = callback;
            dataProcessorsDto.ajaxData       = ajaxData;

            return dataProcessorsDto;
        },
        makeCreateData: function (): DataProcessorDto | null {
            return null
        },
        processorName: "Rename folder"
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

            let dataProcessorsDto                 = new DataProcessorDto();
            dataProcessorsDto.successMessage      = successMessage;
            dataProcessorsDto.failMessage         = failMessage;
            dataProcessorsDto.url                 = url;
            dataProcessorsDto.ajaxData            = ajaxData;
            dataProcessorsDto.reloadModuleContent = false;

            return dataProcessorsDto
        },
        processorName: "Setting"
    };

    /**
     * @description handles updating the module lock in system settings
     */
    public static settingsModuleLock: DataProcessorInterface = {
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

            let table              = $($baseElement).closest('tbody');
            let modifiedModuleName = $($baseElement).find('.module-name').text().trim();

            let allRows       = $(table).find('tr');
            let allRowsData   = [];

            if( 0 === table.length || 0 === allRows.length ){
                throw({
                    "message": "Either no form or rows were found for modules lock update",
                    "entity" : "Settings",
                    "method" : "settingsModuleLock::makeUpdateData"
                });
            }

            let callbacksForMenuElements = [];
            $.each(allRows, (index, row) => {

                let name            = $(row).find('.module-name').text().trim();
                let isCheckedInput  = $(row).find('.is-locked').find('input');
                let isChecked       = DomAttributes.isChecked(isCheckedInput);

                // make update before the state is changed in GUI so take opposite state for modified setting
                if( modifiedModuleName === name ){
                    isChecked = !isChecked;
                }

                let rowData = {
                    'name'     : name,
                    'isLocked' : isChecked,
                };

                let callbackForMenuElement = () => {
                    if( SystemInformationReader.isSystemLocked() ){ // don't manipulate menu on unlocked system
                        if(isChecked){
                            Sidebars.hideMenuElementForMenuNodeModuleName(name);
                        }else{
                            Sidebars.showMenuElementForMenuNodeModuleName(name);
                        }
                    }
                }

                callbacksForMenuElements.push(callbackForMenuElement);
                allRowsData.push(rowData);
            });

            let ajaxData = {
                'all_rows_data': allRowsData
            };

            let successMessage = AbstractDataProcessor.messages.entityUpdateSuccess(SpecialAction.settingsModuleLock.processorName);
            let failMessage    = AbstractDataProcessor.messages.entityUpdateFail(SpecialAction.settingsModuleLock.processorName);

            let url = '/api/settings-module/update-lock';

            let dataProcessorsDto                 = new DataProcessorDto();
            dataProcessorsDto.successMessage      = successMessage;
            dataProcessorsDto.failMessage         = failMessage;
            dataProcessorsDto.url                 = url;
            dataProcessorsDto.ajaxData            = ajaxData;
            dataProcessorsDto.reloadModuleContent = false;
            dataProcessorsDto.callbackAfter       = () => {
                for(let callback of callbacksForMenuElements){
                    callback();
                }
            };

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

            let dataProcessorsDto                 = new DataProcessorDto();
            dataProcessorsDto.successMessage      = successMessage;
            dataProcessorsDto.failMessage         = failMessage;
            dataProcessorsDto.url                 = url;
            dataProcessorsDto.ajaxData            = ajaxData;
            dataProcessorsDto.reloadModuleContent = false;

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

    /**
     *  @description special action handled for tags updating
     */
    public static UpdateTags: DataProcessorInterface =  {
        makeCopyData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeRemoveData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeUpdateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            let tags           = $($baseElement).find('.tags-input-wrapper input').val();
            let filePath       = $($baseElement).find('[data-tag-update-file-current-location]').data('tagUpdateFileCurrentLocation');

            let successMessage = AbstractDataProcessor.messages.entityUpdateSuccess(SpecialAction.UpdateTags.processorName);
            let failMessage    = AbstractDataProcessor.messages.entityUpdateFail(SpecialAction.UpdateTags.processorName);

            let ajaxUpdateUrl  = Ajax.getUrlForPathName('api_files_tagger_update_tags');

            let ajaxData = {
                'name'          : name,
                'tags'          : tags,
                'file_full_path': filePath,
            };

            let dataProcessorsDto            = new DataProcessorDto();
            dataProcessorsDto.successMessage = successMessage;
            dataProcessorsDto.failMessage    = failMessage;
            dataProcessorsDto.url            = ajaxUpdateUrl;
            dataProcessorsDto.ajaxData       = ajaxData;

            return dataProcessorsDto;
        },
        makeCreateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        },
        processorName: "Tags"
    };

    public static CopyDataBetweenFolders: DataProcessorInterface = {
        makeCopyData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeRemoveData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            return null;
        }, makeUpdateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto | null {
            let url             = '/files/actions/move-or-copy-data-between-folders';
            let successMessage  = AbstractDataProcessor.messages.entityCreatedRecordSuccess(SpecialAction.RenameFolder.processorName);
            let failMessage     = AbstractDataProcessor.messages.entityCreatedRecordFail(SpecialAction.RenameFolder.processorName);

            let currentUploadModuleDir                   = $baseElement.find('.current-upload-module-dir select').val();
            let targetUploadModuleDir                    = $baseElement.find('.target-upload-module-dir select').val() as string;
            let subdirectoryCurrentPathInModuleUploadDir = $baseElement.find('.subdirectory-current-path-in-module-upload-dir select').val();
            let subdirectoryTargetPathInModuleUploadDir  = $baseElement.find('.subdirectory-target-path-in-module-upload-dir select').val();
            let moveFolder                               = $baseElement.find('.move-folder-checkbox-wrapper input').prop('checked');
            let reloadedMenuNode                         = $baseElement.find('.reloaded-menu-node').val() as string;

            let ajaxData = {
                current_upload_module_dir                      : currentUploadModuleDir,
                target_upload_module_dir                       : targetUploadModuleDir,
                subdirectory_current_path_in_module_upload_dir : subdirectoryCurrentPathInModuleUploadDir,
                subdirectory_target_path_in_module_upload_dir  : subdirectoryTargetPathInModuleUploadDir,
                move_folder                                    : moveFolder,
                url_called_from                                : decodeURI(location.pathname),
            };

            let callback = (dataCallbackParams) => {
                let ajax = new Ajax();
                if(
                        null !== dataCallbackParams
                    &&  "undefined" !== typeof dataCallbackParams
                ){
                    let menuNodeModuleName           = dataCallbackParams.menuNodeModuleName;
                    let menuNodeModulesNamesToReload = dataCallbackParams.menuNodeModulesNamesToReload;

                    if( !StringUtils.isEmptyString(menuNodeModuleName)){
                        ajax.singleMenuNodeReload(menuNodeModuleName, false);
                    }else if( !StringUtils.isEmptyString(menuNodeModulesNamesToReload) ){
                        let arrayOfMenuNodeModuleNames = JSON.parse(menuNodeModulesNamesToReload);
                        $.each(arrayOfMenuNodeModuleNames, function(index, menuNodeModuleName){
                            ajax.singleMenuNodeReload(menuNodeModuleName, false);
                        })
                    }
                }else{
                    if( !StringUtils.isEmptyString(reloadedMenuNode) ){
                        ajax.singleMenuNodeReload(reloadedMenuNode, false);
                    }
                }

                BootboxWrapper.hideAll();
            };

            let dataProcessorsDto            = new DataProcessorDto();
            dataProcessorsDto.url            = url;
            dataProcessorsDto.successMessage = successMessage;
            dataProcessorsDto.failMessage    = failMessage;
            dataProcessorsDto.callback       = callback;
            dataProcessorsDto.ajaxData       = ajaxData;

            return dataProcessorsDto;
        },
        makeCreateData: function (): DataProcessorDto | null {
            return null
        },
        processorName: "Copy data between folders"
    };

}
