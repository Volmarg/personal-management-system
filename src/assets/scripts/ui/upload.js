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
                clearSelectionButton    : ".clear-selection"
            },
            id: {
                fileSelectButton        : "#file-upload-file-select",
                filesInput              : "#upload_form_file",
                currentFileSizeWrapper  : "#currentFileSizeWrapper",
                maxUploadSizeWrapper    : "#maxUploadSizeWrapper",
                submitButton            : "#upload_form_submit",
                selectedFilesList       : "#selectedFilesList",
                settings: {
                    subdirectoryRenameSubmit    : "#upload_subdirectory_rename_submit",
                    subdirectoryMoveDataSubmit  : "#upload_subdirectory_move_data_submit",
                    createSubdirectorySubmit    : "#upload_subdirectory_create_submit"
                }
            }
        },
        elements: {
          init: function () {
            this.currentSizeContainer      = $(ui.upload.selectors.classes.currentSizeContainer);
            this.clearSelectionButton      = $(ui.upload.selectors.classes.clearSelectionButton);
            this.fileSelectButton          = $(ui.upload.selectors.id.fileSelectButton);
            this.filesInput                = $(ui.upload.selectors.id.filesInput);
            this.currentFileSizeWrapper    = $(ui.upload.selectors.id.currentFileSizeWrapper);
            this.maxUploadSizeWrapper      = $(ui.upload.selectors.id.maxUploadSizeWrapper);
            this.submitButton              = $(ui.upload.selectors.id.submitButton);
            this.selectedFilesList         = $(ui.upload.selectors.id.selectedFilesList);

            this.settings.subdirectoryRenameSubmit        = $(ui.upload.selectors.id.settings.subdirectoryRenameSubmit);
            this.settings.subdirectoryMoveDataSubmit      = $(ui.upload.selectors.id.settings.subdirectoryMoveDataSubmit);
            this.settings.createSubdirectorySubmit        = $(ui.upload.selectors.id.settings.createSubdirectorySubmit);

          },
          currentSizeContainer    : '',
          filesInputResetButton   : '',
          fileSelectButton        : '',
          currentFileSizeWrapper  : '',
          maxUploadSizeWrapper    : '',
          submitButton            : '',
          clearSelectionButton    : '',
          selectedFilesList       : '',
            settings: {
                subdirectoryRenameSubmit    : "",
                subdirectoryMoveDataSubmit  : "",
                createSubdirectorySubmit    : ""
            }            
        },
        attributes:{
            maxUploadSize         : "data-max-upload-size"
        },
        vars: {
            init: function(){
                this.maxUploadSize = $(ui.upload.elements.maxUploadSizeWrapper).attr('data-max-upload-size');
            },
            filesTotalSizeBytes    : 0,
            filesTotalSizeMb       : 0,
            bytesInMb              : 1048576,
            filesNames             : [],
            maxUploadSize          : 1
        },
        init: function () {
            this.elements.init();
            this.vars.init();
            this.setSelectedFilesNamesAndSize();
            this.attachFilesInputResetEventToXButton();

            this.settings.addConfirmationBoxesToForms();
        },
        setSelectedFilesNamesAndSize: function () {
            let _this = this;

            this.elements.fileSelectButton.on('change', function () {
                let selectedFiles       = $(_this.elements.filesInput)[0].files;

                for (let x = 0; x <= selectedFiles.length - 1 ; x++){
                    _this.vars.filesNames.push(selectedFiles[x].name);
                    _this.vars.filesTotalSizeBytes += selectedFiles[x].size;

                    let filesListWrapper = $("<LI>");
                    $(filesListWrapper).append(selectedFiles[x].name);

                    $(_this.elements.selectedFilesList).append(filesListWrapper);
                }

                _this.vars.filesTotalSizeMb = Math.floor(_this.vars.filesTotalSizeBytes/_this.vars.bytesInMb);
                _this.appendFilesNamesAndSizeToDom();

            });

        },
        appendFilesNamesAndSizeToDom: function(){

            $(this.elements.currentSizeContainer).html(this.vars.filesTotalSizeMb);
            $(this.elements.currentFileSizeWrapper).attr("class","");

            if( this.vars.filesTotalSizeMb < this.vars.maxUploadSize ){
                $(this.elements.currentFileSizeWrapper).addClass("text-success");
            }else{
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

                $(_this.elements.submitButton).removeClass('disabled');

                $(_this.elements.currentFileSizeWrapper).attr("class","");

                _this.vars.filesTotalSizeBytes = 0;
            });

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