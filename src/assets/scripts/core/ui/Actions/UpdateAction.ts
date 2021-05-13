import AbstractAction       from "./AbstractAction";
import Loader               from "../../../libs/loader/Loader";
import AjaxResponseDto      from "../../../DTO/AjaxResponseDto";
import Navigation           from "../../Navigation";
import Ajax                 from "../../ajax/Ajax";
import BootstrapNotify      from "../../../libs/bootstrap-notify/BootstrapNotify";
import DataProcessorLoader  from "../DataProcessor/DataProcessorLoader";
import DataProcessorDto     from "../../../DTO/DataProcessorDto";
import BootboxWrapper       from "../../../libs/bootbox/BootboxWrapper";
import Ui                   from "../Ui";

export default class UpdateAction extends AbstractAction {

    public init()
    {
        this.attachContentEditEventOnEditIcon();
        this.attachContentSaveEventOnSaveIcon();
        this.attachRecordUpdateViaAjaxOnSubmitForSingleForm();
    }

    /**
     * @description attaches logic for saving record edited inline (tables)
     */
    public attachContentSaveEventOnSaveIcon() {
        let _this      = this;
        let saveButton = $('.save-record');

        $(saveButton).off('click'); // to prevent double attachement on reinit
        $(saveButton).on('click', function (event) {
            event.preventDefault();

            let closestParent = this.closest(_this.elements["saved-element-class"]);
            _this.ajaxUpdateDatabaseRecord(closestParent);
        });
    }

    private attachContentEditEventOnEditIcon() {
        let _this      = this;
        let editButton = $('.edit-record');

        $(editButton).off('click'); // to prevent double attachement on reinit
        $(editButton).click(function () {
            let closest_parent = this.closest(_this.elements["edited-element-class"]);
            _this.toggleContentEditable(closest_parent);
        });
    }

    /**
     * @description Handles performing update for single-target forms (via ajax)
     */
    private attachRecordUpdateViaAjaxOnSubmitForSingleForm() {
        let _this = this;

        $('.update-record-form form').submit(function (event) {
            let $form = $(event.target);

            //@ts-ignore
            if( !$form[0].checkValidity() ){
                //@ts-ignore
                $form[0].reportValidity();
                return;
            }

            let formTarget = $form.attr('data-form-target');

            let $submitButton        = $($form).find('button[type="submit"]');
            let callbackParamsJson   = $($submitButton).attr('data-params');
            let dataCallbackParams   = ( "undefined" != typeof callbackParamsJson ? JSON.parse(callbackParamsJson) : null );

            let dataProcessorDto = DataProcessorLoader.getUpdateDataProcessorDto(DataProcessorLoader.PROCESSOR_TYPE_ENTITY, formTarget, $form);

            if( !(dataProcessorDto instanceof DataProcessorDto) ){
                dataProcessorDto = DataProcessorLoader.getUpdateDataProcessorDto(DataProcessorLoader.PROCESSOR_TYPE_SPECIAL_ACTION, formTarget, $form);
            }

            Loader.showMainLoader();
            $.ajax({
                url : dataProcessorDto.url,
                type: Ajax.REQUEST_TYPE_POST,
                data: dataProcessorDto.ajaxData,
            }).always((data) => {

                Loader.hideMainLoader();

                try{
                    var ajaxResponseDto = AjaxResponseDto.fromArray(data);
                } catch(Exception){
                    throw({
                        "message"   : "Could not handle ajax call",
                        "data"      : data,
                        "exception" : Exception
                    })
                }

                if( ajaxResponseDto.isSuccessCode() ){
                    _this.bootstrapNotify.showGreenNotification(ajaxResponseDto.message);
                }else{
                    _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                    return;
                }

                if( ajaxResponseDto.isRouteSet() ){
                    _this.ajaxEvents.loadModuleContentByUrl(ajaxResponseDto.routeUrl);
                }else if( ajaxResponseDto.isTemplateSet() ){
                    Ui.insertIntoMainContent(ajaxResponseDto.template);
                }

                _this.initializer.reinitializeLogic();
                dataProcessorDto.callback(dataCallbackParams);

                if( ajaxResponseDto.reloadPage ){
                    if( ajaxResponseDto.isReloadMessageSet() ){
                        _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                    }
                    location.reload();
                }
            });

            event.preventDefault();
        });
    }

    /**
     * @description Update entry in DB
     * @param baseElement
     */
    private ajaxUpdateDatabaseRecord(baseElement) {
        let paramEntityName = $(baseElement).attr('data-type');
        let _this           = this;

        let dataProcessorDto = DataProcessorLoader.getUpdateDataProcessorDto(DataProcessorLoader.PROCESSOR_TYPE_ENTITY, paramEntityName, baseElement);

        if( !(dataProcessorDto instanceof DataProcessorDto) ){
            dataProcessorDto = DataProcessorLoader.getUpdateDataProcessorDto(DataProcessorLoader.PROCESSOR_TYPE_SPECIAL_ACTION, paramEntityName, baseElement);
        }

        if ( dataProcessorDto.invokeAlert && dataProcessorDto.isInvokedAlertBodySet() ) {

            BootboxWrapper.confirm({
                message: dataProcessorDto.invokedAlertBody,
                backdrop: true,
                callback: function (result) {
                    if (result) {
                        _this.makeAjaxRecordUpdateCall(dataProcessorDto);
                        _this.toggleActionIconsVisibility(baseElement, true);
                    }
                }
            });

        } else {
            _this.makeAjaxRecordUpdateCall(dataProcessorDto);
        }

    }

    /**
     * @description Updates record (mostly entity) via ajax call
     *              Works with Entities file to build collect data from front and send it for update on back
     * @param dataProcessorDto DataProcessorDto
     */
    private makeAjaxRecordUpdateCall(dataProcessorDto: DataProcessorDto): void {

        let _this = this;
        Loader.showMainLoader();

        $.ajax({
            url: dataProcessorDto.url,
            method: Ajax.REQUEST_TYPE_POST,
            data: dataProcessorDto.ajaxData
        }).fail(() => {
            _this.bootstrapNotify.showRedNotification(dataProcessorDto.failMessage)
        }).always((data) => {
            let ajaxResponseDto = AjaxResponseDto.fromArray(data);

            let messageType = BootstrapNotify.MESSAGE_TYPE_SUCCESS;
            if( !ajaxResponseDto.isSuccessCode() ){
                messageType = BootstrapNotify.MESSAGE_TYPE_DANGER;
            }

            if( !ajaxResponseDto.isMessageSet()){
                _this.bootstrapNotify.notify(dataProcessorDto.successMessage, messageType);
            } else {
                _this.bootstrapNotify.notify(ajaxResponseDto.message, messageType);
            }

            if(dataProcessorDto.reloadModuleContent){
                _this.ajaxEvents.loadModuleContentByUrl(Navigation.getCurrentUri());
            }else{
                Loader.hideMainLoader();
            }

            if( ajaxResponseDto.reloadPage ){
                if( ajaxResponseDto.isReloadMessageSet() ){
                    _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                }
                location.reload();
            }

            if( $.isFunction(dataProcessorDto.callbackAfter) ){
                dataProcessorDto.callbackAfter();
            }

        });
    }
}