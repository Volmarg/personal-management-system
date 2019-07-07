import * as $ from 'jquery';
import 'datatables';

export default (function () {
    window.datatable = {};
    datatable = {
        destroy: function (table_id) {
            $('#' + table_id).DataTable().destroy();
        },
        init: function () {
            $(document).ready(() => {
                let all_tables = $('body').find('table[data-table="true"]');
                $(all_tables).each(function (index, element) {
                    $(element).DataTable();
                });
            })
            // $('#MyPaymentsProductTable_non_rejected').DataTable();
        },
        reinit: function (table_id) {
            $('#' + table_id).DataTable();
        }
    };


}())
