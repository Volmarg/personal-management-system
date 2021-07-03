import * as $ from 'jquery';
import Navigation       from "../../../Navigation";
import ModulesStructure from "../../BackendStructure/ModulesStructure";
import RouterStructure  from "../../BackendStructure/RouterStructure";
import StringUtils      from "../../../utils/StringUtils";
import Application      from "../../../Application";
import BootstrapSelect  from "../../../../libs/bootstrap-select/BootstrapSelect";

export default class DirectoriesBasedWidget {

    /**
     * @description When using in upload directory - this will select currently
     *              opened directory for given module from the list
     */
    public selectCurrentModuleAndUploadDirOption(moduleSelectSelector: string, directorySelectSelector: string, forceSelectMainDirectory: boolean = false) {

        let moduleSelect            = $(moduleSelectSelector);
        let directorySelect         = $(directorySelectSelector);

        let getAttrs                = JSON.parse(Navigation.getCurrentGetAttrs());
        let directoryPath           = unescape(getAttrs.encodedSubdirectoryPath);
        let route                   = Navigation.getCurrentRoute();

        let moduleToSelect = '';

        // swap module
        if ( route === RouterStructure.MODULES_MY_FILES_PATH ) {
            moduleToSelect = ModulesStructure.MODULE_UPLOAD_DIR_FOR_FILES;
        } else if( route === RouterStructure.MODULES_MY_IMAGES_PATH ){
            moduleToSelect = ModulesStructure.MODULE_UPLOAD_DIR_FOR_IMAGES;
        }else if( route === RouterStructure.MODULES_MY_VIDEO_PATH ){
            moduleToSelect = ModulesStructure.MODULE_UPLOAD_DIR_FOR_VIDEOS;
        } else{
            Application.abort(`Unhandled route ${route}`);
        }

        moduleSelect.val(moduleToSelect);

        //swap dir
        let allOptgroups = $("optgroup");
        $.each(allOptgroups, (index, optgroup) => {
            let $optgroup = $(optgroup);
            $optgroup.addClass('d-none');
        });

        let optgroupForModule = directorySelect.find("optgroup[label^='" + moduleToSelect + "']");
        let options           = optgroupForModule.find('option');

        optgroupForModule.attr('class', '');

        if(
                StringUtils.isEmptyString(directoryPath)
            ||  forceSelectMainDirectory
        ){
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