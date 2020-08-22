import * as Bootbox from 'bootbox';

export default class BootboxWrapper {

    /**
     * @type BootboxStatic
     */
    public static mainLogic:BootboxStatic = Bootbox;

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
}
