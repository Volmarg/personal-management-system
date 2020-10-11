import ModalLogicLoader from "./ModalLogicLoader";
import ModalDataDto     from "../../../DTO/ModalDataDto";

/**
 * @description This class handles standard modals, hardcoded in twig templates - not the one called via js bootbox
 * @deprecated  This logic needs to be investigated and most likely merged to the Dialogs logic
 *              Probably this also has something to do with bootbox
 */
export default class Modal {

    /**
     * @type Object
     */
    private static attributes = {
        isModal   : 'data-is-modal',
        modalName : 'data-modal-name'
    };

    /**
     * @type ModalLogicLoader
     */
    private modalLogicLoader = new ModalLogicLoader();

    public init(): void
    {
        this.onModalBeingShown();
    }

    /**
     * @description Handles the logic for given modal upon showing
     */
    public onModalBeingShown(): void
    {
        let allModals = $("[" + Modal.attributes.isModal + "='true']");

        $.each(allModals, (index, element) => {
            let $element  = $(element);
            let modalName = $element.attr(Modal.attributes.modalName);

            $element.on('shown.bs.modal', () => {

                let modalDataDto = this.modalLogicLoader.getModalDataDto(modalName);

                if( !(modalDataDto instanceof ModalDataDto) ){
                    return;
                }

                modalDataDto.callback($element);
            })
        });
    }

    /**
     * @description Will hide the modal backdrop only
     * Fix: Bootbox/Bootstrap based problem - the backdrop wont be sometimes remove on closing modal
     */
    public hideModalBackdrop() //todo: remove after testing
    {
        //$('.modal-backdrop').remove();
    }

}