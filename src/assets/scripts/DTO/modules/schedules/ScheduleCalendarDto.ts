export default class ScheduleCalendarDto
{
    private _id                  : number;
    private _name                : string;
    private _color               : string;
    private _backgroundColor     : string;
    private _dragBackgroundColor : string;
    private _borderColor         : string;
    private _deleted             : boolean;

    get id(): number {
        return this._id;
    }

    get name(): string {
        return this._name;
    }

    get color(): string {
        return this._color;
    }

    get backgroundColor(): string {
        return this._backgroundColor;
    }

    get dragBackgroundColor(): string {
        return this._dragBackgroundColor;
    }

    get borderColor(): string {
        return this._borderColor;
    }

    get deleted(): boolean {
        return this._deleted;
    }

    /**
     * @description will create dto from json
     */
    public static fromJson(json: string): ScheduleCalendarDto
    {
        let object = JSON.parse(json);

        let dto = new ScheduleCalendarDto();
        dto._id                  = object.id;
        dto._name                = object.name;
        dto._color               = object.color;
        dto._backgroundColor     = object.backgroundColor;
        dto._dragBackgroundColor = object.dragBackgroundColor;
        dto._borderColor         = object.borderColor;
        dto._deleted             = object.deleted;

        return dto;
    }

}