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
            // todo: add more attrs via data
        },
        init: function () {
            this.applyDateTimePicker();
        },
        applyDateTimePicker: function () {
            let $dateTimePickerElementsToInitialize = $('[' + this.data.isDateTimePicker + '=true]');

            $.each($dateTimePickerElementsToInitialize, function (index, element) {
                flatpickr(element, {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    time_24hr : true,
                });
            });
        }
    };
}())


