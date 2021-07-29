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
import Ajax             from "./Ajax";
import BootboxWrapper   from "../../libs/bootbox/BootboxWrapper";
import Application      from "../Application";
import UiUtils          from "../utils/UiUtils";

/**
 * @default This class contains definitions of events and it's logic attached on GUI elements
 *          This could be remain in Ajax.ts however there are issues with circular dependencies event with statics
 */
export default class AjaxEvents extends AbstractAjax {

    /**
     * @type BootstrapNotify
     */
    private bootstrapNotify = new BootstrapNotify();

    /**
     * @type Initializer
     */
    private initializer = new Initializer();

    /**
     * @type UiUtils
     */
    private uiUtils = new UiUtils();

    public init()
    {
        this.attachModuleContentLoadingViaAjaxOnLinks();
    }

    /**
     * @description Attaches module content load by clicking on links in menu
     */
    public attachModuleContentLoadingViaAjaxOnLinks(): void
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
     * @param url                            {string}
     * @param callbackAfterInsertingTemplate {function}
     * @param showMessages                   {boolean}
     * @param callbackAfterReinitialize      {function}
     * @description This method will fetch module template and load it into mainBody, not showing message here on purpose
     */
    public loadModuleContentByUrl(url:string, callbackAfterInsertingTemplate:Function = undefined, showMessages:boolean = false, callbackAfterReinitialize:Function = undefined): void
    {
        let _this = this;

        let showLoaderTimeout = setTimeout(function(){
            Loader.showMainLoader();
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

            if( ajaxResponseDto.isRouteSet() ){
                this.loadModuleContentByUrl(ajaxResponseDto.routeUrl);
                return;
            }

            if( ajaxResponseDto.isTemplateSet() ){
                Ui.insertIntoMainContent(ajaxResponseDto.template);
            }

            if( $.isFunction(callbackAfterInsertingTemplate) ){
                callbackAfterInsertingTemplate();
            }

            /**
             * Despite this being called imagesLoaded it works fine with normal content as well
             * This is badly required for case when module contains images
             */
            imagesLoaded( twigBodySection, function() {
                _this.initializer.reinitializeLogic();
                Loader.hideMainLoader();
                clearTimeout(showLoaderTimeout);
                history.pushState({}, null, url);
                Sidebars.markCurrentMenuElementAsActive();
                _this.uiUtils.keepUploadBasedMenuOpen();
                MasonryGallery.init();
                if( $.isFunction(callbackAfterReinitialize) ){
                    callbackAfterReinitialize();
                }
            });

            if( ajaxResponseDto.isTitleSet() ){
                Application.setTitle(ajaxResponseDto.pageTitle);
            }

            if( ajaxResponseDto.reloadPage ){
                if( ajaxResponseDto.isReloadMessageSet() ){
                    _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                }
                location.reload();
            }
        });
    }

    /**
     * @description Calls file removal for given files paths - via ajax
     * @param filesPaths
     * @param callback
     * @param async
     */
    public callAjaxFileRemovalForFilePath(filesPaths: Array<string>, callback = null, async = true){
        let _this             = this;
        let escapedFilesPaths = [];

        $.each(filesPaths, (index, filePath) => {
            let escapedFilePath = ( filePath.indexOf('/') === 0 ? filePath.replace("/", "") : filePath ) ;
            escapedFilesPaths.push(escapedFilePath);
        })

        let data = {
            "files_full_paths":  escapedFilesPaths
        };

        Loader.showMainLoader();
        $.ajax({
            method:  Ajax.REQUEST_TYPE_POST,
            url:     AbstractAjax.API_URLS.fileRemoval,
            data:    data,
            async:   async,
        }).always((data) => {

            Loader.hideMainLoader();
            BootboxWrapper.hideAll();
            let ajaxResponseDto = AjaxResponseDto.fromArray(data);

            if( !ajaxResponseDto.isSuccessCode() ) {
                _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                return;
            }

            _this.bootstrapNotify.showGreenNotification(ajaxResponseDto.message);

            if( $.isFunction(callback) ){
                callback();
            }

        });
    };

}