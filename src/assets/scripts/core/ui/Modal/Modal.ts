/**
 * @description This class handles standard modals, hardcoded in twig templates - not the one called via js bootbox
 */
import ModalLogicLoader from "./ModalLogicLoader";
import ModalDataDto     from "../../../DTO/ModalDataDto";

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

}