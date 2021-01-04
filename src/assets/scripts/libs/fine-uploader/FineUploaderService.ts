import {FineUploader, UIOptions} from 'fine-uploader';
import DomElements               from "../../core/utils/DomElements";

require('fine-uploader/fine-uploader/fine-uploader.min.css');

/**
 * @description this class handles the upload via jquery by using the package:
 * @link https://www.npmjs.com/package/fine-uploader
 */
export default class FineUploaderService
{

    public static readonly selectors = {
        fineUploadPanelSelector   : '[data-is-fine-upload]',
        triggerManualUploadButton : '.trigger-manual-upload',
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

}