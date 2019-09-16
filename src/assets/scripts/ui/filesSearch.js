/**
 * This file handles top bar searching for files. Supports:
    * searching by tags
 */
var bootbox = require('bootbox');

export default (function () {

    if (typeof window.filesSearch === 'undefined') {
        window.ui.filesSearch = {};
    }

    ui.filesSearch = {

        selectors: {
            ids: {
                filesSearchByTagsInput: "#filesSearchByTags"
            },
            classes: {
            },
            other: {
            }
        },
        messages: {
        },
        methods: {
            getSearchResultsDataForTag: '/api/search/get-results-data'
        },
        vars: {
        },
        init: function(){
            this.attachAjaxCallOnChangeOfSearchInput();
        },
        attachAjaxCallOnChangeOfSearchInput: function(){

            let _this                  = this;
            let filesSearchByTagsInput = $(this.selectors.ids.filesSearchByTagsInput);

            filesSearchByTagsInput.on('change', () => {

                let tags = $(filesSearchByTagsInput).val();

                let data = {
                    'tags' : tags
                };

                // this is used to prevent instant search whenever new character is inserted in search input
                // TODO: prevent stacking the timeouted calls - break previous if new is being sent
                setTimeout( () => {
                    $.ajax({
                        method  : "POST",
                        url     : _this.methods.getSearchResultsDataForTag,
                        data    : data
                    }).always((data)=>{
                        console.log(data);
                    })
                }, 2000)

            });


        }
    };

}());
