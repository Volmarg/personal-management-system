import AbstractAction               from "./AbstractAction";
import DomElements                  from "../../utils/DomElements";
import JsColor                      from "../../../libs/jscolor/JsColor";
import FormAppendAction             from "./FormAppendAction";
import UpdateAction                 from "./UpdateAction";
import CallableViaDataAttrsDialogs  from "../Dialogs/CallableViaDataAttrsDialogs";
import BootstrapSelect              from "../../../libs/bootstrap-select/BootstrapSelect";
import Ajax                         from "../../ajax/Ajax";
import SmartTab                     from "../../../libs/smarttab/SmartTab";

export default class EditViaModalPrefilledWithEntityDataAction extends AbstractAction {

    /**
     * @type FormAppendAction
     */
    private formAppendAction = new FormAppendAction();

    /**
     * @type UpdateAction
     */
    private updateAction = new UpdateAction();

    /**
     * @type CallableViaDataAttrsDialogs
     */
    private dialogsViaAttr = new CallableViaDataAttrsDialogs();

    /**
     * @type Object
     */
    protected apiMethods = {

        buildEditEntityModalByRepositoryName: {
            MyContactRepository: {
                url     : "/dialog/body/edit-contact-card",
                method  : "POST",
                callback: () => {
                    let smartTab = new SmartTab();

                    this.formAppendAction.attachFormViewAppendEvent();
                    this.formAppendAction.attachRemoveParentEvent();
                    JsColor.init();
                    this.updateAction.attachContentSaveEventOnSaveIcon();
                    BootstrapSelect.init();
                    smartTab.init();
                }
            },
            MyTravelsIdeasRepository: {
                url     : "/dialog/body/edit-travel-idea",
                method  : Ajax.REQUEST_TYPE_POST,
                callback: () => {
                    this.updateAction.init();
                }
            },
            /**
             * Each dialog method should have target repository
             * @param entityId
             */
            callModal: function(entityId){}
        },
    };

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

        $(element).off('click');
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
        let modalUrl = this.apiMethods.buildEditEntityModalByRepositoryName[repositoryName].url;
        let method   = this.apiMethods.buildEditEntityModalByRepositoryName[repositoryName].method;
        let callback = this.apiMethods.buildEditEntityModalByRepositoryName[repositoryName].callback;


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