import UpdateAction             from "../../Actions/UpdateAction";
import FormAppendAction         from "../../Actions/FormAppendAction";
import JsColor                  from "../../../../libs/jscolor/JsColor";
import TinyMce                  from "../../../../libs/tiny-mce/TinyMce";
import ActionsInitializer       from "../../Actions/ActionsInitializer";
import NotesTinyMce             from "../../../../modules/Notes/NotesTinyMce";
import Upload                   from "../../../../modules/Files/Upload";
import FormsUtils               from "../../../utils/FormsUtils";
import WidgetDataDto            from "../../../../DTO/WidgetDataDto";
import Ajax                     from "../../Ajax";
import ControllerStructure      from "../../BackendStructure/ControllerStructure";
import RouterStructure          from "../../BackendStructure/RouterStructure";
import DirectoriesBasedWidget   from "../DirectoriesBased/DirectoriesBasedWidget";

/**
 * @description this class contains definitions of logic used for given widget
 */
export default class WidgetData {

    /**
     * @type Object
     */
    static readonly widgetsIds = {
        addNote                   : 'add-note',
        myFilesUploadFilesWidget  : 'my-files-upload-files-widget',
        myFilesNewFolderWidget    : 'my-files-new-folder-widget',
        myImagesUploadFilesWidget : 'my-images-upload-files-widget',
        myImagesNewFolderWidget   : 'my-images-new-folder-widget',
        addContactCardWidget      : 'add-contact-card-widget',
        pendingIssuesCreateIssue  : 'pendingIssuesCreateIssue'
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
            let jscolor          = new JsColor();

            formAppendAction.attachFormViewAppendEvent();
            jscolor.init();
            updateAction.attachContentSaveEventOnSaveIcon();
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
            let tinyMce = new TinyMce();

            TinyMce.remove(".tiny-mce");
            tinyMce.init();
            ActionsInitializer.initializeCreateAction();
            NotesTinyMce.selectCurrentCategoryOptionForQuickNoteCreator();

            let formSubmitButton = $('#my_notes_submit');
            formSubmitButton.attr('data-template-url', window.location.pathname);
            formSubmitButton.attr('data-template-method', 'GET');
        };

        let url           = '';//url: '/dialog/body/create-note/' ~ getAttrs.category ~ '/' ~ getAttrs.category_id, ;
        let widgetDataDto = new WidgetDataDto();

        widgetDataDto.url      = url;
        widgetDataDto.type     = WidgetData.TYPE_TEMPLATE;
        widgetDataDto.callback = callback;

        return widgetDataDto;
    }

    public static myFilesUpload(): WidgetDataDto
    {
        let callback = () => {
            let upload                  = new Upload();
            let forms                   = new FormsUtils();
            let directoriesBasedWidget  = new DirectoriesBasedWidget();
            let moduleSelectSelector    = 'select#upload_form_upload_module_dir';
            let directorySelectSelector = 'select#upload_form_subdirectory';

            upload.init();
            forms.init();
            directoriesBasedWidget.selectCurrentModuleAndUploadDirOptionForQuickCreateFolder(moduleSelectSelector, directorySelectSelector);

            let mainContentInModal = $(".bootbox-body main.main-content");
            mainContentInModal.css({
                "min-height" : "auto",
                "padding"    : "10px"
            });
        };

        let url           = '/dialog/body/upload';
        let widgetDataDto = new WidgetDataDto();

        widgetDataDto.url      = url;
        widgetDataDto.type     = WidgetData.TYPE_TEMPLATE;
        widgetDataDto.callback = callback;

        return widgetDataDto;
    }

    public static myFilesNewFolder(): WidgetDataDto
    {
        let moduleName = Ajax.getConstantValueFromBackend(ControllerStructure.ModulesController.getNamespace(), ControllerStructure.CONST_MODULE_NAME_FILES);

        let callback = () => {
            let upload                  = new Upload();
            let forms                   = new FormsUtils();
            let directoriesBasedWidget  = new DirectoriesBasedWidget();
            let moduleSelectSelector    = 'select#upload_subdirectory_create_upload_module_dir';
            let directorySelectSelector = 'select#upload_subdirectory_create_subdirectory_target_path_in_module_upload_dir';

            ActionsInitializer.initializeCreateAction(false);  // dont reinitialize logic
            upload.init();
            forms.init();

            directoriesBasedWidget.selectCurrentModuleAndUploadDirOptionForQuickCreateFolder(moduleSelectSelector, directorySelectSelector);

            let $mainContentInModal = $('.bootbox-body main.main-content');
            $mainContentInModal.css({
                'min-height' : "auto",
                'padding'    : '10px',
            });
        };

        let url           = "/dialog/body/create-folder";
        let widgetDataDto = new WidgetDataDto();
        let ajaxData      = {
            moduleName: moduleName
        };

        widgetDataDto.url      = url;
        widgetDataDto.type     = WidgetData.TYPE_TEMPLATE;
        widgetDataDto.callback = callback;
        widgetDataDto.ajaxData = ajaxData;

        return widgetDataDto;
    }

    public static myImagesUpload(): WidgetDataDto
    {
        let callback = () => {
            let upload                  = new Upload();
            let forms                   = new FormsUtils();
            let directoriesBasedWidget  = new DirectoriesBasedWidget();
            let moduleSelectSelector    = 'select#upload_form_upload_module_dir';
            let directorySelectSelector = 'select#upload_form_subdirectory';

            upload.init();
            forms.init();

            directoriesBasedWidget.selectCurrentModuleAndUploadDirOptionForQuickCreateFolder(moduleSelectSelector, directorySelectSelector);

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
    };

    public static myImagesNewFolder(): WidgetDataDto
    {
        let moduleName = Ajax.getConstantValueFromBackend(ControllerStructure.ModulesController.getNamespace(), ControllerStructure.CONST_MODULE_NAME_IMAGES);

        let callback = () => {
            let upload                  = new Upload();
            let forms                   = new FormsUtils();
            let directoriesBasedWidget  = new DirectoriesBasedWidget();

            let moduleSelectSelector    = 'select#upload_subdirectory_create_upload_module_dir';
            let directorySelectSelector = 'select#upload_subdirectory_create_subdirectory_target_path_in_module_upload_dir';

            ActionsInitializer.initializeCreateAction(false);  // dont reinitialize logic
            upload.init();
            forms.init();
            directoriesBasedWidget.selectCurrentModuleAndUploadDirOptionForQuickCreateFolder(moduleSelectSelector, directorySelectSelector);

            let $mainContentInModal = $('.bootbox-body main.main-content');
            $mainContentInModal.css({
                'min-height' : "auto",
                'padding'    : '10px',
            });
        };

        let url           = Ajax.getUrlForPathName('dialog_body_create_folder');
        let widgetDataDto = new WidgetDataDto();
        let ajaxData      = {
            moduleName: moduleName
        };

        widgetDataDto.url      = url;
        widgetDataDto.type     = WidgetData.TYPE_TEMPLATE;
        widgetDataDto.callback = callback;
        widgetDataDto.ajaxData = ajaxData;

        return widgetDataDto;
    }

    public static pendingIssuesCreateIssue(): WidgetDataDto
    {
        let callback = () => {
            ActionsInitializer.initializeCreateAction();
        };

        let url           = Ajax.getUrlForPathName('dialog_body_create_issue');
        let widgetDataDto = new WidgetDataDto();

        widgetDataDto.url      = url;
        widgetDataDto.type     = WidgetData.TYPE_TEMPLATE;
        widgetDataDto.callback = callback;

        return widgetDataDto;
    }

}