import ArrayUtils from "../../../core/utils/ArrayUtils";

export default class ScheduleDto
{
    private _id            : string;
    private _title         : string;
    private _body          : string;
    private _allDay        : boolean;
    private _start         : string;
    private _end           : string;
    private _category      : string;
    private _location      : string;
    private _calendarId    : string;
    private _calendarColor : string;
    private _reminders     : Object;

    get id(): string {
        return this._id;
    }

    get title(): string {
        return this._title;
    }

    get allDay(): boolean {
        return this._allDay;
    }

    get start(): string {
        return this._start;
    }

    get end(): string {
        return this._end;
    }

    get category(): string {
        return this._category;
    }

    get location(): string {
        return this._location;
    }

    get calendarId(): string {
        return this._calendarId;
    }

    get calendarColor(): string {
        return this._calendarColor;
    }

    set calendarColor(value: string) {
        this._calendarColor = value;
    }

    get body(): string {
        return this._body;
    }

    set body(value: string) {
        this._body = value;
    }

    get reminders(): Object {
        return this._reminders;
    }

    /**
     * Returns the reminders dates in form of a string
     */
    public remindersDatesAsString(): string {
        let dates = Object.values(this._reminders);

        if( ArrayUtils.isEmpty(dates) ){
            return "";
        }

        let remindersDatesStrings = dates.join(", ");
        return remindersDatesStrings;
    }

    set reminders(value: Object) {
        this._reminders = value;
    }

    /**
     * @description will create dto from json
     */
    public static fromJson(json: string): ScheduleDto
    {
        let object = JSON.parse(json);

        let dto            = new ScheduleDto();
        dto._id            = object.id;
        dto._title         = object.title;
        dto._body          = object.body;
        dto._allDay        = object.allDay;
        dto._start         = object.start;
        dto._end           = object.end;
        dto._category      = object.category;
        dto._location      = object.location;
        dto._calendarId    = object.calendarId;
        dto._calendarColor = object.calendarColor;
        dto._reminders     = object.reminders;

        return dto;
    }
}