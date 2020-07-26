import AbstractAction   from "./AbstractAction";
import Loader           from "../../../libs/loader/Loader";
import Ajax             from "../Ajax";
import AjaxResponseDto  from "../../../DTO/AjaxResponseDto";
import DomElements      from "../../utils/DomElements";

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
                    let clickedElement    = $(event.target);
                    let parent_wrapper    = $(clickedElement).closest(_this.elements["removed-element-class"]);
                    let param_entity_name = $(parent_wrapper).attr('data-type');
                    let copy_data         = dataProcessors.entities[param_entity_name].makeCopyData(parent_wrapper);

                    let temporaryCopyDataInput = $("<input>");
                    $("body").append(temporaryCopyDataInput);
                    Loader.showLoader();
                    $.ajax({
                        url: copy_data.url,
                        method: Ajax.REQUEST_TYPE_GET,
                    }).always((data) => {
                        Loader.hideLoader();

                        let ajaxResponseDto = AjaxResponseDto.fromArray(data);

                        if( ajaxResponseDto.isMessageSet() ){
                            _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                            return;
                        }

                        if( !ajaxResponseDto.isPasswordSet() ){
                            _this.bootstrapNotify.showRedNotification(copy_data.fail_message);
                            return;
                        }

                        temporaryCopyDataInput.val(ajaxResponseDto.password).select();
                        document.execCommand("copy");
                        temporaryCopyDataInput.remove();

                        _this.bootstrapNotify.showGreenNotification(copy_data.success_message);

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