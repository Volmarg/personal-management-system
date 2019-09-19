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
                filesSearchByTagsInput  : "#filesSearchByTags",
                fileSearchResultWrapper : "#searchResultListWrapper"
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

                let tags                    = $(filesSearchByTagsInput).val();
                let fileSearchResultWrapper = $(_this.selectors.ids.fileSearchResultWrapper);


                if( "" === tags ){

                    if( undefined !== window.timeout ){
                        clearTimeout(window.timeout);
                    }

                    $(fileSearchResultWrapper).empty();
                    return;
                }

                let data = {
                    'tags' : tags
                };

                // this is used to prevent instant search whenever new character is inserted in search input
                if( undefined !== window.timeout ){
                    clearTimeout(window.timeout);
                }

                window.timeout = setTimeout( () => {
                    $.ajax({
                        method  : "POST",
                        url     : _this.methods.getSearchResultsDataForTag,
                        data    : data,
                    }).always((data)=>{

                        let resultsCount = data['searchResults'].length;

                        if( 0 === resultsCount ){
                            bootstrap_notifications.notify("No results for given tags.", 'danger');
                            return;
                        }

                        let searchResultsList       = _this.buildSearchResultsList(data['searchResults']);

                        $(fileSearchResultWrapper).empty();
                        $(fileSearchResultWrapper).append(searchResultsList);
                        bootstrap_notifications.notify("Found " + resultsCount + " matching file/s", 'success');


                    })
                }, 2000)

            });


        },
        buildSearchResultsList: function (data) {

            let ul = $('<ul>');

            $.each(data, (index, result) => {
                let module   = result['module'];
                let filename = result['filename'];
                let filePath = result['fullFilePath'];

                let form = $('<form>');
                $(form).attr('method', "POST");
                $(form).attr('action', "/download/file");
                $(form).addClass('file-download-form d-inline');

                let input = $('<input>');
                $(input).attr('type','hidden');
                $(input).attr('name','file_full_path');
                $(input).val(filePath);

                let button = $('<button>');
                $(button).addClass('file-download d-inline');

                let downloadIcon = $('<i>');
                $(downloadIcon).addClass('fa fa-download');

                $(button).append(downloadIcon);
                $(form).append(input);
                $(form).append(button);

                let moduleIcon = $('<span>');
                $(moduleIcon).addClass('search-result-module-icon');

                if( 'My Images' === module ){
                    $(moduleIcon).addClass('fas fa-folder-open d-inline');
                }else if( 'My Files' === module ){
                    $(moduleIcon).addClass('fas fa-image d-inline');
                }

                let name = $('<span>');
                $(name).html(filename);
                $(name).addClass("d-inline search-result-file-name");

                let li = $('<li>');
                $(li).append(moduleIcon);
                $(li).append(name);
                $(li).append(form);

                $(ul).append(li);
            });

            return ul;

        }
    };

}());
