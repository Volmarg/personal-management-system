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
import StringUtils from "../../core/utils/StringUtils";

/**
 * Todo:
 *  - add some search logic to easily find by subject, with `goto`, `edit` (creation popup) , `remove` (creation popup)
 *  - the time picker in tui popup is not working
 *  - handle showing schedules in widget + bell,
 *  - handle information field + adjust migration after that
 *  - on the very end add task for later to integrate reminder + NPL with them
 *    - add also info to handle adding reminders to the calendar, so far this will work like before
 */

/**
 * @description handles the calendar logic, keep in mind that the logic assume that there will
 *              be only one TuiCalendar instance on the page, otherwise the logic needs to be adjusted
 *
 *              The logic inside this action is different that in other actions where everything relies on the
 *              DataLoaders in front entities updates etc. Normally whenever something is added / changed then whole
 *              page is reloaded but in this case there is fully interactive calendar with built in actions handling
 *
 *              Info: as long as property of schedule is provided, it seems like it's shown in GUI
 *                    the only issue is that update/create does not have the additional fields
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

    private readonly DATA_ATTRIBUTE_CALENDAR_ID               = 'data-calendar-id';
    private readonly DATA_ATTRIBUTE_CALENDAR_NAME             = 'data-calendar-name';
    private readonly DATA_ATTRIBUTE_CALENDAR_COLOR            = 'data-calendar-color';
    private readonly DATA_ATTRIBUTE_CALENDAR_DRAG_COLOR       = 'data-calendar-drag-color';
    private readonly DATA_ATTRIBUTE_CALENDAR_BORDER_COLOR     = 'data-calendar-border-color';
    private readonly DATA_ATTRIBUTE_CALENDAR_BACKGROUND_COLOR = 'data-calendar-background-color';

    private readonly ACTION_MOVE_TODAY              = 'move-today';
    private readonly ACTION_MOVE_PREVIOUS           = 'move-previous';
    private readonly ACTION_MOVE_NEXT               = 'move-next';

    private readonly ACTION_VIEW_DAILY              = 'toggle-view-daily';
    private readonly ACTION_VIEW_WEEKLY             = 'toggle-view-weekly';
    private readonly ACTION_VIEW_MONTHLY            = 'toggle-view-monthly';

    private readonly CALENDAR_NEW_SCHEDULE_BUTTON   = '#btn-new-schedule';
    private readonly VIEW_ALL_CALENDARS_SELECTOR    = '.view-all-calendars';
    private readonly CALENDAR_IN_LIST               = '.lnb-calendars-item';
    private readonly CALENDAR_IN_LIST_INPUT_ROUND   = '.tui-full-calendar-checkbox-round';
    private readonly POPUP_CONTAINER_SELECTOR       = '.tui-full-calendar-popup-container';

    private readonly POPUP_CONTAINER_SECTION_TITLE_SELECTOR = '.tui-full-calendar-section-title';

    private readonly SCHEDULE_DEFAULT_TEXT_COLOR = '#ffffff';

    private readonly POPUP_CUSTOM_FIELD_NAME_BODY = "body";

    private bootstrapNotify = new BootstrapNotify();

    private lastClickedScheduleId ?: string  = null;

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
                _this.handleCalendarList(calendarInstance);
                _this.createNewScheduleOnNewScheduleClick(calendarInstance);
                _this.attachFilterSchedulesOnViewAllCheckboxInCalendarsList();
                _this.modifyScheduleCreationAndDetailPopup(calendarInstance);

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
                    body  : scheduleDto.body,
                    start : scheduleDto.start,
                    end   : scheduleDto.end,

                    color       : _this.SCHEDULE_DEFAULT_TEXT_COLOR, // this is hardcoded on purpose
                    bgColor     : '#' + scheduleDto.calendarColor,
                    dragBgColor : '#' + scheduleDto.calendarColor,
                    borderColor : '#' + scheduleDto.calendarColor,

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
                let ajaxCallUrl = '/modules/schedules/save-schedule';
                _this.saveSchedule(event, ajaxCallUrl, calendarInstance);
                this.lastClickedScheduleId = null;
            },
            /**
             * @description handles updating the calendar like for example via drag n drop
             *              some of the attributes are custom made, therefore standard update
             *              is not working on them
             */
            beforeUpdateSchedule: (event) => {
                let $creationPopupContainer = $(this.POPUP_CONTAINER_SELECTOR);
                let ajaxCallUrl             = `/modules/schedules/save-schedule/${event.schedule.id}`;
                let changes                 = event.changes;

                // no changes in standard properties
                if(null === changes){
                    changes = {};
                }

                // handle additional properties
                changes.body        = $creationPopupContainer.find('#body').val() as string;
                let updatedSchedule = _this.buildUpdatedSchedule(event.schedule, changes);

                _this.saveSchedule(updatedSchedule, ajaxCallUrl, calendarInstance, false);
                calendarInstance.updateSchedule(event.schedule.id, event.schedule.calendarId, changes);
                this.lastClickedScheduleId = null;
            },
            /**
             * @description handles removal of schedule
             */
            beforeDeleteSchedule: (event) => {
                _this.deleteSchedule(event.schedule.id);
                calendarInstance.deleteSchedule(event.schedule.id, event.schedule.calendarId);
                this.lastClickedScheduleId = null;
            },
            /**
             * @description handles clicking on the schedule
             */
            clickSchedule: (event) => {
                this.lastClickedScheduleId = event.schedule.id;
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
            scheduleView     : true,
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
    private insertICalendarsIntoCalendarInstance(calendarInstance: Calendar): Calendar
    {
        let _this               = this;
        let $allCalendarsInList = $(this.CALENDAR_IN_LIST);

        let arrayOfCalendars = [];
        $allCalendarsInList.each( (index, element) => {
            let $element = $(element);

            let id                  = $element.attr(_this.DATA_ATTRIBUTE_CALENDAR_ID);
            let name                = $element.attr(_this.DATA_ATTRIBUTE_CALENDAR_NAME);
            let color               = $element.attr(_this.DATA_ATTRIBUTE_CALENDAR_COLOR);
            let backgroundColor     = $element.attr(_this.DATA_ATTRIBUTE_CALENDAR_DRAG_COLOR);
            let dragBackgroundColor = $element.attr(_this.DATA_ATTRIBUTE_CALENDAR_BORDER_COLOR);
            let borderColor         = $element.attr(_this.DATA_ATTRIBUTE_CALENDAR_BACKGROUND_COLOR);

            let iCalendar = {
                id          : id,
                name        : name,
                color       : '#' + color,
                bgColor     : '#' + backgroundColor,
                dragBgColor : '#' + dragBackgroundColor,
                borderColor : '#' + borderColor,
            }

            arrayOfCalendars.push(iCalendar);
        })

        calendarInstance.setCalendars(arrayOfCalendars);
        return calendarInstance;
    }

    /**
     * @description Will create new schedule in Calendar
     *
     * @param scheduleData
     * @param ajaxCallUrl
     * @param calendarInstance
     * @param createInstance
     * @private
     */
    private saveSchedule(scheduleData: ISchedule, ajaxCallUrl: string, calendarInstance: Calendar, createInstance: boolean = true): void
    {
        let $creationPopupContainer = $(this.POPUP_CONTAINER_SELECTOR);
        let calendar                = this.findCalendar(scheduleData.calendarId, calendarInstance);
        let _this                   = this;

        //@ts-ignore
        let startDate = scheduleData.start.toDate();
        //@ts-ignore
        let endDate   = scheduleData.end.toDate();

        let scheduleBody = $creationPopupContainer.find('#body').val() as string;

        let dataBag  = {
            title       : scheduleData.title,
            body        : scheduleBody,
            isAllDay    : scheduleData.isAllDay,
            start       : startDate,
            end         : endDate,
            category    : scheduleData.isAllDay ? 'allday' : 'time',
            location    : scheduleData.location,
            calendarId  : calendar.id
        };

        Loader.showLoader();
        axios.post(ajaxCallUrl, dataBag)
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
                    body        : scheduleBody,
                    isAllDay    : scheduleData.isAllDay,
                    start       : scheduleData.start,
                    end         : scheduleData.end,
                    category    : scheduleData.isAllDay ? 'allday' : 'time',
                    location    : scheduleData.location,

                    calendarId  : calendar.id,
                    color       : _this.SCHEDULE_DEFAULT_TEXT_COLOR,
                    bgColor     : calendar.bgColor,
                    dragBgColor : calendar.bgColor,
                    borderColor : calendar.borderColor,
                };

                if(createInstance){
                    calendarInstance.createSchedules([schedule]);
                }
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
     * @description will search for defined ISchedule with given id in the calendar instance
     *
     * @param scheduleId
     * @param calendarInstance
     */
    private findSchedule(scheduleId: string, calendarInstance: Calendar): ISchedule | null
    {
        let allSchedulesInInstance = this.getAllSchedulesFromCalendarInstance(calendarInstance);
        let foundSchedule          = null;
        let schedulesArray         = Object.values(allSchedulesInInstance);

        for(let schedule of schedulesArray){
            if (schedule.id === scheduleId) {
                foundSchedule = schedule;
            }
        }

        return foundSchedule;
    }

    /**
     * @description will handle adding calendars to the dom element thus displays them in the GUI
     */
    private handleCalendarList(calendarInstance: Calendar): void
    {
        let _this               = this;
        let $allCalendarsInList = $(this.CALENDAR_IN_LIST);

        $allCalendarsInList.each( (index, element) => {
            let $calendarListElement = $(element);
            _this.attachFilterSchedulesOnCalendarChangeInCalendarList(calendarInstance, $calendarListElement);
        })
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
     * @description will return array of ISchedules defined in Calendar instance
     */
    private getAllSchedulesFromCalendarInstance(calendarInstance: Calendar): Array<ISchedule>
    {
        //@ts-ignore
        return calendarInstance._controller.schedules.items;
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
        calendarInstance      : Calendar,
        $calendarListElement  : JQuery<HTMLElement>,
    ): void
    {
        let _this           = this;
        let $inputCheckbox  = $calendarListElement.find('input');
        let $colorWrapper   = $calendarListElement.find('.color-wrapper');

        let calendarId      = $calendarListElement.attr(this.DATA_ATTRIBUTE_CALENDAR_ID);
        let backgroundColor = $calendarListElement.attr(this.DATA_ATTRIBUTE_CALENDAR_BACKGROUND_COLOR);

        $inputCheckbox.on('change', (event) => {
            let $clickedElement = $(event.currentTarget);
            let isChecked       = $clickedElement.prop('checked');

            calendarInstance.toggleSchedules(calendarId, !isChecked, false);
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

            $colorWrapper.css({
                'background-color': (isChecked ? '#' + backgroundColor : 'transparent')
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
    private modifyScheduleCreationAndDetailPopup(calendarInstance: Calendar): void
    {
        let parentWrapperObserver = new MutationObserver( (mutations) => {
            // remove fields
            $('.tui-full-calendar-section-allday').addClass('d-none');
            $('.tui-full-calendar-section-state').addClass('d-none');

            // add/prefill fields
            let lastClickedSchedule = this.findSchedule(this.lastClickedScheduleId, calendarInstance);
            if(null === lastClickedSchedule){
                throw {
                    "message"               : "Could not find the schedule for lastClickedScheduleId",
                    "lastClickedScheduleId" : this.lastClickedScheduleId,
                }
            }

            // BODY
            let containerTitleSection = $(this.POPUP_CONTAINER_SECTION_TITLE_SELECTOR);
            let valueForBodyField     = lastClickedSchedule[this.POPUP_CUSTOM_FIELD_NAME_BODY];
            let $bodySection          = this.buildInputFieldForPopup(this.POPUP_CUSTOM_FIELD_NAME_BODY, 'fas fa-pen', valueForBodyField);

            containerTitleSection.parent().after($bodySection);
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

    /**
     * The schedule update event consist of schedule and object of changes, this method combines both and outputs
     * the schedule with applied changes on it
     *
     * @param schedule
     * @param changes
     */
    private buildUpdatedSchedule(schedule: ISchedule, changes): ISchedule
    {
        let updatedSchedule : ISchedule = {
            id          : ("undefined" === typeof changes.id          ) ? schedule.id          : changes.id,
            title       : ("undefined" === typeof changes.title       ) ? schedule.title       : changes.title,
            body        : ("undefined" === typeof changes.body        ) ? schedule.body        : changes.body,
            isAllDay    : ("undefined" === typeof changes.isAllDay    ) ? schedule.isAllDay    : changes.isAllDay,
            start       : ("undefined" === typeof changes.start       ) ? schedule.start       : changes.start,
            end         : ("undefined" === typeof changes.end         ) ? schedule.end         : changes.end,
            category    : ("undefined" === typeof changes.category    ) ? schedule.category    : changes.category,
            location    : ("undefined" === typeof changes.location    ) ? schedule.location    : changes.location,
            calendarId  : ("undefined" === typeof changes.calendarId  ) ? schedule.calendarId  : changes.calendarId,
            color       : ("undefined" === typeof changes.color       ) ? schedule.color       : changes.color,
            bgColor     : ("undefined" === typeof changes.bgColor     ) ? schedule.bgColor     : changes.bgColor,
            dragBgColor : ("undefined" === typeof changes.dragBgColor ) ? schedule.dragBgColor : changes.dragBgColor,
            borderColor : ("undefined" === typeof changes.borderColor ) ? schedule.borderColor : changes.borderColor,
        };

        return updatedSchedule;
    }

    /**
     * Will delete the schedule
     *
     * @param scheduleId
     * @private
     */
    private deleteSchedule(scheduleId: string): void
    {
        Loader.showLoader();
        let _this = this;
        axios.get(`/modules/schedules/delete/${scheduleId}`).then( (response) => {
            Loader.hideLoader();
            let ajaxResponseDto = AjaxResponseDto.fromArray(response.data);

            if(!ajaxResponseDto.success){
                _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                return;
            }
            _this.bootstrapNotify.showGreenNotification(ajaxResponseDto.message);
        });
    }

    /**
     * @description will build input field for popup, returns entire section with icon
     */
    private buildInputFieldForPopup(fieldName: string, fontawesomeIconClasses: string, dataToPrefill?: any): JQuery<HTMLElement>
    {
        let $divPopupSection     = $("<DIV>");
        let $divPopupSectionItem = $("<DIV>");
        let $spanWithIcon        = $("<SPAN>");
        let $fontawesomeIcon     = $('<I>');
        let $input               = $("<INPUT>");

        $divPopupSection.addClass('tui-full-calendar-popup-section');
        $divPopupSectionItem.addClass(`tui-full-calendar-popup-section-item tui-full-calendar-section-${fieldName} w-100`)
        $spanWithIcon.addClass('tui-full-calendar-icon');
        $fontawesomeIcon.addClass(fontawesomeIconClasses);
        $input
            .attr('id', fieldName)
            .addClass('tui-full-calendar-content w-90')
            .attr('placeholder', StringUtils.capitalizeFirstLetter(fieldName));

        if(null !== dataToPrefill){
            $input.val(dataToPrefill);
        }

        // combine all
        $spanWithIcon.append($fontawesomeIcon);
        $divPopupSectionItem.append([$spanWithIcon, $input]);
        $divPopupSection.append($divPopupSectionItem);

        return $divPopupSection;
    }
}