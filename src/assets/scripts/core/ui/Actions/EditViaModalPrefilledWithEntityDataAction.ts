import AbstractAction   from "./AbstractAction";
import DomElements      from "../../utils/DomElements";

export default class EditViaModalPrefilledWithEntityDataAction extends AbstractAction {

    public init()
    {
        this.attachEntityEditModalCallEvent(this.otherSelectors.entityCallEditModalAction);
    }

    /**
     * Editing is based on modal
     * @param selector
     * @returns {boolean}
     */
    private attachEntityEditModalCallEvent(selector){
        let element = $(selector);
        let _this   = this;

        if( !DomElements.doElementsExists(element) ){
            return false;
        }

        $(element).on('click', function() {
            let clickedElement  = $(this);
            let entityId        = $(clickedElement).attr('data-entity-id');
            let repositoryName  = $(clickedElement).attr('data-repository-name'); // consts from Repositories class

            _this.callModalForEntity(entityId, repositoryName);
        })
    }

    /**
     * Uses the modal building logic for calling box with prefilled data
     * @param entityId
     * @param repositoryName
     */
    private callModalForEntity(entityId, repositoryName){
        let modalUrl = this.methods.buildEditEntityModalByRepositoryName[repositoryName].url;
        let method   = this.methods.buildEditEntityModalByRepositoryName[repositoryName].method;
        let callback = this.methods.buildEditEntityModalByRepositoryName[repositoryName].callback;


        if( "undefined" === typeof modalUrl ){
            throw({
                "message"        : "There is no url defined for editing modal call for given repository",
                "repositoryName" : repositoryName
            });
        }

        let requestData = {
            entityId: entityId
        };

        this.dialogsViaAttr.buildDialogBody(modalUrl, method, requestData, callback);
    }
}