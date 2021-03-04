import Loader           from "../../../libs/loader/Loader";
import AbstractDialogs  from "./AbstractDialogs";
import DomElements      from "../../utils/DomElements";
import BootboxWrapper   from "../../../libs/bootbox/BootboxWrapper";
import Ajax             from "../../ajax/Ajax";
import WidgetDataLoader from "../Widgets/DialogBased/WidgetDataLoader";
import AbstractDto      from "../../../DTO/AbstractDto";
import Application      from "../../Application";
import WidgetData       from "../Widgets/DialogBased/WidgetData";
import AjaxResponseDto  from "../../../DTO/AjaxResponseDto";

/**
 * @description Handles building dialogs for given widgets, alongside with building it's content and interaction
 */
export default class WidgetsDialogs extends AbstractDialogs {

    /**
     * @description main initialization logic
     */
    public init(){
        this.callModalForWidget();
    }

    /**
     * @description builds modal for widget
     */
    private callModalForWidget() {
        let $callModalButton = $(AbstractDialogs.selectors.classes.callWidgetModal);

        if ( !DomElements.doElementsExists($callModalButton) ) {
            return;
        }

        let _this = this;

        $callModalButton.off('click'); // prevent adding multiple click events
        $callModalButton.on('click',(event) => {

            let clickedButton = $(event.target).closest('[data-widget="true"]');
            let id            = clickedButton.attr('id');
            let widgetDataDto = WidgetDataLoader.getDataForWidgetId(id);

            if( !(widgetDataDto instanceof AbstractDto) ){
                Application.abort("Not an DTO");
            }

           let dialog = BootboxWrapper.alert({
                message: "-",
                backdrop: true,
                buttons: {
                    ok: {
                        label: 'Cancel'
                    }
                },
                className: AbstractDialogs.selectors.classesNames.widgetModalClassName,
                size: 'large',
                callback: function(){}
            });

            //@ts-ignore
           dialog.init( () => {
                switch (widgetDataDto.type) {
                    case WidgetData.TYPE_TEMPLATE:
                        Loader.toggleMainLoader();
                        $.ajax({
                            method : Ajax.REQUEST_TYPE_POST,
                            data   : widgetDataDto.ajaxData,
                            url    : widgetDataDto.url
                        }).done((responseData) => {

                            let ajaxResponseDto = AjaxResponseDto.fromArray(responseData);
                            let bootboxBody     = $('.' + AbstractDialogs.selectors.classesNames.widgetModalClassName).find('.bootbox-body');

                            bootboxBody.html(ajaxResponseDto.template);
                            widgetDataDto.callback();

                            if( ajaxResponseDto.reloadPage ){
                                if( ajaxResponseDto.isReloadMessageSet() ){
                                    _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                                }
                                location.reload();
                            }

                        }).fail(() => {
                            _this.bootstrapNotify.showRedNotification('There was an error while fetching data for bootbox modal')
                        }).always(() => {
                            Loader.toggleMainLoader();
                        });

                        break;
                    default:
                        Application.abort(`"Unknown type was provided: ${widgetDataDto.type}`);
                }
            });
        })
    };
}