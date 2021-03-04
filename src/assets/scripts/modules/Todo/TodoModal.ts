import DomElements      from "../../core/utils/DomElements";
import DomAttributes    from "../../core/utils/DomAttributes";
import AjaxEvents       from "../../core/ajax/AjaxEvents";
import AjaxResponseDto  from "../../DTO/AjaxResponseDto";
import BootstrapNotify  from "../../libs/bootstrap-notify/BootstrapNotify";
import Loader           from "../../libs/loader/Loader";
import Initializer      from "../../Initializer";
import BootstrapSelect  from "../../libs/bootstrap-select/BootstrapSelect";
import EntityStructure  from "../../core/ui/BackendStructure/EntityStructure";

export default class TodoModal {

    /**
     * @type Object
     * @private
     */
    private classes = {
        moduleMainContainer : '.todo-container',
        todoModal           : '.todo-modal',
        todoModalTitle      : '.todo-title',
        editTodoButton      : '.edit-todo',
        saveTodoButton      : '.save-todo',
        modal               : '.modal',
    }

    private selectors = {
        moduleEntitySelect  : 'select.module-entity-select'
    }

    /**
     * @type Object
     * @private
     */
    private dataAttributes = {
        id              : 'data-id',
        defaultSelected : 'data-default-selected',
        moduleName      : 'data-module-name'
    }

    /**
     * @type BootstrapNotify
     */
    private bootstrapNotify = new BootstrapNotify();

    /**
     * @type AjaxEvents
     */
    private ajaxEvents = new AjaxEvents();

    /**
     * @type Initializer
     */
    private initializer = new Initializer();

    /**
     * @description will initialize whole logic
     */
    public init(): void
    {
        let $moduleMainContainer = $(this.classes.moduleMainContainer);

        if( DomElements.doElementsExists($moduleMainContainer) )
        {
            this.attachEventOnClickEditButton();
            this.attachEventOnClickSaveButton();
        }
    }

    /**
     * @description Attaches the event when clicking the `edit` button
     */
    private attachEventOnClickEditButton()
    {
        let $editTodoButton = $(this.classes.editTodoButton);
        let _this           = this;

        $editTodoButton.off('click');
        $editTodoButton.on('click', function(event){
            let $clickedButton         = $(event.currentTarget);
            let $modal                 = $($clickedButton).closest(_this.classes.todoModal);
            let $todoTitle             = $($modal).find(_this.classes.todoModalTitle);
            let $moduleAndEntitySelect = $modal.find(_this.selectors.moduleEntitySelect);

            if ( !DomAttributes.isDisabledAttribute($moduleAndEntitySelect) ) {
                DomAttributes.setDisabledAttribute($moduleAndEntitySelect)
            }else{
                DomAttributes.unsetDisabledAttribute($moduleAndEntitySelect);
            }
            BootstrapSelect.refreshSelector($moduleAndEntitySelect);

            if( !DomAttributes.isContentEditable($todoTitle) ) {
                DomAttributes.contentEditable($todoTitle, DomAttributes.actions.set)
                $($todoTitle).css({'border-bottom': '1px rgba(0,0,0,0.2) solid'});
            }else{
                DomAttributes.contentEditable($todoTitle, DomAttributes.actions.unset)
                $($todoTitle).attr('style', '');
            }
        });

    }

    /**
     * @description Attaches the event when clicking the `save` button
     */
    private attachEventOnClickSaveButton(): void
    {
        let $saveTodoButton = $(this.classes.saveTodoButton);
        let _this           = this;

        $saveTodoButton.off('click');
        $saveTodoButton.on('click', function(event){

            let $clickedButton                   = $(event.currentTarget);
            let $modal                           = $clickedButton.closest(_this.classes.modal);
            let $todoAndEntitySelect             = $modal.find(_this.selectors.moduleEntitySelect);
            let $todoAndEntityListSelectedOption = $todoAndEntitySelect.find(":selected");

            let $moduleOptGroup      = $todoAndEntityListSelectedOption.closest('optgroup');
            let isBoundEntity        = DomElements.doElementsExists($moduleOptGroup);

            let selectedOptionValue  = $todoAndEntitySelect.val();

            let entityId   = null;
            let moduleName = selectedOptionValue;

            let issueData  = {
                "type"      : "entity",
                'namespace' : EntityStructure.MyIssue.getNamespace(),
                "id"        : null,
                'isNull'    : true,
            }

            if( isBoundEntity ){
                moduleName = $moduleOptGroup.attr(_this.dataAttributes.moduleName);
                entityId   = selectedOptionValue;
                issueData  = {
                    "type"      : "entity",
                    'namespace' : EntityStructure.MyIssue.getNamespace(),
                    'id'        : entityId,
                    'isNull'    : false,
                }
            }

            let name = $modal.find(_this.classes.todoModalTitle).text();
            let id   = $modal.attr(_this.dataAttributes.id);

            let ajaxData = {
                'name'       : name,
                'id'         : id,
                'myIssue'    : issueData,
                'moduleName' : moduleName
            };

            Loader.showMainLoader();
            $.ajax({
                method : AjaxEvents.REQUEST_TYPE_POST,
                url    : '/admin/todo/update',
                data   : ajaxData
            }).always( (response) => {

                Loader.hideMainLoader();
                let ajaxResponseDto = AjaxResponseDto.fromArray(response);

                if( ajaxResponseDto.isSuccessCode() ){
                    _this.bootstrapNotify.showGreenNotification(ajaxResponseDto.message);
                }else{
                    _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                    return;
                }

                _this.ajaxEvents.loadModuleContentByUrl(ajaxResponseDto.routeUrl);
                _this.initializer.reinitializeLogic();
            });

        })
    }

}