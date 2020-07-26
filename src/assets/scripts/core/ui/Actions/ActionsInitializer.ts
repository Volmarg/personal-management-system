import CopyToClipboardAction                        from "./CopyToClipboardAction";
import CreateAction                                 from "./CreateAction";
import EditViaModalPrefilledWithEntityDataAction    from "./EditViaModalPrefilledWithEntityDataAction";
import EditViaTinyMceAction                         from "./EditViaTinyMceAction";
import FontawesomeAction                            from "./FontawesomeAction";
import RemoveAction                                 from "./RemoveAction";
import ToggleBoolvalAction                          from "./ToggleBoolvalAction";
import UpdateAction                                 from "./UpdateAction";

export default class ActionsInitializer {

    public static initializeAll()
    {
        let copyToClipboardAction                  = new CopyToClipboardAction();
        let createAction                           = new CreateAction();
        let editModalPrefilledWithEntityDataAction = new EditViaModalPrefilledWithEntityDataAction();
        let editViaTinyMceAction                   = new EditViaTinyMceAction();
        let fontawesomeAction                      = new FontawesomeAction();
        let removeAction                           = new RemoveAction();
        let toggleBoolvalAction                    = new ToggleBoolvalAction();
        let updateAction                           = new UpdateAction();

        copyToClipboardAction.init();
        createAction.init();
        editModalPrefilledWithEntityDataAction.init();
        editViaTinyMceAction.init();
        fontawesomeAction.init();
        removeAction.init();
        toggleBoolvalAction.init();
        updateAction.init();
    }

    public static initializeEditViaTinyMceAction()
    {
        let editViaTinyMceAction = new EditViaTinyMceAction();
        editViaTinyMceAction.init();
    }

    public static initializeCreateAction(reinitializePageLogicAfterAjaxCall = false)
    {
        let createAction = new CreateAction();
        createAction.init(reinitializePageLogicAfterAjaxCall);
    }

}