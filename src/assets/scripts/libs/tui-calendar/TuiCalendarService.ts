import * as moment from 'moment';

var TuiCalendar   = require('tui-calendar');
var TuiDatePicker = require('tui-date-picker');

import Calendar, {ICalendarInfo, ISchedule} from "tui-calendar";
import axios                                from "axios";

import DomElements     from "../../core/utils/DomElements";
import Loader          from "../loader/Loader";
import BootstrapNotify from "../bootstrap-notify/BootstrapNotify";
import AjaxResponseDto from "../../DTO/AjaxResponseDto";
import ScheduleDto     from "../../DTO/modules/schedules/ScheduleDto";
import StringUtils     from "../../core/utils/StringUtils";
import Dialog          from "../../core/ui/Dialogs/Dialog";
import ArrayUtils      from "../../core/utils/ArrayUtils";
import AutocompleteService from "../autocomplete/AutocompleteService";

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
 * @link https://nhn.github.i o/tui.calendar/latest/Calendar
 */
export default class TuiCalendarService
{
    private readonly DATA_ATTRIBUTE_IS_TUI_CALENDAR = 'data-is-tui-calendar';
    private readonly DATA_ATTRIBUTE_ACTION_MOVE     = 'data-action';

    private readonly DATA_ATTRIBUTE_CALENDAR_ID               = 'data-calendar-id'; // instance of the calendar on list
    private readonly DATA_ATTRIBUTE_CALENDAR_NAME             = 'data-calendar-name';
    private readonly DATA_ATTRIBUTE_CALENDAR_COLOR            = 'data-calendar-color';
    private readonly DATA_ATTRIBUTE_CALENDAR_DRAG_COLOR       = 'data-calendar-drag-color';
    private readonly DATA_ATTRIBUTE_CALENDAR_BORDER_COLOR     = 'data-calendar-border-color';
    private readonly DATA_ATTRIBUTE_CALENDAR_BACKGROUND_COLOR = 'data-calendar-background-color';
    private readonly DATA_SCHEDULE_ID                         = 'data-schedule-id'

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
    private readonly CALENDAR_MODAL_SELECTOR        = '#calendar-settings-modal';
    private readonly INPUT_REMINDER_SELECTOR        = ".reminder-calendar";
    private readonly DATE_RANGE                     = "#dateRange";

    private readonly POPUP_CONTAINER_SECTION_TITLE_SELECTOR = '.tui-full-calendar-section-title';
    private readonly POPUP_CONTAINER_SECTION_LOCATION       = '.tui-full-calendar-section-location';

    private readonly SCHEDULE_DEFAULT_TEXT_COLOR = '#ffffff';

    private readonly POPUP_CUSTOM_FIELD_NAME_BODY     = "body";
    private readonly POPUP_CUSTOM_FIELD_NAME_REMINDER = "reminder";

    private bootstrapNotify = new BootstrapNotify();

    private dialog = new Dialog();

    private lastClickedScheduleId ?: string  = null;
    private lastSearchedSchedule ?:ISchedule  = null;
    private lastHandledCalendarDomElement ?: HTMLElement = null;

    static readonly CALENDAR_VIEW_MONTH = "month";
    static readonly CALENDAR_VIEW_WEEK  = "week";
    static readonly CALENDAR_VIEW_DAY   = "day";

    /**
     * @description this value is used to get unique schedule id upon inserting in GUI,
     *              this is NOT equal to entity id, the Calendar requires that schedule has unique id
     */
    private lastUsedScheduleId: number = 0;

    /**
     * @description this value is used internally to keep track of the reminders added in popup as the datepicker
     *              instance must be applied on input element
     */
    private lastUsedReminderId: number = 0;

    /**
     * @description indicates if the opened popup is update popup
     *              possible states of popup are [UPDATE / CREATE], Tui-calendar does not support detecting these difference
     *              however popup has always title empty when creating new schedule in [CREATE] state, so this is used
     *              to detect the state.
     */
    private isUpdatePopup: boolean;

    /**
     * @description indicates current active view
     *              necessary to detect if the view has been switched
     */
    private activeView ?:string = null;

    /**
     * @description will initialize the calendar instance
     */
    public init(){
        let $allElementsToHandle = $("[" + this.DATA_ATTRIBUTE_IS_TUI_CALENDAR + "]");

        if( !DomElements.doElementsExists($allElementsToHandle) ){
            return;
        }

        this.handleAllCalendarDomElements($allElementsToHandle);
        this.handleCalendarSettingsModal();
    }

    /**
     * @description handles the entire Calendar initialization logic for all of the matching elements in dom
     */
    private handleAllCalendarDomElements($allElementsToHandle: JQuery<HTMLElement>)
    {
        let _this = this;

        //@ts-ignore
        $allElementsToHandle.each( async (index, elementToHandle) => {
            this.lastHandledCalendarDomElement = elementToHandle;

            let calendarInstance = _this.createCalendarInstance(elementToHandle);
            calendarInstance     = _this.insertICalendarsIntoCalendarInstance(calendarInstance);

            this.updateShownDate(calendarInstance);

            Loader.showSubLoader();
            {
                calendarInstance = await _this.insertSchedulesIntoCalendarInstance(calendarInstance);
            }
            Loader.hideSubLoader();

            _this.setLastUsedScheduleId(calendarInstance);

            _this.applyBuiltInEventsToCalendarInstance(calendarInstance);

            _this.attachLogicToSearchInput(calendarInstance);

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
                    recurrenceRule: scheduleDto.remindersDatesAsString(),
                }
                calendarSchedules.push(calendarSchedule)
            }
            calendarInstance.createSchedules(calendarSchedules);
            return calendarInstance;
        } )

    }

    /**
     * @description will apply logic for events built into the Calendar itself
     *              keep in mind that event if the logic for attaching/prefilling/reading custom elements in popup
     *              seems to be the same, it MUST be handled differently in beforeUpdate/beforeCreate - do not merge it!
     */
    private applyBuiltInEventsToCalendarInstance(calendarInstance: Calendar): Calendar
    {
        let _this = this;
        calendarInstance.on({
            /**
             * @description handles the case when the schedule is create upon click
             */
            beforeCreateSchedule: (schedule) => {
                let ajaxCallUrl = '/modules/schedules/save-schedule';

                let $creationPopupContainer = $(this.POPUP_CONTAINER_SELECTOR);
                if( DomElements.doElementsExists($creationPopupContainer) ){
                    let allRemindersArray   = this.getAllRemindersArrayFromCreationPopup();

                    schedule.body           = $creationPopupContainer.find('#body').val() as string;
                    schedule.recurrenceRule = allRemindersArray.join(",");
                }

                _this.saveSchedule(schedule, ajaxCallUrl, calendarInstance);
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

                // @ts-ignore, handle additional properties, creation popup might not exist, example -> drag schedule
                changes.body = event.schedule.body;
                if( DomElements.doElementsExists($creationPopupContainer) ) {
                    changes.body = $creationPopupContainer.find('#body').val() as string;

                    let allRemindersArray  = this.getAllRemindersArrayFromCreationPopup();
                    changes.recurrenceRule = allRemindersArray.join(",");
                }

                /**
                 * @description this must be done on this step as calendar is using internally the `changes` to update elements,
                 *              these are also used later to update data in DB, there is also bug in the plugin
                 *              itself, meaning that when calendar gets updated, the drag color is not updated until calendar
                 *              or schedule will be fully reloaded, but this is not implemented in here
                 */
                if( !StringUtils.isEmptyString(event.changes.calendarId) ){
                    let calendar        = this.findCalendar(event.changes.calendarId, calendarInstance);
                    changes.color       = this.SCHEDULE_DEFAULT_TEXT_COLOR;
                    changes.bgColor     = calendar.bgColor;
                    changes.dragBgColor = calendar.dragBgColor;
                    changes.borderColor = calendar.borderColor;
                }

                let updatedSchedule = _this.buildUpdatedSchedule(event.schedule, changes);

                calendarInstance.deleteSchedule(event.schedule.id, event.schedule.calendarId);
                _this.saveSchedule(updatedSchedule, ajaxCallUrl, calendarInstance);
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
            },
            /**
             * @description calendar is rendered, triggers also upon changing view or clicking next / previous etc.
             *              - but keep in mind that that this is triggered for every single schedule visible
             *              in the grid so for 50 this gets called 50 times
             */
            afterRenderSchedule: (event) => {

                // set initial view value
                if( StringUtils.isEmptyString(this.activeView) ){
                    this.activeView = calendarInstance.getViewName();
                }

                if(
                        null             !== this.lastSearchedSchedule
                    &&  event.schedule.id == this.lastSearchedSchedule.id
                ){
                    // there is no need to unmark since on each search the grid is being loaded anew
                    this.markScheduleInCalendar(this.lastSearchedSchedule);
                    this.scrollToScheduleInCalendar(this.lastSearchedSchedule);
                }

                if( this.activeView !== calendarInstance.getViewName() ){
                    this.activeView = calendarInstance.getViewName(); // to prevent doing this for each schedule - see description
                    this.modifyScheduleCreationAndDetailPopup(calendarInstance);
                }
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
            defaultView      : TuiCalendarService.CALENDAR_VIEW_WEEK,
            week: {
                startDayOfWeek: 1
            },
            month: {
                startDayOfWeek: 1
            },
            template: {
                monthDayname: function(dayname) {
                    return '<span class="calendar-week-dayname-name">' + dayname.label + '</span>';
                },
                /**
                 * @description time visible on left side of calendar
                 */
                timegridDisplayPrimayTime: function(time) {
                    return time.hour + ':00';
                },
                /**
                 * @description date/time visible in popup
                 */
                popupDetailDate: function(isAllDay, start, end) {
                    var isSameDate = moment(start._date.toString()).isSame(end._date.toString());
                    var endFormat  = (isSameDate ? '' : 'YYYY.MM.DD ') + 'HH:mm';

                    if (isAllDay) {
                        return moment(start._date.toString()).format('YYYY.MM.DD') + (isSameDate ? '' : ' - ' + moment(end._date.toString()).format('YYYY.MM.DD'));
                    }

                    return (moment(start._date.toString()).format('YYYY.MM.DD HH:mm') + ' - ' + moment(end._date.toString()).format(endFormat));
                }
            }
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
        let calendar = this.findCalendar(scheduleData.calendarId, calendarInstance);
        let _this    = this;

        //@ts-ignore
        let startDate = scheduleData.start.toDate();
        //@ts-ignore
        let endDate   = scheduleData.end.toDate();

        let dataBag  = {
            title       : scheduleData.title,
            body        : scheduleData.body,
            isAllDay    : scheduleData.isAllDay,
            start       : startDate,
            end         : endDate,
            category    : scheduleData.isAllDay ? 'allday' : 'time',
            location    : scheduleData.location,
            calendarId  : calendar.id,
            reminders   : scheduleData.recurrenceRule,
        };

        Loader.showMainLoader();
        axios.post(ajaxCallUrl, dataBag)
            .then( (response) => {
                Loader.hideMainLoader();

                let ajaxResponseDto = AjaxResponseDto.fromArray(response.data);
                if(!ajaxResponseDto.success){
                    _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                    return;
                }
                _this.bootstrapNotify.showGreenNotification(ajaxResponseDto.message);

                let usedScheduleId = ( ("undefined" === typeof scheduleData.id) ? ++_this.lastUsedScheduleId : scheduleData.id);

                var schedule = {
                    id             : usedScheduleId.toString(),
                    title          : scheduleData.title,
                    body           : scheduleData.body,
                    isAllDay       : scheduleData.isAllDay,
                    start          : scheduleData.start,
                    end            : scheduleData.end,
                    category       : scheduleData.isAllDay ? 'allday' : 'time',
                    location       : scheduleData.location,
                    recurrenceRule : scheduleData.recurrenceRule,

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
                Loader.hideMainLoader();
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
            this.updateShownDate(calendarInstance);
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
            this.updateShownDate(calendarInstance);
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
            this.updateShownDate(calendarInstance);
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
            this.updateShownDate(calendarInstance);
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
            this.updateShownDate(calendarInstance);
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
            this.updateShownDate(calendarInstance);
        })
    }

    /**
     * @description will modify the creation popup by watch for parent dom element and on the moment that children are
     *              added it will attempt to find the popup elements and modify them
     */
    private modifyScheduleCreationAndDetailPopup(calendarInstance: Calendar): void
    {
        let parentWrapperObserver = new MutationObserver( (mutations) => {

            for( let mutation of mutations ){
                if(0 == mutation.target.childNodes.length){
                    // inactive popup - don't do anything
                    return;
                }
            }

            // remove fields
            $('.tui-full-calendar-section-allday').addClass('d-none');
            $('.tui-full-calendar-section-state').addClass('d-none');
            $('#tui-full-calendar-schedule-private').addClass('d-none');

            // modify fields
            $('.tui-full-calendar-section-title').addClass("w-100");

            // add/prefill fields
            let lastClickedSchedule = this.findSchedule(this.lastClickedScheduleId, calendarInstance);
            let valueForBodyField   = "";

            // check if popup is opened in state of UPDATE or CREATE
            let containerTitleSection = $(this.POPUP_CONTAINER_SECTION_TITLE_SELECTOR);
            let scheduleTitle         = containerTitleSection.find("input").val() as string;
            this.isUpdatePopup        = !StringUtils.isEmptyString(scheduleTitle);

            // this is null when creating new schedule via popup
            if(null != lastClickedSchedule){
                valueForBodyField = lastClickedSchedule[this.POPUP_CUSTOM_FIELD_NAME_BODY];
            }

            // BODY
            let $bodySection = this.buildInputFieldForPopup(
                this.POPUP_CUSTOM_FIELD_NAME_BODY,
                'fas fa-pen',
                valueForBodyField,
                "",
                "",
                false,
                false,
                false,
                "",
                "",
                lastClickedSchedule
            );

            containerTitleSection.parent().after($bodySection);

            // Reminders handling
            // add inputs for all reminders dates
            if(
                    null !== lastClickedSchedule
                &&  !StringUtils.isEmptyString(lastClickedSchedule.recurrenceRule)
            ){
                let arrayOfRemindersDates = lastClickedSchedule.recurrenceRule.split(",");

                for(let counter = 0; counter <= (arrayOfRemindersDates.length -1) ; counter++){
                    let date = new Date(arrayOfRemindersDates[counter]);
                    if(counter === 0){
                        this.buildReminderInputForPopup(lastClickedSchedule, true, false, date);
                    }else{
                        this.buildReminderInputForPopup(lastClickedSchedule, true, true, date);
                    }
                }

            }else{
                // add new base input reminder
                this.buildReminderInputForPopup(lastClickedSchedule, true);
            }
        })

        // each view has it's own popover - the active one has child nodes
        let allPopoverWrappers   = document.querySelectorAll('.tui-full-calendar-floating-layer');
        allPopoverWrappers.forEach((popoverWrapper) => {

            parentWrapperObserver.observe(popoverWrapper, {
                childList: true,
            })
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
            id              : ("undefined" === typeof changes.id             ) ? schedule.id             : changes.id,
            title           : ("undefined" === typeof changes.title          ) ? schedule.title          : changes.title,
            body            : ("undefined" === typeof changes.body           ) ? schedule.body           : changes.body,
            isAllDay        : ("undefined" === typeof changes.isAllDay       ) ? schedule.isAllDay       : changes.isAllDay,
            start           : ("undefined" === typeof changes.start          ) ? schedule.start          : changes.start,
            end             : ("undefined" === typeof changes.end            ) ? schedule.end            : changes.end,
            category        : ("undefined" === typeof changes.category       ) ? schedule.category       : changes.category,
            location        : ("undefined" === typeof changes.location       ) ? schedule.location       : changes.location,
            calendarId      : ("undefined" === typeof changes.calendarId     ) ? schedule.calendarId     : changes.calendarId,
            color           : ("undefined" === typeof changes.color          ) ? schedule.color          : changes.color,
            bgColor         : ("undefined" === typeof changes.bgColor        ) ? schedule.bgColor        : changes.bgColor,
            dragBgColor     : ("undefined" === typeof changes.dragBgColor    ) ? schedule.dragBgColor    : changes.dragBgColor,
            borderColor     : ("undefined" === typeof changes.borderColor    ) ? schedule.borderColor    : changes.borderColor,
            recurrenceRule  : ("undefined" === typeof changes.recurrenceRule ) ? schedule.recurrenceRule : changes.recurrenceRule,
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
        Loader.showMainLoader();
        let _this = this;
        axios.get(`/modules/schedules/delete/${scheduleId}`).then( (response) => {
            Loader.hideMainLoader();
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
    private buildInputFieldForPopup(
        fieldName               : string,
        fontawesomeIconClasses  : string,
        dataToPrefill          ?: any,
        inputClasses           ?: string,
        additionalWrapperId    ?: string,
        allowClone              : boolean   = false,
        allowRemove             : boolean   = false,
        allowClear              : boolean   = false,
        popupSectionClasses     : string    = "",
        innerDivWrapperClasses  : string    = "",
        lastClickedSchedule     : ISchedule = null
    ): JQuery<HTMLElement>
    {
        let $divPopupSection     = $("<DIV>");
        let $divPopupSectionItem = $("<DIV>");
        let $spanWithIcon        = $("<SPAN>");
        let $fontawesomeIcon     = $("<I>");
        let $input               = $("<INPUT>");
        let $additionalWrapper   = $("<DIV>");

        if( !StringUtils.isEmptyString(additionalWrapperId) ){
            $additionalWrapper.attr("id", additionalWrapperId);
        }

        $divPopupSection.addClass('tui-full-calendar-popup-section').addClass(popupSectionClasses);
        $divPopupSectionItem.addClass(`tui-full-calendar-popup-section-item tui-full-calendar-section-${fieldName} w-100`).addClass(innerDivWrapperClasses);
        $spanWithIcon.addClass('tui-full-calendar-icon');
        $fontawesomeIcon.addClass(fontawesomeIconClasses);
        $input
            .addClass('tui-full-calendar-content w-90')
            .addClass(inputClasses)
            .attr('id', fieldName)
            .attr('placeholder', StringUtils.capitalizeFirstLetter(fieldName))
            .attr("autocomplete", "off");

        if(
               this.isUpdatePopup
            && null !== dataToPrefill
        ){
            $input.val(dataToPrefill);
        }

        // combine all
        $spanWithIcon.append($fontawesomeIcon);
        $divPopupSectionItem.append([$spanWithIcon, $input]);
        $divPopupSection.append($divPopupSectionItem);
        $divPopupSection.append($additionalWrapper);

        if(allowClone || allowRemove || allowClear){
            let $actionsWrapper = $("<SECTION>");

            if(allowClone){
                let addButtonCallback = () => {
                    this.buildReminderInputForPopup(lastClickedSchedule, true, true);
                };
                $actionsWrapper = this.addSingleActionButtonForPopup($actionsWrapper, "fas fa-plus-circle", addButtonCallback, "text-success");
            }

            if(allowRemove){
                let removeButtonCallback = (event) => {
                    $(event.currentTarget).closest('.tui-full-calendar-popup-section').remove();
                };
                $actionsWrapper = this.addSingleActionButtonForPopup($actionsWrapper, "fas fa-trash", removeButtonCallback, "text-danger");
            }

            let clearButtonCallback = (event) => {
                $(event.currentTarget).closest('.tui-full-calendar-popup-section').find('input').val("");
            };
            $actionsWrapper = this.addSingleActionButtonForPopup($actionsWrapper, "fas fa-times-circle", clearButtonCallback, "text-dark-orange");

            $divPopupSection.append($actionsWrapper);
        }

        return $divPopupSection;
    }

    /**
     * @description will add the input for reminder
     */
    private buildReminderInputForPopup(lastClickedSchedule: ISchedule, allowClone: boolean = false, allowRemove: boolean = false, date: Date|null = null): void
    {
        this.lastUsedReminderId++;
        let reminderIdentifier = "reminder-calendar" + this.lastUsedReminderId;
        let addedClasses       = `reminder-calendar ${reminderIdentifier}`;

        let $containerLocation = $(this.POPUP_CONTAINER_SECTION_LOCATION);
        let $reminderSection   = this.buildInputFieldForPopup(
            this.POPUP_CUSTOM_FIELD_NAME_REMINDER,
            'fas fa-redo',
            "",
            addedClasses,
            reminderIdentifier,
            allowClone,
            allowRemove,
            true,
            "d-flex",
            "col-6",
            lastClickedSchedule
        );
        $containerLocation.parent().before($reminderSection);

        let usedDate = null; // on purpose to keep the field empty so that it's known that no reminder is set
        if(
               this.isUpdatePopup
            && date instanceof Date
        ){
            usedDate = date;
        }

        // apply date picker
        new TuiDatePicker(`#${reminderIdentifier}`, {
            date: usedDate,
            input: {
                element: `.${reminderIdentifier}`,
                format: 'yyyy-MM-dd hh:mm'
            },
            timePicker: true
        });
    }

    /**
     * @description returns the start date for given schedule
     */
    private getScheduleStartDate(schedule: ISchedule): Date
    {
        // @ts-ignore
        return schedule.start._date;
    }

    /**
     *
     * @private
     */
    private handleCalendarSettingsModal()
    {
        let $modal = $(this.CALENDAR_MODAL_SELECTOR) ;
        this.dialog.moveBackdropToReloadablePageContainer($modal);
    }

    /**
     * @description will build single action button for popup
     */
    private addSingleActionButtonForPopup($actionsWrapper: JQuery<HTMLElement>, icon: string, clickCallback: Function, buttonClasses: string = ""): JQuery<HTMLElement>
    {
        let $icon   = $("<I>").addClass(icon);
        let $button = $("<SPAN>"); // that's correct, it's a button in terms of logic but using span to show fontawesome icon
        $button.addClass(buttonClasses)
            .addClass("popup-action-button");

        $button.on('click', (event) => {
            event.preventDefault();
            clickCallback(event);
        })

        $button.append($icon);
        $actionsWrapper.append($button);

        return $actionsWrapper;
    }

    /**
     * @description will return all reminders array from creation popup
     */
    private getAllRemindersArrayFromCreationPopup(): Array<any>
    {
        let $creationPopupContainer = $(this.POPUP_CONTAINER_SELECTOR);
        if( !DomElements.doElementsExists($creationPopupContainer) ){
            throw{
                "message": "Cannot get remindersArray from the popup as the popup is not present!"
            }
        }

        let allInputReminders = $(this.INPUT_REMINDER_SELECTOR) as JQuery<HTMLInputElement>;
        let allRemindersArray = [];

        $.each(allInputReminders, (index ,element) => {

            /**
             * @description skip empty fields and ignore duplicates
             */
            if(
                    !StringUtils.isEmptyString(element.value)
                &&  !ArrayUtils.inArray(element.value, allRemindersArray)
            ){
                allRemindersArray.push(element.value)
            }
        })

        return allRemindersArray;
    }

    /**
     * @description applies the search logic to the calendar instance
     */
    private attachLogicToSearchInput(calendarInstance: Calendar): void
    {
        let allSchedules = this.getAllSchedulesFromCalendarInstance(calendarInstance);
        if( null !== this.lastSearchedSchedule ){
            // this.unmarkScheduleInCalendar(this.lastSearchedSchedule);
            // this.lastSearchedSchedule.id = null;
        }

        let arrayOfSchedules  = Object.values(allSchedules); // is object due to indexes not being sorted
        let selectionCallback = (schedule: ISchedule) => {
            let startDate             = this.getScheduleStartDate(schedule);
            this.lastSearchedSchedule = schedule;
            this.goToDate(calendarInstance, startDate);
            // this.markScheduleInCalendar(this.lastSearchedSchedule);
        }

        let searchResultModificationCallback = (searchResultData: Object): Object => {
            //@ts-ignore
            let schedule        = searchResultData.value as ISchedule;
            let starDate        = this.getScheduleStartDate(schedule);
            let momentStartDate = moment(starDate.toString());

            //@ts-ignore (must be a string instead of nodes)
            let matchingContent = `<small>${searchResultData.match}</small>`;
            let dateLine        = `<small class="d-block">${momentStartDate.format("DD.MM.YYYY HH:mm")}</small>`;

            //@ts-ignore
            searchResultData.match = matchingContent + dateLine;
            return searchResultData;
        }
        AutocompleteService.init(arrayOfSchedules, ["title"], selectionCallback, searchResultModificationCallback);
    }

    /**
     * @description will move calendar view to given date
     */
    private goToDate(calendar: Calendar, date: Date): void
    {
        calendar.changeView(TuiCalendarService.CALENDAR_VIEW_WEEK);
        calendar.setDate(date);
        this.updateShownDate(calendar);
    }

    /**
     * @description handles showing the date
     */
    private updateShownDate(calendarInstance: Calendar): void
    {
        let $dateRangeElement = $(this.DATE_RANGE);
        let currentDateMoment = moment(calendarInstance.getDate().toDate().toDateString());

        let shownDate = currentDateMoment.format("dddd MMMM YYYY");
        if( TuiCalendarService.CALENDAR_VIEW_MONTH === calendarInstance.getViewName() ){
            let momentDate = moment(calendarInstance.getDate().toDate().toDateString());
            shownDate      = `${momentDate.format("MMMM")} ${momentDate.format("YYYY")}`;
        }else if( TuiCalendarService.CALENDAR_VIEW_WEEK === calendarInstance.getViewName() ){
            let momentStartDate = moment(calendarInstance.getDateRangeStart().toDate().toDateString());
            let momentEndDate   = moment(calendarInstance.getDateRangeEnd().toDate());

            shownDate = `${momentStartDate.format("DD MMMM YYYY")} - ${momentEndDate.format("DD MMMM YYYY")}`;
        }

        $dateRangeElement.html(shownDate);
    }

    /**
     * @description Will mark schedule in calendar
     * Not working so far since there is issue that this gets called before calendar grid dom is updated
     */
    private markScheduleInCalendar(schedule: ISchedule): void {
        $(`[${this.DATA_SCHEDULE_ID}='${schedule.id}']`).addClass("schedule-mark");
    }

    /**
     * @description Will mark schedule in calendar
     * Not working so far since there is issue that this gets called before calendar grid dom is updated
     */
    private scrollToScheduleInCalendar(schedule: ISchedule): void {
        document.querySelector(`[${this.DATA_SCHEDULE_ID}='${schedule.id}']`).scrollIntoView();
    }
}