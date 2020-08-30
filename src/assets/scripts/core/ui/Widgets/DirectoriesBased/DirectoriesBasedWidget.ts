import * as $ from 'jquery';
import Navigation       from "../../../Navigation";
import ModulesStructure from "../../BackendStructure/ModulesStructure";
import RouterStructure  from "../../BackendStructure/RouterStructure";
import StringUtils      from "../../../utils/StringUtils";
import Application      from "../../../Application";
import BootstrapSelect from "../../../../libs/bootstrap-select/BootstrapSelect";

export default class DirectoriesBasedWidget {

    /**
     * @description When using in upload directory - this will select currently
     *              opened directory for given module from the list
     */
    public selectCurrentModuleAndUploadDirOptionForQuickCreateFolder(moduleSelectSelector: string, directorySelectSelector: string) {

        let moduleSelect            = $(moduleSelectSelector);
        let directorySelect         = $(directorySelectSelector);

        let moduleSelectOptions     = moduleSelect.find('option');
        let directorySelectOptions  = document.querySelectorAll("select#upload_subdirectory_create_subdirectory_target_path_in_module_upload_dir option");

        let getAttrs                = JSON.parse(Navigation.getCurrentGetAttrs());
        let directoryPath           = unescape(getAttrs.encoded_subdirectory_path);
        let route                   = Navigation.getCurrentRoute();

        let moduleToSelect = '';

        // swap module
        if ( route === RouterStructure.MODULES_MY_FILES_PATH ) {
            moduleToSelect = ModulesStructure.MODULE_UPLOAD_DIR_FOR_FILES;
        } else if( route === RouterStructure.MODULES_MY_IMAGES_PATH ){
            moduleToSelect = ModulesStructure.MODULE_UPLOAD_DIR_FOR_IMAGES;
        } else{
            Application.abort(`Unhandled route ${route}`);
        }

        moduleSelect.val(moduleToSelect);
        $.each(moduleSelectOptions, (index, option) => {
            let $option = $(option);
            if ($option.val() == moduleToSelect) {
                $option.attr("selected", "true");
            }
        });

        //swap dir
        let allOptgroups = $("optgroup");
        $.each(allOptgroups, (index, optgroup) => {
            let $optgroup = $(optgroup);
            $optgroup.addClass('d-none');
        });

        let optgroupForModule = directorySelect.find("optgroup[label^='" + moduleToSelect + "']");
        let options           = optgroupForModule.find('option');

        optgroupForModule.attr('class', '');

        // first remove "selected" attr from currently auto selected option
        $.each(directorySelectOptions, (index, option) => {
            let $option    = $(option);
            let isSelected = $option.attr('selected');

            if( isSelected ){
                $option.removeAttr("selected");
                return false;
            }
        });

        if( StringUtils.isEmptyString(directoryPath) ){ // main dir
            // main dir is always first on list so I just select it
            let $firstOption = $(options[0]);
            directoryPath    = $firstOption.val() as string;
        }

        directorySelect.val(directoryPath);
        $.each(options, (index, option) => {
            let $option = $(option);

            $option.attr('class','');

            if ($option.val() == directoryPath) {
                $option.attr("selected", "true");
            }
        });

        //handle bootstrap-select
        if( BootstrapSelect.isSelectpicter(directorySelect) ){
            BootstrapSelect.refreshSelector(directorySelect);
        }

    }
}