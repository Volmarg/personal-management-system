import ModalDataDto from "../../../DTO/ModalDataDto";
import Ajax         from "../../ajax/Ajax";
import AjaxEvents from "../../ajax/AjaxEvents";

/**
 * @description This class contains definitions of logic for given modals
 */
export default class ModalLogic {

    /**
     * @description contains definition of logic for:
     *
     * @see templates/modules/my-notes/components/note-edit-modal-body.html.twig
     */
    public static noteEdit(): ModalDataDto
    {

        let callback = ($modalWrapper: JQuery<HTMLElement>) => {
            $('.save-note').on('click', () => {
                let ajaxEvents = new AjaxEvents();
                ajaxEvents.loadModuleContentByUrl(location.pathname);
            })
        };

        let modalDataDto      = new ModalDataDto();
        modalDataDto.callback = callback;

        return modalDataDto;
    }

}