import tinymce from 'tinymce/tinymce';
import 'tinymce/themes/silver';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';
import 'tinymce/plugins/image';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/paste';
import 'tinymce/plugins/codesample';

import utils_custom from "../utils/utils_custom";
import Prism from 'prismjs';

var IconPicker = require('@furcan/iconpicker');

export default (function () {

    if (typeof window.tinymce === 'undefined') {
        window.tinymce = {};
    }

    tinymce.custom = {
        classes: {
            'tiny-mce-selector': '.tiny-mce',
            'note-modal-buttons': '.modal-note-details-buttons',
            'note-modal-delete': '.delete-note',
            'note-modal-edit': '.edit-note',
            'note-modal-save': '.save-note',
            'note-modal-content': ".modal-content",
            'note-modal-tinymce-content': ".modal-tinymce-body-", //requires id to this - added in function, cannot be used as standalone
            'modal': ".modal",
            'note-modal-title': '.note-title',
            'note-modal-category': '.note-category',
            'note-modal-categories-list': '.note-modal-categories-list',
            'note-wrapper': '.single-note-details',
            'modal-shadow': '.modal-backdrop',
            'note-button': '.note-button',
            'note-modal-close-button': 'button.close',
            prefixless: {
                'hidden': 'd-none'
            }
        },
        messages: {
            'note-delete-success': 'Note has been successfully deleted',
            'note-delete-fail': 'There was an error while deleting the note',
            'note-update-success': 'Note has been successfully updated',
            'note-update-fail': 'There was an error while updating the note',
            'note-save-fail': 'Cannot save note changes without editing it first!',
        },
        init: function () {
            let config = this.config;
            tinymce.remove(tinymce.custom.classes["tiny-mce-selector"]);
            config.selector = tinymce.custom.classes["tiny-mce-selector"];
            tinymce.init(config);

            this.setDefaultTextAlignment();
            this.changeClearFormattingButtonLogic();

            this.MyNotes.attachEditEvent();
            this.MyNotes.attachSaveEvent();
            this.MyNotes.attachDeleteNoteEvent();

            this.preventFocusHijack();
        },
        /**
         * Fix Problem with misbehaving text-alignment
         */
        setDefaultTextAlignment: function () {
            $(document).ready(() => {
                let iframe_body = $('iframe').contents().find("body");

                $(iframe_body).on("DOMNodeInserted", function (event) {
                    $(event.target)
                        .addClass('left')
                        .css({"text-align": "left"})
                        .attr("data-mce-style", "text-align: left");
                });
            });
        },
        /**
         * Gets content of the tinymce editor body (html)
         * @param tinyMceInstanceSelector {string}
         * @returns {string}
         */
        getTextContentForTinymceIdSelector: function(tinyMceInstanceSelector){
            let tinymceInstance = tinymce.get(tinyMceInstanceSelector);

            if( tinymceInstance === null ){
                throw{
                    "message"  : "This is not a tinymce instance",
                    "selector" : tinyMceInstanceSelector
                }
            }

            let tinymceContent  = tinymceInstance.getContent();
            return tinymceContent;
        },
        changeClearFormattingButtonLogic: function () {

        },
        config: {
            menubar: false,
            mode: "specific_textareas",
            plugins: ['lists', 'table', 'image', 'preview', 'paste', 'codesample'],
            toolbar: 'bold italic | formatselect fontselect | forecolor colorpicker | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | removeformat | image | codesample | preview',
            height: 400,
            forced_root_block: '',
            paste_data_images: true,
            image_uploadtab: true,
            codesample_global_prismjs: true,
            // codesample_languages - whenver You add anything in here, add also import for given language in `src/assets/scripts/prism/index.js:2`
            codesample_languages: [
                { text: 'HTML/XML', value: 'markup' },
                { text: 'JavaScript', value: 'javascript' },
                { text: 'CSS', value: 'css' },
                { text: 'PHP', value: 'php' },
                { text: 'BASH', value: 'bash' },
            ],
            images_dataimg_filter: function(img) {
                return img.hasAttribute('internal-blob');
            },
            setup: function (ed) {
                ed.on('init', function () {
                    this.getDoc().body.style.fontSize = '12';
                    this.getDoc().body.style.fontFamily = 'Arial';
                });
                ed.on('change', function () {
                    tinymce.triggerSave();
                });
            },
        },
        /**
         * This fixes the problem where jquery/Bootstrap is stealing focus from TinyMCE textarea
         * In this case for plugin:
         * - codesample
         */
        preventFocusHijack: function(){
            $(document).on('focusin', function(event) {

                // this is handled for codesample plugin - without it textarea is unclickable
                let $toxTextArea = $(event.target).closest(".tox-textarea");

                if ( $toxTextArea.length ) {
                    event.stopImmediatePropagation();
                }
            });
        },
        "MyNotes": {
            init: function (editButton) {
                let id      = $(editButton).attr('data-id');
                let config  = tinymce.custom.config;

                config.selector = tinymce.custom.classes["note-modal-tinymce-content"] + id;

                tinymce.init(config);
            },
            attachEditEvent: function () {
                let editButtons = tinymce.custom.classes["note-modal-edit"];

                $(editButtons).each((index, button) => {

                    $(button).click((event) => {

                        let clickedButton = event.target;
                        let modal = $(clickedButton).closest(tinymce.custom.classes['note-modal-content']);
                        let noteTitle = $(modal).find(tinymce.custom.classes["note-modal-title"]);
                        let categoriesList = $(modal).find(tinymce.custom.classes["note-modal-categories-list"]);

                        if ($(categoriesList).hasClass(tinymce.custom.classes.prefixless.hidden)) {
                            $(categoriesList).removeClass(tinymce.custom.classes.prefixless.hidden);
                        }

                        $(noteTitle).attr('contenteditable', 'true');
                        $(noteTitle).css({'border-bottom': '1px rgba(0,0,0,0.2) solid'});

                        this.init(button);
                    })

                })
            },
            attachSaveEvent: function () {
                let saveButtons = tinymce.custom.classes["note-modal-save"];

                $(saveButtons).each((index, button) => {

                    $(button).click((event) => {
                        let clickedButton       = event.target;
                        let modal               = $(clickedButton).closest(tinymce.custom.classes.modal);
                        let tinymceModal        = $(modal).find('iframe');
                        let modalCloseButton    = $(modal).find(tinymce.custom.classes["note-modal-close-button"]);

                        if (tinymceModal.length !== 0) {
                            let noteId = $(clickedButton).attr('data-id');
                            let noteBody = $(tinymceModal).contents().find('body').html();
                            let noteTitle = $(modal).find(tinymce.custom.classes["note-modal-title"]);
                            let noteCategoryId = $(modal).find(tinymce.custom.classes["note-modal-category"]).find(':selected');

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

                            $.ajax({
                                method: 'POST',
                                url: '/my-notes/update/',
                                data: data,
                            }).done(() => {
                                bootstrap_notifications.notify(tinymce.custom.messages["note-update-success"], 'success');
                                ui.ajax.loadModuleContentByUrl(location.pathname);
                                $(modalCloseButton).click();
                                $('.modal-backdrop').remove();
                            }).fail(() => {
                                bootstrap_notifications.notify(tinymce.custom.messages["note-update-fail"], 'danger');
                            });

                        }

                    })

                })
            },
            attachDeleteNoteEvent: function () {
                let deleteButtons = tinymce.custom.classes["note-modal-delete"];
                let _this = this;
                $(deleteButtons).each((index, button) => {

                    $(button).click((event) => {
                        let clickedButton = event.target;
                        let noteId = $(clickedButton).attr('data-id');
                        let data = {
                            'id': noteId,
                        };

                        ui.widgets.loader.showLoader();

                        $.ajax({
                            method: 'POST',
                            url: '/my-notes/delete-note/',
                            data: data,
                        }).done(() => {
                            bootstrap_notifications.notify(tinymce.custom.messages["note-delete-success"], 'success');
                            $(clickedButton).closest(tinymce.custom.classes["note-wrapper"]).html("");
                            $(tinymce.custom.classes["modal-shadow"]).remove();

                            let allNotes = $(tinymce.custom.classes["note-button"]);

                            if ($(allNotes).length === 0) {
                                utils.window.redirect('/my-notes/create', 'There are no notes left in this category, You will be redirected in a moment');
                            }

                        }).fail(() => {
                            bootstrap_notifications.notify(tinymce.custom.messages["note-delete-fail"], 'danger');
                        }).always( () => {
                            ui.widgets.loader.hideLoader();
                        });

                    })
                })
            }
        }
    };

}());
