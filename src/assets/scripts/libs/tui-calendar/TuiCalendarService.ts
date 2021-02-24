import MonthViewTemplate from "./views-templates/MonthViewTemplate";
import * as moment from 'moment';

var TuiCalendar = require('tui-calendar');

import Calendar, {ICalendarInfo, ISchedule} from "tui-calendar";
import axios                                from "axios";

import DomElements         from "../../core/utils/DomElements";
import ScheduleCalendarDto from "../../DTO/modules/schedules/ScheduleCalendarDto";
import Loader              from "../loader/Loader";
import BootstrapNotify     from "../bootstrap-notify/BootstrapNotify";
import AjaxResponseDto     from "../../DTO/AjaxResponseDto";
import ScheduleDto         from "../../DTO/modules/schedules/ScheduleDto";

/**
 * Todo:
 *  - add logic to create calendars on fly + update them
 *  - add some search logic to easily find by subject, with `goto`, `edit` (creation popup) , `remove` (creation popup)
 *  - the time picker in tui popup is not working
 *  - also add action to update schedule when moving / updating via modal
 *  - add settings to change color/name of calendar
 *  - test creating schedule via `new schedule`
 */

/**
 * @description handles the calendar logic, keep in mind that the logic assume that there will
 *              be only one TuiCalendar instance on the page, otherwise the logic needs to be adjusted
 *
 *              The logic inside this action is different that in other actions where everything relies on the
 *              DataLoaders in front entities updates etc. Normally whenever something is added / changed then whole
 *              page is reloaded but in this case there is fully interactive calendar with built in actions handling
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

    private bootstrapNotify = new BootstrapNotify();

    /**
     * @description this value is used to get unique schedule id upon inserting in GUI,
     *              this is NOT equal to entity id, the Calendar requires that schedule has unique id
     */
    private lastUsedScheduleId: number = 0;

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
        Loader.showLoader(); // todo: fix it, wont work in this case - why?
        {
            let _this = this;

            //@ts-ignore
            $allElementsToHandle.each( async (index, elementToHandle) => {
                let calendarInstance = _this.createCalendarInstance(elementToHandle);

                Loader.showLoader(); // todo: not working (!?)
                {
                    calendarInstance = await _this.insertICalendarsIntoCalendarInstance(calendarInstance);
                    calendarInstance = await _this.insertSchedulesIntoCalendarInstance(calendarInstance);
                }
                Loader.hideLoader();

                _this.setLastUsedScheduleId(calendarInstance);

                _this.applyBuiltInEventsToCalendarInstance(calendarInstance);
                _this.displayAndHandleCalendarsList(calendarInstance);
                _this.createNewScheduleOnNewScheduleClick(calendarInstance);
                _this.attachFilterSchedulesOnViewAllCheckboxInCalendarsList();
                _this.modifyScheduleCreationPopup();

                _this.attachActionMoveNext(calendarInstance);
                _this.attachActionMovePrevious(calendarInstance);
                _this.attachActionToday(calendarInstance);

                _this.attachActionViewMonthly(calendarInstance);
                _this.attachActionViewWeekly(calendarInstance);
                _this.attachActionViewToday(calendarInstance);
            });
        }
        Loader.hideLoader();
    }

    /**
     * @description will insert schedules into calendar
     */
    private insertSchedulesIntoCalendarInstance(calendarInstance: Calendar): Promise<Calendar>
    {
        let _this = this;

        return axios.get('/modules/schedules/get-all-not-deleted').then( (response) => {
            let ajaxResponseDto = AjaxResponseDto.fromArray(response.data);
            if( !ajaxResponseDto.isDataBagSet() ){
                throw {
                    "message"  : "The data bag was not filled in the backed!",
                    "response" : response,
                }
            }

            if(!ajaxResponseDto.success){
                _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                return;
            }

            let calendarSchedules: Array<ISchedule> = [];
            for(let scheduleJson of ajaxResponseDto.dataBag.schedulesDtoJsons){

                let scheduleDto = ScheduleDto.fromJson(scheduleJson);
                let calendarSchedule: ISchedule = {
                    id    : scheduleDto.id,
                    title : scheduleDto.title,
                    start : scheduleDto.start,
                    end   : scheduleDto.end,

                    color       : '#ffffff',
                    bgColor     : '#69BB2D',
                    dragBgColor : '#69BB2D',
                    borderColor : '#69BB2D',

                    calendarId : scheduleDto.calendarId,
                    category   : scheduleDto.category,
                    location   : scheduleDto.location,
                }
                calendarSchedules.push(calendarSchedule)
            }
            calendarInstance.createSchedules(calendarSchedules);
            return calendarInstance;
        } )

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
            useCreationPopup : true,
            useDetailPopup   : true,
            taskView         : false,
            scheduleView     : ['time'],
            defaultView      : "week",
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
    private insertICalendarsIntoCalendarInstance(calendarInstance: Calendar): Promise<Calendar>
    {
        let _this = this;

        return axios.get("/modules/schedules/calendar/get-all-non-deleted-calendars-data").then( (response) => {

            let ajaxResponseDto = AjaxResponseDto.fromArray(response.data);
            if( !ajaxResponseDto.isDataBagSet() ){
                throw {
                    "message"  : "The data bag was not filled in the backed!",
                    "response" : response,
                }
            }

            if(!ajaxResponseDto.success){
                _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                return;
            }

            let calendarsDataJsons = ajaxResponseDto.dataBag.calendarsDataJsons;
            let arrayOfCalendars   = [];
            for(let calendarDataJson of calendarsDataJsons){
                let dto = ScheduleCalendarDto.fromJson(calendarDataJson);
                let iCalendar = {
                    id          : dto.id.toString(),
                    name        : dto.name,
                    color       : dto.color,
                    bgColor     : dto.backgroundColor,
                    dragBgColor : dto.dragBackgroundColor,
                    borderColor : dto.borderColor,
                }

                arrayOfCalendars.push(iCalendar);
            }

            calendarInstance.setCalendars(arrayOfCalendars);
            return calendarInstance;
        })
    }

    /**
     * Will create new schedule in Calendar
     * @param scheduleData
     * @param calendarInstance
     * @private
     */
    private saveNewSchedule(scheduleData: ISchedule, calendarInstance: Calendar): void
    {
        let calendar = this.findCalendar(scheduleData.calendarId, calendarInstance);
        let _this    = this;

        //@ts-ignore
        let startDate = scheduleData.start.toDate();
        //@ts-ignore
        let endDate   = scheduleData.end.toDate();

        let dataBag  = {
            title       : scheduleData.title,
            isAllDay    : scheduleData.isAllDay,
            start       : startDate,
            end         : endDate,
            category    : scheduleData.isAllDay ? 'allday' : 'time',
            location    : scheduleData.location,
            calendarId  : calendar.id
        };

        Loader.showLoader();
        axios.post('/modules/schedules/save-new-schedule', dataBag)
            .then( (response) => {
                Loader.hideLoader();

                let ajaxResponseDto = AjaxResponseDto.fromArray(response.data);
                if(!ajaxResponseDto.success){
                    _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                    return;
                }

                let usedScheduleId = ++_this.lastUsedScheduleId;

                var schedule = {
                    id          : usedScheduleId.toString(),
                    title       : scheduleData.title,
                    isAllDay    : scheduleData.isAllDay,
                    start       : scheduleData.start,
                    end         : scheduleData.end,
                    category    : scheduleData.isAllDay ? 'allday' : 'time',
                    location    : scheduleData.location,

                    calendarId  : calendar.id,
                    color       : calendar.color,
                    bgColor     : calendar.bgColor,
                    dragBgColor : calendar.bgColor,
                    borderColor : calendar.borderColor,
                };

                calendarInstance.createSchedules([schedule]);
            })
            .catch( (reason) => {
                Loader.hideLoader();
                throw {
                    "message" : "Could not handle saving new schedule in database",
                    "reason"  : reason,
                }
            })

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

    /**
     * @description will modify the creation popup by watch for parent dom element and on the moment that children are
     *              added it will attempt to find the popup elements and modify them
     */
    private modifyScheduleCreationPopup(): void
    {
        let parentWrapperObserver = new MutationObserver( (mutations) => {
            $('.tui-full-calendar-section-allday').addClass('d-none');
            $('.tui-full-calendar-section-state').addClass('d-none');
        })

        let htmlDomElement = document.querySelector('.tui-full-calendar-floating-layer');
        parentWrapperObserver.observe(htmlDomElement, {
            childList: true,
        })
    }

    /**
     * @description will set last used schedule id
     */
    private setLastUsedScheduleId(calendarInstance: Calendar): void
    {
        //@ts-ignore
        let allSchedules = calendarInstance._controller.schedules.items;
        if( 0 === allSchedules.length ){
            return;
        }

        for(let scheduleIndex in allSchedules){
            let schedule   = allSchedules[scheduleIndex];
            let scheduleId = parseInt(schedule.id)

            if(this.lastUsedScheduleId < scheduleId){
                this.lastUsedScheduleId = scheduleId;
            }
        }
    }

}