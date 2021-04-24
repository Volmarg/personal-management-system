/**
 * @description This class should contain logic responsible for interacting with globally with UI
 *              Consider adding portions of logic which is not bound to any strict class yet is reusable in other places
 */
export default class Ui {

    /**
     * @description Will insert given string into the DOM - main container for reloaded modules etc.
     *              After that will trigger other logic.
     */
    public static insertIntoMainContent(content: JQuery.htmlString): void
    {
        let twigBodySection = $('.twig-body-section');
        twigBodySection.html(content);
    }
}