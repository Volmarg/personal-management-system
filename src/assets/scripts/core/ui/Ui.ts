import Modal from "./Modal/Modal";

/**
 * @description This class should contain logic responsible for interacting with globally with UI
 *              Consider adding portions of logic which is not bound to any strict class yet is reusable in other places
 */
export default class Ui {

    /**
     * @description Will insert given string into the DOM - main container for reloaded modules etc.
     *              After that will trigger other logic.
     */
    public static insertIntoMainContent(content: JQuery.htmlString, hideModalBackdrop: boolean = true): void
    {
        let twigBodySection = $('.twig-body-section');
        let modal           = new Modal();

        twigBodySection.html(content);

        /**
         * Hiding is required because replacing the twig-body removes the dialogs and some the backdrop is not being removed
         * as the backdrop is inserted automatically at the end of dom
         */
        if(hideModalBackdrop){
            modal.hideModalBackdrop();
        }
    }
}