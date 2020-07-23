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
        this.buildTagsArrayFromTagsForImages();
    }

    /*******************
     *  Tags Handling  *
     ******************/
    addTagsButtonsEvents() {
        let tagsButtons = $('.shuffler-tags-options').find('button');
        let _this   = this;

        if (tagsButtons.length === 0) {
            return;
        }

        $.each(tagsButtons, (index, button) => {
            $(button).on('click', (event) => {
                _this.handleTagClick(event);
            });
        });
    };

    handleTagClick(event) {
        let btn      = event.currentTarget;
        let isActive = $(btn).hasClass('active');
        let btnGroup = $(btn).attr('data-group');

        // 'lets only one filter button be active at a time.
        this.removeActiveClassFromChildrenOnTagClick(btn.parentNode);

        let filterGroup;

        if (isActive) {
            $(btn).removeClass('active');
            filterGroup = Shuffle.ALL_ITEMS;
        } else {
            $(btn).addClass('active');
            filterGroup = btnGroup;
        }

        this.shuffle.filter(filterGroup);
    };

    removeActiveClassFromChildrenOnTagClick(parent) {
        let children = parent.children;
        for (var i = children.length - 1; i >= 0; i--) {
            $(children[i]).removeClass('active');
        }
    };

    buildTagsArrayFromTagsForImages(){
        let allIShuffleItems = this.shuffle.items;
        let _this = this;

        this.tags = [];

        $.each(allIShuffleItems, (index, item) => {

           let targetImage    = $(item.element);
           let tagsJsonString = $(targetImage).attr('data-groups');

           // skip empty tags
           if( "" != tagsJsonString ){
               let tagsArray = JSON.parse(tagsJsonString);
               _this.tags = _this.tags.concat(tagsArray);
           }

        });

        // will get only unique tags (distinct)
        this.tags = Array.from(new Set(this.tags));

        return this.tags;
    }

    /*******************
     * Search Handling  *
     ******************/
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

            // If there is a group(tag) applied, ignore elements that don't belong to it.
            if (shuffle.group !== Shuffle.ALL_ITEMS) {
                // Get the item's groups.
                let groups    = $(element).attr('data-groups');
                let groupsObj = JSON.parse(groups);
                let isElementInCurrentGroup = groupsObj.indexOf(shuffle.group) !== -1;

                // Only search elements in the current group
                if (!isElementInCurrentGroup) {
                    return false; //go to next element
                }
            }

            let dataTitleOfImage = element.getAttribute("data-title").toLowerCase().trim();
            return dataTitleOfImage.indexOf(searchedText) !== -1;
        });

    }
}

