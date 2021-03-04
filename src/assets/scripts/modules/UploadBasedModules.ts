import Loader           from "../libs/loader/Loader";
import DomElements      from "../core/utils/DomElements";
import DomAttributes    from "../core/utils/DomAttributes";
import Ajax             from "../core/ajax/Ajax";
import BootstrapNotify  from "../libs/bootstrap-notify/BootstrapNotify";
import BootboxWrapper   from "../libs/bootbox/BootboxWrapper";
import AjaxResponseDto  from "../DTO/AjaxResponseDto";

export default class UploadBasedModules {

    private selectors = {
        classes: {
            widgetRemoveFolderClassName : '.widget-remove-folder',
        },
        data: {
            folderPathInUploadDir : 'data-folder-path-in-upload-dir',
            uploadModuleDir       : 'data-upload-module-dir'
        },
    };

    private placeholders = {
        uploadModuleDir : '{upload_module_dir}'
    };

    private apiUrl = {
        removeFolderViaPost: '/files/{upload_module_dir}/remove-subdirectory'
    };

    /**
     * @type BootstrapNotify
     */
    private bootstrapNotify = new BootstrapNotify();

    public init()
    {
        this.removeFolderOnFolderRemovalIconClick();
    }

    private removeFolderOnFolderRemovalIconClick() {
        let folderRemovalButton = $(this.selectors.classes.widgetRemoveFolderClassName);
        let _this               = this;

        if( DomElements.doElementsExists($(folderRemovalButton)) ){

            $(folderRemovalButton).on('click', (event) => {

                let clickedButton = $(event.target);

                if( DomAttributes.isDisabledClass($(clickedButton)) ){
                    return;
                }

                // bootbox
                BootboxWrapper.confirm({
                    message: 'Do You really want to remove this folder?',
                    backdrop: true,
                    callback: function (result) {
                        if (result) {
                            // confirmation logic
                            let subdirectoryPathInUploadDir = $(clickedButton).attr(_this.selectors.data.folderPathInUploadDir);
                            let uploadModuleDir             = $(clickedButton).attr(_this.selectors.data.uploadModuleDir);
                            let apiUrl                      = _this.apiUrl.removeFolderViaPost.replace(_this.placeholders.uploadModuleDir, uploadModuleDir);
                            let data = {
                                'subdirectory_current_path_in_module_upload_dir': subdirectoryPathInUploadDir,
                                'block_removal'                                 : true
                            };
                            Loader.toggleMainLoader();
                            $.ajax({
                                method  : Ajax.REQUEST_TYPE_POST,
                                url     : apiUrl,
                                data    : data
                            }).always((data) => {
                                Loader.toggleMainLoader();

                                let ajaxResponseDto = AjaxResponseDto.fromArray(data);

                                // should not happen, but that legacy check was here
                                if( !ajaxResponseDto.isCodeSet() ){
                                    return;
                                }

                                if( ajaxResponseDto.isSuccessCode() ){
                                    _this.bootstrapNotify.showGreenNotification(ajaxResponseDto.message);

                                    window.setTimeout( () => {
                                        window.location.reload();
                                    }, 1000)

                                }else{
                                    _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                                }


                                if( ajaxResponseDto.reloadPage ){
                                    if( ajaxResponseDto.isReloadMessageSet() ){
                                        _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                                    }
                                    location.reload();
                                }
                            });
                        }
                    }
                });
            });
        }
    }
}