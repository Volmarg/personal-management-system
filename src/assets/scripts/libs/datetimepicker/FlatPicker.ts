import * as $ from 'jquery';
import * as flatpickr from "flatpickr";

/**
 * @see https://flatpickr.js.org/examples/
 */
export default class FlatPicker {

    /**
     * @type Object
     */
    private static data = {
        isDateTimePicker: "data-is-datetime-picker"
    };

    /**
     * @description Main initialization logic
     */
    public init(): void
    {
        this.applyDateTimePicker();
    };

    /**
     * @description Invokes DTP on given elements
     */
    private applyDateTimePicker(): void
    {
        let $dateTimePickerElementsToInitialize = $('[' + FlatPicker.data.isDateTimePicker + '=true]');

        $.each($dateTimePickerElementsToInitialize, function (index, element) {

            let $element     = $(element);
            let $parentModal = $element.closest('.modal');

            let onOpenFunction = <Function> function(){};

            if( $parentModal.length === 1  ){
                onOpenFunction = function (selectedDates, dateStr, instance) {
                    let $calendarElement = $(instance.calendarContainer);
                    $calendarElement.detach().appendTo($parentModal);
                }
            }

            //@ts-ignore
            let flatpicker = flatpickr(element, {
                enableTime    : true,
                dateFormat    : "Y-m-d H:i",
                time_24hr     : true,
                defaultDate   : new Date(),
                defaultHour   : new Date().getHours(),
                defaultMinute : new Date().getMinutes(),
                locale: {
                    firstDayOfWeek: 1
                },
                onOpen: onOpenFunction
            });
        });
    }
}