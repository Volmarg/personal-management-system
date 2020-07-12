import BootstrapNotify from "../bootstrap-notify/BootstrapNotify";

export default (function () {

    if (typeof window.myGoals === 'undefined') {
        window.myGoals = {};
    }

    myGoals.ui = {
        messages: {
            'mark-subgoal-completed-success'    : 'Goal status has been changed',
            'mark-subgoal-completed-fail'       : 'Goal status could not been changed',
            'mark-goal-completed-success'       : 'All subgoals are done, goal has been marked as completed',
            'mark-goal-not-completed-success'   : 'You have unchecked one os subgoals therefore goal status has been marked as not completed',
        },
        strings: {
            'done'                              : 'Done',
            'notDone'                           : 'Not Done'
        },
        selectors: {
            classes: {
                'checklist-checkbox'            : '.checklist-checkbox',
                'badge'                         : '.badge-pill',
            },
            attributes: {
                'goalId'                        : 'data-goal-id',
                'subGoalId'                     : 'data-subgoal-id',
            },
            strings: {
                'badge-done'                    : 'badge-success',
                'badge-todo'                    : 'badge-info'
            }
        },
        bootstrapNotify: new BootstrapNotify(),
        init: function () {
            this.attachSubgoalStatusChangedOnCheckbox();
        },
        attachSubgoalStatusChangedOnCheckbox: function () {
            let _this      = this;
            let checkboxes = $(this.selectors.classes["checklist-checkbox"]);

            if ($(checkboxes).length === 0) {
                return;
            }

            $(checkboxes).each((index, checkbox) => {

                $(checkbox).click((event) => {
                    let clickedCheckbox = event.target;
                    let goalId              = $(clickedCheckbox).attr(_this.selectors.attributes.goalId);
                    let subGoalId           = $(clickedCheckbox).attr(_this.selectors.attributes.subGoalId);
                    let completed           = true;
                    let checkboxCheckedAttr = $(clickedCheckbox).attr('checked');
                    let liParent            = $(clickedCheckbox).closest('li');
                    let badge               = $(liParent).find(_this.selectors.classes.badge);

                    if( undefined !== checkboxCheckedAttr && null !== checkboxCheckedAttr){
                        $(clickedCheckbox).removeAttr('checked');

                        $(badge).removeClass(_this.selectors.strings["badge-done"]);
                        $(badge).addClass(_this.selectors.strings["badge-todo"]);
                        $(badge).html(_this.strings.notDone);

                        completed = false;
                    }else{
                        $(clickedCheckbox).attr('checked','true');

                        $(badge).removeClass(_this.selectors.strings["badge-todo"]);
                        $(badge).addClass(_this.selectors.strings["badge-done"]);
                        $(badge).html(_this.strings.done)
                    }

                    let data = {
                        'id'        : subGoalId,
                        'myGoal': {
                            "type": "entity",
                            'namespace': 'App\\Entity\\Modules\\Goals\\MyGoals',
                            'id': goalId,
                        },
                        'completed' : completed
                    };

                    $.ajax({
                        method  : 'POST',
                        url     : '/admin/subgoals/update/',
                        data    : data,
                    }).done(() => {
                        myGoals.ui.bootstrapNotify.notify(_this.messages["mark-subgoal-completed-success"], 'success');
                    }).fail(() => {
                        myGoals.ui.bootstrapNotify.notify(_this.messages["mark-subgoal-completed-fail"], 'danger');
                    });

                })

            })
        }
    };

}());
