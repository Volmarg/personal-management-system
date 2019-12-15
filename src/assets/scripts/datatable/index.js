import * as $ from 'jquery';
import 'datatables';
import 'datatables.net-select';
var bootbox = require('bootbox');

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
                massActionButtonsSection        : ".datatable-mass-actions",
                massActionRemoveFilesButton     : ".datatable-remove-files",
                massActionTransferFilesButton   : ".datatable-transfer-files",
                checkboxCell                    : ".select-checkbox"
            },
            attributes: {
                targetTable: "data-target-table-selector"
            }
        },
        destroy: function (table_id) {
            $('#' + table_id).DataTable().destroy();
        },
        api: {
          removeFiles: '/my-files/remove-file'
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

                    // reinitializing
                    if( !$.fn.dataTable.isDataTable(table) )
                    {
                        $(table).DataTable(config);

                        if( isSelectable ){
                            _this.initSelectOptions(table);
                        }
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

            let massActionButtonsSection = $(this.selectors.classes.massActionButtonsSection);
            let massActionButtons        = $(massActionButtonsSection).find('button');
            // Buttons MUST be there for this options logic
            if( 0 === $(massActionButtons).length ){
                return;
            }

            this.attachSelectingCheckboxForCheckboxCell(table);
            this.attachEnablingAndDisablingMassActionButtonsToCheckboxCells(table, massActionButtons);

            let massActionRemoveFilesButton   = $(massActionButtonsSection).find(this.selectors.classes.massActionRemoveFilesButton);
            let massActionTransferFilesButton = $(massActionButtonsSection).find(this.selectors.classes.massActionTransferFilesButton);

            if( 0 !==  $(massActionRemoveFilesButton).length ){
                this.attachFilesRemoveEventOnRemoveFileButton(massActionRemoveFilesButton);
            }

            if( 0 !==  $(massActionTransferFilesButton).length ){
                this.attachFilesTransferEventOnTransferFileButton(massActionTransferFilesButton);
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
                let url                     = _this.api.removeFiles;

                if( 0 === selectedRows.count() ){
                    return;
                }

                let filePathCellIndex           = selectedRows.row(1).cell('.mass-action-remove-file-path').index().column;
                let fileSubdirectoryCellIndex   = selectedRows.row(1).cell('.mass-action-remove-file-subdirectory').index().column;
                let subdirectory                = '';

                selectedRows.indexes().each((index) => {
                    let rowData  = selectedRows.row(index).data();
                    let filePath = rowData[filePathCellIndex];

                    pathsOfFilesToRemove.push(filePath);
                    subdirectory = rowData[fileSubdirectoryCellIndex];
                });

                let data = {
                    'files_full_paths': pathsOfFilesToRemove,
                    'subdirectory'    : subdirectory
                };

                bootbox.confirm({
                    message: "Do You really want to remove selected files?",
                    backdrop: true,
                    callback: function (result) {
                        if (result) {
                            ui.widgets.loader.showLoader();
                            $.ajax({
                                url: url,
                                method: "POST",
                                data: data,
                                success: (template) => {
                                    bootstrap_notifications.notify("Files have been removed", 'success');

                                    $('.twig-body-section').html(template);
                                    initializer.reinitialize();
                                },
                            }).fail(() => {
                                bootstrap_notifications.notify("There was an error while trying to remove the files", 'danger')
                            }).always(() => {
                                ui.widgets.loader.hideLoader();
                            });
                        }
                    }
                });

            });
        },
        /**
         * This function is written specifically for files module
         * @param massActionRemoveRecordsButton
         */
        attachFilesTransferEventOnTransferFileButton: function(massActionRemoveRecordsButton){
            let _this = this;
            $(massActionRemoveRecordsButton).on('click', () => {
                let targetTableSelector     = $(massActionRemoveRecordsButton).attr(_this.selectors.attributes.targetTable);
                let table                   = $(targetTableSelector);
                let dataTable               = $(table).DataTable();
                let selectedRows            = dataTable.rows( { selected: true } );
                let pathsOfFilesToTransfer  = [];

                if( 0 === selectedRows.count() ){
                    return;
                }

                let filePathCellIndex = selectedRows.row(1).cell('.mass-action-remove-file-path').index().column;

                selectedRows.indexes().each((index) => {
                    let rowData  = selectedRows.row(index).data();
                    let filePath = rowData[filePathCellIndex];

                    pathsOfFilesToTransfer.push(filePath);
                });

                let callback = function (){
                    if( "undefined" === typeof TWIG_REQUEST_URI ){
                        throw({
                           "message" : "Variable TWIG_REQUEST_URI was not defined."
                        });
                    }
                    ui.ajax.loadModuleContentByUrl(TWIG_REQUEST_URI);
                };

                dialogs.ui.dataTransfer.buildDataTransferDialog(pathsOfFilesToTransfer, 'My Files', callback);
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

        },
        attachEnablingAndDisablingMassActionButtonsToCheckboxCells: function(table, massActionButtons){
            let dataTable = $(table).DataTable();

            dataTable.on('select deselect', () => {
                let selectedRows      = dataTable.rows( { selected: true } );
                let selectedRowsCount = 0;

                selectedRows.indexes().each((index) => {
                    selectedRowsCount++;
                });

                if( 0 === selectedRowsCount ){
                    $(massActionButtons).addClass('disabled');
                    return;
                }

                $(massActionButtons).removeClass('disabled');

            });

        }
    };


}())
