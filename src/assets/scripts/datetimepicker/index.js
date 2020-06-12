import * as $ from 'jquery';
import flatpickr from "flatpickr";

/**
 * @see https://flatpickr.js.org/examples/
 */
export default (function () {
    window.datetimepicker = {};
    datetimepicker = {
        data: {
            isDateTimePicker: "data-is-datetime-picker"
        },
        init: function () {
            this.applyDateTimePicker();
        },
        applyDateTimePicker: function () {
            let $dateTimePickerElementsToInitialize = $('[' + this.data.isDateTimePicker + '=true]');

            $.each($dateTimePickerElementsToInitialize, function (index, element) {

                let $element     = $(element);
                let $parentModal = $element.closest('.modal');

                let onOpenFunction = function(){};

                if( $parentModal.length === 1  ){
                    onOpenFunction = function (selectedDates, dateStr, instance) {
                        let $calendarElement = $(instance.calendarContainer);
                        $calendarElement.detach().appendTo($parentModal);
                    }
                }

                flatpickr(element, {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    time_24hr : true,
                    onOpen: onOpenFunction
                });
            });
        }
    };
}())


