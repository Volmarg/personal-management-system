import MonthViewTemplate from "./views-templates/MonthViewTemplate";
import * as moment from 'moment';

var TuiCalendar = require('tui-calendar');

import DomElements from "../../core/utils/DomElements";
import Calendar, {ICalendarInfo, ISchedule} from "tui-calendar";

/**
 * @description handles the calendar logic, keep in mind that the logic assume that there will
 *              be only one TuiCalendar instance on the page, otherwise the logic needs to be adjusted
 *
 * @link https://www.npmjs.com/package/tui-calendar
 * @link https://github.com/nhn/tui.calendar/blob/master/docs/getting-started.md
 * @link https://github.com/nhn/tui.calendar/tree/master/docs
 * @link https://nhn.github.io/tui.calendar/latest/Calendar
 */
export default class TuiCalendarService
{
    private readonly DATA_ATTRIBUTE_IS_TUI_CALENDAR = 'data-is-tui-calendar';
    private readonly DATA_ATTRIBUTE_ACTION_MOVE     = 'data-action';

    private readonly ACTION_MOVE_TODAY              = 'move-today';
    private readonly ACTION_MOVE_PREVIOUS           = 'move-previous';
    private readonly ACTION_MOVE_NEXT               = 'move-next';

    private readonly ACTION_VIEW_DAILY              = 'toggle-view-daily';
    private readonly ACTION_VIEW_WEEKLY             = 'toggle-view-weekly';
    private readonly ACTION_VIEW_MONTHLY            = 'toggle-view-monthly';

    private readonly CALENDAR_LIST_SELECTOR         = '#calendarList';
    private readonly CALENDAR_NEW_SCHEDULE_BUTTON   = '#btn-new-schedule';
    private readonly VIEW_ALL_CALENDARS_SELECTOR    = '.view-all-calendars';
    private readonly CALENDAR_IN_LIST               = '.lnb-calendars-item';
    private readonly CALENDAR_IN_LIST_INPUT_ROUND   = '.tui-full-calendar-checkbox-round';

    /**
     * @description will initialize the calendar instance
     */
    public init(){
        let $allElementsToHandle = $("[" + this.DATA_ATTRIBUTE_IS_TUI_CALENDAR + "]");

        if( !DomElements.doElementsExists($allElementsToHandle) ){
            return;
        }

        this.handleAllCalendarDomElements($allElementsToHandle);
    }

    /**
     * @description handles the entire Calendar initialization logic for all of the matching elements in dom
     */
    private handleAllCalendarDomElements($allElementsToHandle: JQuery<HTMLElement>)
    {
        let _this = this;

        $allElementsToHandle.each( (index, elementToHandle) => {
            let calendarInstance = _this.createCalendarInstance(elementToHandle);
            calendarInstance     = _this.insertICalendarsIntoCalendarInstance(calendarInstance);
            calendarInstance     = _this.insertSchedulesIntoCalendarInstance(calendarInstance);

            _this.applyBuiltInEventsToCalendarInstance(calendarInstance);
            _this.displayAndHandleCalendarsList(calendarInstance);
            _this.createNewScheduleOnNewScheduleClick(calendarInstance);
            _this.attachFilterSchedulesOnViewAllCheckboxInCalendarsList();

            _this.attachActionMoveNext(calendarInstance);
            _this.attachActionMovePrevious(calendarInstance);
            _this.attachActionToday(calendarInstance);

            _this.attachActionViewMonthly(calendarInstance);
            _this.attachActionViewWeekly(calendarInstance);
            _this.attachActionViewToday(calendarInstance);
        })
    }

    /**
     * @description will insert schedules into calendar
     */
    private insertSchedulesIntoCalendarInstance(calendarInstance: Calendar): Calendar
    {
        // todo: testing showing schedules - remove later
        calendarInstance.createSchedules([
            {
                id: "489273",
                title: 'Workout for 2020-04-05',
                isAllDay: false,
                start: '2021-02-15T11:30:00+09:00',
                end: '2021-02-15T12:00:00+09:00',
                goingDuration: 30,
                comingDuration: 30,
                color: '#ffffff',
                isVisible: true,
                bgColor: '#69BB2D',
                dragBgColor: '#69BB2D',
                borderColor: '#69BB2D',
                calendarId: '1',
                category: 'time',
                dueDateClass: '',
                customStyle: 'cursor: default;',
                isPending: false,
                isFocused: false,
                isReadOnly: false,
                isPrivate: false,
                location: '',
                attendees: [],
                recurrenceRule: '',
                state: ''
            }
        ]);

        return calendarInstance;
    }

    /**
     * @description will apply logic for events built into the Calendar itself
     */
    private applyBuiltInEventsToCalendarInstance(calendarInstance: Calendar): Calendar
    {
        let _this = this;
        calendarInstance.on({
            /**
             * @description handles the case when the schedule is create upon click
             */
            beforeCreateSchedule: (event) => {
                _this.saveNewSchedule(event, calendarInstance);
            },
            /**
             * @description handles updating the calendar like for example via drag n drop
             */
            beforeUpdateSchedule: (event) => {
                calendarInstance.updateSchedule(event.schedule.id, event.schedule.calendarId, event.changes);
            },
            /**
             * @description handles removal of schedule
             */
            beforeDeleteSchedule: (event) => {
                calendarInstance.deleteSchedule(event.schedule.id, event.schedule.calendarId);
            }
        });

        return calendarInstance;
    }

    /**
     * @description creates calendar instance for dom element
     */
    private createCalendarInstance(domElement: HTMLElement): Calendar
    {
        return new TuiCalendar(domElement, {
            useCreationPopup: true,
            useDetailPopup: true,
            taskView: true,
            scheduleView: true,
            defaultView: "week",
            week: {
                startDayOfWeek: 1
            },
            month: {
                startDayOfWeek: 1
            },
        });
    }

    /**
     * @description will insert all iCalendar instances int Calendar instance
     */
    private insertICalendarsIntoCalendarInstance(calendarInstance: Calendar): Calendar
    {
        // todo: testing showing calendars - remove later
        let allCalendars = [
            {
                id: '1', // todo: this will be entity id from backend
                name: 'My Calendar',
                color: '#ffffff',
                bgColor: '#9e5fff',
                dragBgColor: '#9e5fff',
                borderColor: '#9e5fff'
            },
            {
                id: '2',
                name: 'My Calendar 2',
                color: '#ffffff',
                bgColor: '#ff5f5f',
                dragBgColor: '#ff5f5f',
                borderColor: '#ff5f5f'
            }
        ];

        calendarInstance.setCalendars(allCalendars)
        return calendarInstance;
    }

    /**
     * Will create new schedule in Calendar
     * @param scheduleData
     * @param calendarInstance
     * @private
     */
    private saveNewSchedule(scheduleData: ISchedule, calendarInstance: Calendar): void
    {
        var calendar = this.findCalendar(scheduleData.calendarId, calendarInstance);

        // todo: handle with promises

        var schedule = {
            id: '1', // todo -- will be fetched from backend as entity id
            title: scheduleData.title,
            // isAllDay: scheduleData.isAllDay,
            start: scheduleData.start,
            end: scheduleData.end,
            category: 'time',
            // category: scheduleData.isAllDay ? 'allday' : 'time',
            // dueDateClass: '',
            calendarId: calendar.id,
            color: calendar.color,
            bgColor: calendar.bgColor,
            dragBgColor: calendar.bgColor,
            borderColor: calendar.borderColor,
            location: scheduleData.location,
            // raw: {
            //     class: scheduleData.raw['class']
            // },
            // state: scheduleData.state
        };
        // if (calendar) {
        //     schedule.calendarId = calendar.id;
        //     schedule.color = calendar.color;
        //     schedule.bgColor = calendar.bgColor;
        //     schedule.borderColor = calendar.borderColor;
        // }

        calendarInstance.createSchedules([schedule]);
    }

    /**
     * @description will search for defined ICalendar with given id in given Calendar instance
     *
     * @param searchedCalendarId
     * @param calendarInstance
     * @private
     */
    private findCalendar(searchedCalendarId: string, calendarInstance: Calendar): ICalendarInfo | null
    {
        let allCalendarsInInstance = this.getAllCalendarsFromCalendarInstance(calendarInstance);
        let foundCalendar          = null;

        allCalendarsInInstance.forEach(function(calendar) {
            if (calendar.id === searchedCalendarId) {
                foundCalendar = calendar;
                return;
            }
        });

        return foundCalendar;
    }

    /**
     * @description will handle adding calendars to the dom element thus displays them in the GUI
     */
    private displayAndHandleCalendarsList(calendarInstance: Calendar): void
    {
        let allCalendarsInInstance = this.getAllCalendarsFromCalendarInstance(calendarInstance);
        let $calendarListWrapper   = $(this.CALENDAR_LIST_SELECTOR);
        let _this                  = this;

        for( let iCalendar of allCalendarsInInstance ){
            let $inputCheckbox           = $('<INPUT>');
            let $spanColorWrapper        = $('<SPAN>');
            let $spanCalendarNameWrapper = $('<SPAN>');
            let $divMainWrapper          = $('<DIV>');
            let $labelMainWrapper        = $('<LABEL>');

            $divMainWrapper.addClass('lnb-calendars-item');

            $inputCheckbox
                .attr('type', 'checkbox')
                .attr('checked', 'true')
                .addClass('tui-full-calendar-checkbox-round')
                .val(iCalendar.id);

            $spanColorWrapper.css({
                "border-color"     : iCalendar.borderColor,
                "background-color" : iCalendar.borderColor,
            })

            $spanCalendarNameWrapper.html(iCalendar.name);

            $labelMainWrapper.append([$inputCheckbox, $spanColorWrapper, $spanCalendarNameWrapper])
            $divMainWrapper.append($labelMainWrapper);

            $calendarListWrapper.append($divMainWrapper);
            _this.attachFilterSchedulesOnCalendarChangeInCalendarList($inputCheckbox, $spanColorWrapper, calendarInstance, iCalendar);
        }

    }

    /**
     * @description will return array of ICalendars defined in Calendar instance
     */
    private getAllCalendarsFromCalendarInstance(calendarInstance: Calendar): Array<ICalendarInfo>
    {
        //@ts-ignore
        return calendarInstance._controller.calendars;
    }

    /**
     * @description will handle creating new schedule when clicking on the `new schedule` button
     */
    private createNewScheduleOnNewScheduleClick(calendarInstance: Calendar)
    {
        let $newScheduleButton = $(this.CALENDAR_NEW_SCHEDULE_BUTTON);
        $newScheduleButton.on('click', () => {
            calendarInstance.openCreationPopup({
                start : moment().toDate(),
                end   : moment().add(1, "hours").toDate(),
            });
        })
    }

    /**
     * @description handle clicking on the `next` button
     */
    private attachActionMoveNext(calendarInstance: Calendar)
    {
        let $elementToHandle = $(`[${this.DATA_ATTRIBUTE_ACTION_MOVE}^="${this.ACTION_MOVE_NEXT}"]`);
        $elementToHandle.on('click', () => {
            calendarInstance.next();
        })
    }

    /**
     * @description handle clicking on the `previous` button
     */
    private attachActionMovePrevious(calendarInstance: Calendar)
    {
        let $elementToHandle = $(`[${this.DATA_ATTRIBUTE_ACTION_MOVE}^="${this.ACTION_MOVE_PREVIOUS}"]`);
        $elementToHandle.on('click', () => {
            calendarInstance.prev();
        })
    }

    /**
     * @description handle clicking on the `today` button
     */
    private attachActionToday(calendarInstance: Calendar)
    {
        let $elementToHandle = $(`[${this.DATA_ATTRIBUTE_ACTION_MOVE}^="${this.ACTION_MOVE_TODAY}"]`);
        $elementToHandle.on('click', () => {
            calendarInstance.today();
        })
    }

    /**
     * @description will attach filtering logic when clicking on the calendar checkbox on the calendar list
     */
    private attachFilterSchedulesOnCalendarChangeInCalendarList(
        $inputCheckbox    : JQuery<HTMLElement>,
        $spanColorWrapper : JQuery<HTMLElement>,
        calendarInstance  : Calendar,
        iCalendar         : ICalendarInfo
    ): void
    {
        let _this = this;
        $inputCheckbox.on('change', (event) => {
            let $clickedElement = $(event.currentTarget);
            let isChecked       = $clickedElement.prop('checked');

            calendarInstance.toggleSchedules(iCalendar.id, !isChecked, false);
            calendarInstance.render(true);

            let $viewAllCalendarsCheckbox = $(_this.VIEW_ALL_CALENDARS_SELECTOR);
            let $allCalendarsInList       = $(_this.CALENDAR_IN_LIST).find(_this.CALENDAR_IN_LIST_INPUT_ROUND);
            let allAreCalendarsChecked    = true;

            $allCalendarsInList.each( (index, element) => {

                let $calendarInList = $(element);
                if(!$calendarInList.prop('checked')){
                    allAreCalendarsChecked = false;
                    return;
                }
            })

            if( !allAreCalendarsChecked ){
                $viewAllCalendarsCheckbox.prop('checked', false);
            }else{
                $viewAllCalendarsCheckbox.prop('checked', true);
            }

            $spanColorWrapper.css({
                'background-color': (isChecked ? $spanColorWrapper.css('border-color') : 'transparent')
            })
        })
    }

    /**
     * @description handles the action where user clicks on the `view all checkbox`
     */
    private attachFilterSchedulesOnViewAllCheckboxInCalendarsList(): void
    {
        let $viewAllCalendarsCheckbox = $(this.VIEW_ALL_CALENDARS_SELECTOR);
        let _this                     = this;

        $viewAllCalendarsCheckbox.on('click', () => {
            let $allCalendarsInList = $(_this.CALENDAR_IN_LIST).find(_this.CALENDAR_IN_LIST_INPUT_ROUND);

            if( $viewAllCalendarsCheckbox.prop('checked') ){
                $allCalendarsInList.prop('checked', true);
            }else{
                $allCalendarsInList.prop('checked', false);
            }

            $allCalendarsInList.trigger('change');
        })
    }

    /**
     * @description will toggle the schedules visibility for the state of instance
     * @private
     */
    private toggleSchedulesVisibilityForInstance()
    {
        //todo foreach list elements
    }

    /**
     * @description will handle clicking on the action (toggle view monthly)
     */
    private attachActionViewMonthly(calendarInstance: Calendar): void
    {
        let $actionElement = $(`[${this.DATA_ATTRIBUTE_ACTION_MOVE}='${this.ACTION_VIEW_MONTHLY}'`);
        $actionElement.on('click', () => {
            calendarInstance.changeView('month');
        })
    }

    /**
     * @description will handle clicking on the action (toggle view weekly)
     */
    private attachActionViewWeekly(calendarInstance: Calendar): void
    {
        let $actionElement = $(`[${this.DATA_ATTRIBUTE_ACTION_MOVE}='${this.ACTION_VIEW_WEEKLY}'`);
        $actionElement.on('click', () => {
            calendarInstance.changeView('week');
        })
    }

    /**
     * @description will handle clicking on the action (toggle view daily)
     */
    private attachActionViewToday(calendarInstance: Calendar): void
    {
        let $actionElement = $(`[${this.DATA_ATTRIBUTE_ACTION_MOVE}='${this.ACTION_VIEW_DAILY}'`);
        $actionElement.on('click', () => {
            calendarInstance.changeView('day');
        })
    }

}