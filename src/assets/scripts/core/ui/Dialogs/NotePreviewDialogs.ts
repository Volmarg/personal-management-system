import AbstractDialogs from "./AbstractDialogs";
import Loader from "../../../libs/loader/Loader";
import Ajax from "../Ajax";

export default class NotePreviewDialogs extends AbstractDialogs {

    public buildNotePreviewDialog(noteId, categoryId, callback = null) {
        let _this             = this;
        let getDialogTemplate = this.methods.getNotePreviewDialogTemplate;

        let url = getDialogTemplate.replace(this.placeholders.categoryId, categoryId);
        url     = url.replace(this.placeholders.noteId, noteId);

        Loader.toggleLoader();
        $.ajax({
            method: Ajax.REQUEST_TYPE_GET,
            url: url
        }).always((data) => {
            _this.handleCommonAjaxCallLogicForBuildingDialog(data, callback, _this.callNotePreviewDialog)
        })
    };

    private callNotePreviewDialog(template, callback = null) {

        let dialog = bootbox.alert({
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
            this.tinyMce.init();
        });
    };

}