import TemporaryTwigImbuedLogicExecutionForWidgets from "../../../temp/TemporaryTwigImbuedLogicExecutionForWidgets";
import Loader           from "../../../libs/loader/Loader";
import AbstractDialogs  from "./AbstractDialogs";
import DomElements      from "../../utils/DomElements";
import BootboxWrapper   from "../../../libs/bootbox/BootboxWrapper";
import Ajax from "../Ajax";

export default class WidgetsDialogs extends AbstractDialogs {


    public init(){
        this.callModalOnWidgetIcon();
    }

    private callModalOnWidgetIcon() {
        let callModalButton = $(this.selectors.classes.callWidgetModal);

        if (!DomElements.doElementsExists(callModalButton)) {
            return;
        }

        let _this = this;

        callModalButton.off('click'); // prevent adding multiple click events
        callModalButton.click((event) => {

            let clickedButton = $(event.target).closest('[data-widget="true"]');
            let settings      = null;

            /* Temporary start */
            let id = clickedButton.attr('id');

            if( "add-note" == id ){
                settings = TemporaryTwigImbuedLogicExecutionForWidgets.addNoteWidget();
            }else if("my-files-upload-files-widget" == id){
                settings = TemporaryTwigImbuedLogicExecutionForWidgets.myFilesUpload();
            }else if("my-files-new-folder-widget" == id){
                settings = TemporaryTwigImbuedLogicExecutionForWidgets.myFilesNewFolder();
            }else if("my-images-upload-files-widget" == id){
                settings = TemporaryTwigImbuedLogicExecutionForWidgets.myImagesUpload();
            }else if("my-images-new-folder-widget" == id){
                settings = TemporaryTwigImbuedLogicExecutionForWidgets.myImagesNewFolder();
            }else if("add-contact-card-widget" == id){
                settings = TemporaryTwigImbuedLogicExecutionForWidgets.addContactCardWidget();
            }else{
                throw{
                    "message"       : "there is some more widget?",
                    "clickedButton" : clickedButton
                }
            }

            /* Temporary end */

           let dialog = BootboxWrapper.mainLogic.alert({
                message: "",
                backdrop: true,
                buttons: {
                    ok: {
                        label: 'Cancel'
                    }
                },
                className: _this.selectors.classesNames.widgetModalClassName,
                size: 'large',
                callback: function(){}
            });

            //@ts-ignore
            // todo: might not work
            dialog.init( () => {
                if (settings.type !== undefined && settings.type !== null) {

                    let ajaxData = '';

                    if( "undefined" !== typeof settings.data ){
                        ajaxData = settings.data;
                    }

                    switch (settings.type) {
                        case 'template':
                            Loader.toggleLoader();
                            $.ajax({
                                method: Ajax.REQUEST_TYPE_POST,
                                data: ajaxData,
                                url: settings.url
                            }).done((responseData) => {

                                if( undefined !== responseData['template'] ){
                                    responseData = responseData['template'];
                                }

                                let bootboxBody = $('.' + _this.selectors.classesNames.widgetModalClassName).find('.bootbox-body');
                                bootboxBody.html(responseData);

                                // rewrite new start
                                if( $.isFunction(settings.callback) ){
                                    settings.callback()
                                }
                                // rewrite new end

                                if( undefined !== settings.subtype ){

                                    switch(settings.subtype){
                                        case "add-note":
                                            let formSubmitButton = $('#my_notes_submit');
                                            formSubmitButton.attr('data-template-url', window.location.pathname);
                                            formSubmitButton.attr('data-template-method', 'GET');
                                            break;
                                    }

                                }

                                let reloadPage    = responseData['reload_page'];
                                let reloadMessage = responseData['reload_message'];

                                if( reloadPage ){
                                    if( "" !== reloadMessage ){
                                        _this.bootstrapNotify.showBlueNotification(reloadMessage);
                                    }
                                    location.reload();
                                }

                            }).fail(() => {
                                _this.bootstrapNotify.notify('There was an error while fetching data for bootbox modal', 'danger')
                            }).always(() => {
                                Loader.toggleLoader();
                            });

                            break;
                        default:
                            throw "Unknown type was provided: " + settings.type;
                    }

                }
            });

        })
    };

}