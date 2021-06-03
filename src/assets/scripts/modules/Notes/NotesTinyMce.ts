import 'tinymce/themes/silver';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';
import 'tinymce/plugins/image';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/paste';
import 'tinymce/plugins/codesample';

import BootstrapNotify from "../../libs/bootstrap-notify/BootstrapNotify";
import TinyMce         from "../../libs/tiny-mce/TinyMce";
import DomElements     from "../../core/utils/DomElements";
import Ajax            from "../../core/ajax/Ajax";
import Loader          from "../../libs/loader/Loader";
import UiUtils         from "../../core/utils/UiUtils";
import Navigation      from "../../core/Navigation";
import StringUtils     from "../../core/utils/StringUtils";
import AjaxEvents      from "../../core/ajax/AjaxEvents";
import BootboxWrapper  from "../../libs/bootbox/BootboxWrapper";
import AbstractAction  from "../../core/ui/Actions/AbstractAction";

export default class NotesTinyMce {

    /**
     * @type BootstrapNotify
     */
    private bootstrapNotify = new BootstrapNotify();

    /**
     * @type Ajax
     */
    private ajax = new Ajax();

    /**
     * @type AjaxEvents
     */
    private ajaxEvents = new AjaxEvents();

    /**
     * @type TinyMce
     */
    private tinyMce = new TinyMce();

    /**
     * @description partial selector of list element (requires adding id on the end)
     */
    static readonly NOTE_LIST_ELEMENT_PARTIAL_SELECTOR = "#noteListElement";

    /**
     * Main initialization logic
     */
    public init(): void
    {
        let $allNoteDialogs = $('[data-modal-name^="noteEdit"]');

        if( !DomElements.doElementsExists($allNoteDialogs) ){
            return;
        }

        this.attachEditEvent();
        this.attachSaveEvent();
        this.attachDeleteNoteEvent();
    };

    /**
     * @description Selecting option corresponding to the category on which im at atm.
     */
    public static selectCurrentCategoryOptionForQuickNoteCreator() {
        let $quickNoteSelect = $("select#my_notes_category");
        let $selectOptions   = $("select#my_notes_category option");
        let getAttrs         = JSON.parse(Navigation.getCurrentGetAttrs());

        if ( !StringUtils.isEmptyString(getAttrs.categoryId) ) {
            let categoryId = getAttrs.categoryId;
            $quickNoteSelect.val(categoryId);

            $selectOptions.each(function (index, option) {
                let $option = $(option);
                if ($option.val() == categoryId) {
                    $option.attr("selected", "true");
                }
            });
        }
    }

    /**
     * Attaches event responsible for allowing user to edit note by clicking on edit button (modal)
     */
    private attachEditEvent(): void
    {
        let _this       = this;
        let editButtons = TinyMce.classes["note-modal-edit"];

        $(editButtons).each((index, button) => {

            $(button).click((event) => {

                let clickedButton   = event.target;
                let modal           = $(clickedButton).closest(TinyMce.classes['note-modal-content']);
                let $tinyMceWrapper = modal.find(`#${TinyMce.TINY_MCE_WRAPPER_ID_NAME}`);
                let noteTitle       = $(modal).find(TinyMce.classes["note-modal-title"]);
                let categoriesList  = $(modal).find(TinyMce.classes["note-modal-categories-list"]);

                if ($(categoriesList).hasClass(TinyMce.classes.prefixless.hidden)) {
                    $(categoriesList).removeClass(TinyMce.classes.prefixless.hidden);
                }

                $(noteTitle).attr('contenteditable', 'true');
                $(noteTitle).css({'border-bottom': '1px rgba(0,0,0,0.2) solid'});

                let id              = $(button).attr('data-id');
                let tinyMceSelector = TinyMce.classes["note-modal-tinymce-content"] + id;

                $tinyMceWrapper.addClass("is-edited");

                _this.tinyMce.init(tinyMceSelector);
            })
        })
    };

    /**
     * Attaches save event responsible for saving note content (modal)
     */
    private attachSaveEvent(): void
    {
        let _this       = this;
        let saveButtons = TinyMce.classes["note-modal-save"];

        $(saveButtons).each((index, button) => {

            $(button).click((event) => {
                let clickedButton    = event.target;
                let modal            = $(clickedButton).closest(TinyMce.classes.modal);
                let tinymceModal     = $(modal).find('iframe');
                let modalCloseButton = $(modal).find(TinyMce.classes["note-modal-close-button"]);

                if ( DomElements.doElementsExists(tinymceModal) ) {
                    let noteId         = $(clickedButton).attr('data-id');
                    let noteBody       = $(tinymceModal).contents().find('body').html();
                    let noteTitle      = $(modal).find(TinyMce.classes["note-modal-title"]);
                    let noteCategoryId = $(modal).find(TinyMce.classes["note-modal-category"]).find(':selected');

                    let data = {
                        'id': noteId,
                        'body': noteBody,
                        'title': noteTitle.text(),
                        'category': {
                            "type": "entity",
                            'namespace': 'App\\Entity\\Modules\\Notes\\MyNotesCategories',
                            'id': $(noteCategoryId).val(),
                        },
                    };

                    Loader.showMainLoader();

                    $.ajax({
                        method: 'POST',
                        url: '/my-notes/update/',
                        data: data,
                    }).done(() => {
                        _this.bootstrapNotify.showGreenNotification(TinyMce.messages["note-update-success"]);
                        _this.setNoteTitleForNoteOnList(noteId, noteTitle.text());
                    }).fail(() => {
                        _this.bootstrapNotify.showRedNotification(TinyMce.messages["note-update-fail"]);
                    }).always(() => {
                        Loader.hideMainLoader();
                    });

                }

            })

        })
    };

    /**
     * Attaches logic for removing note aftet pressing button (in modal)
     */
    private attachDeleteNoteEvent(): void
    {
        let _this         = this;
        let deleteButtons = TinyMce.classes["note-modal-delete"];
        $(deleteButtons).each((index, button) => {

            $(button).click((event) => {
                let clickedButton = event.target;
                let noteId = $(clickedButton).attr('data-id');
                let data = {
                    'id': noteId,
                };

                BootboxWrapper.confirm({
                    message:  AbstractAction.messages.doYouWantToRemoveThisRecord(),
                    backdrop: true,
                    callback: function (result) {
                        if (result) {
                            Loader.showMainLoader();

                            $.ajax({
                                method : Ajax.REQUEST_TYPE_POST,
                                url    : '/my-notes/delete-note/',
                                data   : data,
                            }).done(() => {
                                _this.bootstrapNotify.showGreenNotification(TinyMce.messages["note-delete-success"]);
                                $(clickedButton).closest(TinyMce.classes["note-wrapper"]).html("");
                                $(TinyMce.classes["modal-shadow"]).remove();

                                let allNotes = $(TinyMce.classes["note-button"]);

                                if ($(allNotes).length === 0) {
                                    UiUtils.redirectWithMessage('/my-notes/create', 'There are no notes left in this category, You will be redirected in a moment');
                                }

                            }).fail(() => {
                                _this.bootstrapNotify.showRedNotification(TinyMce.messages["note-delete-fail"]);
                            }).always( () => {
                                Loader.hideMainLoader();
                            });
                        }
                    }
                });
            })
        })
    };

    /**
     * @description will set title of note with given id
     *
     * @param noteId {string}
     * @param newTitle {string}
     * @private
     */
    private setNoteTitleForNoteOnList(noteId: string, newTitle: string): void
    {
        let $listElement = $(`${NotesTinyMce.NOTE_LIST_ELEMENT_PARTIAL_SELECTOR}${noteId}`);
        $listElement.find('span').text(newTitle);
    }

}