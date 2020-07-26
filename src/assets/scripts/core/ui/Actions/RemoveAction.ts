import AbstractAction   from "./AbstractAction";
import Loader           from "../../../libs/loader/Loader";
import BootboxWrapper   from "../../../libs/bootbox/BootboxWrapper";
import Ajax             from "../Ajax";
import AjaxResponseDto  from "../../../DTO/AjaxResponseDto";
import DataTable        from "../../../libs/datatable/DataTable";
import DomElements      from "../../utils/DomElements";
import Navigation       from "../../Navigation";
import StringUtils      from "../../utils/StringUtils";

export default class RemoveAction extends AbstractAction {

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
            let parent_wrapper    = $(this).closest(_this.elements["removed-element-class"]);
            let param_entity_name = $(parent_wrapper).attr('data-type');
            let remove_data       = dataProcessors.entities[param_entity_name].makeRemoveData(parent_wrapper);

            let removal_message = (
                remove_data.confirm_message !== undefined
                    ? remove_data.confirm_message
                    : AbstractAction.messages.default_record_removal_confirmation_message
            );

            BootboxWrapper.mainLogic.confirm({
                message: removal_message,
                backdrop: true,
                callback: function (result) {
                    if (result) {
                        Loader.showLoader();
                        $.ajax({
                            url: remove_data.url,
                            method: Ajax.REQUEST_TYPE_POST,
                            data: remove_data.data
                        }).always( (data) => {

                            Loader.hideLoader();

                            let $twigBodySection = $('.twig-body-section');
                            let ajaxResponseDto  = AjaxResponseDto.fromArray(data);

                            let message = ajaxResponseDto.message;
                            if( !ajaxResponseDto.isMessageSet() ){
                                message = remove_data.success_message;
                            }

                            if (remove_data.callback_after) {
                                remove_data.callback();
                            }

                            if( !ajaxResponseDto.isSuccessCode() ) {
                                _this.bootstrapNotify.showRedNotification(message);
                                return;
                            }

                            if( !ajaxResponseDto.isTemplateSet() ){
                                $twigBodySection.html(ajaxResponseDto.template);
                                _this.initializer.reinitializeLogic();
                            }else if ( remove_data['is_dataTable'] ) {
                                let table_id = $(parent_wrapper).closest('tbody').closest('table').attr('id');
                                _this.removeDataTableTableRow(table_id, parent_wrapper);
                            }else{
                                _this.removeTableRow(parent_wrapper);
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
            _this.ajax.loadModuleContentByUrl(Navigation.getCurrentUri());
            bootbox.hideAll();
        } ;

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

        let doYouWantToRemoveThisRecordMessage = this.messages.doYouWantToRemoveThisRecord();

        bootbox.confirm({
            message:  doYouWantToRemoveThisRecordMessage,
            backdrop: true,
            callback: function (result) {
                if (result) {
                    Loader.showLoader();

                    $.ajax({
                        url: url,
                        method: _this.methods.removeEntity.method,
                    }).always((data) => {

                        Loader.hideLoader();

                        let ajaxResponseDto = AjaxResponseDto.fromArray(data);
                        let message         = ajaxResponseDto.message;

                        if( ajaxResponseDto.isSuccessCode() ){
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