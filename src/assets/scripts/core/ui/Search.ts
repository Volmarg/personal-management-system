import BootstrapNotify  from "../../libs/bootstrap-notify/BootstrapNotify";
import ArrayUtils       from "../../core/utils/ArrayUtils";
import Loader           from "../../libs/loader/Loader";
import Popover          from "../../libs/popover/Popover";
import NotePreviewDialogs from "./Dialogs/NotePreviewDialogs";

export default class Search {

    /**
     * @type Object
     */
    private selectors = {
        ids: {
            searchInput             : "#search",
            fileSearchResultWrapper : "#searchResultListWrapper"
        },
        classes: {
        },
        other: {
        }
    };

    /**
     * @type Object
     */
    private methods = {
        getSearchResultsDataForTag: '/api/search/get-results-data'
    };

    /**
     * @type Object
     */
    private resultTypes = {
        file: "file",
        note: "note"
    };

    /**
     * @type BootstrapNotify
     */
    private bootstrapNotify = new BootstrapNotify();

    /**
     * @type NotePreviewDialogs
     */
    private notePreviewDialogs = new NotePreviewDialogs();

    /**
     * @description Initialize main logic
     */
    public init(): void
    {
        this.attachAjaxCallOnChangeOfSearchInput();
    };

    /**
     * @description Will attach on change event on the search input
     */
    private attachAjaxCallOnChangeOfSearchInput(): void
    {

        let _this       = this;
        let searchInput = $(this.selectors.ids.searchInput);
        let timeout     = null;

        searchInput.off('change'); // prevent stacking
        searchInput.on('change', () => {

            let tags                = $(searchInput).val();
            let searchResultWrapper = $(_this.selectors.ids.fileSearchResultWrapper);

            if( ArrayUtils.isEmpty(tags)){

                if( undefined !== timeout ){
                    clearTimeout(timeout);
                }

                $(searchResultWrapper).empty();
                return;
            }

            let data = {
                'tags' : tags
            };

            // this is used to prevent instant search whenever new character is inserted in search input
            if( undefined !== timeout ){
                clearTimeout(timeout);
            }

            Loader.showLoader();

            timeout = setTimeout( () => {
                $.ajax({
                    method  : "POST",
                    url     : _this.methods.getSearchResultsDataForTag,
                    data    : data,
                }).always((data)=>{
                    Loader.hideLoader();

                    let resultsCount  = data['searchResults'].length;
                    let reloadPage    = data['reload_page'];
                    let reloadMessage = data['reload_message'];

                    if( 0 === resultsCount ){
                        _this.bootstrapNotify.notify("No results for given tags.", 'danger');
                        return;
                    }

                    //data search result type

                    let filesSearchResultsList = _this.buildSearchResults(data['searchResults']);

                    $(searchResultWrapper).empty();
                    $(searchResultWrapper).append(filesSearchResultsList);
                    Popover.init();

                    _this.bootstrapNotify.notify("Found " + resultsCount + " matching result/s", 'success');

                    if( reloadPage ){
                        if( "" !== reloadMessage ){
                            _this.bootstrapNotify.showBlueNotification(reloadMessage);
                        }
                        location.reload();
                    }
                })
            }, 2000)

        });
    };

    /**
     * @description Will call dialog on given element
     *
     * @param element
     * @param noteId
     * @param categoryId
     */
    private attachDialogCallOnTargetElement(element: JQuery, noteId: string, categoryId: string): void
    {
        $(element).on('click', () => {
            this.notePreviewDialogs.buildNotePreviewDialog(noteId, categoryId);
        })
    };

    /**
     * @description Return UL list with the result list
     *
     * @param data
     */
    private buildSearchResults(data: Array<string>): JQuery
    {
        let _this = this;
        let ul    = $('<ul>');
        let li    = null;

        $.each(data, (index, result) => {

            let type:String = result['type'];

            switch(type){
                case _this.resultTypes.file:
                    //@ts-ignore
                    li = _this.buildFilesSearchResultsListElement(result);
                    break;
                case _this.resultTypes.note:
                    //@ts-ignore
                    li = _this.buildNotesSearchResultsListElement(result);
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
    };

    /**
     * @description Builds single element of search results for files
     *
     * @param data
     */
   private buildFilesSearchResultsListElement(data: Array<string>): JQuery
   {

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
        $.each(arrayOfTags, (idx: Number, tag) => {

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

    };

    /**
     * @description Builds single element of search results for notes
     *
     * @param data
     */
   private buildNotesSearchResultsListElement(data: Array<string>){
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
        this.attachDialogCallOnTargetElement(button, noteId, categoryId);

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
    };

}