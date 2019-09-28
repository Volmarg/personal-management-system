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

            document.addEventListener('DOMContentLoaded', () => {
                let domGalleryHolder = document.getElementById(_this.selectors.other.galleryHolder);
                let itemSelector     = _this.selectors.classes.itemSelector;
                let searchSelector   = _this.selectors.classes.searchSelector;

                let shuffler = new Shuffler(domGalleryHolder, itemSelector, searchSelector);
                shuffler.init();

                let tagsArray = shuffler.buildTagsArrayFromTagsForImages();
                _this.appendTagsToFilter(tagsArray);

                shuffler.addTagsButtonsEvents();

            });

        },
        appendTagsToFilter: function(tagsArray){
            let domTagsHolder = $(this.selectors.classes.tagsHolder);

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
        }
    };

}());