import {FineUploader, UIOptions} from 'fine-uploader';
import DomElements               from "../../core/utils/DomElements";
import Selectize                 from "../selectize/Selectize";
import Tippy                     from "../tippy/Tippy";
import BootstrapNotify           from "../bootstrap-notify/BootstrapNotify";
import Loader                    from "../loader/Loader";
import Navigation                from "../../core/Navigation";
import AjaxEvents                from "../../core/ajax/AjaxEvents";
import DirectoriesBasedWidget    from "../../core/ui/Widgets/DirectoriesBased/DirectoriesBasedWidget";

require('fine-uploader/fine-uploader/fine-uploader.min.css');

/**
 * @description this class handles the upload via jquery by using the package:
 *              keep in mind that some of the strings/texts are hardcoded
 *              this is due to the fact that there is no logic to load translations from within js
 *              also the FineUpload uses it's own internal hardcoded translations
 *
 * @link https://www.npmjs.com/package/fine-uploader
 * @link https://docs.fineuploader.com/quickstart/03-setting_up_server.html
 * @link https://docs.fineuploader.com/api/events.html
 * @link https://docs.fineuploader.com/api/methods.html
 */
export default class FineUploaderService
{

    /**
     * @type FineUploader
     */
    private fineUploaderInstance = null;

    private bootstrapNotify = new BootstrapNotify();

    private selectize = new Selectize();

    private ajaxEvents = new AjaxEvents();

    private directoriesBasedWidget = new DirectoriesBasedWidget();

    private uploadedFilesNames = [];

    public static readonly selectors = {
        fineUploadPanelSelector       : '[data-is-fine-upload]',
        triggerManualUploadButton     : '.trigger-manual-upload',
        filenameEditButton            : '.qq-edit-filename-icon-selector',
        uploadList                    : '.qq-upload-list',
        fontawesomeUploadCancelButton : '.qq-upload-cancel-icon',
        fineUploadCancelButton        : '.qq-upload-cancel-selector',
        fineUploadRetryButton         : '.qq-upload-retry-selector',
        fontawesomeUploadRetryButton  : '.qq-retry-icon',
        tagsInputSelector             : 'input.tags',
        moduleSelectSelector          : 'select#module_and_directory_select_upload_module_dir',
        directorySelectSelector       : 'select#module_and_directory_select_subdirectory',
    }

    public static readonly attributes = {
        uploadEndpointUrl       : 'data-upload-endpoint-url',
        successFileUploadUrl    : 'data-success-file-upload-url',
        uniqueFileIdentifier    : 'data-unique-file-identifier',
        maxUploadFilesSizeBytes : 'data-max-upload-size-bytes',
        isInstanceInitialized   : 'data-fine-upload-is-initialized',
    }

    /**
     * @description will initialize the logic of the file uploader
     */
    public init(reloadPageAfterSuccessfulUpload = false): void
    {
        let _this                = this;
        let $allFineUploadPanels = $(FineUploaderService.selectors.fineUploadPanelSelector);

        if( DomElements.doElementsExists($allFineUploadPanels) ){
            $.each($allFineUploadPanels, (index, element) => {

                let $element = $(element);

                /**
                 * @description the instance is already active, prevent reactivating it
                 *              might block initializing multiple instances on the same page,
                 *              however such case should never ever happen,
                 */
                if( $element.is("[" + FineUploaderService.attributes.isInstanceInitialized + "]") ){
                    return;
                }

                let uploadEndpointUrl     = $element.attr(FineUploaderService.attributes.uploadEndpointUrl);
                let successFileUploadUrl  = $element.attr(FineUploaderService.attributes.successFileUploadUrl);

                let options = {
                   element: element,
                   request: {
                        endpoint: uploadEndpointUrl
                    },
                    chunking: {
                        enabled: false,
                            concurrent: {
                            enabled: false
                        },
                        success: {
                            endpoint: successFileUploadUrl
                        }
                    },
                    resume: {
                        enabled: true
                    },
                    autoUpload: false,
                    callbacks: {
                        /**
                         * @description called when the item becomes a candidate to the queue, is not yet in queue
                         *              returning false will reject the item from adding it to the queue, else true
                         *              files validations should be added explicitly here
                         */
                       onSubmit: (uploadedFileId) => {
                            let uploadedFileData           = _this.fineUploaderInstance.getUploads({id: uploadedFileId});
                            let uploadedFileName           = uploadedFileData['name'];
                            let uploadedFileSizeInBytes    = uploadedFileData['size'];
                            let maxUploadSizeBytes         = Number.parseInt($("[" + FineUploaderService.attributes.maxUploadFilesSizeBytes + "]").attr(FineUploaderService.attributes.maxUploadFilesSizeBytes));

                            if( this.uploadedFilesNames.includes(uploadedFileName) ){
                                this.bootstrapNotify.showRedNotification(`Duplicate: ${uploadedFileName}!`);
                                return false;
                            }

                            if( uploadedFileSizeInBytes > maxUploadSizeBytes ){
                                this.bootstrapNotify.showRedNotification(`Filesize is to big: ${uploadedFileName}!`);
                                return false;
                            }

                            return true;
                        },
                        /**
                         * @description triggered when file is added to the upload queue
                         */
                        onSubmitted: (uploadedFileId) => {
                            let uploadedFileData           = _this.fineUploaderInstance.getUploads({id: uploadedFileId});
                            let uploadedFileName           = uploadedFileData['name'];
                            let uniqueFileIdentifier       = uploadedFileData['uuid'];
                            let $listElementForCurrentFile = $('[title^="' + uploadedFileName + '"]').closest('li');

                            this.uploadedFilesNames.push(uploadedFileName);

                            /**
                             * @description binding the unique record identifier (generated by fine-upload) to the list
                             *              element so that the element can be later on easily tracked
                             */
                            $listElementForCurrentFile.attr(FineUploaderService.attributes.uniqueFileIdentifier, uniqueFileIdentifier);
                        },
                        /**
                         * @description id of the file that is removed from the upload queue
                         */
                        onCancel: (deletedFileId) => {
                            let uploadedFileData = _this.fineUploaderInstance.getUploads({id: deletedFileId});
                            let uploadedFileName = uploadedFileData['name'];

                            this.uploadedFilesNames = this.uploadedFilesNames.filter( (alreadyUploadedFileName) => {
                                return !(alreadyUploadedFileName === uploadedFileName);
                            })

                        },
                        /**
                         * @description triggered right a moment before when the `upload` process has started for single file
                         */
                        onUpload: (uploadedFileId) => {
                            Loader.showMainLoader();

                            let $listElementForCurrentFile = _this.getListElementForUploadedFileId(uploadedFileId);
                            let tags                       = $listElementForCurrentFile.find('.tags').val();

                            let uploadModuleDir                = $('#module_and_directory_select_upload_module_dir').val();
                            let targetDirectoryPathInModuleDir = $('#module_and_directory_select_subdirectory').val();

                            let additionalParams = {
                                tags                                          : tags,
                                upload_module_dir                             : uploadModuleDir,
                                subdirectory_target_path_in_module_upload_dir : targetDirectoryPathInModuleDir
                            };
                            _this.handleAdditionalParamsInRequest(additionalParams);
                        },
                        /**
                         * @description triggered when all of the files in upload queue are handled
                         */
                        onAllComplete: (successfullyUploadedFilesIds, failedUploadedFilesIds) => {

                            for(let fileId of successfullyUploadedFilesIds){
                                let $listElementForCurrentFile = _this.getListElementForUploadedFileId(fileId);
                                let uploadedFileData           = _this.fineUploaderInstance.getUploads({id: fileId});
                                let uploadedFileName           = uploadedFileData['name'];

                                this.uploadedFilesNames.splice(uploadedFileName);
                                $listElementForCurrentFile.fadeToggle(400);
                                setTimeout(() => {
                                    $listElementForCurrentFile.remove();
                                }, 600);
                            }

                            Loader.hideMainLoader();
                            if(reloadPageAfterSuccessfulUpload){
                                let afterReinitializeCallback = () => {
                                    _this.directoriesBasedWidget.selectCurrentModuleAndUploadDirOption(FineUploaderService.selectors.moduleSelectSelector, FineUploaderService.selectors.directorySelectSelector);
                                };
                                _this.ajaxEvents.loadModuleContentByUrl(Navigation.getCurrentUri(), undefined, false, afterReinitializeCallback);
                            }

                            if( 0 === failedUploadedFilesIds.length ){
                                this.bootstrapNotify.showGreenNotification("Upload finished with success");
                            }else{
                                this.bootstrapNotify.showRedNotification("Some of the files could not be uploaded");
                            }
                        }

                    }
                } as UIOptions;

                // the order is very important here, some logic must be added after the uploader has been initialized
                this.fineUploaderInstance = new FineUploader(options);
                this.handleManualUploadButton();
                $element.attr(FineUploaderService.attributes.isInstanceInitialized, "");
            })

            this.observerFilesList();
        }

    }

    /**
     * @description will add logic for handling the upload after manually clicking on the upload button
     *
     * @private
     */
    private handleManualUploadButton(): void
    {
        let _this = this;
        // if the upload panel exist then the upload button must be also there
        let $uploadButton = $(FineUploaderService.selectors.triggerManualUploadButton);
        if( !DomElements.doElementsExists($uploadButton) ){
            throw {
                "message"  : "Upload button does not exist for FineUploader template!",
                "selector" : FineUploaderService.selectors.triggerManualUploadButton
            }
        }

        $uploadButton.off('click');
        $uploadButton.on('click', () => {
            _this.fineUploaderInstance.uploadStoredFiles();
        });
    }

    /**
     * @description there is an issue where fontawesome icon steals the click for FineUpload action
     *              this method handles such case and passes the click further to the proper removal button
     */
    private bindRemoveFileFromQueueListOnIconRemovalClick($cancelButtonFontawesome: JQuery, $fineUploadCancelButton: JQuery)
    {
        $cancelButtonFontawesome.on('click', (event) => {
            $fineUploadCancelButton.trigger('click');
        })
    }

    /**
     * @description there is an issue where fontawesome icon steals the click for FineUpload action
     *              this method handles such case and passes the click further to the proper removal button
     */
    private bindRetryFileUploadInQueueListOnIconRetryClick($retryButtonFontawesome: JQuery, $fineUploadRetryButton: JQuery)
    {
        $retryButtonFontawesome.on('click', (event) => {
            $fineUploadRetryButton.trigger('click');
        })
    }

    /**
     * @description will observer the uploaded files list, allows to react on adding/changing element etc.
     */
    private observerFilesList(): void
    {
        let $uploadedFilesList = $(FineUploaderService.selectors.uploadList);
        let _this              = this;

        // watch for the list being updated
        let uploadedFilesListObserver = new MutationObserver( (mutations, observer) => {
            let newNodes = mutations[0].addedNodes as Array<HTMLElement>|NodeList;

            for(let listElement of newNodes as Array<HTMLElement>) {
                let $listElement    = $(listElement);
                let $nameEditButton = $listElement.find(FineUploaderService.selectors.filenameEditButton);
                let $inputElements  = $listElement.find('input');

                let $cancelButtonFontawesome = $listElement.find(FineUploaderService.selectors.fontawesomeUploadCancelButton);
                let $retryButtonFontawesome = $listElement.find(FineUploaderService.selectors.fontawesomeUploadRetryButton);

                let $fineUploadCancelButton  = $listElement.find(FineUploaderService.selectors.fineUploadCancelButton);
                let $fineUploadRetryButton   = $listElement.find(FineUploaderService.selectors.fineUploadRetryButton);

                $nameEditButton.trigger('click');
                $inputElements.blur(); // escape the focus as plugin itself keeps focus upon clicking edit

                _this.handleInitialFilenamesShowingInTheEditInputsByObservingMutations($listElement);
                _this.handleAddingSelectizeTagsForNewInputsByObservingMutations($listElement);
                _this.bindRemoveFileFromQueueListOnIconRemovalClick($cancelButtonFontawesome, $fineUploadCancelButton)
                _this.bindRetryFileUploadInQueueListOnIconRetryClick($retryButtonFontawesome, $fineUploadRetryButton)
                Tippy.init();
            }
        });

        uploadedFilesListObserver.observe($uploadedFilesList[0], {
            childList: true,
        })
    }

    /**
     * @description the plugin itself works the way that the name is being shown only when we first click
     *              on the edit button but since the input fields is now constantly visible and the edit
     *              button remains hidden - it needs to be `clicked` for each uploaded file
     *
     */
    private handleInitialFilenamesShowingInTheEditInputsByObservingMutations(listElement: JQuery<HTMLElement>): void
    {
        let $listElement    = $(listElement);
        let $nameEditButton = $listElement.find(FineUploaderService.selectors.filenameEditButton);
        let $inputElements  = $listElement.find('input');

        $nameEditButton.trigger('click');
        $inputElements.blur(); // escape the focus as plugin itself keeps focus upon clicking edit
    }

    /**
     * @description upon adding new file the new DOM row is inserted, but each new element need to have selectize tag logic initialized
     *
     */
    private handleAddingSelectizeTagsForNewInputsByObservingMutations(listElement: JQuery<HTMLElement>): void
    {
        let $listElement     = $(listElement);
        let $tagsInput       = $listElement.find(FineUploaderService.selectors.tagsInputSelector);
        let inputHtmlElement = $tagsInput[0];

        this.selectize.applyTagsSelectizeForSingleInput(inputHtmlElement);
    }

    /**
     * @description will add logic for handling the upload after manually clicking on the upload button
     *
     */
    private handleAdditionalParamsInRequest(additionalParams: Object)
    {
        this.fineUploaderInstance.setParams(additionalParams);
    }

    /**
     * @description will return DOM <li> element for uploaded file id
     */
    private getListElementForUploadedFileId(fileId: number): JQuery<HTMLElement>
    {
        let uploadedFileData           = this.fineUploaderInstance.getUploads({id: fileId});
        let uniqueFileIdentifier       = uploadedFileData['uuid'];
        let $listElementForCurrentFile = $("[" + FineUploaderService.attributes.uniqueFileIdentifier + "^='" + uniqueFileIdentifier + "']");

        return $listElementForCurrentFile;
    }

}