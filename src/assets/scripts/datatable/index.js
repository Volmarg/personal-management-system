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
                massActionRemoveRecordsButton   : ".datatable-remove-records",
                checkboxCell                    : ".select-checkbox"
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
                $(all_tables).each(function (index, table) {

                    let config          = {};
                    let checkboxesAttr  = $(table).attr('data-table-checkboxes');
                    let isSelectable    = ( "true" === checkboxesAttr );

                    if( isSelectable ){
                        config = _this.configs.checkboxes;
                    }

                    $(table).DataTable(config);

                    if( isSelectable ){
                        _this.initSelectOptions(table);
                    }

                });
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
        initSelectOptions: function(table){
            // TODO: check how this behaves for 2 tables like Payments Products

            let massActionButtons = $(this.selectors.classes.massActionButtons);
            // Buttons MUST be there for this options logic
            if( 0 === $(massActionButtons).length ){
                return;
            }

            this.attachSelectingCheckboxForCheckboxCell(table);

            let massActionRemoveRecordsButton = $(massActionButtons).find(this.selectors.classes.massActionRemoveRecordsButton);

            if( 0 !==  $(massActionRemoveRecordsButton).length ){ // replace selector for files
                this.attachFilesRemoveEventOnRemoveFileButton(massActionRemoveRecordsButton);
            }

        },
        initSelectOptionRemoveRows: function(){

        },
        /**
         * This function is written specifically for files module
         * @param massActionRemoveRecordsButton
         */
        attachFilesRemoveEventOnRemoveFileButton: function(massActionRemoveRecordsButton){
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
        },
        attachSelectingCheckboxForCheckboxCell: function(table){

            let allSelectCells = $(table).find(this.selectors.classes.checkboxCell);
            allSelectCells.on('click', (event) => {

                let clickedCell = event.currentTarget;
                let checkbox    = $(clickedCell).find('input');
                let isChecked   = utils.domAttributes.isChecked(checkbox);

                if( isChecked ){
                    utils.domAttributes.unsetChecked(checkbox)
                }else{
                    utils.domAttributes.setChecked(checkbox)
                }

            });

        }
    };


}())
