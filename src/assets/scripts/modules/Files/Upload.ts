import * as $ from "jquery";
import Selectize from "../../libs/selectize/Selectize";

export default class Upload {

    /**
     * @type Selectize
     */
    private selectize = new Selectize();

    /**
     * @type Object
     */
    private static selectors = {
        classes: {
            currentSizeContainer    : ".selected-files-size",
            clearSelectionButton    : ".clear-selection",
            selectedFilesCount      : ".selected-files-count"
        },
        id: {
            fileSelectButton                 : "#file-upload-file-select",
            filesInput                       : "#upload_form_file",
            currentFileSizeWrapper           : "#currentFileSizeWrapper",
            currentUploadedFilesCountWrapper : "#currentUploadedFilesCount",
            maxUploadSizeWrapper             : "#maxUploadSizeWrapper",
            submitButton                     : "#upload_form_submit",
            selectedFilesList                : "#selectedFilesList",
            maxAllowedFilesUploadCount       : "#maxAllowedFilesUploadCount",
            uploadTable                      : "#uploadTable",
        }
    };

    /**
     * @type Object
     */
    private elements = {
        /**
         * Initialize DOM elements
         */
        init: function () {
            this.currentSizeContainer               = $(Upload.selectors.classes.currentSizeContainer);
            this.clearSelectionButton               = $(Upload.selectors.classes.clearSelectionButton);
            this.selectedFilesCount                 = $(Upload.selectors.classes.selectedFilesCount);
            this.fileSelectButton                   = $(Upload.selectors.id.fileSelectButton);
            this.filesInput                         = $(Upload.selectors.id.filesInput);
            this.currentFileSizeWrapper             = $(Upload.selectors.id.currentFileSizeWrapper);
            this.currentUploadedFilesCountWrapper   = $(Upload.selectors.id.currentUploadedFilesCountWrapper);
            this.maxUploadSizeWrapper               = $(Upload.selectors.id.maxUploadSizeWrapper);
            this.submitButton                       = $(Upload.selectors.id.submitButton);
            this.selectedFilesList                  = $(Upload.selectors.id.selectedFilesList);
            this.maxAllowedFilesUploadCount         = $(Upload.selectors.id.maxAllowedFilesUploadCount);
        },
        currentSizeContainer             : <JQuery> null,
        filesInputResetButton            : <JQuery> null,
        fileSelectButton                 : <JQuery> null,
        filesInput                       : <JQuery> null,
        currentFileSizeWrapper           : <JQuery> null,
        currentUploadedFilesCountWrapper : <JQuery> null,
        selectedFilesCount               : <JQuery> null,
        maxUploadSizeWrapper             : <JQuery> null,
        submitButton                     : <JQuery> null,
        clearSelectionButton             : <JQuery> null,
        selectedFilesList                : <JQuery> null,
        maxAllowedFilesUploadCount       : <JQuery> null
    };

    /**
     * @type Object
     */
    private static attributes = {
        maxUploadSize              : "data-max-upload-size",
        maxAllowedFilesUploadCount : "data-max-allowed-files-count"
    };

    /**
     * @type Object
     */
    private vars = {
        /**
         * Initialize vars
         */
        init: () => {
            this.vars.maxUploadSize          = parseInt($(this.elements.maxUploadSizeWrapper).attr(Upload.attributes.maxUploadSize));
            this.vars.maxUploadedFilesCount  = parseInt($(this.elements.maxAllowedFilesUploadCount).attr(Upload.attributes.maxAllowedFilesUploadCount));
            this.vars.uploadTable            = $(Upload.selectors.id.uploadTable);
            // @ts-ignore
            this.vars.uploadDataTable        = $(this.vars.uploadTable).DataTable();
        },
        filesTotalSizeBytes    : 0,
        filesTotalSizeMb       : 0,
        bytesInMb              : 1048576,
        filesNames             : [],
        maxUploadSize          : 1,
        maxUploadedFilesCount  : 1,
        uploadTable            : null,
        uploadDataTable        : null
    };

    /**
     * @description Initialize Upload logic
     */
    public init(): void
    {
        this.elements.init();
        this.vars.init();

        this.handleFilesSelectOnChangeEvent();
        this.attachFilesInputResetEventToXButton();
        this.attachEventsToFormSubmitButton();
    };

    /**
     * @description Handles event on files select change (when something was selected to upload
     */
    private handleFilesSelectOnChangeEvent(): void
    {
        let _this = this;

        this.elements.fileSelectButton.on('change', function () {
            // @ts-ignore
            let selectedFiles = $(_this.elements.filesInput)[0].files;

            //for reset as form resets its internal files list when picking new data
            _this.appendFilesSizeToDom();
            _this.setSelectedFilesCount();

            _this.setSelectedFilesSize(selectedFiles);
            _this.setSelectedFilesCount();
            _this.handleFillingDatatable(selectedFiles);
        });
    };

    /**
     * @description Will set size of selected files to upload
     *
     * @param selectedFiles
     */
    private setSelectedFilesSize(selectedFiles: Array<string>): void
    {
        this.vars.filesTotalSizeBytes = 0;

        for (let x = 0; x <= selectedFiles.length - 1 ; x++){
            // @ts-ignore
            this.vars.filesTotalSizeBytes += selectedFiles[x].size;
        }

        this.vars.filesTotalSizeMb = Math.floor(this.vars.filesTotalSizeBytes/this.vars.bytesInMb);
        this.appendFilesSizeToDom();
    };

    /**
     * @description Will set count of files selected to upload
     */
    private setSelectedFilesCount(): void
    {
        // @ts-ignore
        let selectedFiles                    = $(this.elements.filesInput)[0].files;
        let selectedFilesCount               = selectedFiles.length;
        let currentUploadedFilesCountWrapper = this.elements.currentUploadedFilesCountWrapper;

        $(Upload.selectors.classes.selectedFilesCount).html(selectedFilesCount);

        if( selectedFilesCount < this.vars.maxUploadedFilesCount ){

            if( $(this.elements.currentFileSizeWrapper).hasClass("text-danger") ){
                return;
            }

            $(currentUploadedFilesCountWrapper).removeClass('text-success text-danger');
            $(currentUploadedFilesCountWrapper).addClass("text-success");
        }else{
            $(currentUploadedFilesCountWrapper).attr("text-success text-danger");
            $(currentUploadedFilesCountWrapper).addClass("text-danger");
            $(this.elements.submitButton).addClass("disabled");
        }
    };

    /**
     * @description Appends counted files size to the DOM
     */
    private appendFilesSizeToDom():void
    {
        $(this.elements.currentSizeContainer).html(String(this.vars.filesTotalSizeMb));

        if( this.vars.filesTotalSizeMb < this.vars.maxUploadSize ){

            if(
                    $(this.elements.currentFileSizeWrapper).hasClass("text-danger")
                &&  0 !== this.vars.filesTotalSizeMb
            ){ //something is blocking upload
                return;
            }

            $(this.elements.currentFileSizeWrapper).removeClass('text-success text-danger');
            $(this.elements.currentFileSizeWrapper).addClass("text-success");
            $(this.elements.submitButton).removeClass("disabled");
        }else{
            $(this.elements.currentFileSizeWrapper).removeClass('text-success text-danger');
            $(this.elements.currentFileSizeWrapper).addClass("text-danger");
            $(this.elements.submitButton).addClass("disabled");
        }
    };

    /**
     * @description Attaches logic on reset button [X]
     */
    private attachFilesInputResetEventToXButton(): void
    {
        let _this = this;

        $(this.elements.clearSelectionButton).on('click', function(){
            $(_this.elements.filesInput).val("");

            $(_this.elements.selectedFilesList).html("");
            $(_this.elements.currentSizeContainer).html("0");
            $(_this.elements.selectedFilesCount).html("0");

            $(_this.elements.submitButton).removeClass('disabled');

            $(_this.elements.currentFileSizeWrapper).removeClass('text-danger');
            $(_this.elements.currentFileSizeWrapper).addClass("text-success");
            $(_this.elements.currentUploadedFilesCountWrapper).removeClass('text-danger');
            $(_this.elements.currentUploadedFilesCountWrapper).addClass("text-success");

            _this.vars.filesTotalSizeBytes = 0;
            _this.vars.uploadDataTable.clear().draw();
        });

    };

    /**
     * @description Handles logic of showing selected files data in the table
     */
    private handleFillingDatatable(selectedFiles: Array<string>): void
    {
        this.cleaUploadTable();

        for (let x = 0; x <= selectedFiles.length - 1 ; x++){

            //@ts-ignore
            let fullFileName             = selectedFiles[x].name;

            let filenameRegex            = /((.*)\.([^.]+))?$/;
            let matches                  = filenameRegex.exec(fullFileName);

            let fileNameWithoutExtension = matches[2];
            let fileExtension            = matches[3];

            let inputTagsString          = this.buildInput(x, 'tag', 'tags');
            let inputFileNameString      = this.buildInput(x, 'fileName', 'form-control', fileNameWithoutExtension);
            let inputFileExtensionString = this.buildInput(x, 'fileExtension', 'disabled form-control', fileExtension);

            this.vars.uploadDataTable.row.add([
                x,
                inputFileNameString,
                inputFileExtensionString,
                inputTagsString
            ]).draw( false );

        }

        this.selectize.applyTagsSelectize();
    };

    /**
     * @description Builds and returns input string element
     *
     * @param id
     * @param prefix
     * @param classes
     * @param value
     * @param dataValue
     */
    private buildInput(id, prefix, classes = '', value = '', dataValue = '{}'): string
    {
        return `<input class="` + classes + `" 
                            id="` + prefix+id + `" 
                            name="upload_table[` + prefix + id + `]" 
                            value="` + value + `" 
                            data-value="` + dataValue + `"/>`;
    };

    /**
     * @description Handles logic on submitting form
     */
    private attachEventsToFormSubmitButton(): void
    {
        let $submitButton = $(this.elements.submitButton);
        let $form         = $submitButton.closest('form');
        let _this         = this;

        $submitButton.on('click', function(event) {
            event.preventDefault();

            let formHtmlElement = <HTMLFormElement> $form[0];
            let isFormValid     = formHtmlElement.checkValidity();

            if( !isFormValid ){
                formHtmlElement.reportValidity();
                return;
            }

            _this.vars.uploadTable.DataTable().destroy();
            $form.submit();
            _this.vars.uploadTable.DataTable();
        })
    };

    /**
     * @description Cleans up the table
     */
    private cleaUploadTable(): void
    {
        this.vars.uploadDataTable.clear().draw();
    }

}