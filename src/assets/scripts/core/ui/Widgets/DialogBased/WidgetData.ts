import UpdateAction             from "../../Actions/UpdateAction";
import FormAppendAction         from "../../Actions/FormAppendAction";
import JsColor                  from "../../../../libs/jscolor/JsColor";
import TinyMce                  from "../../../../libs/tiny-mce/TinyMce";
import NotesTinyMce             from "../../../../modules/Notes/NotesTinyMce";
import FormsUtils               from "../../../utils/FormsUtils";
import WidgetDataDto            from "../../../../DTO/WidgetDataDto";
import Ajax                     from "../../../ajax/Ajax";
import ControllerStructure      from "../../BackendStructure/ControllerStructure";
import RouterStructure          from "../../BackendStructure/RouterStructure";
import DirectoriesBasedWidget   from "../DirectoriesBased/DirectoriesBasedWidget";
import Navigation               from "../../../Navigation";
import CreateAction             from "../../Actions/CreateAction";
import BootstrapSelect          from "../../../../libs/bootstrap-select/BootstrapSelect";
import FineUploaderService      from "../../../../libs/fine-uploader/FineUploaderService";
import SmartTab                 from "../../../../libs/smarttab/SmartTab";

/**
 * @description this class contains definitions of logic used for given widget
 */
export default class WidgetData {

    /**
     * @type Object
     */
    static readonly widgetsIds = {
        addNote                   : 'add-note',
        myFilesNewFolderWidget    : 'my-files-new-folder-widget',
        myImagesNewFolderWidget   : 'my-images-new-folder-widget',
        filesUploadWidget         : 'my-files-upload-widget',
        myVideoNewFolderWidget    : 'my-video-new-folder-widget',
        addContactCardWidget      : 'add-contact-card-widget',
        pendingIssuesCreateIssue  : 'pendingIssuesCreateIssue',
        addMyTodo                 : 'widget-add-my-todo',
    };

    /**
     * @type string
     */
    static readonly TYPE_TEMPLATE = "template";

    public static addContactCardWidget(): WidgetDataDto
    {
        let callback = () => {
            let updateAction     = new UpdateAction();
            let formAppendAction = new FormAppendAction();
            let smartTab         = new SmartTab();

            formAppendAction.attachFormViewAppendEvent();
            JsColor.init();
            updateAction.attachContentSaveEventOnSaveIcon();
            smartTab.init();
        };

        let url           = Ajax.getUrlForPathName(RouterStructure.DIALOG_BODY_CREATE_CONTACT_CARD_PATH);
        let widgetDataDto = new WidgetDataDto();

        widgetDataDto.url      = url;
        widgetDataDto.type     = WidgetData.TYPE_TEMPLATE;
        widgetDataDto.callback = callback;

        return widgetDataDto;
    };

    public static addNoteWidget(): WidgetDataDto
    {
        let callback = () => {
            let tinyMce      = new TinyMce();
            let createAction = new CreateAction();

            TinyMce.remove(".tiny-mce");
            tinyMce.init();
            createAction.init();
            BootstrapSelect.init();
            NotesTinyMce.selectCurrentCategoryOptionForQuickNoteCreator();

            let formSubmitButton = $('#my_notes_submit');
            formSubmitButton.attr('data-template-url', window.location.pathname);
            formSubmitButton.attr('data-template-method', 'GET');
        };
        let getAttrs      = Navigation.getCurrentGetAttrs();
        let categoryName  = getAttrs['category'];
        let categoryId    = getAttrs['categoryId'];

        let url           = `/dialog/body/create-note/${categoryName}/${categoryId}`;
        let widgetDataDto = new WidgetDataDto();

        widgetDataDto.url      = url;
        widgetDataDto.type     = WidgetData.TYPE_TEMPLATE;
        widgetDataDto.callback = callback;

        return widgetDataDto;
    }

    public static myFilesNewFolder(): WidgetDataDto
    {
        return WidgetData.newFolder(ControllerStructure.CONST_MODULE_NAME_FILES);
    }

    /**
     * @description handles the logic of folder creation widget for files module
     */
    public static filesUpload(): WidgetDataDto
    {
        let callback = () => {
            let forms                   = new FormsUtils();
            let directoriesBasedWidget  = new DirectoriesBasedWidget();
            let fineUploaderService     = new FineUploaderService();

            BootstrapSelect.init();
            forms.init();
            fineUploaderService.init(true);

            directoriesBasedWidget.selectCurrentModuleAndUploadDirOption(FineUploaderService.selectors.moduleSelectSelector, FineUploaderService.selectors.directorySelectSelector);

            let $mainContentInModal = $('.bootbox-body main.main-content');
            $mainContentInModal.css({
                'min-height' : "auto",
                'padding'    : '10px',
            });
        };

        let url           = Ajax.getUrlForPathName(RouterStructure.DIALOG_BODY_UPLOAD_PATH);
        let widgetDataDto = new WidgetDataDto();

        widgetDataDto.url      = url;
        widgetDataDto.type     = WidgetData.TYPE_TEMPLATE;
        widgetDataDto.callback = callback;

        return widgetDataDto;
    }

    /**
     * @description handles the logic of folder creation widget for images module
     */
    public static myImagesNewFolder(): WidgetDataDto
    {
        return WidgetData.newFolder(ControllerStructure.CONST_MODULE_NAME_IMAGES);
    }

    /**
     * @description handles the logic of folder creation widget for video module
     */
    public static myVideoNewFolder(): WidgetDataDto
    {
        return WidgetData.newFolder(ControllerStructure.CONST_MODULE_NAME_VIDEO);
    }

    public static pendingIssuesCreateIssue(): WidgetDataDto
    {
        let callback = () => {
            let createAction = new CreateAction();
            createAction.init();
        };

        let url           = Ajax.getUrlForPathName('dialog_body_create_issue');
        let widgetDataDto = new WidgetDataDto();

        widgetDataDto.url      = url;
        widgetDataDto.type     = WidgetData.TYPE_TEMPLATE;
        widgetDataDto.callback = callback;

        return widgetDataDto;
    }

    /**
     * @description handles the logic of folder creation widget
     *
     * @param moduleNameBackendConstant
     * @private
     */
    private static newFolder(moduleNameBackendConstant: string): WidgetDataDto
    {
        let moduleName = Ajax.getConstantValueFromBackend(ControllerStructure.ModulesController.getNamespace(), moduleNameBackendConstant);

        let callback = () => {
            let forms                   = new FormsUtils();
            let directoriesBasedWidget  = new DirectoriesBasedWidget();
            let createAction            = new CreateAction();

            let moduleSelectSelector    = 'select#upload_subdirectory_create_upload_module_dir';
            let directorySelectSelector = 'select#upload_subdirectory_create_subdirectory_target_path_in_module_upload_dir';

            createAction.init(false);  // dont reinitialize logic
            forms.init();
            BootstrapSelect.init();
            directoriesBasedWidget.selectCurrentModuleAndUploadDirOption(moduleSelectSelector, directorySelectSelector);

            let $mainContentInModal = $('.bootbox-body main.main-content');
            $mainContentInModal.css({
                'min-height' : "auto",
                'padding'    : '10px',
            });
        };

        let url           = "/dialog/body/create-folder";
        let widgetDataDto = new WidgetDataDto();
        let params        = {
            moduleName: moduleName
        };

        widgetDataDto.url            = url;
        widgetDataDto.type           = WidgetData.TYPE_TEMPLATE;
        widgetDataDto.callback       = callback;
        widgetDataDto.ajaxData       = params;
        widgetDataDto.callbackParams = params;

        return widgetDataDto;
    }
}