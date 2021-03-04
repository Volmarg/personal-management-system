import AbstractDialogs  from "./AbstractDialogs";
import Loader           from "../../../libs/loader/Loader";
import Ajax             from "../../ajax/Ajax";
import BootboxWrapper   from "../../../libs/bootbox/BootboxWrapper";
import TinyMce from "../../../libs/tiny-mce/TinyMce";

export default class NotePreviewDialogs extends AbstractDialogs {

    /**
     * @description fetches the template from backend then calls bootbox dialog and inserts the template alongside
     *              with triggering logic initialization
     *
     * @param noteId
     * @param categoryId
     * @param callback
     */
    public buildNotePreviewDialog(noteId, categoryId, callback = null) {
        let _this             = this;
        let getDialogTemplate = this.methods.getNotePreviewDialogTemplate;

        let url = getDialogTemplate.replace(this.placeholders.categoryId, categoryId);
        url     = url.replace(this.placeholders.noteId, noteId);

        Loader.toggleMainLoader();
        $.ajax({
            method: Ajax.REQUEST_TYPE_GET,
            url: url
        }).always((data) => {
            _this.handleCommonAjaxCallLogicForBuildingDialog(data, callback, _this.callNotePreviewDialog)
        })
    };

    /**
     * @description builds the bootbox instance, inserts template and calls js logic
     * @param template
     * @param callback
     */
    private callNotePreviewDialog(template, callback = null) {

        let dialog = BootboxWrapper.alert({
            size: "large",
            backdrop: true,
            closeButton: false,
            message: template,
            buttons: {
                ok: {
                    label: 'Cancel',
                    className: 'btn-primary dialog-ok-button',
                    callback: () => {}
                },
            },
            callback: function () {
            }
        });

        //@ts-ignore
        dialog.init( () => {

            /**
             * @description This is required due to loosing context of `this`
             */
            let tinymce = new TinyMce();
            tinymce.init();
        });
    };

}