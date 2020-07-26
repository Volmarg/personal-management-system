import * as $ from 'jquery';
import 'bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js';
import 'bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css';

export default class BootstrapDatepicker {

    /**
     * Main initialization logic
     */
    public static init(): void
    {
        //@ts-ignore
        $('.start-date').datepicker();

        //@ts-ignore
        $('.end-date').datepicker();
    }

}