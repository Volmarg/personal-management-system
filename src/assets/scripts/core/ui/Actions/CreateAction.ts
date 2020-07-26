import AbstractAction from "./AbstractAction";
import Loader from "../../../libs/loader/Loader";
import AjaxResponseDto from "../../../DTO/AjaxResponseDto";
import FormsValidator from "../../validators/FormsValidator";

export default class CreateAction extends AbstractAction {

    public init(reinitializePageLogicAfterAjaxCall = false)
    {
        this.attachRecordAddViaAjaxOnSubmit(reinitializePageLogicAfterAjaxCall);
    }

    /**
     * @description This is general method used for handling forms which are associated with entities or single-target forms
     *              single-target form can be any custom form not associated with entities directly
     * @info        Method is also used in twig as string which is then parsed and executed
     * @param reinitializePageLogicAfterAjaxCall {bool}
     */
    private attachRecordAddViaAjaxOnSubmit(reinitializePageLogicAfterAjaxCall = true) {

        let _this = this;
        let form  = $('.add-record-form form');

        $(form).off("submit");
        $(form).submit(function (event) {
            event.preventDefault();

            let form                 = $(event.target);
            let submitButton         = $(form).find('button[type="submit"]');
            let callbackParamsJson   = $(submitButton).attr('data-params');
            let dataCallbackParams   = ( "undefined" != typeof callbackParamsJson ? JSON.parse(callbackParamsJson) : null );

            /**
             * @description with this there is a possibility to load different template than the one from url used in ajax
             *              normally the same page should be reloaded but this is helpful for widgets when we want to call
             *              action from one page but load template of other
             */
            let dataTemplateUrl                = $(submitButton).attr('data-template-url');
            let doReloadTemplateViaTemplateUrl = ("undefined" !== typeof dataTemplateUrl);

            let formSubmissionType                = form.attr('method');
            let processedEntityName               = form.attr('data-entity');
            let singleProcessedFormDefinitionName = form.attr('data-form-target');

            let ajaxRequestDataBag  = null;
            let isEntityBasedForm   = ("undefined" != typeof processedEntityName);

            /**
             * @description build data bag for request using either entity based form or single-target one
             *              and build callbacks used after receiving backend response
             */
            try{
                if(isEntityBasedForm ){
                    ajaxRequestDataBag = dataProcessors.entities[processedEntityName].makeCreateData();
                }else{
                    ajaxRequestDataBag = dataProcessors.singleTargets[singleProcessedFormDefinitionName].makeCreateData();
                }
            }catch(Exception){
                throw({
                    "message"   : "Failed on getting data bag for creating record via form submit (ajax call)",
                    "exception" : Exception
                })
            }

            if( null === ajaxRequestDataBag ){
                _this.bootstrapNotify.showRedNotification("Databag for creating record via form submit (ajax) is null.");
                return;
            }

            Loader.showLoader();
            $.ajax({
                url: ajaxRequestDataBag.url,
                type: formSubmissionType,
                data: form.serialize(),
            }).always((data) => {
                let twigBodySection = $('.twig-body-section');
                let callback        = () => {};

                if (ajaxRequestDataBag.callback_before) {
                    ajaxRequestDataBag.callback(dataCallbackParams);
                }

                try{
                    var ajaxResponseDto = AjaxResponseDto.fromArray(data);
                } catch(Exception){
                    throw({
                        "message"   : "Could not handle ajax call",
                        "data"      : data,
                        "exception" : Exception
                    })
                }

                if( !ajaxResponseDto.isSuccessCode() ){

                    if( ajaxResponseDto.hasInvalidFields() && !ajaxResponseDto.isInternalServerErrorCode() ){
                        let formValidator = new FormsValidator(ajaxResponseDto.validatedFormPrefix, ajaxResponseDto.invalidFormFields);
                        formValidator.handleInvalidFields();
                        Loader.hideLoader();
                        return;
                    }

                    Loader.hideLoader();
                    _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                    return;
                }

                /**
                 * @info handle the way of reloading template
                 */
                if( doReloadTemplateViaTemplateUrl ){
                    if(ajaxRequestDataBag.callback_for_data_template_url){
                        callback = () => {
                            ajaxRequestDataBag.callback(dataCallbackParams)
                        };
                    }

                    _this.ajax.loadModuleContentByUrl(dataTemplateUrl, callback, true);
                }else if(ajaxResponseDto.isTemplateSet()){
                    twigBodySection.html(ajaxResponseDto.template);
                }

                if (ajaxRequestDataBag.callback_after) {
                    ajaxRequestDataBag.callback(dataCallbackParams);
                }

                /**
                 * @info handle logic reinitialization
                 */
                if ( !reinitializePageLogicAfterAjaxCall ) {
                    Loader.hideLoader();

                    if( !ajaxResponseDto.isSuccessCode() ){
                        _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                    }else{
                        _this.bootstrapNotify.showGreenNotification(ajaxResponseDto.message);
                    }

                    return;
                }

                try{
                    _this.initializer.reinitializeLogic();
                    Loader.hideLoader();

                    _this.bootstrapNotify.showGreenNotification( ajaxResponseDto.isMessageSet() ? ajaxResponseDto.message : ajaxRequestDataBag.success_message );

                    if( ajaxResponseDto.reloadPage ){
                        if( ajaxResponseDto.isReloadMessageSet() ){
                            _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                        }
                        location.reload();
                    }

                    bootbox.hideAll();

                }catch(Exception){
                    Loader.hideLoader();
                    _this.bootstrapNotify.showRedNotification("Failed reinitializing logic");
                    throw Exception;
                }

            });
        });
    }

}