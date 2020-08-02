/**
 * This class is only a temporary placeholder for executing logic which was added directly to twig
 *  this needs to be reorganized later, but also it's almost impossible to proceed with TS rewriting
 *  without dealing with this first
 */
import Upload from "../modules/Files/Upload";
import FormsUtils from "../core/utils/FormsUtils";
import Initializer from "../Initializer";
import ActionsInitializer from "../core/ui/Actions/ActionsInitializer";
import UpdateAction from "../core/ui/Actions/UpdateAction";
import TinyMce from "../libs/tiny-mce/TinyMce";
import FormAppendAction from "../core/ui/Actions/FormAppendAction";

export default class TemporaryTwigImbuedLogicExecutionForWidgets {


    public static addNoteWidget()
    {
        let tinyMce = new TinyMce();

        return {
            type: 'template',
            //url: '/dialog/body/create-note/' ~ getAttrs.category ~ '/' ~ getAttrs.category_id,
            callback: () => {
                TinyMce.remove(".tiny-mce");
                tinyMce.init();
                ActionsInitializer.initializeCreateAction();

                // this one is in public _scripts
                myNotesCategory.selectCurrentCategoryOptionForQuickNoteCreator();
            },
            subtype:'add-note'
        }
    }

    public static myFilesUpload()
    {
        return {
            type      : 'template',
            url       : '/dialog/body/upload',
            callback  : () => {
                let upload = new Upload();
                let forms  = new FormsUtils();

                upload.init();
                forms.init();

                // this one is in public _scripts
                uploadWidget.selectCurrentModuleAndUploadDirOptionForQuickUpload();

                let mainContentInModal = $(".bootbox-body main.main-content");
                mainContentInModal.css({
                    "min-height" : "auto",
                    "padding"    : "10px"
                });
            },
            subtype :'form'
        }
    }

    public static myFilesNewFolder()
    {
        let upload = new Upload();
        let forms  = new FormsUtils();

        return {
            type     : 'template',
            url      : "/dialog/body/create-folder",
            callback : () => {
                ActionsInitializer.initializeCreateAction(false);  // dont reinitialize logic
                upload.init();
                forms.init();

                // this one is in public _scripts
                createFolderWidget.selectCurrentModuleAndUploadDirOptionForQuickCreateFolder();

                let $mainContentInModal = $('.bootbox-body main.main-content');
                $mainContentInModal.css({
                    'min-height' : "auto",
                    'padding'    : '10px',
                });
            },
            subtype :'quickFolderCreation',
            data    : {
                moduleName: constant(modules_controller~'MODULE_NAME_FILES')
            }
        }
    }

    public static myImagesUpload()
    {
        let upload = new Upload();
        let forms  = new FormsUtils();

        return {
            "type" : 'template',
            "url"  : path('dialog_body_upload'),
            callback: () => {
                upload.init();
                forms.init();

                uploadWidget.selectCurrentModuleAndUploadDirOptionForQuickUpload();

                let $mainContentInModal = $('.bootbox-body main.main-content');
                $mainContentInModal.css({
                    'min-height' : "auto",
                    'padding'    : '10px',
                });
            },
            subtype:'quickFolderCreation'
        }
    };

    public static myImagesNewFolder()
    {
        let upload = new Upload();
        let forms  = new FormsUtils();

        return {
            type: 'template',
            url: path('dialog_body_create_folder'),
            callback: () => {
                ActionsInitializer.initializeCreateAction(false);  // dont reinitialize logic
                upload.init();
                forms.init();
                createFolderWidget.selectCurrentModuleAndUploadDirOptionForQuickCreateFolder();

                let $mainContentInModal = $('.bootbox-body main.main-content');
                $mainContentInModal.css({
                    'min-height' : "auto",
                    'padding'    : '10px',
                });
            },
            subtype: 'form',
            data: {
                "moduleName": constant(modules_controller~'MODULE_NAME_IMAGES')
            }
        }
    }

    public static addContactCardWidget()
    {
        let updateAction     = new UpdateAction();
        let formAppendAction = new FormAppendAction();

        return {
            type: 'template',
            url: path('dialog_body_create_contact_card'),
            callback: () => {
                formAppendAction.attachFormViewAppendEvent();
                jscolorCustom.init();
                updateAction.attachContentSaveEventOnSaveIcon();
            },
            subtype:'contactCardCreation'
        }
    };

}