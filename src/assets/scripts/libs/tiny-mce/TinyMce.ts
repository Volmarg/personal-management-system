var tinymce = require('tinymce/tinymce');// must be here, because themes are using it - otherwise will crash

import 'tinymce/themes/silver';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';
import 'tinymce/plugins/image';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/paste';
import 'tinymce/plugins/codesample';
import 'tinymce/icons/default'

import StringUtils from "../../core/utils/StringUtils";

/**
 * @description Handles tinymce logic
 *              window.tinymce - must be used instead of `tinymce` directly because the tinymce import has assignment
 *              to window inside
 */
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
        'tinyMceWrapper'             : ".tox-tinymce",
        prefixless: {
            'hidden': 'd-none'
        }
    };

    /**
     * @type Object
     */
    public static messages = {
        'note-delete-success' : 'Note has been successfully deleted',
        'note-delete-fail'    : 'There was an error while deleting the note',
        'note-update-success' : 'Note has been successfully updated',
        'note-update-fail'    : 'There was an error while updating the note',
        'note-save-fail'      : 'Cannot save note changes without editing it first!',
    };

    public static dataAttributes = {
      styleCopy: "data-style-copy"
    };

    static readonly TINY_MCE_WRAPPER_ID_NAME = "tiny-mce-wrapper";

    /**
     *@description will return the default(common) configuration for tinymce
     *
     * @param selector {string}
     * @param tinyMceWrapperClasses
     */
    public static getDefaultTinyMceConfig(selector: string, tinyMceWrapperClasses: string = null): object
    {
        return {
            menubar: false,
            selector: selector,
            mode: "specific_textareas",
            plugins: ['lists', 'table', 'image', 'preview', 'paste', 'codesample'],
            toolbar: 'bold italic strikethrough | formatselect fontselect | forecolor colorpicker | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | removeformat | image | codesample | preview',
            toolbar_mode: 'wrap',
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

                    if(
                            !StringUtils.isEmptyString(selector)
                        &&  !StringUtils.isEmptyString(tinyMceWrapperClasses)
                    ){
                        TinyMce.setTinyMceWrapperClasses(selector, tinyMceWrapperClasses);
                    }
                });
                ed.on('change', function () {
                    //@ts-ignore
                    window.tinymce.triggerSave();
                });
            }
        }
    };

    /**
     * @description Main initialization logic
     */
    public init(tinyMceSelector: string = null, tinyMceWrapperClasses: string = null) {

        if( StringUtils.isEmptyString(tinyMceSelector) ){
            tinyMceSelector = TinyMce.classes["tiny-mce-selector"];
        }

        let config = TinyMce.getDefaultTinyMceConfig(tinyMceSelector, tinyMceWrapperClasses);

        if( StringUtils.isEmptyString(tinyMceSelector) ){
            TinyMce.remove(TinyMce.classes["tiny-mce-selector"]);
        }else{
            TinyMce.remove(tinyMceSelector);
        }

        //@ts-ignore
        window.tinymce.init(config);

        this.setDefaultTextAlignment();
        this.preventFocusHijack();
    };


    /**
     * @description Gets content of the tinymce editor body (html)
     *
     * @param tinyMceInstanceSelector {string}
     * @returns {string}
     */
    public static getTextContentForTinymceIdSelector(tinyMceInstanceSelector){
        //@ts-ignore
        let tinymceInstance = window.tinymce.get(tinyMceInstanceSelector);

        if( tinymceInstance === null ){
            throw{
                "message"  : "This is not a tinymce instance",
                "selector" : tinyMceInstanceSelector
            }
        }

        let tinymceContent  = tinymceInstance.getContent({format : 'raw'});
        return tinymceContent;
    };

    /**
     * @description Returns tinymce instance for given selector
     *
     * @param selector
     */
    public static getTinyMceInstanceForSelector(selector: string)
    {
        //@ts-ignore
        let tinymceInstance = window.tinymce.get(selector);
        return tinymceInstance;
    }

    /**
     * @description Will destroy the tinymce instance for given selector
     *
     * @param tinyMceInstanceSelector
     */
    public static remove(tinyMceInstanceSelector)
    {
        /**
         * @description there is some issue in TinyMCE itself - it removes pretty much like all tags upon removing/destroying
         *              instance, that's why this workaround is needed (save all styles, then revert them)
         */
        let $contentWrapper = $(tinyMceInstanceSelector);
        let $allElements    = $contentWrapper.find("*");

        let arrayOfSaveStylesForElementIndex = [];
        $.each($allElements, (index, element) => {
            let $element     = $(element);
            let elementStyle = $element.attr('style');

            if( StringUtils.isEmptyString(elementStyle) ){
                elementStyle = "";
            }

            arrayOfSaveStylesForElementIndex.push(elementStyle);
        });

        //@ts-ignore
        window.tinymce.remove(tinyMceInstanceSelector);

        /**
         * @description even if these are still the same elements - the ones above have different `grip/state` so
         *              these above still have tags and thus these are not reapplied, new state must be used
         */
        $allElements = $contentWrapper.find("*");
        $.each($allElements, (index, element) => {
            let $element     = $(element);
            let elementStyle = arrayOfSaveStylesForElementIndex[index];

            if( !StringUtils.isEmptyString(elementStyle) ){
                $element.attr('style', elementStyle);
            }
        });
    }

    /**
     * @description Will destroy all instances of tinymce
     */
    public static removeAllInstances(): void
    {
        //@ts-ignore
        while ( window.tinymce.editors.length > 0) {
            //@ts-ignore
            TinyMce.remove(window.tinymce.editors[0]);
        }
    }

    /**
     * @description Fix Problem with misbehaving text-alignment
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
     * @description This fixes the problem where jquery/Bootstrap is stealing focus from TinyMCE textarea
     *              In this case for plugin:
     *              - codesample
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

    /**
     * @description will set additional classes on the whole (top) wrapper of tinymce
     *              this allows to for example add some padding etc.
     *
     * @private
     */
    private static setTinyMceWrapperClasses(tinyMceSelector: string, tinyMceWrapperClasses: string): void
    {
        let $tinyMceDomElement = $(tinyMceSelector).parent().find(TinyMce.classes.tinyMceWrapper);
        $tinyMceDomElement.addClass(tinyMceWrapperClasses);
    }

}