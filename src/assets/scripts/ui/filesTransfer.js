/**
 * This file handles only the dialog used to transfer files between modules
 */
export default (function () {

    if (typeof window.filesTransfer === 'undefined') {
        window.filesTransfer = {};
    }

    filesTransfer.ui = {

        init: function(){
            this.attachCallDialogForDataTransfer();
        },
        attachCallDialogForDataTransfer: function () {
            this.buildDataTransferDialog();
            this.callDataTransferDialog();
        },
        buildDataTransferDialog: function () {
            // TODO: check if exists - if does call rebuild
        },
        rebuildDataTransferDialog: function () {

        },
        callDataTransferDialog: function () {

        },
        attachDataTransferToDialogSubmit: function (){

        },
        makeAjaxCallForDataTransfer(){

        }

    };

}());
