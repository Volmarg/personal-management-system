import * as $ from 'jquery';

import BootstrapNotify  from "../../libs/bootstrap-notify/BootstrapNotify";
import AjaxResponseDto  from "../../DTO/AjaxResponseDto";
import Loader           from "../../libs/loader/Loader";
import Sidebars         from "../../core/sidebar/Sidebars";
import MasonryGallery   from "../../libs/masonry/MasonryGallery";
import Initializer      from "../../Initializer";
import StringUtils      from "../utils/StringUtils";
import DomElements      from "../utils/DomElements";
import AjaxEvents       from "./AjaxEvents";
import AbstractAjax     from "./AbstractAjax";

var imagesLoaded = require('imagesloaded');

/**
 * @description This class contains the most common/reusable ajax calls required in many places of the project
 *              like for example loading template for url or rebuilding menu
 */
export default class Ajax extends AbstractAjax{

    /**
     * @type Object
     */
    private static methods = {
        singleMenuNodeReload: {
            url: '/actions/render-menu-node-template',
            method: Ajax.REQUEST_TYPE_POST
        },
        getFormView: {
            url: '/api/get-form-view-by-class-name',
            method: Ajax.REQUEST_TYPE_POST
        },
        getTemplateData: {
            url: '/api/get-template-data', // not implemented on backend
            method: Ajax.REQUEST_TYPE_GET
        },
        getUrlForPathName: {
            url: '/api/system/get-url-for-path-name',
            method: Ajax.REQUEST_TYPE_POST
        },
        getConstantValueFromBackend: {
            url: '/api/system/get-constant-value-from-backend',
            method: Ajax.REQUEST_TYPE_POST
        }
    };

    /**
     * @type Object
     */
    private selectors = {
        data: {
            reloadTargetElementData : "data-reload-target-element-data",
            targetElementSelector   : "data-target-element-selector",
            templateLocation        : "data-template-location",
            templateData            : "data-template-data",
            jsLogic                 : "data-js-logic"
        }
    };

    /**
     * @type BootstrapNotify
     */
    private bootstrapNotify = new BootstrapNotify();

    /**
     * @type Initializer
     */
    private initializer = new Initializer();

    /**
     * @type AjaxEvents
     */
    private ajaxEvents = new AjaxEvents();

    /**
     * Will reload menu node
     * @param menuNodeModuleName {string}
     * @param returnNotification {boolean}
     * @param callbackAfter
     * @param async
     */
    public singleMenuNodeReload(menuNodeModuleName:string = null, returnNotification:boolean = false, callbackAfter: Function = () => {}, async: boolean = true): void
    {
        let $menuNode = $('.sidebar-menu-node-element[data-menu-node-name^="' + menuNodeModuleName + '"]');

        if( StringUtils.isEmptyString(menuNodeModuleName) ){
            let $currentActiveMenuLink = $('a.sidebar-link.active');
            $menuNode                   = $currentActiveMenuLink.closest('.sidebar-menu-node-element');
            menuNodeModuleName          = $menuNode.attr('data-menu-node-name');
        }

        if( StringUtils.isEmptyString(menuNodeModuleName) ){
            throw("Menu node name was not defined");
        }

        if( !DomElements.doElementsExists($menuNode) ){
            throw('Menu node with name: ' + menuNodeModuleName + ' - was not found');
        }

        if( 1 < $menuNode.length ){
            throw('More than one menu nodes with name: ' + menuNodeModuleName + ' were found.');
        }

        let data = {
            "menu_node_module_name": menuNodeModuleName
        };

        let _this  = this;
        let url    = Ajax.methods.singleMenuNodeReload.url;
        let method = Ajax.methods.singleMenuNodeReload.method;

        $.ajax({
            url:    url,
            method: method,
            data:   data,
            async: async,
        }).always((data) => {

            let ajaxResponseDto  = AjaxResponseDto.fromArray(data);
            let notificationType = ( ajaxResponseDto.isSuccessCode() ? "success" : "danger" );

            if( !ajaxResponseDto.isSuccessCode() ){
                _this.bootstrapNotify.showRedNotification("Internal server error");
                return;
            }

            if( !ajaxResponseDto.isMessageSet() ){
                return;
            }

            if( ajaxResponseDto.isTemplateSet() ){
                $menuNode.replaceWith(ajaxResponseDto.template);

                //@ts-ignore
                Sidebars.init();
                _this.initializer.reinitializeLogic();
            }

            if(returnNotification){
                _this.bootstrapNotify.notify(ajaxResponseDto.message, notificationType)
            }

            _this.ajaxEvents.attachModuleContentLoadingViaAjaxOnLinks();

            if( ajaxResponseDto.reloadPage ){
                if( ajaxResponseDto.isReloadMessageSet() ){
                    _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                }
                location.reload();
            }
            callbackAfter();
        });
    }

    /**
     * This function will get the form view for namespace if such form exists, otherwise - error
     * @param namespace
     * @param callback
     * @param stripFormTag    - if set to true then form opening and closing tags will removed ,
     *  this is required in case when one form is subform, it won't even send data to backend,
     *  symfony does not allow to submit multiple forms at once.
     */
    public getFormViewByNamespace(namespace:string, callback:Function, stripFormTag:boolean = true): void
    {
        let _this = this;
        Loader.showMainLoader();

        let requestData = {
            'form_namespace': namespace
        };

        $.ajax({
            url:    Ajax.methods.getFormView.url,
            method: Ajax.methods.getFormView.method,
            data:   requestData
        }).always((data) => {
            Loader.hideMainLoader();

            let ajaxResponseDto = AjaxResponseDto.fromArray(data);
            let formTemplate    = ajaxResponseDto.formTemplate;

            if( !ajaxResponseDto.success ){
                _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                return false;
            }

            if( stripFormTag ){
                formTemplate = formTemplate.replace(/<(.*?)form(.*?)>/, '');
                formTemplate = formTemplate.replace('</form>','');
            }

            if( $.isFunction(callback) ){
                callback(formTemplate);
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
     * Will fetch value of constant from backend
     * @param namespace
     * @param constant
     * @return string
     */
    public static getConstantValueFromBackend(namespace: string, constant:string): string
    {
        let bootstrapNotify = new BootstrapNotify();
        let constantValue   = "";
        let ajaxData        = {
            "constantName"  : constant,
            "namespace"     : namespace
        };

        $.ajax({
            url    : Ajax.methods.getConstantValueFromBackend.url,
            method : Ajax.methods.getConstantValueFromBackend.method,
            data   : ajaxData,
            async  : false, // must be async, need to return url, and cannot process other requests without it
        }).always(function(data){

            try{
                var ajaxResponseDto = AjaxResponseDto.fromArray(data);
            }catch(Exception){
                throw{
                    "message": "Exception was throw while trying to get data from url generator",
                    "exc"    : Exception,
                }
            }

            if( ajaxResponseDto.isSuccessCode() ){
                constantValue = ajaxResponseDto.constantValue;
                return;
            }else{
                bootstrapNotify.showRedNotification(ajaxResponseDto.message);
            }
        });

        return constantValue;
    }

    /**
     * @description Will fetch backend defined url for given path
     * @param pathName - path/route name for which url should be generated
     */
    public static getUrlForPathName(pathName: string): string
    {
        let bootstrapNotify = new BootstrapNotify();
        let returnedUrl     = "";
        let ajaxData        = {
            "pathName" : pathName
        };

        $.ajax({
            url    : Ajax.methods.getUrlForPathName.url,
            method : Ajax.methods.getUrlForPathName.method,
            data   : ajaxData,
            async  : false, // must be async, need to return url, and cannot process other requests without it
        }).always(function(data){

            try{
                var ajaxResponseDto = AjaxResponseDto.fromArray(data);
            }catch(Exception){
                throw{
                    "message": "Exception was throw while trying to get data from url generator",
                    "exc"    : Exception,
                }
            }

            if( ajaxResponseDto.isSuccessCode() ){
                returnedUrl = ajaxResponseDto.routeUrl;
                return;
            }else{
                bootstrapNotify.showRedNotification(ajaxResponseDto.message);
            }
        });

        return returnedUrl;
    }

    /**
     * @deprecated - not used and not tested after adding
     * @description
     * - fetches template content,
     * - uses the provided data to generate template,
     * - inserts template data into target element,
     * - attaches additional js logic,
     */
    private reloadTargetPageElementByTemplateLocationAndTemplateDataOnClick():void
    {
        let $allElementsToAttachEventOn = $(this.selectors.data.reloadTargetElementData + "=[true]");
        let _this                       = this;

        $.each($allElementsToAttachEventOn, function(index, element){
            let $element = $(element);

            $element.on('click', function(){

                let targetElementSelector = $element.attr(_this.selectors.data.targetElementSelector);
                let templateLocation      = $element.attr(_this.selectors.data.templateLocation);
                let templateData          = $element.attr(_this.selectors.data.templateData);
                let jsLogic               = $element.attr(_this.selectors.data.jsLogic);

                let $targetElement = $(targetElementSelector);

                if( !DomElements.doElementsExists($targetElement) ){
                    throw({
                        "message"  : "No element was found for given selector",
                        "selector" : targetElementSelector
                    });
                }else if(1 < $targetElement.length){
                    throw({
                        "message"  : "More than one element was found for given selector",
                        "selector" : targetElementSelector,
                        "elements" : $targetElement
                    })
                }

                let callbackAfterGettingRenderedTemplate = function(renderedTemplate){
                    $targetElement.html(renderedTemplate);
                    let func = new Function(jsLogic);
                    func();
                };

                let dataArray = {
                    'templateLocation' : templateLocation,
                    'templateData'     : templateData,
                };

                _this.getRenderedTemplateForTemplateLocationAndData(dataArray, callbackAfterGettingRenderedTemplate)

            })
        });
    }

    /**
     * @deprecated - not used and not tested after adding
     * @param dataArray  {object}
     * @param callback   {function}
     */
    private getRenderedTemplateForTemplateLocationAndData(dataArray, callback = null)
    {
        let _this = this;

        $.ajax({
            url:  Ajax.methods.getTemplateData.url,
            data: dataArray,
        }).always(function(data){

            try{
                var ajaxResponseDto = AjaxResponseDto.fromArray(data);
            }catch(Exception){
                throw{
                    "message": "Exception was throw while trying to get data from ajax get rendered template",
                    "exc"    : Exception,
                }
            }

            if( ajaxResponseDto.isSuccessCode()){
                if( $.isFunction(callback) ){
                    callback(ajaxResponseDto.template);
                }
            }

            if( ajaxResponseDto.reloadPage ){
                if( ajaxResponseDto.isReloadMessageSet() ){
                    _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                }
                location.reload();
            }
        })
    }
}