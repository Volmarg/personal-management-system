import AbstractAction       from "./AbstractAction";
import Loader               from "../../../libs/loader/Loader";
import AjaxResponseDto      from "../../../DTO/AjaxResponseDto";
import Navigation           from "../../Navigation";
import Ajax                 from "../Ajax";
import BootstrapNotify      from "../../../libs/bootstrap-notify/BootstrapNotify";
import DataProcessorLoader  from "../DataProcessor/DataProcessorLoader";
import DataProcessorDto     from "../../../DTO/DataProcessorDto";

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
            let $form      = $(event.target);
            let formTarget = $form.attr('data-form-target');

            let dataProcessorDto = DataProcessorLoader.getUpdateDataProcessorDto(DataProcessorLoader.PROCESSOR_TYPE_ENTITY, formTarget);

            Loader.showLoader();
            $.ajax({
                url : dataProcessorDto.url,
                type: Ajax.REQUEST_TYPE_POST,
                data: dataProcessorDto.ajaxData,
            }).always((data) => {

                Loader.hideLoader();

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

                $('.twig-body-section').html(ajaxResponseDto.template);
                _this.initializer.reinitializeLogic();

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

        let dataProcessorDto = DataProcessorLoader.getUpdateDataProcessorDto(DataProcessorLoader.PROCESSOR_TYPE_ENTITY, paramEntityName, baseElement);
        let _this           = this;

        if ( dataProcessorDto.invokeAlert && dataProcessorDto.isInvokedAlertBodySet() ) {

            bootbox.confirm({
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
        Loader.showLoader();

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

            dataProcessorDto.callbackAfter();

            _this.ajax.loadModuleContentByUrl(Navigation.getCurrentUri());

            if( ajaxResponseDto.reloadPage ){
                if( ajaxResponseDto.isReloadMessageSet() ){
                    _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                }
                location.reload();
            }
        });
    }
}