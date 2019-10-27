import * as $ from 'jquery';
import 'datatables';
import 'datatables.net-select';

export default (function () {
    window.datatable = {};
    datatable = {
        configs:{
            checkboxes: {
                columnDefs: [ {
                    orderable: false,
                    className: 'select-checkbox',
                    targets:   0
                } ],
                select: {
                    style: 'multi',
                    selector: 'td:first-child'
                },
                order: [[ 1, 'asc' ]]
            }
        },
        selectors: {
            classes:{
                massActionButtons               : ".datatable-mass-actions",
                massActionRemoveRecordsButton   : ".datatable-remove-records"
            },
            attributes: {
                targetTable: "data-target-table-selector"
            }
        },
        destroy: function (table_id) {
            $('#' + table_id).DataTable().destroy();
        },
        init: function () {
            let _this = this;
            $(document).ready(() => {

                let all_tables = $('body').find('table[data-table="true"]');
                $(all_tables).each(function (index, element) {

                    let config = {};
                    let checkboxesAttr = $(element).attr('data-table-checkboxes');

                    if( "true" === checkboxesAttr ){
                        config = _this.configs.checkboxes;
                    }

                    $(element).DataTable(config);
                });

                _this.initSelectOptions();
            })
        },
        reinit: function (table_id) {
            let config = {};
            let table  = $('#' + table_id);
            let checkboxesAttr = $(table).attr('data-table-checkboxes');

            if( "true" === checkboxesAttr ){
                config = this.configs.checkboxes;
            }

            $(table).DataTable(config);
        },
        initSelectOptions: function(){

            let massActionButtons = $(this.selectors.classes.massActionButtons);

            if( 0 === $(massActionButtons).length ){
                return;
            }

            let massActionRemoveRecordsButton = $(massActionButtons).find(this.selectors.classes.massActionRemoveRecordsButton);

            if( 0 !==  $(massActionRemoveRecordsButton).length ){ // replace selector for files
                this.iniSelectOptionRemoveFiles(massActionRemoveRecordsButton);
            }

        },
        initSelectOptionRemoveRows: function(){

        },
        /**
         * This function is written specifically for files module
         * @param massActionRemoveRecordsButton
         */
        iniSelectOptionRemoveFiles: function(massActionRemoveRecordsButton){
            let _this = this;
            $(massActionRemoveRecordsButton).on('click', () => {
                let targetTableSelector     = $(massActionRemoveRecordsButton).attr(_this.selectors.attributes.targetTable);
                let table                   = $(targetTableSelector);
                let dataTable               = $(table).DataTable();
                let selectedRows            = dataTable.rows( { selected: true } );
                let pathsOfFilesToRemove    = [];

                if( 0 === selectedRows.count() ){
                    return;
                }

                let filePathCellIndex  = selectedRows.row(1).cell('.mass-action-remove-file-path').index().column;

                selectedRows.indexes().each((index) => {
                    let rowData  = selectedRows.row(index).data();
                    let filePath = rowData[filePathCellIndex];

                    pathsOfFilesToRemove.push(filePath);
                });

            });
        }
    };


}())
