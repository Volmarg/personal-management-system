import DialogDataDto from "../../../../DTO/DialogDataDto";

/**
 * @description this class is responsible for loading dialog logic
 */
export default class DialogLogicLoader {

    /**
     * @description returns ModalDataDto for given dialog definition by it's name
     *              if no definition is present - returns null
     * @param dialogName
     */
    public getDialogDataDto(dialogName: string): DialogDataDto|null
    {
        let dialogData = DialogDataDto[dialogName];

        if( dialogData instanceof DialogDataDto ){
            return dialogData;
        }

        return null;
    }

}