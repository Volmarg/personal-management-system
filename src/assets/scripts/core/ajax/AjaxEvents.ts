import Ui from "../ui/Ui";

var imagesLoaded = require('imagesloaded');

import * as $           from "jquery";
import StringUtils      from "../utils/StringUtils";
import Loader           from "../../libs/loader/Loader";
import AjaxResponseDto  from "../../DTO/AjaxResponseDto";
import Sidebars         from "../sidebar/Sidebars";
import MasonryGallery   from "../../libs/masonry/MasonryGallery";
import BootstrapNotify  from "../../libs/bootstrap-notify/BootstrapNotify";
import Initializer      from "../../Initializer";
import AbstractAjax     from "./AbstractAjax";
import Modal            from "../ui/Modal/Modal";

/**
 * @default This class contains definitions of events and it's logic attached on GUI elements
 *          This could be remain in Ajax.ts however there are issues with circular dependencies event with statics
 */
export default class AjaxEvents extends AbstractAjax {

    /**
     * @type Modal
     */
    private modal = new Modal();

    /**
     * @type BootstrapNotify
     */
    private bootstrapNotify = new BootstrapNotify();

    /**
     * @type Initializer
     */
    private initializer = new Initializer();

    public init()
    {
        this.attachModuleContentLoadingViaAjaxOnMenuLinks();
    }

    /**
     * @description Attaches module content load by clicking on links in menu
     */
    public attachModuleContentLoadingViaAjaxOnMenuLinks(): void
    {
        let _this = this;
        let excludeHrefsRegexPatterns = [
            /^javascript.*/g,
            /^#.*/g,
            /^\/logout/g
        ];

        let allElements = $('.sidebar-menu .sidebar-link, .ajax-content-load');

        $.each(allElements, (index, element) => {
            let href = $(element).attr('href');
            let skip = false;

            $.each(excludeHrefsRegexPatterns, (index, pattern) => {
                if( null !== href.match(pattern) ){
                    skip = true;
                }
            });

            if ( !skip && StringUtils.isEmptyString(href) ){
                skip = true;
            }

            if( !skip ){

                $(element).off('click'); //prevent stacking
                $(element).on('click', (event) => {

                    event.preventDefault();
                    _this.loadModuleContentByUrl(href);
                })

            }

        })
    }

    /**
     * @param url           {string}
     * @param callbackAfter {function}
     * @param showMessages  {boolean}
     * @description This method will fetch module template and load it into mainBody, not showing message here on purpose
     */
    public loadModuleContentByUrl(url:string, callbackAfter:Function = undefined, showMessages:boolean = false): void
    {
        let _this = this;

        let showLoaderTimeout = setTimeout(function(){
            Loader.showLoader();
        }, 500);

        $.ajax({
            url:    url,
            method: AbstractAjax.REQUEST_TYPE_GET,
        }).always((data) => {
            let twigBodySection = $('.twig-body-section');

            try{
                var ajaxResponseDto = AjaxResponseDto.fromArray(data);
            } catch(Exception){
                throw({
                    "message"   : "Could not handle ajax call",
                    "data"      : data,
                    "exception" : Exception
                })
            }

            if( ajaxResponseDto.isTemplateSet() ){
                Ui.insertIntoMainContent(ajaxResponseDto.template);
            }

            if( $.isFunction(callbackAfter) ){
                callbackAfter();
            }

            /**
             * Despite this being called imagesLoaded it works fine with normal content as well
             * This is badly required for case when module contains images
             */
            imagesLoaded( twigBodySection, function() {
                _this.initializer.reinitializeLogic();
                Loader.hideLoader();
                clearTimeout(showLoaderTimeout);
                history.pushState({}, null, url);
                Sidebars.markCurrentMenuElementAsActive();
                MasonryGallery.init();
            });

            if( ajaxResponseDto.reloadPage ){
                if( ajaxResponseDto.isReloadMessageSet() ){
                    _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                }
                location.reload();
            }
        });
    }

}