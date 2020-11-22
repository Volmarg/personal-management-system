import BootstrapNotify  from "../../libs/bootstrap-notify/BootstrapNotify";
import Ajax             from "../../core/ajax/Ajax";
import DomElements      from "../../core/utils/DomElements";
import AjaxResponseDto  from "../../DTO/AjaxResponseDto";
import DomAttributes    from "../../core/utils/DomAttributes";

export default class TodoChecklist {

    private static strings = {
        'done'    : 'Done',
        'notDone' : 'Not Done'
    };

    private static selectors = {
        classes: {
            'checklist-checkbox' : '.checklist-checkbox',
            'badge'              : '.badge-pill',
        },
        attributes: {
            'todoId'        : 'data-todo-id',
            'todoElementId' : 'data-element-id',
        },
        strings: {
            'badge-done' : 'badge-success',
            'badge-todo' : 'badge-info'
        }
    };

    /**
     * @type BootstrapNotify
     */
    private bootstrapNotify = new BootstrapNotify();

    /**
     * @description Main initialization logic
     */
    public init(): void 
    {
        this.attachTodoElementStatusChangedOnCheckbox();
    };
    
    private attachTodoElementStatusChangedOnCheckbox() {
        let _this      = this;
        let checkboxes = $(TodoChecklist.selectors.classes["checklist-checkbox"]);

        if ( !DomElements.doElementsExists($(checkboxes)) ) {
            return;
        }

        $(checkboxes).each((index, checkbox) => {

            $(checkbox).click((event) => {
                let clickedCheckbox = event.target;

                //deny changing anything if any of the contents is being edited
                if( DomAttributes.isContentEditable($(clickedCheckbox).closest('.checkbox'), '.toggle-content-editable') ){
                    event.preventDefault();
                    return;
                }

                let todoId              = $(clickedCheckbox).attr(TodoChecklist.selectors.attributes.todoId);
                let elementId           = $(clickedCheckbox).attr(TodoChecklist.selectors.attributes.todoElementId);
                let completed           = true;
                let checkboxCheckedAttr = $(clickedCheckbox).attr('checked');
                let liParent            = $(clickedCheckbox).closest('li');
                let badge               = $(liParent).find(TodoChecklist.selectors.classes.badge);

                if( undefined !== checkboxCheckedAttr && null !== checkboxCheckedAttr){
                    $(clickedCheckbox).removeAttr('checked');

                    $(badge).removeClass(TodoChecklist.selectors.strings["badge-done"]);
                    $(badge).addClass(TodoChecklist.selectors.strings["badge-todo"]);
                    $(badge).html(TodoChecklist.strings.notDone);

                    completed = false;
                }else{
                    $(clickedCheckbox).attr('checked','true');

                    $(badge).removeClass(TodoChecklist.selectors.strings["badge-todo"]);
                    $(badge).addClass(TodoChecklist.selectors.strings["badge-done"]);
                    $(badge).html(TodoChecklist.strings.done)
                }

                let requestData = {
                    'id'        : elementId,
                    'myTodo': {
                        "type": "entity",
                        'namespace': 'App\\Entity\\Modules\\Todo\\MyTodo',
                        'id': todoId,
                    },
                    'completed' : completed
                };

                $.ajax({
                    method  : Ajax.REQUEST_TYPE_POST,
                    url     : '/admin/todo/element/update/',
                    data    : requestData,
                }).always((data) => {

                    let ajaxResponseDto  = AjaxResponseDto.fromArray(data);

                    if( ajaxResponseDto.isSuccessCode() ){
                        this.bootstrapNotify.showGreenNotification(ajaxResponseDto.message);
                    }else{
                        this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                    }

                });
            })

        })
    }
};