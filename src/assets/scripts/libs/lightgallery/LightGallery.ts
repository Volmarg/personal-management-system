import BootstrapNotify      from "../../libs/bootstrap-notify/BootstrapNotify";
import Loader               from "../../libs/loader/Loader";
import DomAttributes        from "../../core/utils/DomAttributes";
import ShuffleWrapper       from "../shuffle/ShuffleWrapper";
import Ajax                 from "../../core/ajax/Ajax";
import Navigation           from "../../core/Navigation";
import DomElements          from "../../core/utils/DomElements";
import StringUtils          from "../../core/utils/StringUtils";
import DataTransferDialogs  from "../../core/ui/Dialogs/DataTransferDialogs";
import TagManagementDialogs from "../../core/ui/Dialogs/TagManagementDialogs";
import BootboxWrapper       from "../bootbox/BootboxWrapper";
import AjaxResponseDto      from "../../DTO/AjaxResponseDto";
import AjaxEvents           from "../../core/ajax/AjaxEvents";
import AbstractAjax         from "../../core/ajax/AbstractAjax";

var bootbox = require('bootbox');

import * as $ from 'jquery';
import 'lightgallery';
import 'lightgallery/modules/lg-thumbnail'
import 'lightgallery/modules/lg-zoom'
import 'lightgallery/dist/css/lightgallery.min.css'
import 'lightgallery/dist/css/lg-transitions.min.css'

export default class LightGallery {

    /**
     * @type Object
     */
    private selectors = {
        ids: {
            trashButton        : '#lightgallery_trash_button',
            pencilButton       : '#lightgallery_pencil_button',
            saveButton         : '#lightgallery_save_button',
            transferButton     : '#lightgallery_transfer_button',
            downloadButton     : '#lg-download',
            fileTransferButton : '#lightgallery_transfer_button',
            tagsManageButton   : '#lightgallery_manage_tags_button'
        },
        classes: {
            upperToolbar             : '.lg-toolbar',
            thumbnails               : '.lg-thumb',
            nextButton               : '.lg-next ',
            downloadButton           : '.lg-download',
            currentViewedImage       : '.lg-current',
            imagePreviewWrapper      : '.lg-inner',
            currentViewedFilename    : '.lg-sub-html',
            galleryMainWrapper       : '.lg',
            textHolderCaption        : '.caption-text-holder',
            massActionRemoveButton   : '.mass-action-lightgallery-remove-images',
            massActionTransferButton : '.mass-action-lightgallery-transfer-images',
            massActionButtons        : '.mass-action-lightgallery-button',
            lightboxGallery          : '.lightbox-gallery',
        },
        other: {
            checkboxForImage        : '.checkbox-circle input',
            checkboxForImageWrapper :'.checkbox-circle'
        }
    };

    /**
     * @type Object
     */
    private messages = {
        imageRemovalConfirmation  : "Do You want to remove this image/s?",
        imageNameEditConfirmation : "Do You want to rename this image?",
    };

    /**
     * @type Object 
     */
    private keys = {
        KEY_FILE_FULL_PATH : "file_full_path"
    };

    /**
     * @type Object
     */
    private vars = {
        currentFileName : '',
        moduleRoute     : 'modules_my_images'
    };

    /**
     * @type BootstrapNotify
     */
    private bootstrapNotify = new BootstrapNotify();

    /**
     * @type ShuffleWrapper
     */
    private shuffler = new ShuffleWrapper();

    /**
     * @type Ajax
     */
    private ajax = new Ajax();

    /**
     * @type AjaxEvents
     */
    private ajaxEvents = new AjaxEvents();

    /**
     * @type DataTransferDialogs
     */
    private dataTransferDialogs = new DataTransferDialogs();

    /**
     * @type TagManagementDialogs
     */
    private tagManagementDialogs = new TagManagementDialogs();

    public init() {
        this.shuffler.init();
        this.initGallery();
        this.addPlugins();
        this.preventCheckboxEventTriggering();
        this.handleGalleryEvents();
        this.handleCheckboxForImageInGalleryView();
        this.preventSettingMasonryGalleryAsAbsolute();
    };

    private initGallery(){
        let $lightboxGallery = $(this.selectors.classes.lightboxGallery);

        if( DomElements.doElementsExists($lightboxGallery) ){
            //@ts-ignore
            $lightboxGallery.lightGallery({
                thumbnail: true
            });
        }
    };

    public reinitGallery(){
        let $lightboxGallery = $(this.selectors.classes.lightboxGallery);

        if( DomElements.doElementsExists($lightboxGallery) ){
            $lightboxGallery.data('lightGallery').destroy(true);
            this.initGallery();
        }
    };

    private addPlugins(){
        this.addPluginRemoveFile();
        this.addPluginRenameFile(); 
        this.addPluginTransferFile();
        this.addPluginManageFileTags();
    };

    private addPluginRemoveFile(){
        let lightboxGallery = $(this.selectors.classes.lightboxGallery);
        let _this           = this;

        // Handling removing images
        lightboxGallery.on('onAfterOpen.lg', function () {
            let trashIcon = '<a class=\"lg-icon\" id="lightgallery_trash_button"><i class="fa fa-trash" remove-record aria-hidden="true"></i></a>';
            $(_this.selectors.classes.upperToolbar).append(trashIcon);

            let trashButton = $(_this.selectors.ids.trashButton);

            // Button click
            $(trashButton).click(() => {
                let downloadButton  = $(_this.selectors.ids.downloadButton);
                let filePath        = $(downloadButton).attr('href');

                let callback = function(){
                    // Rebuilding thumbnails etc
                    _this.removeImageWithMiniature(filePath);
                    _this.handleClosingGalleryIfThereAreNoMoreImages();
                };

                BootboxWrapper.confirm({
                    message: _this.messages.imageRemovalConfirmation,
                    backdrop: true,
                    callback: function (result) {
                        if (result) {
                            //  File removal ajax
                            _this.ajaxEvents.callAjaxFileRemovalForFilePath([filePath], callback);
                        }
                    }
                });

            })

        });

    };

    private addPluginRenameFile(){
        let lightboxGallery = $(this.selectors.classes.lightboxGallery);
        let _this           = this;

        // Handling editing name
        lightboxGallery.on('onAfterOpen.lg', function (event) {
            let pencilIcon = '<a class=\"lg-icon\" id="lightgallery_pencil_button"><i class="fas fa-pencil-alt"></i></a>';
            let saveIcon   = '<a class=\"lg-icon d-none\" id="lightgallery_save_button"><i class="far fa-save"></i></a>';

            $(_this.selectors.classes.upperToolbar).append(saveIcon);
            $(_this.selectors.classes.upperToolbar).append(pencilIcon);

            let pencilButton     = $(_this.selectors.ids.pencilButton);
            let saveButton       = $(_this.selectors.ids.saveButton);
            let downloadButton   = $(_this.selectors.ids.downloadButton);

            $(saveButton).click( () => {

                // Confirmation box
                BootboxWrapper.confirm({
                    message: _this.messages.imageNameEditConfirmation,
                    backdrop: true,
                    callback: function (result) {

                        if (result) {

                            let filePath    = $(downloadButton).attr('href');
                            let newFileName = $(_this.selectors.classes.currentViewedFilename).text();

                            let data = {
                                file_new_name   :  newFileName,
                                file_full_path  :  filePath.replace("/", "")
                            };

                            Loader.toggleMainLoader();
                            $.ajax({
                                method:  Ajax.REQUEST_TYPE_POST,
                                url:     AbstractAjax.API_URLS.fileRename,
                                data:    data,
                                success: (data) => {

                                    // Info: filePath must also be updated
                                    if( !StringUtils.areTheSame(_this.vars.currentFileName, newFileName) ){
                                        let newFilePath = filePath.replace(_this.vars.currentFileName, newFileName);
                                        let links       = $("[href^='" + filePath + "']");
                                        let images      = $("[data-src-real^='" + filePath + "']");
                                        let dataSrcDivs = $('[data-src^="' + filePath + '"]');

                                        $(images).attr('data-src-real', newFilePath);
                                        $(images).attr('alt', newFileName);
                                        $(links).attr('href', newFilePath);
                                        $(dataSrcDivs).attr('data-src', newFilePath);

                                        _this.handleGalleryCaptionOnFileRename(_this.vars.currentFileName, newFileName);
                                        _this.vars.currentFileName  = $(_this.selectors.classes.currentViewedFilename).text();
                                    }

                                    _this.bootstrapNotify.showGreenNotification(data);
                                },
                            }).fail((data) => {
                                _this.bootstrapNotify.showRedNotification(data.responseText);
                            }).always(() => {
                                Loader.toggleMainLoader();
                            });

                        }
                    }
                });

            });

            // Handles toggling blocking everything in gallery besides filename area
            $(pencilButton).click(() => {

                _this.vars.currentFileName  = $(_this.selectors.classes.currentViewedFilename).text();

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
                    //@ts-ignore
                    DomAttributes.unsetDisabledClass($(element));
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

    };

    private addPluginTransferFile() {

        let lightboxGallery = $(this.selectors.classes.lightboxGallery);
        let _this           = this;

        // Handling editing name
        lightboxGallery.on('onAfterOpen.lg', function (event) {
            let transferIcon = '<a class=\"lg-icon\" id="lightgallery_transfer_button"><i class="fas fa-random file-transfer"></i></a>';
            $(_this.selectors.classes.upperToolbar).append(transferIcon);

            _this.attachCallDialogForDataTransfer();
        });

    };

    private addPluginManageFileTags() {

        let lightboxGallery = $(this.selectors.classes.lightboxGallery);
        let _this           = this;

        // Handling managing tags
        lightboxGallery.on('onAfterOpen.lg', function (event) {
            let tagsManageIcon = '<a class=\"lg-icon\" id="lightgallery_manage_tags_button"><i class="fas fa-tags manage-file-tags"></i></a>';
            $(_this.selectors.classes.upperToolbar).append(tagsManageIcon);

            _this.attachCallDialogForTagsManagement()
        });

    };

    private attachCallDialogForDataTransfer() {
        let button = $(this.selectors.ids.fileTransferButton);
        let _this  = this;

        if( DomElements.doElementsExists(button) ){

            $(button).on('click', (event) => {
                let clickedButton   = $(event.target);
                let buttonsToolbar  = $(clickedButton).closest(_this.selectors.classes.upperToolbar);
                let fileCurrentPath = $(buttonsToolbar).find(_this.selectors.classes.downloadButton).attr('href');

                let callback = function (){
                    _this.removeImageWithMiniature(fileCurrentPath);
                    _this.handleClosingGalleryIfThereAreNoMoreImages();
                    BootboxWrapper.hideAll();
                };

                let escapedFileCurrentPath = ( fileCurrentPath.indexOf('/') === 0 ? fileCurrentPath.replace("/", "") : fileCurrentPath ) ;

                _this.dataTransferDialogs.buildDataTransferDialog([escapedFileCurrentPath], 'My Images', callback);
            });

        }
    };

    private attachCallDialogForTagsManagement() {
        let button = $(this.selectors.ids.tagsManageButton);
        let _this  = this;

        if( DomElements.doElementsExists(button) ){

            $(button).on('click', (event) => {
                let clickedButton           = $(event.target);
                let buttonsToolbar          = $(clickedButton).closest(_this.selectors.classes.upperToolbar);
                let fileCurrentPath         = $(buttonsToolbar).find(_this.selectors.classes.downloadButton).attr('href');

                let addTagsToImageOnViewAndRebuildShuffleGroups = (tags) => {
                    let gallery   = $(_this.selectors.classes.lightboxGallery);
                    let currImage = $(gallery).find('[data-src^="' + fileCurrentPath + '"]');
                    let tagsArr   = tags.split(',');
                    let tagsJson  = JSON.stringify(tagsArr);

                    if( DomElements.doElementsExists(currImage) ){
                        $(currImage).attr('data-groups', tagsJson);
                    }

                    let tagsArray = _this.shuffler.buildTagsArrayFromTagsForImages();

                    _this.shuffler.removeTagsFromFilter();
                    _this.shuffler.appendTagsToFilter(tagsArray);
                    _this.shuffler.addTagsButtonsEvents();

                };

                /**
                 * SpecialActions::UpdateTags - should be theoretically used here but due to special handling of
                 * miniatures etc in background - this must remain like this
                 */
                _this.tagManagementDialogs.buildTagManagementDialog(fileCurrentPath, 'My Images', addTagsToImageOnViewAndRebuildShuffleGroups);
            });

        }
    };

    public removeImageWithMiniature(filePath){
        let thumbnails               = $(this.selectors.classes.thumbnails);
        let removedImageMiniature    = $(thumbnails).find("[data-src-real^='" + filePath + "']");
        let nextButton               = $(this.selectors.classes.nextButton);
        let currentViewedImage       = $(this.selectors.classes.currentViewedImage);
        let htmlGallery              = $(this.selectors.classes.lightboxGallery);

        $(removedImageMiniature).parent('div').remove();
        $(currentViewedImage).remove();
        $(nextButton).click();
        this.initGallery();

        // now the image that is removed in slider is fine but it must be removed also from shuffler instance to update tags etc
        let currentImageInGalleryView = $(htmlGallery).find('[data-src^="' + filePath + '"]');
        let currentImageUniqueId      = $(currentImageInGalleryView).attr('data-unique-id');

        // ShuffleJS tags need to be rebuilt
        this.shuffler.removeImageByDataUniqueId(currentImageUniqueId);        // first remove image from instance

        let tagsArray = this.shuffler.buildTagsArrayFromTagsForImages();  // prepare updated set of tags

        this.shuffler.removeTagsFromFilter();                              // clean current tags and add new set
        this.shuffler.appendTagsToFilter(tagsArray);
        this.shuffler.addTagsButtonsEvents();
        this.shuffler.switchToGroupAllIfGroupIsRemoved();
    };

    private handleGalleryCaptionOnFileRename(currFilename, newFilename){
        let textHolder = $(this.selectors.classes.textHolderCaption + "[data-filename^='" + currFilename + "']");
        textHolder.text(newFilename);
        textHolder.attr('data-alt', newFilename);
    };

    private handleGalleryEvents(){
        let _this           = this;
        let lightboxGallery = $(this.selectors.classes.lightboxGallery);

        lightboxGallery.on('onAfterSlide.lg', function () {
            _this.handleMovingBetweenImagesAfterImageRemoval(lightboxGallery);
        });

        lightboxGallery.on('onCloseAfter.lg',function() {
            _this.handleRebuildingEntireGalleryWhenClosingIt(lightboxGallery);
        });

        lightboxGallery.on('onAfterOpen.lg',function() {
            _this.modifyThumbnailsWhenOpeningGallery(lightboxGallery);
        });

    };

    private handleMovingBetweenImagesAfterImageRemoval(lightboxGallery){

        // Handling skipping removed images - because of above - this is dirty solution but works
        let downloadButton  = $(this.selectors.ids.downloadButton);
        var filePath        = $(downloadButton).attr('href');
        let isImagePresent  = ( lightboxGallery.find("[data-src-real^='" + filePath + "']").length > 0 );
        let _this           = this;

        if( !isImagePresent ){
            $('.lg-next').click();
        }

        // Handling proper slideback when image was removed - dirty like above
        let prevButton = $('button.lg-prev');

        $(prevButton).on('click', () => {
            let downloadButton  = $(_this.selectors.ids.downloadButton);
            var filePathOnClick = $(downloadButton).attr('href');

            if (filePathOnClick === filePath){
                lightboxGallery.data('lightGallery').goToPrevSlide();
            }
        });
    };

    private handleRebuildingEntireGalleryWhenClosingIt(lightboxGallery){
        // Handling rebuilding entire gallery when closing - needed because plugin somehows stores data in it's object not in storage
        this.reinitGallery();
    };

    private handleClosingGalleryIfThereAreNoMoreImages() {
        let lightboxGallery = $(this.selectors.classes.lightboxGallery);
        let foundImages     = $(lightboxGallery).find('img');
        let closeButton     = $('.lg-close');

        if( !DomElements.doElementsExists(foundImages) ){
            $(closeButton).click();
        }
    };

    /**
     * @description will modify the thumbnails
     *              - add data-src-real attribute (used with backend generated miniatures)
     */
    private modifyThumbnailsWhenOpeningGallery($lightboxGallery: JQuery<HTMLElement>): void
    {
        let $thumbnailsImages = $('.lg-thumb img');
        $.each($thumbnailsImages, (index, thumbnailImage) => {
            let $thumbnailImage = $(thumbnailImage);
            let thumbnailSrc    = $thumbnailImage.attr('src');

            let $correspondingGalleryImage = $lightboxGallery.find('[src^="' +  thumbnailSrc + '"]');
            let dataSrcReal                = $correspondingGalleryImage.attr('data-src-real');

            $thumbnailImage.attr('data-src-real', dataSrcReal);
        })
    }

    /**
     * This function will prevent triggering events such as showing gallery for image in wrapper (click)
     */
    private preventCheckboxEventTriggering(){
        let lightboxGallery              = $(this.selectors.classes.lightboxGallery);
        let checkboxesForImagesWrappers  = $( lightboxGallery.find(this.selectors.other.checkboxForImageWrapper) );
        let checkboxesForImages          = $( lightboxGallery.find(this.selectors.other.checkboxForImage) );

        $(checkboxesForImagesWrappers).on('click', (event) => {
            event.stopImmediatePropagation();

            let clickedElement  = event.currentTarget;
            let isCheckbox      = DomAttributes.isCheckbox(clickedElement, false);

            if( !isCheckbox ){
                let checkbox  = $(clickedElement).find('input');
                let isChecked = DomAttributes.isChecked(checkbox);

                if(isChecked){
                    DomAttributes.unsetChecked(checkbox);
                    $(checkbox).trigger('click');
                    return false;
                }

                DomAttributes.setChecked(checkbox);
                $(checkbox).trigger('click');
            }

        });


        checkboxesForImages.on('click', (event) => {
            event.stopImmediatePropagation();
        })

    }; 
     
    /**
     * This function will handle toggling disability for mass action buttons, at least one image must be checked
     * to remove the disabled class.
     */
    private handleCheckboxForImageInGalleryView(){
        let _this                = this;
        let lightboxGallery      = $(this.selectors.classes.lightboxGallery);
        let checkboxesForImages  = ( lightboxGallery.find(this.selectors.other.checkboxForImage) );

        $(checkboxesForImages).on('change', () => {
            let checkedCheckboxes = ( lightboxGallery.find(this.selectors.other.checkboxForImage + ':checked') );
            let massActionButtons = $(_this.selectors.classes.massActionButtons);

            if( DomElements.doElementsExists(checkedCheckboxes) ){
                DomAttributes.unsetDisabledClass(massActionButtons);
                return false;
            }

            DomAttributes.setDisabledClass(massActionButtons);
        })

    };

    /**
     * This Bugfix is needed because masonry js gallery keeps overwriting styles for gallery
     * so with high number of images inside div - it won't scale anymore, It cannot be changed in twig
     * because JS overwrites it and besides i don't want to interfere with original code of that lib.
     */
    private preventSettingMasonryGalleryAsAbsolute(){
        let _this = this;
        document.addEventListener("DOMContentLoaded", function() {
            let $myGallery  = $('.lightgallery .my-gallery');
            let $thumbnails = $(_this.selectors.classes.lightboxGallery);

            if( DomElements.doElementsExists($myGallery) && DomElements.doElementsExists($thumbnails)){
                $myGallery.attr("style", "");
                $thumbnails.attr("style", "");
            }

        });
    }
}
