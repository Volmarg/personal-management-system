import DomElements from "../../utils/DomElements";

/**
 * @description This class covers common logic for all dialogs
 */
export default class Dialog {

    /**
     * @type Object
     */
    public static readonly selectors = {
        modalBackdrop: ".modal-backdrop"
    }

    public static readonly classesNames = {
        /**
         * @description Special selector bound strictly to the logic of moveBackdropToReloadablePageContainer
         */
        modalMovedBackdrop: "modal-moved-backdrop"
    }

    /**
     * @description will initialize main logic for Dialogs
     */
    public init(): void
    {
        this.attachHideBackdropBackgroundForStandardStaticInDomBootstrapModals();
        this.moveBackdropToReloadablePageContainer();
    }

    /**
     * @description Move backdrop to the main reloadable page container - fixes the issue where upon page container
     *              reload the backdrop remains. This happens because normally bootstrap appends backdrop on very end of
     *              dom but modal remain inside defined dom element. So if the whole content of container is reloaded
     *              then backdrop just remains, it cannot be just hidden upon calling `insert into container` as some
     *              actions hide modal after that, and some don't, so removal should be strictly controller by actions
     *              and the way they work
     *
     *              Also this logic should be triggered to explicitly pointed modals as the Bootbox is a wrapper to
     *              standard modal so relying on modal/bootbox classes causes a problems
     */
    public moveBackdropToReloadablePageContainer($modal ?:JQuery<HTMLElement>): void
    {
        let $domDocument     =  $(document);
        let $listenedElement = $modal ?? $domDocument;

        $listenedElement.on('shown.bs.modal', (event) => {
            let $targetElement = $(event.target);

            if( $targetElement.hasClass(Dialog.classesNames.modalMovedBackdrop) ){
                let $modalBackdrop = $(Dialog.selectors.modalBackdrop)
                $modalBackdrop.remove();

                let $newModalBackdrop = $("<DIV>");
                $newModalBackdrop.addClass('modal-backdrop fade show');
                $('.twig-body-section').append($newModalBackdrop);
            }
        })
    }


    /**
     * @description This is required in addition to:
     *              @see Dialog.moveBackdropToReloadablePageContainer()
     *              because the backdrop has now been moved and Bootstrap doesn't know where it is
     */
    public attachHideBackdropBackgroundForStandardStaticInDomBootstrapModals(): void
    {
        $(document).on('hidden.bs.modal', (event) => {
            let $targetElement = $(event.target);

            if( $targetElement.hasClass(Dialog.classesNames.modalMovedBackdrop) ){
                let $modalBackdrop = $(Dialog.selectors.modalBackdrop)
                $modalBackdrop.remove();
            }
        })
    }

    /**
     * @description Bootstrap modal is stealing focus from the search input once the picker is called from withing modal
     *              so this method will prevent such case to happen
     */
    public static preventModalStealingFocusForTargetSelector(selector: string): void
    {
        $(document).on('focusin', function(event) {
            let $iconPickerSearchInput = $(selector);
            if (
                    DomElements.doElementsExists($iconPickerSearchInput)
                &&  $(event.target).hasClass('modal')
            ) {
                event.stopImmediatePropagation();
                $iconPickerSearchInput.focus();
            }
        });
    }

}