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

                ui.widgets.loader.showLoader();

                window.timeout = setTimeout( () => {
                    $.ajax({
                        method  : "POST",
                        url     : _this.methods.getSearchResultsDataForTag,
                        data    : data,
                    }).always((data)=>{
                        ui.widgets.loader.hideLoader();

                        let resultsCount = data['searchResults'].length;

                        if( 0 === resultsCount ){
                            bootstrap_notifications.notify("No results for given tags.", 'danger');
                            return;
                        }

                        let searchResultsList       = _this.buildSearchResultsList(data['searchResults']);

                        $(fileSearchResultWrapper).empty();
                        $(fileSearchResultWrapper).append(searchResultsList);
                        ui.widgets.popover.init();

                        bootstrap_notifications.notify("Found " + resultsCount + " matching file/s", 'success');


                    })
                }, 2000)

            });


        },
        buildSearchResultsList: function (data) {

            let ul = $('<ul>');

            $.each(data, (index, result) => {
                let tagsJson        = result['tags'];
                let arrayOfTags     = JSON.parse(tagsJson);
                let tagsList        = '';
                let module          = result['module'];
                let filename        = result['filename'];
                let filePath        = result['fullFilePath'];
                let directoryPath   = result['directoryPath'];
                let shortFilename   = filename;
                let shortLen        = 16;

                // build list of tags
                $.each(arrayOfTags, (idx, tag) => {

                    tagsList += tag;

                    if( idx < ( arrayOfTags.length - 1 ) ){
                        tagsList += ', ';
                    }

                });

                // build shortname
                if( filename.length > shortLen ) {
                   shortFilename   = filename.substr(0, shortLen) + '...';
                }

                // build download form
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

                if( 'My Files' === module ){
                    $(moduleIcon).addClass('fas fa-folder-open d-inline');
                }else if( 'My Images' === module ){
                    $(moduleIcon).addClass('fas fa-image d-inline');
                }

                let name = $('<span>');
                $(name).html(shortFilename);
                $(name).addClass("d-inline search-result-file-name");

                let link = $('<a>');
                $(link).attr('href', directoryPath);

                $(link).append(moduleIcon);
                $(link).append(name);

                //add popover to link
                $(link).attr('data-trigger', "hover");

                $(link).attr('data-html', "true");
                $(link).attr('data-toggle-popover', 'true');

                $(link).attr(
                    'data-content',
                    `<p style='display: flex;'>
                        <span style='font-weight: bold; '>
                            Tags:&nbsp;
                        </span> 
                        <span style='word-break: break-all;'>` + tagsList + `</span>
                    </p>`
                );
                $(link).attr('title', filename);

                // combine list elements
                let li = $('<li>');
                $(li).append(link);
                $(li).append(form);

                $(ul).append(li);
            });

            return ul;

        }
    };

}());
