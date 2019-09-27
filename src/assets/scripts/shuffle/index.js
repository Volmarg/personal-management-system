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
                searchSelector : '.js-shuffle-search'
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
            });

        }
    };

}());