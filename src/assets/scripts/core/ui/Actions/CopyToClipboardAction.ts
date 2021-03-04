import AbstractAction       from "./AbstractAction";
import Loader               from "../../../libs/loader/Loader";
import Ajax                 from "../../ajax/Ajax";
import AjaxResponseDto      from "../../../DTO/AjaxResponseDto";
import DomElements          from "../../utils/DomElements";
import DataProcessorLoader  from "../DataProcessor/DataProcessorLoader";

export default class CopyToClipboardAction extends AbstractAction {

    public init()
    {
        this.attachContentCopyEventOnCopyIcon();
    };

    public attachContentCopyEventOnCopyIcon() {
        let allCopyButtons = $('.copy-record');
        let _this          = this;

        if (DomElements.doElementsExists($(allCopyButtons))) {
            $(allCopyButtons).each((index, button) => {

                $(button).on('click', (event) => {
                    let $clickedElement  = $(event.target);
                    let $baseElement     = $clickedElement.closest(_this.elements["copy-element-class"]);
                    let entityName       = $($baseElement).attr('data-type');
                    let dataProcessorDto = DataProcessorLoader.getCopyDataProcessorDto(DataProcessorLoader.PROCESSOR_TYPE_ENTITY, entityName, $baseElement);

                    let temporaryCopyDataInput = $("<input>");
                    $("body").append(temporaryCopyDataInput);
                    Loader.showMainLoader();
                    $.ajax({
                        url: dataProcessorDto.url,
                        method: Ajax.REQUEST_TYPE_GET,
                    }).always((data) => {
                        Loader.hideMainLoader();

                        let ajaxResponseDto = AjaxResponseDto.fromArray(data);

                        if( ajaxResponseDto.isMessageSet() ){
                            _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                            return;
                        }

                        if( !ajaxResponseDto.isPasswordSet() ){
                            _this.bootstrapNotify.showRedNotification(dataProcessorDto.failMessage);
                            return;
                        }

                        temporaryCopyDataInput.val(ajaxResponseDto.password).select();
                        document.execCommand("copy");
                        temporaryCopyDataInput.remove();

                        _this.bootstrapNotify.showGreenNotification(dataProcessorDto.successMessage);

                        if( ajaxResponseDto.reloadPage ){
                            if( ajaxResponseDto.isReloadMessageSet() ){
                                _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                            }
                            location.reload();
                        }
                    });
                })
            });
        }
    };

}