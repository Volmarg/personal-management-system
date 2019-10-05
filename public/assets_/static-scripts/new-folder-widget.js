/**
 * this should NOT contain any main logic of system
 *  only some smaller function passed for example in json configs etc.
 */

var createFolderWidget = {

    /**
     * Selecting option corresponding to the category on which im at atm.
     * raw js while jquerry works within webpack scripts only
     */
    selectCurrentModuleAndUploadDirOptionForQuickCreateFolder: function () {

        let moduleSelect            = document.querySelector("select#upload_subdirectory_create_upload_module_dir");
        let directorySelect         = document.querySelector("select#upload_subdirectory_create_subdirectory_target_path_in_module_upload_dir");

        let moduleSelectOptions     = document.querySelectorAll("select#upload_subdirectory_create_upload_module_dir option");
        let directorySelectOptions  = document.querySelectorAll("select#upload_subdirectory_create_subdirectory_target_path_in_module_upload_dir option");

        let getAttrs                = JSON.parse(TWIG_GET_ATTRS);
        let directoryPath           = unescape(getAttrs.encoded_subdirectory_path)
        let route                   = TWIG_ROUTE;

        let moduleToSelect = '';

        // swap module
        if ( route === 'modules_my_files' ) {
            moduleToSelect = 'files';
        } else if( route === 'modules_my_images' ){
            moduleToSelect = 'images';
        }

        moduleSelect.setAttribute("value", moduleToSelect);
        moduleSelectOptions.forEach(function (option, index) {
            if (option.getAttribute("value") == moduleToSelect) {
                option.setAttribute("selected", "true");
            }
        });

        //swap dir
        let allOptgroups      = directorySelect.querySelectorAll("optgroup");
        allOptgroups.forEach(function (optgroup, index) {
            optgroup.setAttribute('class', 'd-none');
        });

        let optgroupForModule = directorySelect.querySelectorAll("optgroup[label^='" + moduleToSelect + "']")[0];
        let options           = optgroupForModule.childNodes;

        optgroupForModule.setAttribute('class', '');

        // first remove "selected" attr from currently auto selected option
        directorySelectOptions.forEach((option, index) => {
            let isSelected = option.getAttribute('selected');

            if( isSelected ){
                option.removeAttribute("selected");
                return false;
            }

        });

        if( "null" === directoryPath ){ // main dir
            // main dir is always first on list so I just select it
            directoryPath = options[0].getAttribute("value");
        }

        directorySelect.setAttribute("value", directoryPath);
        options.forEach(function (option, index) {
            option.setAttribute('class','');
            if (option.getAttribute("value") == directoryPath) {
                option.setAttribute("selected", "true");
            }
        });

    }

}
