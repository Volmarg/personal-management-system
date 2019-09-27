/**
    This script is basically using Shuffle but the instance itself must have other name
    You can call this a Wrapper which is needed to implement Shuffle functionality
 */

import Shuffle from 'shufflejs';

export default class Shuffler {

    constructor(domGalleryHolder, itemSelector, searchSelector) {

        this.domGalleryHolder = domGalleryHolder;
        this.itemSelector     = itemSelector;
        this.searchSelector   = searchSelector;

    }

    init(){
        let _this = this;

        this.shuffle = new Shuffle(_this.domGalleryHolder, {
            itemSelector: _this.itemSelector,
            columnWidth: 30
        });

        this.addSearchFilter();
    }

    // Advanced filtering
    addSearchFilter() {
        const searchInput = document.querySelector(this.searchSelector);
        if (!searchInput) {
            return;
        }
        searchInput.addEventListener('keyup', this.searchByDataTitle.bind(this));
    }

    /**
     * Filter the shuffle instance by items with a data-title that matches the search input.
     * @param {Event} evt Event object.
     */
    searchByDataTitle(evt) {
        let searchedText = evt.target.value.toLowerCase();

        this.shuffle.filter((element, shuffle) => {
            let dataTitleOfImage = element.getAttribute("data-title").toLowerCase().trim();
            return dataTitleOfImage.indexOf(searchedText) !== -1;
        });

    }
}

