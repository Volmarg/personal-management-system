import * as $ from 'jquery';
import 'datatables';
import 'datatables.net-select';
var bootbox = require('bootbox');

export default (function () {

    if (typeof window.ui === 'undefined') {
        window.ui = {};
    }

    ui.upload = {
        messages: {
            settings: {
                upload_subdirectory_rename_submit       : "Do You really want to rename this folder?",
                upload_subdirectory_move_data_submit    : "Do You really want to move data between these two folders?",
                upload_subdirectory_create_submit       : "Do You want to create folder with this name?"
            }
        },
        selectors: {
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
                settings: {
                    subdirectoryRenameSubmit    : "#upload_subdirectory_rename_submit",
                    subdirectoryMoveDataSubmit  : "#upload_subdirectory_move_data_submit",
                    createSubdirectorySubmit    : "#upload_subdirectory_create_submit"
                }
            }
        },
        elements: {
          init: function () {
            this.currentSizeContainer               = $(ui.upload.selectors.classes.currentSizeContainer);
            this.clearSelectionButton               = $(ui.upload.selectors.classes.clearSelectionButton);
            this.selectedFilesCount                 = $(ui.upload.selectors.classes.selectedFilesCount);
            this.fileSelectButton                   = $(ui.upload.selectors.id.fileSelectButton);
            this.filesInput                         = $(ui.upload.selectors.id.filesInput);
            this.currentFileSizeWrapper             = $(ui.upload.selectors.id.currentFileSizeWrapper);
            this.currentUploadedFilesCountWrapper   = $(ui.upload.selectors.id.currentUploadedFilesCountWrapper);
            this.maxUploadSizeWrapper               = $(ui.upload.selectors.id.maxUploadSizeWrapper);
            this.submitButton                       = $(ui.upload.selectors.id.submitButton);
            this.selectedFilesList                  = $(ui.upload.selectors.id.selectedFilesList);
            this.maxAllowedFilesUploadCount         = $(ui.upload.selectors.id.maxAllowedFilesUploadCount);

            this.settings.subdirectoryRenameSubmit        = $(ui.upload.selectors.id.settings.subdirectoryRenameSubmit);
            this.settings.subdirectoryMoveDataSubmit      = $(ui.upload.selectors.id.settings.subdirectoryMoveDataSubmit);
            this.settings.createSubdirectorySubmit        = $(ui.upload.selectors.id.settings.createSubdirectorySubmit);

          },
          currentSizeContainer          : '',
          filesInputResetButton         : '',
          fileSelectButton              : '',
          currentFileSizeWrapper        : '',
          selectedFilesCount            : '',
          maxUploadSizeWrapper          : '',
          submitButton                  : '',
          clearSelectionButton          : '',
          selectedFilesList             : '',
          maxAllowedFilesUploadCount    : '',
            settings: {
                subdirectoryRenameSubmit    : "",
                subdirectoryMoveDataSubmit  : "",
                createSubdirectorySubmit    : ""
            }            
        },
        attributes:{
            maxUploadSize              : "data-max-upload-size",
            maxAllowedFilesUploadCount : "data-max-allowed-files-count"
        },
        vars: {
            init: function(){
                this.maxUploadSize          = $(ui.upload.elements.maxUploadSizeWrapper).attr(ui.upload.attributes.maxUploadSize);
                this.maxUploadedFilesCount  = $(ui.upload.elements.maxAllowedFilesUploadCount).attr(ui.upload.attributes.maxAllowedFilesUploadCount);
                this.uploadTable            = $(ui.upload.selectors.id.uploadTable);
                this.uploadDataTable        = $(this.uploadTable).DataTable();
            },
            filesTotalSizeBytes    : 0,
            filesTotalSizeMb       : 0,
            bytesInMb              : 1048576,
            filesNames             : [],
            maxUploadSize          : 1,
            maxUploadedFilesCount  : 1,
            uploadTable            : null,
            uploadDataTable        : null
        },
        init: function () {
            this.elements.init();
            this.vars.init();

            this.handleFilesSelectOnChangeEvent();
            this.attachFilesInputResetEventToXButton();
            this.attachEventsToFormSubmitButton();

            this.settings.addConfirmationBoxesToForms();
        },
        handleFilesSelectOnChangeEvent: function(){
            let _this = this;

            this.elements.fileSelectButton.on('change', function () {
                let selectedFiles = $(_this.elements.filesInput)[0].files;

                //for reset as form resets its internal files list when picking new data
                _this.appendFilesSizeToDom();
                _this.setSelectedFilesCount();

                _this.setSelectedFilesSize(selectedFiles);
                _this.setSelectedFilesCount();
                _this.handleFillingDatatable(selectedFiles);
            });
        },
        setSelectedFilesSize: function (selectedFiles) {

            this.vars.filesTotalSizeBytes = 0;

            for (let x = 0; x <= selectedFiles.length - 1 ; x++){
                this.vars.filesTotalSizeBytes += selectedFiles[x].size;
            }

            this.vars.filesTotalSizeMb = Math.floor(this.vars.filesTotalSizeBytes/this.vars.bytesInMb);
            this.appendFilesSizeToDom();
        },
        setSelectedFilesCount: function () {
            let selectedFiles                    = $(this.elements.filesInput)[0].files;
            let selectedFilesCount               = selectedFiles.length;
            let currentUploadedFilesCountWrapper = this.elements.currentUploadedFilesCountWrapper;

            $(this.selectors.classes.selectedFilesCount).html(selectedFilesCount);

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
        },
        appendFilesSizeToDom: function(){

            $(this.elements.currentSizeContainer).html(this.vars.filesTotalSizeMb);

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

        },
        attachFilesInputResetEventToXButton: function(){
            let _this = this;

            $(this.elements.clearSelectionButton).on('click', function(){
                $(_this.elements.filesInput).val("");

                $(_this.elements.selectedFilesList).html("");
                $(_this.elements.currentSizeContainer).html(0);
                $(_this.elements.filesTotalSizeBytes).html(0);
                $(_this.elements.selectedFilesCount).html(0);

                $(_this.elements.submitButton).removeClass('disabled');

                $(_this.elements.currentFileSizeWrapper).removeClass('text-danger');
                $(_this.elements.currentFileSizeWrapper).addClass("text-success");
                $(_this.elements.currentUploadedFilesCountWrapper).removeClass('text-danger');
                $(_this.elements.currentUploadedFilesCountWrapper).addClass("text-success");

                _this.vars.filesTotalSizeBytes = 0;
                _this.vars.uploadDataTable.clear().draw();
            });

        },
        handleFillingDatatable: function(selectedFiles){
            this.cleaUploadTable();

            for (let x = 0; x <= selectedFiles.length - 1 ; x++){

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

            ui.widgets.selectize.applyTagsSelectize();

        },
        buildInput: function(id, prefix, classes = '', value = '', dataValue = '{}'){
            return `<input class="` + classes + `" 
                            id="` + prefix+id + `" 
                            name="upload_table[` + prefix + id + `]" 
                            value="` + value + `" 
                            data-value="` + dataValue + `"/>`;
        },
        attachEventsToFormSubmitButton: function(){
            let $submitButton = $(this.elements.submitButton);
            let $form         = $submitButton.closest('form');
            let _this         = this;

            $submitButton.on('click', function(event) {
                event.preventDefault();

                let isFormValid = $form[0].checkValidity();

                if( !isFormValid ){
                    $form[0].reportValidity();
                    return;
                }

                _this.vars.uploadTable.DataTable().destroy();
                $form.submit();
                _this.vars.uploadTable.DataTable();
            })
        },
        cleaUploadTable: function(){
            this.vars.uploadDataTable.clear().draw();
        },
        settings: {
            addConfirmationBoxesToForms: function(){
                let _this = this;
                let submitButtons = [
                    ui.upload.elements.settings.createSubdirectorySubmit,
                    ui.upload.elements.settings.subdirectoryMoveDataSubmit,
                    ui.upload.elements.settings.subdirectoryRenameSubmit,
                ];

                $.each(submitButtons, (index, submitButton) => {

                    $(submitButton).on('click', (event) => {
                        let clickedButton   = $(event.target);
                        let buttonId        = $(clickedButton).attr('id');
                        let form            = $(clickedButton).closest('form');

                        let message         = ui.upload.messages.settings[buttonId];

                        event.preventDefault();

                        _this.callBootBoxWithFormSubmitionOnAccept(message, form);
                    });

                });

            },
            callBootBoxWithFormSubmitionOnAccept: function (message, $form) {

                bootbox.confirm({
                    message: message,
                    backdrop: true,
                    callback: function (result) {
                        if (result) {
                            $($form).submit();
                        }
                    }
                });

            }
        }
    }

}());