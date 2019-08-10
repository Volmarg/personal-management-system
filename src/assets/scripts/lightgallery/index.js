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
                downloadButton          : '#lg-download',
            },
            classes: {
                upperToolbar            : '.lg-toolbar',
                thumbnails              : '.lg-thumb',
                nextButton              : '.lg-next ',
                currentViewedImage      : '.lg-current'
            }
        },
        messages: {
            imageRemovalConfirmation    : "Do You want to remove this image?"
        },
        apiUrls: {
            fileRemoval                 : "/upload/action/remove-file"
        },
        keys: {
            KEY_FILE_FULL_PATH          : "file_full_path"
        },
        init: function () {

            this.initGallery();
            this.addPlugins();

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
        },
        addPluginRemoveFile(){
            let lightboxGallery = $(this.selectors.ids.lightboxGallery);
            let _this           = this;
            var filePath        = '';

            // Handling rebuilding entire gallery when closing - needed because plugin somehows stores data in it's object not in storage
            lightboxGallery.on('onCloseAfter.lg',function(event, index, fromTouch, fromThumb){
                try{
                    lightboxGallery.data('lightGallery').destroy(true);
                    _this.initGallery();
                }catch(ex){

                };
            });

            // Handling skipping removed images - because of above - this is dirty solution but works

            lightboxGallery.on('onAfterSlide.lg', function(event){
                let downloadButton  = $(_this.selectors.ids.downloadButton);
                var filePath        = $(downloadButton).attr('href');
                let isImagePresent  = ( lightboxGallery.find("[src^='" + filePath + "']").length > 0 );

                if( !isImagePresent ){
                    console.log('this.image.was.removed');
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

            });

            // Handling removing images
            lightboxGallery.on('onAfterOpen.lg', function (event) {
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
                                       let thumbnails               = $(_this.selectors.classes.thumbnails);
                                       let removedImageMiniature    = $(thumbnails).find("[src^='" + filePath + "']");
                                       let nextButton               = $(_this.selectors.classes.nextButton);
                                       let currentViewedImage       = $(_this.selectors.classes.currentViewedImage);
                                       let htmlGallery              = $(_this.selectors.ids.lightboxGallery);

                                       $(removedImageMiniature).parent('div').remove();
                                       $(currentViewedImage).remove();
                                       $(nextButton).click();
                                       _this.initGallery();
                                       $(htmlGallery).find('[href^="' + filePath + '"]').remove();

                                    },
                                }).fail((data) => {
                                    bootstrap_notifications.notify(data.responseText, 'danger')
                                });

                            }
                        }
                    });

                })

            });

        }
    }

}());


