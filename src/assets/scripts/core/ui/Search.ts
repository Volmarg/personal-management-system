import BootstrapNotify    from "../../libs/bootstrap-notify/BootstrapNotify";
import ArrayUtils         from "../../core/utils/ArrayUtils";
import Loader             from "../../libs/loader/Loader";
import NotePreviewDialogs from "./Dialogs/NotePreviewDialogs";
import Tippy              from "../../libs/tippy/Tippy";

export default class Search {

    /**
     * @type Object
     */
    private selectors = {
        ids: {
            searchInput         : "#search",
            SearchResultWrapper : "#searchResultListWrapper"
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
            let searchResultWrapper = $(_this.selectors.ids.SearchResultWrapper);

            if( ArrayUtils.isEmpty(tags)){

                if( undefined !== timeout ){
                    clearTimeout(timeout);
                }

                $(searchResultWrapper).empty();
                $(searchResultWrapper).attr('style', "");
                return;
            }

            let data = {
                'tags' : tags
            };

            // this is used to prevent instant search whenever new character is inserted in search input
            if( undefined !== timeout ){
                clearTimeout(timeout);
            }

            Loader.showMainLoader();

            timeout = setTimeout( () => {
                $.ajax({
                    method  : "POST",
                    url     : _this.methods.getSearchResultsDataForTag,
                    data    : data,
                }).always((data)=>{
                    Loader.hideMainLoader();

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
                    $(searchResultWrapper).css({
                        "overflow": "scroll"
                    });
                    Tippy.init();

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
        ul.addClass('dropdown-list animated zoomIn list-unstyled');

        $.each(data, (index, result) => {

            let type:String = result['type'];
            let $hr         = $("<hr>"); // must be created each time otherwise there are appending issues
            $hr.addClass("m-0");

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
            $(ul).append($hr);
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

        // build download link
        let downloadLink = $("<A>");
        downloadLink.attr("download", "");
        downloadLink.attr("href", '/' + filePath);

        let downloadIcon = $('<i>');
        $(downloadIcon).addClass('fa fa-download');

        downloadLink.append(downloadIcon);

        let moduleIcon = $('<span>');
        $(moduleIcon).addClass('search-data-module-icon');

        if( 'My Files' === module ){
            $(moduleIcon).addClass('fas fa-folder-open d-inline');
        }else if( 'My Images' === module ){
            $(moduleIcon).addClass('fas fa-image d-inline');
        }else if( 'My Video' === module ){
            $(moduleIcon).addClass('fas fa-film d-inline');
        }

        let name = $('<span>');
        name.css({
            "margin-left": "7px"
        });

        $(name).html(shortFilename);
        $(name).addClass("d-inline search-data-file-name");

        let link = $('<a>');
        $(link).attr('href', directoryPath);

        $(link).append(moduleIcon);
        $(link).append(name);

        $(link).attr('title', filename);

       // combine all to final output list element
       let $singleActionDownloadLink = this.buildSingleActionWrapper();
       let $li                       = this.buildListElement("Tags", tagsList);
       let $contentWrapper           = this.buildContentWrapperForListElement(link);

        // build download action
       $singleActionDownloadLink.append(downloadLink);

        let allActions = [
            $singleActionDownloadLink
        ];

       let $actionsWrapperWithActions = this.buildActionsWrapper(allActions);

        $($li).append($contentWrapper);
        $($li).append($actionsWrapperWithActions);

        return $li;
    };

    /**
     * @description Builds single element of search results for notes
     *
     * @param data
     */
   private buildNotesSearchResultsListElement(data: Array<string>): JQuery<HTMLElement>
   {
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

        // build note preview
        let $notePreviewButton = $('<button>');
        $notePreviewButton.addClass('note-preview-search-result button-icon d-inline');
        this.attachDialogCallOnTargetElement($notePreviewButton, noteId, categoryId);

        let previewIcon = $('<i>');
        $(previewIcon).addClass('far fa-eye');

        $notePreviewButton.append(previewIcon);

        let moduleIcon = $('<span>');
        $(moduleIcon).addClass('search-data-module-icon');
        $(moduleIcon).addClass('fas fa-book d-inline');

        let name = $('<span>');
        $(name).html(shortTitle);
        $(name).addClass("d-inline search-data-file-name");
        name.css({
           "margin-left": "7px"
        });

        let link = $('<a>');
        $(link).attr('href', '/my-notes/category/' + category + '/' + categoryId);

        $(link).append(moduleIcon);
        $(link).append(name);

        // combine all to final output list element
        let $singleActionNotePreview  = this.buildSingleActionWrapper();
        let $li                       = this.buildListElement("Title", title);
        let $contentWrapper           = this.buildContentWrapperForListElement(link);

        $singleActionNotePreview.append($notePreviewButton);

        let allActions = [
            $singleActionNotePreview
        ];

        let $actionsWrapperWithActions = this.buildActionsWrapper(allActions);

        $($li).append($contentWrapper);
        $($li).append($actionsWrapperWithActions);

        return $li;
    };

    /**
     * @description will return single list element used in the search result
     */
   private buildListElement(popoverContentName: String, popoverContent: String): JQuery<HTMLElement>
   {
       let li = $('<li>');

       //add popover to list element
       $(li).attr(
           'data-content',
           `<p style='display: flex;'>
                    <span style='font-weight: bold; '>
                        ${popoverContentName}:&nbsp;
                    </span> 
                    <span style='word-break: break-all;'>${popoverContent}</span>
                </p>`
       );

       $(li).attr('data-html', "true");
       $(li).attr('data-toggle-popover', 'true');
       $(li).attr('data-placement', 'right');
       $(li).attr('data-offset', "0 -25%");
       $(li).attr('data-offset', "0 -25%");

       li.addClass('option d-flex justify-content-around');
       li.css({
           "padding": "10px !important"
       });

       return li;
   }

    /**
     * @description will return content wrapper element used to contain the link to page
     */
   private buildContentWrapperForListElement($link: JQuery<HTMLElement>): JQuery<HTMLElement>
   {
       let contentWrapper = $('<SPAN>');
       contentWrapper.css({
           "width": "100%"
       })
       contentWrapper.append($link);
       return contentWrapper;
   }

    /**
     * @description will return single element used as a wrapper for action
     */
   private buildSingleActionWrapper(): JQuery<HTMLElement>
   {
       let $actionWrapperElement = $('<SPAN>');
       $actionWrapperElement.addClass("text-center pointer");
       $actionWrapperElement.css({
           "width"  : "50px",
           "display": "block"
       })

       return $actionWrapperElement;
   }

    /**
     * @description will return the actions wrapper filled up with actions
     * @param actionsWrappers
     */
   private buildActionsWrapper(actionsWrappers: Array<JQuery<HTMLElement>>): JQuery<HTMLElement>
   {
       let $actionsWrapper = $('<SPAN>')

       $.each(actionsWrappers, (index, $element) => {
           $actionsWrapper.append($element);
       })

       return $actionsWrapper;
   }

}