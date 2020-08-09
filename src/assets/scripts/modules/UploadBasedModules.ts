import Loader           from "../libs/loader/Loader";
import DomElements      from "../core/utils/DomElements";
import DomAttributes    from "../core/utils/DomAttributes";
import Ajax             from "../core/ajax/Ajax";
import BootstrapNotify  from "../libs/bootstrap-notify/BootstrapNotify";

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

                if( DomAttributes.isDisabled($(clickedButton)) ){
                    return;
                }

                // bootbox
                bootbox.confirm({
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
                            Loader.toggleLoader();
                            $.ajax({
                                method  : Ajax.REQUEST_TYPE_POST,
                                url     : apiUrl,
                                data    : data
                            }).always((data) => {
                                Loader.toggleLoader();
                                // if there is code there also must be message so i dont check it
                                let code                = data['code'];
                                let message             = data['message'];
                                let reloadPage          = data['reload_page'];
                                let reloadMessage       = data['reload_message'];
                                let notification_type   = '';

                                if( undefined === code ){
                                    return;
                                }

                                if( code === 200 ){
                                    notification_type = 'success';

                                    window.setTimeout( () => {
                                        window.location.reload();
                                    }, 1000)

                                }else{
                                    notification_type = 'danger';
                                }

                                _this.bootstrapNotify.notify(message, notification_type);

                                if( reloadPage ){
                                    if( "" !== reloadMessage ){
                                        _this.bootstrapNotify.showBlueNotification(reloadMessage);
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