import Modal from "./Modal/Modal";

export default class Ui {

    /**
     * @description Will insert given string into the DOM - main container for reloaded modules etc.
     *              After that will trigger other logic.
     */
    public static inertIntoMainContent(content: JQuery.htmlString): void
    {
        let twigBodySection = $('.twig-body-section');
        let modal           = new Modal();

        twigBodySection.html(content);

        /**
         * Hiding is required because replacing the twig-body removes the dialogs and some the backdrop is not being removed
         * as the backdrop is inserted automatically at the end of dom
         */
        modal.hideModalBackdrop();
    }
}