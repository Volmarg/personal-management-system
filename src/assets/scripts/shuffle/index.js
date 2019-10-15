/*
    This functions utilize the ShuffleJS for searching images by tags and names in the MyImages category view
 */

import Shuffler from './Shuffler';

export default (function () {

    if (typeof window.shuffler === 'undefined') {
        window.ui.shuffler = {};
    }

    ui.shuffler = {

        selectors: {
            ids: {
            },
            classes: {
                itemSelector   : '.grid-item',
                searchSelector : '.js-shuffle-search',
                tagsHolder     : '.shuffler-tags-options'
            },
            other: {
                galleryHolder  : "aniimated-thumbnials",
            }
        },
        messages: {
        },
        methods: {
        },
        vars: {
        },
        init: function(){
            this.initShuffler();
        },
        initShuffler: function () {
            let _this = this;

            $(document).ready(() => {
                let domGalleryHolder = document.getElementById(_this.selectors.other.galleryHolder);
                let itemSelector     = _this.selectors.classes.itemSelector;
                let searchSelector   = _this.selectors.classes.searchSelector;

                if( undefined === domGalleryHolder || null === domGalleryHolder ){
                    return;
                }

                window.shuffler = new Shuffler(domGalleryHolder, itemSelector, searchSelector);
                window.shuffler.init();

                let tagsArray = window.shuffler.buildTagsArrayFromTagsForImages();
                _this.appendTagsToFilter(tagsArray);

                window.shuffler.addTagsButtonsEvents();
            });

        },
        appendTagsToFilter: function(tagsArray){
            let domTagsHolder = $(this.selectors.classes.tagsHolder);
            domTagsHolder.html('');

            //append each tag as option
            $.each(tagsArray, (index, tag) => {
                let button = $('<button>');
                button.addClass('btn btn-sm btn-primary');
                button.css({
                    "margin": "2px",
                    "height": '30px'
                });
                button.attr('data-group', tag);
                button.html(tag);

                $(domTagsHolder).append(button);
            });
        },
        removeTagsFromFilter: function(){
            let domTagsHolder = $(this.selectors.classes.tagsHolder);
            $(domTagsHolder).html('');
        },
        removeImageByDataUniqueId: function(id){
            let allIShuffleItems = window.shuffler.shuffle.items;
            let removedItem      = null;
            let removedElement   = null;

            $.each(allIShuffleItems, (index, item) => {

                let domElement = item.element;
                let uniqueId   = $(domElement).attr('data-unique-id');

                if( uniqueId == id){
                    removedItem     = item;
                    removedElement  = domElement;
                }

            });

            window.shuffler.shuffle.remove([removedElement]);
        },
        switchToGroupAllIfGroupIsRemoved: function(){
            let selectedGroup       = window.shuffler.shuffle.group;
            let domTagsHolder       = $(this.selectors.classes.tagsHolder);
            let correspondingButton = $(domTagsHolder).find('[data-group^="' + selectedGroup + '"]');

            if( 0 === correspondingButton.length ){
                window.shuffler.shuffle.filter("all");
            }

        }
    };

}());