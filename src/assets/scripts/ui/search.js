/**
 * This file handles top bar searching for data in modules by using tags mechanism in selectize
 * Backend searches data by provided mechanisms and returns the minimum required data
 */
var bootbox = require('bootbox');

export default (function () {

    if (typeof window.search === 'undefined') {
        window.ui.filesSearch = {};
    }

    ui.search = {

        selectors: {
            ids: {
                searchInput             : "#search",
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
        resultTypes: {
            file: "file",
            note: "note"
        },
        init: function(){
            this.attachAjaxCallOnChangeOfSearchInput();
        },
        attachAjaxCallOnChangeOfSearchInput: function(){

            let _this                  = this;
            let searchInput = $(this.selectors.ids.searchInput);

            searchInput.on('change', () => {

                let tags                    = $(searchInput).val();
                let searchResultWrapper = $(_this.selectors.ids.fileSearchResultWrapper);


                if( "" === tags ){

                    if( undefined !== window.timeout ){
                        clearTimeout(window.timeout);
                    }

                    $(searchResultWrapper).empty();
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

                        //data search result type

                        let filesSearchResultsList = _this.buildSearchResults(data['searchResults']);

                        $(searchResultWrapper).empty();
                        $(searchResultWrapper).append(filesSearchResultsList);
                        ui.widgets.popover.init();

                        bootstrap_notifications.notify("Found " + resultsCount + " matching result/s", 'success');


                    })
                }, 2000)

            });


        },
        attachDialogCallOnTargetSelector: function(element, noteId, categoryId){
            $(element).on('click', (event) => {
                dialogs.ui.notePreview.buildTagManagementDialog(noteId, categoryId);
            })
        },
        buildSearchResults: function(data){

            let ul = $('<ul>');
            let li = null;

            $.each(data, (index, result) => {

                let type = result['type'];

                switch(type){
                    case this.resultTypes.file:
                        li = this.buildFilesSearchResultsListElement(result);
                        break;
                    case this.resultTypes.note:
                        li = this.buildNotesSearchResultsListElement(result);
                        break;
                    default:
                        throw({
                            "message": "Unsupported search result type",
                            "type"   : type,
                            "result" : result
                        })
                }

                $(ul).append(li);
            });

            return ul;
        },
        buildFilesSearchResultsListElement: function (data) {

            let tagsJson        = data['tags'];
            let arrayOfTags     = JSON.parse(tagsJson);
            let tagsList        = '';
            let module          = data['module'];
            let filename        = data['filename'];
            let filePath        = data['fullFilePath'];
            let directoryPath   = data['directoryPath'];
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
            $(moduleIcon).addClass('search-data-module-icon');

            if( 'My Files' === module ){
                $(moduleIcon).addClass('fas fa-folder-open d-inline');
            }else if( 'My Images' === module ){
                $(moduleIcon).addClass('fas fa-image d-inline');
            }

            let name = $('<span>');
            $(name).html(shortFilename);
            $(name).addClass("d-inline search-data-file-name");

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

            return li;

        },
        buildNotesSearchResultsListElement: function(data){
            let title           = data['title'];
            let shortTitle      = title;
            let category        = data['category'];
            let categoryId      = data['categoryId'];
            let noteId          = data['noteId'];
            let shortLen        = 16;

            // build short title
            if( title.length > shortLen ) {
                shortTitle   = title.substr(0, shortLen) + '...';
            }

            let button = $('<button>');
            $(button).addClass('note-preview-search-result button-icon d-inline');
            this.attachDialogCallOnTargetSelector(button, noteId, categoryId);

            let previewIcon = $('<i>');
            $(previewIcon).addClass('far fa-eye');

            $(button).append(previewIcon);

            let moduleIcon = $('<span>');
            $(moduleIcon).addClass('search-data-module-icon');
            $(moduleIcon).addClass('fas fa-book d-inline');

            let name = $('<span>');
            $(name).html(shortTitle);
            $(name).addClass("d-inline search-data-file-name");

            let link = $('<a>');
            $(link).attr('href', '/my-notes/category/' + category + '/' + categoryId);

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
                        Title:&nbsp;
                    </span> 
                    <span style='word-break: break-all;'>` + title + `</span>
                </p>`
            );
            $(link).attr('title', title);

            // combine list elements
            let li = $('<li>');
            $(li).append(link);
            $(li).append(button);

            return li;
        }
    };

}());
