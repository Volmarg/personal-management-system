import AbstractAction       from "./AbstractAction";
import Loader               from "../../../libs/loader/Loader";
import AjaxResponseDto      from "../../../DTO/AjaxResponseDto";
import FormsValidator       from "../../validators/FormsValidator";
import DataProcessorLoader  from "../DataProcessor/DataProcessorLoader";
import DataProcessorDto     from "../../../DTO/DataProcessorDto";
import BootboxWrapper       from "../../../libs/bootbox/BootboxWrapper";
import StringUtils from "../../utils/StringUtils";

export default class CreateAction extends AbstractAction {

    public init(reinitializePageLogicAfterAjaxCall = true)
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
            let doReloadTemplateViaTemplateUrl = !StringUtils.isEmptyString(dataTemplateUrl);

            let formSubmissionType                = form.attr('method');
            let processedEntityName               = form.attr('data-entity');
            let singleProcessedFormDefinitionName = form.attr('data-form-target');

            /**
             * @description build data bag for request using either entity based form or single-target one
             *              and build callbacks used after receiving backend response
             */
            let dataProcessorDto = DataProcessorLoader.getCreateDataProcessorDto(DataProcessorLoader.PROCESSOR_TYPE_ENTITY, processedEntityName);

            if( !(dataProcessorDto instanceof DataProcessorDto) ){
                dataProcessorDto = DataProcessorLoader.getCreateDataProcessorDto(DataProcessorLoader.PROCESSOR_TYPE_SPECIAL_ACTION, singleProcessedFormDefinitionName);
            }

            Loader.showLoader();
            $.ajax({
                url: dataProcessorDto.url,
                type: formSubmissionType, //todo
                data: form.serialize(),
            }).always((data) => {
                let twigBodySection = $('.twig-body-section');

                dataProcessorDto.callback(dataCallbackParams);

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
                    _this.ajaxEvents.loadModuleContentByUrl(dataTemplateUrl, dataProcessorDto.callbackForLoadingModuleContentByUrl(), true);
                }else if(ajaxResponseDto.isTemplateSet()){
                    twigBodySection.html(ajaxResponseDto.template);
                }

                dataProcessorDto.callbackAfter(dataCallbackParams);

                /**
                 * @info handle logic reinitialization
                 */
                if ( !reinitializePageLogicAfterAjaxCall ) {
                    Loader.hideLoader();

                    let message = "";

                    if( !ajaxResponseDto.isSuccessCode() ){
                        message = ( ajaxResponseDto.isMessageSet() ? ajaxResponseDto.message : dataProcessorDto.failMessage );

                        _this.bootstrapNotify.showRedNotification(message);
                    }else{
                        message = ( ajaxResponseDto.isMessageSet() ? ajaxResponseDto.message : dataProcessorDto.successMessage );

                        _this.bootstrapNotify.showGreenNotification(message);
                    }

                    return;
                }

                try{
                    _this.initializer.reinitializeLogic();
                    Loader.hideLoader();

                    _this.bootstrapNotify.showGreenNotification( ajaxResponseDto.isMessageSet() ? ajaxResponseDto.message : dataProcessorDto.successMessage );

                    if( ajaxResponseDto.reloadPage ){
                        if( ajaxResponseDto.isReloadMessageSet() ){
                            _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                        }
                        location.reload();
                    }

                    BootboxWrapper.mainLogic.hideAll();

                }catch(Exception){
                    Loader.hideLoader();
                    _this.bootstrapNotify.showRedNotification("Failed reinitializing logic");
                    throw Exception;
                }

            });
        });
    }

}