var bootbox = require('bootbox');

import 'lightgallery';
import 'lightgallery/modules/lg-thumbnail'
import 'lightgallery/modules/lg-zoom'
import 'lightgallery/dist/css/lightgallery.min.css'
import 'lightgallery/dist/css/lg-transitions.min.css'

export default (function () {

    if (typeof window.gallery === 'undefined') {
        window.gallery = {};
    }

    gallery.lightgallery = {

        selectors: {
            ids: {
                lightboxGallery         : '#aniimated-thumbnials',
                trashButton             : '#lightgallery_trash_button',
                pencilButton            : '#lightgallery_pencil_button',
                saveButton              : '#lightgallery_save_button',
                transferButton          : '#lightgallery_transfer_button',
                downloadButton          : '#lg-download',
                fileTransferButton      : '#lightgallery_transfer_button'
            },
            classes: {
                upperToolbar            : '.lg-toolbar',
                thumbnails              : '.lg-thumb',
                nextButton              : '.lg-next ',
                downloadButton          : '.lg-download',
                currentViewedImage      : '.lg-current',
                imagePreviewWrapper     : '.lg-inner',
                currentViewedFilename   : '.lg-sub-html',
                galleryMainWrapper      : '.lg',
            }
        },
        messages: {
            imageRemovalConfirmation    : "Do You want to remove this image?",
            imageNameEditConfirmation   : "Do You want to rename this image?",
        },
        apiUrls: {
            fileRemoval                 : "/upload/action/remove-file",
            fileRename                  : "/upload/action/rename-file",
        },
        keys: {
            KEY_FILE_FULL_PATH          : "file_full_path"
        },
        init: function () {
            this.initGallery();
            this.addPlugins();
            this.handleGalleryEvents();
        },
        initGallery: function(){
            if( $(this.selectors.ids.lightboxGallery).length > 0 ){

                $(this.selectors.ids.lightboxGallery).lightGallery({
                    thumbnail: true
                });

            }
        },
        addPlugins: function(){
            this.addPluginRemoveFile();
            this.addPluginRenameFile();
            this.addPluginTransferFile();
        },
        addPluginRemoveFile(){
            let lightboxGallery = $(this.selectors.ids.lightboxGallery);
            let _this           = this;
            var filePath        = '';

            // Handling removing images
            lightboxGallery.on('onAfterOpen.lg', function () {
                let trashIcon = '<a class=\"lg-icon\" id="lightgallery_trash_button"><i class="fa fa-trash" aria-hidden="true"></i></a>';
                $(_this.selectors.classes.upperToolbar).append(trashIcon);

                let trashButton     = $(_this.selectors.ids.trashButton);
                let downloadButton  = $(_this.selectors.ids.downloadButton);

                // Button click
                $(trashButton).click(() => {

                    // Confirmation box
                    bootbox.confirm({
                        message: _this.messages.imageRemovalConfirmation,
                        backdrop: true,
                        callback: function (result) {
                            if (result) {

                                var filePath = $(downloadButton).attr('href');

                                let data = {
                                    "file_full_path":  filePath
                                };

                                //  File removal ajax
                                $.ajax({
                                   method: "POST",
                                   url:     _this.apiUrls.fileRemoval,
                                   data:    data,
                                   success: (data) => {
                                        bootstrap_notifications.notify(data, 'success');

                                        // Rebuilding thumbnails etc
                                       _this.removeImageWithMiniature(filePath);

                                    },
                                }).fail((data) => {
                                    bootstrap_notifications.notify(data.responseText, 'danger')
                                });

                            }
                        }
                    });

                })

            });

        },
        addPluginRenameFile(){
            let lightboxGallery = $(this.selectors.ids.lightboxGallery);
            let _this           = this;
            var filePath        = '';

            // Handling editing name
            lightboxGallery.on('onAfterOpen.lg', function (event) {
                let pencilIcon = '<a class=\"lg-icon\" id="lightgallery_pencil_button"><i class="fas fa-pencil"></i></a>';
                let saveIcon   = '<a class=\"lg-icon d-none\" id="lightgallery_save_button"><i class="far fa-save"></i></a>';

                $(_this.selectors.classes.upperToolbar).append(saveIcon);
                $(_this.selectors.classes.upperToolbar).append(pencilIcon);

                let pencilButton     = $(_this.selectors.ids.pencilButton);
                let saveButton       = $(_this.selectors.ids.saveButton);
                let downloadButton   = $(_this.selectors.ids.downloadButton);


                $(saveButton).click( () => {

                    // Confirmation box
                    bootbox.confirm({
                        message: _this.messages.imageNameEditConfirmation,
                        backdrop: true,
                        callback: function (result) {
                            if (result) {

                                let filePath = $(downloadButton).attr('href');
                                let newFileName = $(_this.selectors.classes.currentViewedFilename).text();

                                let data = {
                                    file_new_name   :  newFileName,
                                    file_full_path  :  filePath
                                };

                                $.ajax({
                                    method: "POST",
                                    url:     _this.apiUrls.fileRename,
                                    data:    data,
                                    success: (data) => {
                                        bootstrap_notifications.notify(data, 'success');
                                    },
                                }).fail((data) => {
                                    bootstrap_notifications.notify(data.responseText, 'danger')
                                });

                            }
                        }
                    });

                });

                // Handles toggling blocking everything in gallery besides filename area
                $(pencilButton).click(() => {

                    let galleryMainWrapper  = $('.lg');
                    let allGalleryElements  = $('.lg *');
                    let textHolder          = $(_this.selectors.classes.currentViewedFilename);
                    let isNameEdited        = false;
                    let enabledElements     = [
                        textHolder,
                        pencilButton,
                        saveButton,
                        _this.selectors.classes.upperToolbar
                    ];
                    let elementsToToggleBlurr = [
                        _this.selectors.classes.imagePreviewWrapper,
                        _this.selectors.classes.thumbnails
                    ];

                    // Toggle disabling all gui elements while editing - do not allow user to leave while editing is on
                    $.each(allGalleryElements, (index, element) => {
                        $(element).toggleClass('disabled');
                    });

                    // Toggle blurring images while editing - additional change to blocking user from leaving
                    $(galleryMainWrapper).toggleClass('blurred');

                    $.each(enabledElements, (index, element) => {
                        $(element).removeClass('disabled');
                    });

                    if( $(galleryMainWrapper).hasClass('blurred') ){

                        $.each(elementsToToggleBlurr, (index, element) => {
                            $(element).css({filter: "blur(3px)"});
                        });

                        $(saveButton).removeClass('d-none');
                        isNameEdited = true;

                    }else{

                        $.each(elementsToToggleBlurr, (index, element) => {
                            $(element).css({filter: "blur(0px)"});
                        });

                        $(saveButton).addClass('d-none');
                        isNameEdited = false;
                        
                    }

                    //Toggle content editable
                    if(isNameEdited){
                        $(textHolder).attr('contenteditable','true');
                    }else{
                        $(textHolder).removeAttr('contenteditable');
                    }

                })

            });

        },
        addPluginTransferFile: function () {

            let lightboxGallery = $(this.selectors.ids.lightboxGallery);
            let _this           = this;
            var filePath        = '';

            // Handling editing name
            lightboxGallery.on('onAfterOpen.lg', function (event) {
                let transferIcon = '<a class=\"lg-icon\" id="lightgallery_transfer_button"><i class="fas fa-random file-transfer"></i></a>';
                $(_this.selectors.classes.upperToolbar).append(transferIcon);

                _this.attachCallDialogForDataTransfer()
                // TODO: add removing moved item from views
            });

        },
        attachCallDialogForDataTransfer: function () {
            let button  = $(this.selectors.ids.fileTransferButton);
            let _this   = this;

            if( $(button).length > 0 ){

                $(button).on('click', (event) => {
                    let clickedButton           = $(event.target);
                    let buttonsToolbar          = $(clickedButton).closest(_this.selectors.classes.upperToolbar);
                    let galleryMainWrapper      = $(clickedButton).closest(_this.selectors.classes.galleryMainWrapper);

                    let fileCurrentPath         = $(buttonsToolbar).find(_this.selectors.classes.downloadButton).attr('href');
                    let fileName                = $(galleryMainWrapper).find(_this.selectors.classes.currentViewedFilename).text();

                    let callback = function (){
                        _this.removeImageWithMiniature(fileCurrentPath);
                    };

                    dialogs.ui.dataTransfer.buildDataTransferDialog(fileName, fileCurrentPath, 'My Images', callback);
                });

            }
        },
        removeImageWithMiniature: function(filePath){
            let thumbnails               = $(this.selectors.classes.thumbnails);
            let removedImageMiniature    = $(thumbnails).find("[src^='" + filePath + "']");
            let nextButton               = $(this.selectors.classes.nextButton);
            let currentViewedImage       = $(this.selectors.classes.currentViewedImage);
            let htmlGallery              = $(this.selectors.ids.lightboxGallery);

            $(removedImageMiniature).parent('div').remove();
            $(currentViewedImage).remove();
            $(nextButton).click();
            this.initGallery();
            $(htmlGallery).find('[href^="' + filePath + '"]').remove();
        },
        handleGalleryEvents: function (){
            let _this           = this;
            let lightboxGallery = $(this.selectors.ids.lightboxGallery);

            lightboxGallery.on('onAfterSlide.lg', function () {
                _this.handleMovingBetweenImagesAfterImageRemoval(lightboxGallery);
            });

            lightboxGallery.on('onCloseAfter.lg',function() {
                _this.handleRebuildingEntireGaleryWhenClosingIt(lightboxGallery);
            });

        },
        handleMovingBetweenImagesAfterImageRemoval: function(lightboxGallery){

            // Handling skipping removed images - because of above - this is dirty solution but works
            let downloadButton  = $(this.selectors.ids.downloadButton);
            var filePath        = $(downloadButton).attr('href');
            let isImagePresent  = ( lightboxGallery.find("[src^='" + filePath + "']").length > 0 );
            let _this           = this;

            if( !isImagePresent ){
                $('.lg-next').click();
            }

            // Handling proper slideback when image was removed - dirty like above
            let prevButton       = $('button.lg-prev');

            $(prevButton).on('click', () => {
                let downloadButton  = $(_this.selectors.ids.downloadButton);
                var filePathOnClick = $(downloadButton).attr('href');

                if (filePathOnClick === filePath){
                    lightboxGallery.data('lightGallery').goToPrevSlide();
                }
            });
        },
        handleRebuildingEntireGaleryWhenClosingIt: function(lightboxGallery){
            // Handling rebuilding entire gallery when closing - needed because plugin somehows stores data in it's object not in storage
            lightboxGallery.data('lightGallery').destroy(true);
            this.initGallery();
        }
    }

}());


