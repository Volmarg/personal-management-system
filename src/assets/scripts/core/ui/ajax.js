import BootstrapNotify from "../../libs/bootstrap-notify/BootstrapNotify";
import AjaxResponseDto from "../../DTO/AjaxResponseDto";
import MasonryGallery  from "../../libs/masonry/MasonryGallery";
import Loader          from "../../libs/loader/Loader";

var imagesLoaded = require('imagesloaded');

// use AjaxGuiElementsReload.ts
export default (function () {
    if (typeof window.ui === 'undefined') {
        window.ui = {};
    }
    ui.ajax = {
        methods: {
            singleMenuNodeReload: {
                url: '/actions/render-menu-node-template',
                method: "POST"
            },
            getFormView: {
                url: '/api/get-form-view-by-class-name',
                method: "POST"
            },
            getTemplateData: {
                url: '/api/get-template-data', // not implemented on backend
                method: "GET"
            }
        },
        data: {
          reloadTargetElementData : "data-reload-target-element-data",
          targetElementSelector   : "data-target-element-selector",
          templateLocation        : "data-template-location",
          templateData            : "data-template-data",
          jsLogic                 : "data-js-logic"
        },
        bootstrapNotify: new BootstrapNotify(),
        init: function(){
            this.attachModuleContentLoadingViaAjaxOnMenuLinks();
        },
        /**
         * Will reload menu node
         * @param menuNodeModuleName
         * @param returnNotification
         */
        singleMenuNodeReload: function(menuNodeModuleName = null, returnNotification = false) {

            let $menuNode = null;

            if(
                    null === menuNodeModuleName
                || "undefined" === typeof menuNodeModuleName
            ){
                let $currentActiveMenuLink = $('a.sidebar-link.active');
                $menuNode                   = $currentActiveMenuLink.closest('.sidebar-menu-node-element');
                menuNodeModuleName          = $menuNode.attr('data-menu-node-name');
            }else{
                $menuNode = $('.sidebar-menu-node-element[data-menu-node-name^="' + menuNodeModuleName + '"]');
            }

            if( "undefined" === typeof menuNodeModuleName ){
                throw("Menu node name was not defined");
            }

            if( 0 === $menuNode.length ){
                throw('Menu node with name: ' + menuNodeModuleName + ' - was not found');
            }

            if( 1 < $menuNode.length ){
                throw('More than one menu nodes with name: ' + menuNodeModuleName + ' were found.');
            }

            let data = {
                "menu_node_module_name": menuNodeModuleName
            };

            let url    = this.methods.singleMenuNodeReload.url;
            let method = this.methods.singleMenuNodeReload.method;

            $.ajax({
                url: url,
                method: method,
                data: data,
            }).always((data) => {

                let ajaxResponseDto  = AjaxResponseDto.fromArray(data);
                let notificationType = ( ajaxResponseDto.isSuccessCode() ? "success" : "danger" );

                if( !ajaxResponseDto.isSuccessCode() ){
                    ui.ajax.bootstrapNotify.showRedNotification("Internal server error");
                    return;
                }

                if( !ajaxResponseDto.isMessageSet() ){
                    return;
                }

                if( ajaxResponseDto.isTemplateSet() ){
                    $menuNode.replaceWith(ajaxResponseDto.template);
                    window.sidebar.links.init();
                    initializer.reinitialize();
                }

                if(returnNotification){
                    ui.ajax.bootstrapNotify.notify(ajaxResponseDto.message, notificationType)
                }

                this.attachModuleContentLoadingViaAjaxOnMenuLinks();

                if( ajaxResponseDto.reloadPage ){
                    if( ajaxResponseDto.isReloadMessageSet() ){
                        ui.ajax.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                    }
                    location.reload();
                }
            });
        },
        /**
         * Attaches ajax call event `onclick` - will reload main content of module
         */
        attachModuleContentLoadingViaAjaxOnMenuLinks: function(){

            let _this = this;
            let excludeHrefsRegexPatterns = [
                /^javascript.*/g,
                /^#.*/g
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

                if ( !skip && "" == href ){
                    skip = true;
                }

                if( !skip ){

                    $(element).on('click', (event) => {

                        event.preventDefault();
                        _this.loadModuleContentByUrl(href);
                    })

                }

            })



        },
        /**
         * @param url           {string}
         * @param callbackAfter {function}
         * @param showMessages  {boolean}
         * @description This method will fetch module template and load it into mainBody, not showing message here on purpose
         */
        loadModuleContentByUrl: function (url, callbackAfter = undefined, showMessages = false){

            // fix for case when this call comes as second and somehow the previous call for hideLoader instantly hides also this one
            setTimeout(function(){
                Loader.showLoader();
            }, 500);

            $.ajax({
                url: url,
                method: "GET",
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
                    twigBodySection.html(ajaxResponseDto.template);
                }

                if( "function" === typeof callbackAfter ){
                    callbackAfter();
                }

                /**
                 * Despite this being called imagesLoaded it works fine with normal content as well
                 * This is badly required for case when module contains images
                 */
                imagesLoaded( twigBodySection, function() {
                    initializer.reinitialize();
                    Loader.hideLoader();
                    history.pushState({}, null, url);
                    sidebar.links.markCurrentMenuElementAsActive();
                    MasonryGallery.init();
                });

                if( ajaxResponseDto.reloadPage ){
                    if( ajaxResponseDto.isReloadMessageSet() ){
                        ui.ajax.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                    }
                    location.reload();
                }
            });
        },
        /**
         * This function will get the form view for namespace if such form exists, otherwise - error
         * @param namespace
         * @param callback
         * @param stripFormTag    - if set to true then form opening and closing tags will removed ,
         *  this is required in case when one form is subform, it won't even send data to backend,
         *  symfony does not allow to submit multiple forms at once.
         */
        getFormViewByNamespace: function(namespace, callback, stripFormTag = true){

            Loader.showLoader();

            let data = {
                'form_namespace': namespace
            };

            $.ajax({
                url: this.methods.getFormView.url,
                method: this.methods.getFormView.method,
                data: data
            }).always((data) => {
                Loader.hideLoader();

                let ajaxResponseDto = AjaxResponseDto.fromArray(data);
                let formTemplate    = ajaxResponseDto.formTemplate;

                if( !ajaxResponseDto.success ){
                    ui.ajax.bootstrapNotify.notify(ajaxResponseDto.message, 'danger');
                    return false;
                }

                if( stripFormTag ){
                    formTemplate = formTemplate.replace(/<(.*?)form(.*?)>/, '');
                    formTemplate = formTemplate.replace('</form>','');
                }

                if( "function" === typeof callback ){
                    callback(formTemplate);
                }

                if( ajaxResponseDto.reloadPage ){
                    if( ajaxResponseDto.isReloadMessageSet() ){
                        ui.ajax.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                    }
                    location.reload();
                }
            });
        },
        /**
         * @deprecated - not used and not tested after adding
         * @description
         * - fetches template content,
         * - uses the provided data to generate template,
         * - inserts template data into target element,
         * - attaches additional js logic,
         */
        reloadTargetPageElementByTemplateLocationAndTemplateDataOnClick: function(){

            let $allElementsToAttachEventOn = $(this.data.reloadTargetElementData + "=[true]");
            let _this                       = this;

            $.each($allElementsToAttachEventOn, function(element, index){
               let $element = $(element);

               $element.on('click', function(){

                   let targetElementSelector = $element.attr(_this.data.targetElementSelector);
                   let templateLocation      = $element.attr(_this.data.templateLocation);
                   let templateData          = $element.attr(_this.data.templateData);
                   let jsLogic               = $element.attr(_this.data.jsLogic);

                   let $targetElement = $(targetElementSelector);

                   if( 0 === $targetElement.length ){
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
        },
        /**
         * @deprecated - not used and not tested after adding
         * @param dataArray  {object}
         * @param callback   {function}
         */
        getRenderedTemplateForTemplateLocationAndData: function(dataArray, callback = null){

            let _this = this;

            $.ajax({
                url: _this.methods.getTemplateData,
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
                    if( "function" === typeof callback ){
                        callback(ajaxResponseDto.template);
                    }
                }

                if( ajaxResponseDto.reloadPage ){
                    if( ajaxResponseDto.isReloadMessageSet() ){
                        ui.ajax.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                    }
                    location.reload();
                }
            })
        }
    };
}());
