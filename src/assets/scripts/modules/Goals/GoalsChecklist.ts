import BootstrapNotify from "../../libs/bootstrap-notify/BootstrapNotify";
import Ajax            from "../../core/ui/Ajax";

export default class GoalsChecklist {

    private static messages = {
        'mark-subgoal-completed-success'  : 'Goal status has been changed',
        'mark-subgoal-completed-fail'     : 'Goal status could not been changed',
        'mark-goal-completed-success'     : 'All subgoals are done, goal has been marked as completed',
        'mark-goal-not-completed-success' : 'You have unchecked one os subgoals therefore goal status has been marked as not completed',
    };

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
            'goalId'    : 'data-goal-id',
            'subGoalId' : 'data-subgoal-id',
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
     * Main initialization logic
     */
    public init(): void 
    {
        this.attachSubgoalStatusChangedOnCheckbox();
    };
    
    private attachSubgoalStatusChangedOnCheckbox() {
        let _this      = this;
        let checkboxes = $(GoalsChecklist.selectors.classes["checklist-checkbox"]);

        if ($(checkboxes).length === 0) {
            return;
        }

        $(checkboxes).each((index, checkbox) => {

            $(checkbox).click((event) => {
                let clickedCheckbox = event.target;
                let goalId              = $(clickedCheckbox).attr(GoalsChecklist.selectors.attributes.goalId);
                let subGoalId           = $(clickedCheckbox).attr(GoalsChecklist.selectors.attributes.subGoalId);
                let completed           = true;
                let checkboxCheckedAttr = $(clickedCheckbox).attr('checked');
                let liParent            = $(clickedCheckbox).closest('li');
                let badge               = $(liParent).find(GoalsChecklist.selectors.classes.badge);

                if( undefined !== checkboxCheckedAttr && null !== checkboxCheckedAttr){
                    $(clickedCheckbox).removeAttr('checked');

                    $(badge).removeClass(GoalsChecklist.selectors.strings["badge-done"]);
                    $(badge).addClass(GoalsChecklist.selectors.strings["badge-todo"]);
                    $(badge).html(GoalsChecklist.strings.notDone);

                    completed = false;
                }else{
                    $(clickedCheckbox).attr('checked','true');

                    $(badge).removeClass(GoalsChecklist.selectors.strings["badge-todo"]);
                    $(badge).addClass(GoalsChecklist.selectors.strings["badge-done"]);
                    $(badge).html(GoalsChecklist.strings.done)
                }

                let requestData = {
                    'id'        : subGoalId,
                    'myGoal': {
                        "type": "entity",
                        'namespace': 'App\\Entity\\Modules\\Goals\\MyGoals',
                        'id': goalId,
                    },
                    'completed' : completed
                };

                $.ajax({
                    method  : Ajax.REQUEST_TYPE_POST,
                    url     : '/admin/subgoals/update/',
                    data    : requestData,
                }).done(() => {
                    _this.bootstrapNotify.showGreenNotification(GoalsChecklist.messages["mark-subgoal-completed-success"]);
                }).fail(() => {
                    _this.bootstrapNotify.showRedNotification(GoalsChecklist.messages["mark-subgoal-completed-fail"]);
                });

            })

        })
    }
};