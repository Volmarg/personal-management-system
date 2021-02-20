let moment = require('moment');

/**
 * @description provides the template definitions for TuiCalendar, taken from demo examples,
 *              the logic was adjusted only a bit, so if the functions are messy then it was most likely
 *              done that way already in demo example
 *
 * @link https://nhn.github.io/tui.calendar/latest/tutorial-example14-template-for-month
 */
export default class MonthViewTemplate
{

    public static getTemplateDefinition(){
        return {
            milestone: function(schedule) {
                return MonthViewTemplate.getGridCategoryTemplate('milestone', schedule);
            },
            milestoneTitle: function() {
                return MonthViewTemplate.getGridTitleTemplate('milestone');
            },
            task: function(schedule) {
                return MonthViewTemplate.getGridCategoryTemplate('task', schedule);
            },
            taskTitle: function() {
                return MonthViewTemplate.getGridTitleTemplate('task');
            },
            allday: function(schedule) {
                return MonthViewTemplate.getTimeTemplate(schedule, true);
            },
            alldayTitle: function() {
                return MonthViewTemplate.getGridTitleTemplate('allday');
            },
            time: function(schedule) {
                return MonthViewTemplate.getTimeTemplate(schedule, false);
            },
            goingDuration: function(schedule) {
                return '<span class="calendar-icon ic-travel-time"></span>' + schedule.goingDuration + 'min.';
            },
            comingDuration: function(schedule) {
                return '<span class="calendar-icon ic-travel-time"></span>' + schedule.comingDuration + 'min.';
            },
            monthMoreTitleDate: function(date, dayname) {
                var day = date.split('.')[2];
                return '<span class="tui-full-calendar-month-more-title-day">' + day + '</span> <span class="tui-full-calendar-month-more-title-day-label">' + dayname + '</span>';
            },
            monthMoreClose: function() {
                return '<span class="tui-full-calendar-icon tui-full-calendar-ic-close"></span>';
            },
            monthGridHeader: function(dayModel) {
                var date = parseInt(dayModel.date.split('-')[2], 10);
                var classNames = ['tui-full-calendar-weekday-grid-date '];

                if (dayModel.isToday) {
                    classNames.push('tui-full-calendar-weekday-grid-date-decorator');
                }

                return '<span class="' + classNames.join(' ') + '">' + date + '</span>';
            },
            monthGridHeaderExceed: function(hiddenSchedules) {
                return '<span class="weekday-grid-more-schedules">+' + hiddenSchedules + '</span>';
            },
            monthGridFooter: function() {
                return '';
            },
            monthGridFooterExceed: function(hiddenSchedules) {
                return '';
            },
            monthDayname: function(model) {
                return String(model.label).toLocaleUpperCase();
            },
            dayGridTitle: function(viewName) {
                /*
                 * use another functions instead of 'dayGridTitle'
                 * milestoneTitle: function() {...}
                 * taskTitle: function() {...}
                 * alldayTitle: function() {...}
                */

                return MonthViewTemplate.getGridTitleTemplate(viewName);
            },
            schedule: function(schedule) {
                /*
                 * use another functions instead of 'schedule'
                 * milestone: function() {...}
                 * task: function() {...}
                 * allday: function() {...}
                */

                return MonthViewTemplate.getGridCategoryTemplate(schedule.category, schedule);
            }
        }
    }
    private static getTimeTemplate(schedule, isAllDay) {
        var html = [];

        if (!isAllDay) {
            html.push('<strong>' + moment(schedule.start.getTime()).format('HH:mm') + '</strong> ');
        }
        if (schedule.isPrivate) {
            html.push('<span class="calendar-font-icon ic-lock-b"></span>');
            html.push(' Private');
        } else {
            if (schedule.isReadOnly) {
                html.push('<span class="calendar-font-icon ic-readonly-b"></span>');
            } else if (schedule.recurrenceRule) {
                html.push('<span class="calendar-font-icon ic-repeat-b"></span>');
            } else if (schedule.attendees.length) {
                html.push('<span class="calendar-font-icon ic-user-b"></span>');
            } else if (schedule.location) {
                html.push('<span class="calendar-font-icon ic-location-b"></span>');
            }
            html.push(' ' + schedule.title);
        }

        return html.join('');
    }

    private static getGridTitleTemplate(type) {
        var title = '';

        switch(type) {
            case 'milestone':
                title = '<span class="tui-full-calendar-left-content">MILESTONE</span>';
                break;
            case 'task':
                title = '<span class="tui-full-calendar-left-content">TASK</span>';
                break;
            case 'allday':
                title = '<span class="tui-full-calendar-left-content">ALL DAY</span>';
                break;
        }

        return title;
    }

    private static getGridCategoryTemplate(category, schedule) {
        var tpl;

        switch(category) {
            case 'milestone':
                tpl = '<span class="calendar-font-icon ic-milestone-b"></span> <span style="background-color: ' + schedule.bgColor + '">' + schedule.title + '</span>';
                break;
            case 'task':
                tpl = '#' + schedule.title;
                break;
            case 'allday':
                tpl = MonthViewTemplate.getTimeTemplate(schedule, true);
                break;
        }

        return tpl;
    }
}