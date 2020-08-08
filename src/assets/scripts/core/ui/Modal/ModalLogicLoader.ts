/**
 * @description this class is responsible for loading modal logic
 */
import ModalDataDto from "../../../DTO/ModalDataDto";
import ModalLogic   from "./ModalLogic";

export default class ModalLogicLoader {

    /**
     * @description returns ModalDataDto for given modal definition by it's name
     *              if no definition is present - returns null
     * @param modalName
     */
    public getModalDataDto(modalName: string): ModalDataDto|null
    {
        let modalData = ModalLogic[modalName];

        if( modalData instanceof ModalDataDto ){
            return modalData;
        }

        return null;
    }

}