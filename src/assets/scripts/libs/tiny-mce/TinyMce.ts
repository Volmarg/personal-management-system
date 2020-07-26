import tinymce from 'tinymce/tinymce';
import 'tinymce/themes/silver';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';
import 'tinymce/plugins/image';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/paste';
import 'tinymce/plugins/codesample';

import BootstrapNotify from "../bootstrap-notify/BootstrapNotify";
import StringUtils from "../../core/utils/StringUtils";

export default class TinyMce {

    /**
     * @type Object
     */
    public static classes = {
        'tiny-mce-selector'          : '.tiny-mce',
        'note-modal-buttons'         : '.modal-note-details-buttons',
        'note-modal-delete'          : '.delete-note',
        'note-modal-edit'            : '.edit-note',
        'note-modal-save'            : '.save-note',
        'note-modal-content'         : ".modal-content",
        'note-modal-tinymce-content' : ".modal-tinymce-body-", //requires id to this - added in function, cannot be used as standalone
        'modal'                      : ".modal",
        'note-modal-title'           : '.note-title',
        'note-modal-category'        : '.note-category',
        'note-modal-categories-list' : '.note-modal-categories-list',
        'note-wrapper'               : '.single-note-details',
        'modal-shadow'               : '.modal-backdrop',
        'note-button'                : '.note-button',
        'note-modal-close-button'    : 'button.close',
         prefixless: {
            'hidden': 'd-none'
        }
    };

    /**
     * @type Object
     */
    public static messages: {
        'note-delete-success' : 'Note has been successfully deleted',
        'note-delete-fail'    : 'There was an error while deleting the note',
        'note-update-success' : 'Note has been successfully updated',
        'note-update-fail'    : 'There was an error while updating the note',
        'note-save-fail'      : 'Cannot save note changes without editing it first!',
    };

    /**
     * @type BootstrapNotify
     */
    private bootstrapNotify = new BootstrapNotify();

    /**
     *
     * @param selector {string}
     */
    public static getDefaultTinyMceConfig(selector: string): object
    {
        return {
            menubar: false,
            selector: selector,
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
                { text: 'BASH', value: 'bash' }
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
            }
        }
    };

    /**
     * Main initialization logic
     */
    public init(tinyMceSelector: string = null) {

        if( StringUtils.isEmptyString(tinyMceSelector) ){
            tinyMceSelector = TinyMce.classes["tiny-mce-selector"];
        }

        let config = TinyMce.getDefaultTinyMceConfig(tinyMceSelector);

        tinymce.remove(TinyMce.classes["tiny-mce-selector"]);
        tinymce.init(config);

        this.setDefaultTextAlignment();
        this.preventFocusHijack();
    };


    /**
     * Gets content of the tinymce editor body (html)
     * @param tinyMceInstanceSelector {string}
     * @returns {string}
     */
    public static getTextContentForTinymceIdSelector(tinyMceInstanceSelector){
        let tinymceInstance = tinymce.get(tinyMceInstanceSelector);

        if( tinymceInstance === null ){
            throw{
                "message"  : "This is not a tinymce instance",
                "selector" : tinyMceInstanceSelector
            }
        }

        let tinymceContent  = tinymceInstance.getContent();
        return tinymceContent;
    };

    /**
     * Returns tinymce instance for given selector
     * @param selector
     */
    public static getTinyMceInstanceForSelector(selector: string)
    {
        let tinymceInstance = tinymce.get(selector);
        return tinymceInstance;
    }

    /**
     * Will destroy the tinymce instance for given selector
     * @param tinyMceInstanceSelector
     */
    public static remove(tinyMceInstanceSelector)
    {
        tinymce.remove(tinyMceInstanceSelector);
    }

    /**
     * Fix Problem with misbehaving text-alignment
     */
    private setDefaultTextAlignment(): void
    {
        $(document).ready(() => {
            let iframe_body = $('iframe').contents().find("body");

            $(iframe_body).on("DOMNodeInserted", function (event) {
                $(event.target)
                    .addClass('left')
                    .css({"text-align": "left"})
                    .attr("data-mce-style", "text-align: left");
            });
        });
    };

    /**
     * This fixes the problem where jquery/Bootstrap is stealing focus from TinyMCE textarea
     * In this case for plugin:
     * - codesample
     */
    private preventFocusHijack(): void
    {
        $(document).on('focusin', function(event) {

            // this is handled for codesample plugin - without it textarea is unclickable
            let $toxTextArea = $(event.target).closest(".tox-textarea");

            if ( $toxTextArea.length ) {
                event.stopImmediatePropagation();
            }
        });
    };

}