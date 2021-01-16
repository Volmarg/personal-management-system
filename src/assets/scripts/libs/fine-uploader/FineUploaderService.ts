import {FineUploader, UIOptions} from 'fine-uploader';
import DomElements               from "../../core/utils/DomElements";

require('fine-uploader/fine-uploader/fine-uploader.min.css');

/**
 * @description this class handles the upload via jquery by using the package:
 * @link https://www.npmjs.com/package/fine-uploader
 * @link https://docs.fineuploader.com/quickstart/03-setting_up_server.html
 */
export default class FineUploaderService
{

    public static readonly selectors = {
        fineUploadPanelSelector       : '[data-is-fine-upload]',
        triggerManualUploadButton     : '.trigger-manual-upload',
        filenameEditButton            : '.qq-edit-filename-icon-selector',
        uploadList                    : '.qq-upload-list',
        fontawesomeUploadCancelButton : '.qq-upload-cancel-icon',
        fineUploadCancelButton        : '.qq-upload-cancel-selector'
    }

    public static readonly attributes = {
        uploadEndpointUrl    : 'data-upload-endpoint-url',
        successFileUploadUrl : 'data-success-file-upload-url',
    }

    /**
     * @description will initialize the logic of the file uploader
     */
    public init(): void
    {
        let $allFineUploadPanels = $(FineUploaderService.selectors.fineUploadPanelSelector);

        if( DomElements.doElementsExists($allFineUploadPanels) ){
            $.each($allFineUploadPanels, (index, element) => {

                let $element = $(element);

                let uploadEndpointUrl     = $element.attr(FineUploaderService.attributes.uploadEndpointUrl);
                let successFileUploadUrl  = $element.attr(FineUploaderService.attributes.successFileUploadUrl);

                let options = {
                   element: element,
                   request: {
                        endpoint: uploadEndpointUrl
                    },
                    chunking: {
                        enabled: true,
                            concurrent: {
                            enabled: true
                        },
                        success: {
                            endpoint: successFileUploadUrl
                        }
                    },
                    resume: {
                        enabled: true
                    },
                    autoUpload: false
                } as UIOptions;

                // the order is very important here, some logic must be added after the uploader has been initialized
                let fineUploaderInstance = new FineUploader(options);
                this.handleManualUploadButton(fineUploaderInstance);

            })

            this.handleInitialFilenamesShowingInTheEditInputs();
        }

    }

    /**
     * @description will add logic for handling the upload after manually clicking on the upload button
     *
     * @param fineUploaderInstance
     * @private
     */
    private handleManualUploadButton(fineUploaderInstance: FineUploader): FineUploader
    {
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
            fineUploaderInstance.uploadStoredFiles();
        });

        return fineUploaderInstance;
    }

    /**
     * @description the plugin itself works the way that the name is being shown only when we first click
     *              on the edit button but since the input fields is now constantly visible and the edit
     *              button remains hidden - it needs to be `clicked` for each uploaded file
     *
     * @private
     */
    private handleInitialFilenamesShowingInTheEditInputs(): void
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
                let $fineUploadCancelButton  = $listElement.find(FineUploaderService.selectors.fineUploadCancelButton);

                $nameEditButton.trigger('click');
                $inputElements.blur(); // escape the focus as plugin itself keeps focus upon clicking edit

                console.log({$cancelButtonFontawesome, $fineUploadCancelButton});
                _this.bindRemoveFileFromQueueListOnIconRemovalClick($cancelButtonFontawesome, $fineUploadCancelButton)
            }
        });

        uploadedFilesListObserver.observe($uploadedFilesList[0], {
            childList: true,
        })
    }

    /**
     * @description there is an issue where fontawesome icon steals the click for FineUpload action
     *              this method handles such case and passes the click further to the proper removal button
     * @private
     */
    private bindRemoveFileFromQueueListOnIconRemovalClick($cancelButtonFontawesome: JQuery, $fineUploadCancelButton: JQuery)
    {
        // todo: fix it - fontawesome is stealing focus
        $cancelButtonFontawesome.on('click', (event) => {
            event.preventDefault();
            $fineUploadCancelButton.trigger('click');
        })
    }

}