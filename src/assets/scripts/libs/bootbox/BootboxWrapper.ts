import * as Bootbox from 'bootbox';

export default class BootboxWrapper {

    /**
     * @type BootboxStatic
     */
    public static mainLogic:BootboxStatic = Bootbox;

    /**
     * @description Hides all the currently opened modals, but also handles the provided logic
     */
    public static hideAll(){

        BootboxWrapper.mainLogic.hideAll();
    }

    /**
     * @description Creates bootbox alert and handles the additional logic
     *
     * @param options
     */
    public static alert(options: BootboxAlertOptions): JQuery
    {
        let alertInstance = BootboxWrapper.mainLogic.alert(options);

        return alertInstance;
    }

    /**
     * @description Creates bootbox confirm and handles the additional logic
     *
     * @param options
     */
    public static confirm(options: BootboxConfirmOptions): JQuery
    {
        let confirmInstance = BootboxWrapper.mainLogic.confirm(options);

        return confirmInstance;
    }

    /**
     * @description When modal is being called from within bootbox it looses all the focus as bootbox has tabindex = -1
     *
     */
    public static removeTabindexFromActiveModals(){
        let $allActiveBootboxesModals = $('.bootbox.modal');

        $allActiveBootboxesModals.each(function(index, modal){
            let $modal = $(modal);
            $modal.removeAttr('tabindex');
        });
    };

    /**
     * @description The removed tabindex should be restored otherwise it wont be closable by hitting ESC
     *
     */
    public static restoreTabindexForActiveModals(){
        let $allActiveBootboxesModals = $('.bootbox.modal');

        $allActiveBootboxesModals.each(function(index, modal){
            let $modal = $(modal);
            $modal.attr('tabindex', '-1');
        });
    };

    /**
     * Will center the given bootbox dialog
     * @param bootbox
     */
    public static centerDialog(bootbox: JQuery): JQuery
    {
        bootbox.css({
            'top': '50%',
            'margin-top': function () {
                return -(bootbox.height() / 2);
            }
        });
        return bootbox;
    }
}
