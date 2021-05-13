import AbstractAction       from "./AbstractAction";
import Loader               from "../../../libs/loader/Loader";
import BootboxWrapper       from "../../../libs/bootbox/BootboxWrapper";
import Ajax                 from "../../ajax/Ajax";
import AjaxResponseDto      from "../../../DTO/AjaxResponseDto";
import DataTable            from "../../../libs/datatable/DataTable";
import DomElements          from "../../utils/DomElements";
import Navigation           from "../../Navigation";
import StringUtils          from "../../utils/StringUtils";
import DataProcessorLoader  from "../DataProcessor/DataProcessorLoader";
import Ui                   from "../Ui";
import DataProcessorDto from "../../../DTO/DataProcessorDto";

export default class RemoveAction extends AbstractAction {

    /**
     * @type DataTable
     */
    private datatable = new DataTable();

    public init()
    {
        this.attachRemovingEventOnElementsViaDataProcessorEntry();
        this.attachEntityRemovalEventViaHtmlDataAttrForSelector(this.otherSelectors.entityRemoveAction);
    }

    /**
     * @description Removes table row on front (via datatable), must be also handled on back otherwise will reapper on refresh
     * @param table_id
     * @param tr_parent_element
     */
    public removeDataTableTableRow(table_id, tr_parent_element) {
        DataTable.destroyDatatableInstanceForTableId(table_id);
        tr_parent_element.remove();
        this.datatable.reinit(table_id)
    }

    /**
     * @description Just removes row from table, wil be shown on refresh if entry is not also removed/handled on back
     * @param tr_parent_element
     */
    private removeTableRow(tr_parent_element) {
        tr_parent_element.remove();
    }

    /**
     * Uses DataProcessor entry for this removal type
     * this way additional js logic/handling can be provided
     */
    private attachRemovingEventOnElementsViaDataProcessorEntry() {
        let _this        = this;
        let removeButton = $('.remove-record');

        $(removeButton).off('click'); // to prevent double attachement on reinit
        $(removeButton).click(function () {
            let $baseElement    = $(this).closest(_this.elements["removed-element-class"]);
            let paramEntityName = $baseElement.attr('data-type');

            let dataProcessorDto = DataProcessorLoader.getRemoveDataProcessorDto(DataProcessorLoader.PROCESSOR_TYPE_ENTITY, paramEntityName, $baseElement);

            if( !(dataProcessorDto instanceof DataProcessorDto) ){
                dataProcessorDto = DataProcessorLoader.getRemoveDataProcessorDto(DataProcessorLoader.PROCESSOR_TYPE_SPECIAL_ACTION, paramEntityName, $baseElement);
            }

            let removalMessage = ( dataProcessorDto.isConfirmMessageSet()
                    ? dataProcessorDto.confirmMessage
                    : AbstractAction.messages.default_record_removal_confirmation_message
            );

            BootboxWrapper.confirm({
                message: removalMessage,
                backdrop: true,
                callback: function (result) {
                    if (result) {
                        Loader.showMainLoader();
                        $.ajax({
                            url    : dataProcessorDto.url,
                            method : Ajax.REQUEST_TYPE_POST,
                            data   : dataProcessorDto.ajaxData
                        }).always( (data) => {

                            Loader.hideMainLoader();

                            let ajaxResponseDto  = AjaxResponseDto.fromArray(data);

                            let message = ajaxResponseDto.message;
                            if( !ajaxResponseDto.isMessageSet() ){
                                message = dataProcessorDto.successMessage;
                            }

                            dataProcessorDto.callbackAfter();

                            if( !ajaxResponseDto.isSuccessCode() ) {
                                _this.bootstrapNotify.showRedNotification(message);
                                return;
                            }

                            if( ajaxResponseDto.isTemplateSet() ){
                                Ui.insertIntoMainContent(ajaxResponseDto.template);
                                _this.initializer.reinitializeLogic();
                            }else if ( dataProcessorDto.isDataTable ) {
                                let table_id = $($baseElement).closest('tbody').closest('table').attr('id');
                                _this.removeDataTableTableRow(table_id, $baseElement);
                            }else{
                                _this.removeTableRow($baseElement);
                            }

                            _this.bootstrapNotify.showGreenNotification(message);

                            if( ajaxResponseDto.reloadPage ){
                                if( ajaxResponseDto.isReloadMessageSet() ){
                                    _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                                }
                                location.reload();
                            }
                        });
                    }
                }
            });
        });
    }

    /**
     * Removal is based on one click with approval box
     * @param selector
     * @returns {boolean}
     */
    private attachEntityRemovalEventViaHtmlDataAttrForSelector(selector){
        let element = $(selector);
        let _this   = this;

        if( !DomElements.doElementsExists(element) ){
            return false;
        }

        let afterRemovalCallback = function(){
            _this.ajaxEvents.loadModuleContentByUrl(Navigation.getCurrentUri());
            BootboxWrapper.hideAll();
        };

        $(element).on('click', function(event) {
            let clickedElement  = $(this);
            let entityId        = $(clickedElement).attr('data-entity-id');
            let repositoryName  = $(clickedElement).attr('data-repository-name'); // consts from Repositories class

            let $accordionContent     = clickedElement.closest(_this.classes.accordion).find(_this.classes.accordionContent);
            let isActionForAccordion  = ( 0 !== $accordionContent.length );

            _this.removeEntityById(entityId, repositoryName, afterRemovalCallback);

            // used to keep accordion open or closed when clicking on action
            if( isActionForAccordion ){
                event.stopPropagation();
            }
        })
    }

    /**
     * Uses global repositories remove function for all repositories defined there
     * @param entityId
     * @param repositoryName
     * @param afterRemovalCallback
     */
    private removeEntityById(entityId, repositoryName, afterRemovalCallback){

        let _this = this;
        let url   = this.methods.removeEntity.url.replace(this.methods.removeEntity.params.repositoryName, repositoryName);
        url       = url.replace(this.methods.removeEntity.params.id, entityId);

        let doYouWantToRemoveThisRecordMessage = AbstractAction.messages.doYouWantToRemoveThisRecord();

        BootboxWrapper.confirm({
            message:  doYouWantToRemoveThisRecordMessage,
            backdrop: true,
            callback: function (result) {
                if (result) {
                    Loader.showMainLoader();

                    $.ajax({
                        url: url,
                        method: _this.methods.removeEntity.method,
                    }).always((data) => {

                        Loader.hideMainLoader();

                        let ajaxResponseDto = AjaxResponseDto.fromArray(data);
                        let message         = ajaxResponseDto.message;

                        if( !ajaxResponseDto.isSuccessCode() ){
                            _this.bootstrapNotify.showRedNotification(message);
                            return;
                        }else {

                            if( StringUtils.isEmptyString(message) ){
                                message = AbstractAction.messages.entityHasBeenRemovedFromRepository();
                            }

                            _this.bootstrapNotify.showGreenNotification(message);
                        }

                        if( $.isFunction(afterRemovalCallback) ) {
                            afterRemovalCallback();
                        }

                        if( ajaxResponseDto.reloadPage ){
                            if( ajaxResponseDto.isReloadMessageSet() ){
                                _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                            }
                            location.reload();
                        }
                    });
                }
            }
        });
    }
}