import BootstrapNotify              from "../../../libs/bootstrap-notify/BootstrapNotify";
import DataTable                    from "../../../libs/datatable/DataTable";
import PrismHighlight               from "../../../libs/prism/PrismHighlight";
import Ajax                         from "../../ajax/Ajax";
import CallableViaDataAttrsDialogs  from "../Dialogs/CallableViaDataAttrsDialogs";
import BootboxWrapper               from "../../../libs/bootbox/BootboxWrapper";
import Initializer                  from "../../../Initializer";
import DomAttributes                from "../../utils/DomAttributes";
import DomElements                  from "../../utils/DomElements";
import DataProcessorLoader          from "../DataProcessor/DataProcessorLoader";

export default abstract class AbstractAction {

    /**
     * @type Object
     */
    protected elements = {
        'removed-element-class' : '.trash-parent',
        'edited-element-class'  : '.editable-parent',
        'saved-element-class'   : '.save-parent',
        'copy-element-class'    : '.copy-parent',
    };

    /**
     * @type Object
     */
    protected classes = {
        'hidden'                    : 'd-none',
        'disabled'                  : 'disabled',
        'table-active'              : 'table-active',
        'fontawesome-picker-preview': 'fontawesome-preview',
        'fontawesome-picker-input'  : 'fontawesome-input',
        'fontawesome-picker'        : 'fontawesome-picker',
        'entity-remove-action'      : '.entity-remove-action',
        'accordion'                 : '.ui-accordion',
        'accordionContent'          : '.ui-accordion-content',
    };

    protected otherSelectors = {
        entityRemoveAction        : '[data-entity-removal-action="true"]',
        entityCallEditModalAction : '[data-entity-modal-edit-action="true"]'
    };

    /**
     * @type Object
     */
    protected data = {
        entityToggleBoolval                 : "data-entity-toggle-boolval",
        entityToggleBoolvalSuccessMessage   : "data-entity-toggle-success-message",
        entityId                            : "data-entity-id",
        entityRepositoryName                : "data-entity-repository-name",
        entityFieldName                     : "data-entity-field-name",
        baseParentElementSelector           : "data-base-parent-element-selector",
        tinymceElementSelector              : "data-tiny-mce-selector",
        tinymceElementInstanceSelector      : "data-tiny-mce-instance-selector", // must be id name without `#`
    };

    /**
     * @type Object
     */
    public static messages = {

        entityEditStart: function (entity_name) {
            return 'You are currently editing ' + entity_name + ' record'
        },
        entityEditEnd: function (entity_name) {
            return "You've finished editing " + entity_name + ' record';
        },
        formTargetActionUpdateSuccess:function(form_target_action_name){
            return "Update action for " + form_target_action_name + ' has been completed';
        },
        formTargetActionUpdateFail:function(form_target_action_name){
            return "There was a problem while performing update action for " + form_target_action_name;
        },
        doYouWantToRemoveThisRecord: function(){
            return "Do You want to remove this record?";
        },
        couldNotRemoveEntityFromRepository: function(repositoryName){
            return "Could not remove entity from " + repositoryName;
        },
        entityHasBeenRemovedFromRepository: function(){
            return "Record has been removed successfully";
        },
        default_record_removal_confirmation_message: 'Are You sure that You want to remove this record?',
    };

    protected methods = {
        removeEntity: {
            url: "/api/repository/remove/entity/{repository_name}/{id}",
            method: "GET",
            params: {
                repositoryName: "{repository_name}",
                id: "{id}"
            }
        },
        updateEntityByRepositoryName: {
            MyContactRepository: {
                // special submission button goes here - like sending all data at once stripping forms etc
            }
        },
        toggleBoolval: {
            url     : "/api/repository/toggle-boolval",
                method  : "GET",
                /**
                 *
                 * @param paramEntityId         {string}
                 * @param paramRepositoryName   {string}
                 * @param paramFieldName        {string}
                 * @returns {string}
                 */
                buildUrl: function(paramEntityId, paramRepositoryName, paramFieldName){
                if(
                        "" === paramEntityId
                    ||  "" === paramRepositoryName
                    ||  "" === paramFieldName
                ){
                    throw{
                        "message": "At least one of the params required to build url for boolval toggle is missing",
                        paramEntityId       : paramEntityId,
                        paramRepositoryName : paramRepositoryName,
                        paramFieldName      : paramFieldName
                    };
                }

                let url = this.url + '/' + paramEntityId + '/' + paramRepositoryName + '/' + paramFieldName;
                return url;
            }
        }
    };

    /**
     * @type BootstrapNotify
     */
    protected bootstrapNotify = new BootstrapNotify();

    /**
     * @type DataTable
     */
    protected datatable = new DataTable();

    /**
     * @type PrismHighlight
     */
    protected prismjs = new PrismHighlight();

    /**
     * @type Ajax
     */
    protected ajax = new Ajax();

    /**
     * @type CallableViaDataAttrsDialogs
     */
    protected dialogsViaAttr = new CallableViaDataAttrsDialogs();

    /**
     * @type BootboxWrapper
     */
    protected bootboxNotify = new BootboxWrapper();

    /**
     * @type Initializer
     */
    protected initializer = new Initializer();

    /**
     * @description Shows/hides actions icons (for example in tables edit/create/delete)
     * @param $element
     * @param toggleContentEditable
     * @param isContentEditable
     */
    protected toggleActionIconsVisibility($element: JQuery, toggleContentEditable: boolean = false, isContentEditable: boolean = false) {
        let saveIcon        = $($element).find('.save-record');
        let fontawesomeIcon = $($element).find('.action-fontawesome');

        let actionIcons = [saveIcon, fontawesomeIcon];

        $(actionIcons).each((index, icon) => {
           let $icon = $(icon);

            if (DomElements.doElementsExists($icon) && DomAttributes.hasDisplayNoneClass($icon) && !isContentEditable) {
                DomAttributes.unsetDisplayNoneClass($icon);
                return;
            }

            DomAttributes.setDisplayNoneClass($icon);
        });

        if (toggleContentEditable === true) {
            this.toggleContentEditable($element);
        }
    };

    /**
     * @description Toggles css `disabled` class for certain elements in table
     *              like for example after clicking on row edit certain data should be undeditable/interractable
     * @param tr_parent_element
     */
    protected toggleDisabledClassForTableRow(tr_parent_element) {
        let color_pickers   = $(tr_parent_element).find('.color-picker');
        let toggle_buttons  = $(tr_parent_element).find('.toggle-button');
        let option_pickers  = $(tr_parent_element).find('.option-picker');
        let date_pickers    = $(tr_parent_element).find('.date-picker');
        let checkbox        = $(tr_parent_element).find('.checkbox-disabled');
        let selectize       = $(tr_parent_element).find('.selectize-control');
        let dataPreview     = $(tr_parent_element).find('.data-preview');
        let elements_to_toggle = [color_pickers, option_pickers, date_pickers, checkbox, selectize, dataPreview, toggle_buttons];
        let _this = this;

        $(elements_to_toggle).each((index, element_type) => {

            if ($(element_type).length !== 0) {
                $(element_type).each((index, element) => {

                    if ($(element).hasClass(_this.classes.disabled)) {
                        $(element).removeClass(_this.classes.disabled);
                    } else {
                        $(element).addClass(_this.classes.disabled);
                    }
                });
            }
        })
    };

    /**
     * @description Toggles content editable of element - mostly table
     * Todo: should be refactored
     * @param baseElement
     */
    protected toggleContentEditable(baseElement) {
        let isContentEditable = DomAttributes.isContentEditable(baseElement, 'td');
        let paramEntityName   = $(baseElement).attr('data-type');

        let dataProcessorDto = DataProcessorLoader.getUpdateDataProcessorDto(DataProcessorLoader.PROCESSOR_TYPE_ENTITY, paramEntityName);
        let message          = AbstractAction.messages.entityEditStart(dataProcessorDto.processorName);

        if (!isContentEditable) {
            DomAttributes.contentEditable(baseElement, DomAttributes.actions.set,  'td', 'input, select, button, img');
            $(baseElement).addClass(this.classes["table-active"]);
            this.toggleActionIconsVisibility(baseElement, null, isContentEditable);
            this.toggleDisabledClassForTableRow(baseElement);

            this.bootstrapNotify.showOrangeNotification(message);
            return;
        }

        this.toggleActionIconsVisibility(baseElement, null, isContentEditable);
        this.toggleDisabledClassForTableRow(baseElement);

        DomAttributes.contentEditable(baseElement, DomAttributes.actions.unset,'td', 'input, select, button, img');
        $(baseElement).removeClass(this.classes["table-active"]);
        this.bootstrapNotify.showGreenNotification(message);
    };


}