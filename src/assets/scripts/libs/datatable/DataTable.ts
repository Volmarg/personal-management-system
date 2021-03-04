/**
 * Known problems
 *  if by any chance calling this : $targetTable.DataTable();
 *  - will duplicated the table or it's part
 *  - will do nothing
 *  then make sure that:
 *  - your table has id (must be)
 *  - that for more than 1 tables each table has unique id (must be)
 */

import * as $ from 'jquery';
import 'datatables';
import 'datatables.net-select';
import BootstrapNotify      from "../../libs/bootstrap-notify/BootstrapNotify";
import Loader               from "../../libs/loader/Loader";
import DomAttributes        from "../../core/utils/DomAttributes";
import Navigation           from "../../core/Navigation";
import Ajax                 from "../../core/ajax/Ajax";
import Initializer          from "../../Initializer";
import BootboxWrapper       from "../bootbox/BootboxWrapper";
import DataTransferDialogs  from "../../core/ui/Dialogs/DataTransferDialogs";
import ValidationUtils      from "../../core/utils/ValidationUtils";
import DomElements          from "../../core/utils/DomElements";
import AjaxResponseDto      from "../../DTO/AjaxResponseDto";
import AjaxEvents           from "../../core/ajax/AjaxEvents";
import Ui from "../../core/ui/Ui";

export default class DataTable {

    /**
     * @type Object
     */
    private configs = {
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
    };

    /**
     * @type Object
     */
    private selectors = {
        classes:{
            massActionButtonsSection        : ".datatable-mass-actions",
            massActionRemoveFilesButton     : ".datatable-remove-files",
            massActionTransferFilesButton   : ".datatable-transfer-files",
            checkboxCell                    : ".select-checkbox"
        },
        attributes: {
            targetTable               : "data-target-table-selector",
            isFilterForTable          : "data-is-filter-for-table",
            filterTargetTableSelector : "data-filter-target-table-selector",
            filterTargetColumn        : "data-target-column",
        }
    };

    /**
     * @type Object
     */
    private api = {
        removeFiles: '/my-files/remove-file'
    };

    /**
     * @type BootstrapNotify
     */
    private bootstrapNotify = new BootstrapNotify();

    /**
     * @type Ajax
     */
    private ajax = new Ajax();

    /**
     * @type AjaxEvents
     */
    private ajaxEvents = new AjaxEvents();

    /**
     * @type Initializer
     */
    private initializer = new Initializer();

    /**
     * @type DataTransferDialogs
     */
    private dataTransferDialogs = new DataTransferDialogs();

    /**
     * @description Initialize datatable based on DOM attributes
     */
    public init() {
        let _this = this;
        $(document).ready(() => {

            let $allTables = $('body').find('table[data-table="true"]');
            $($allTables).each(function (index, table) {

                let config          = {};
                let checkboxesAttr  = $(table).attr('data-table-checkboxes');
                let isSelectable    = ( ValidationUtils.isTrue(checkboxesAttr) );

                if( isSelectable ){
                    config = _this.configs.checkboxes;
                }

                // reinitializing
                if( !$.fn.dataTable.isDataTable(table) )
                {
                    $(table).DataTable(config);

                    _this.attachFilterDependencyForTable();

                    if( isSelectable ){
                        _this.initSelectOptions(table);
                    }
                }

            });
        })
    };

    /**
     * @description Reinitialize existing datatable instance
     *
     * @param table_id {string}
     */
    public reinit(table_id) {
        let config = {};
        let table  = $('#' + table_id);
        let checkboxesAttr = $(table).attr('data-table-checkboxes');

        if( ValidationUtils.isTrue(checkboxesAttr)  ){
            config = this.configs.checkboxes;
        }

        $(table).DataTable(config);
    };

    /**
     * Might not work
     * @param tableId
     */
    public static destroyDatatableInstanceForTableId(tableId: string)
    {
        let $table = $(`#${tableId}`);
        $table.DataTable().destroy();
    }

    /**
     * Attach all sort of events for special buttons etc. when rows in table are selectable/with checkboxes
     * @param table
     */
   private initSelectOptions(table){
        let massActionButtonsSection  = $(this.selectors.classes.massActionButtonsSection);
        let $massActionButtons        = $(massActionButtonsSection).find('button');
        // Buttons MUST be there for this options logic
        if( !DomElements.doElementsExists($massActionButtons) ){
            return;
        }

        this.attachSelectingCheckboxForCheckboxCell(table);
        this.attachEnablingAndDisablingMassActionButtonsToCheckboxCells(table, $massActionButtons);

        let $massActionRemoveFilesButton   = $(massActionButtonsSection).find(this.selectors.classes.massActionRemoveFilesButton);
        let $massActionTransferFilesButton = $(massActionButtonsSection).find(this.selectors.classes.massActionTransferFilesButton);

        if( DomElements.doElementsExists($massActionRemoveFilesButton) ){
            this.attachFilesRemoveEventOnRemoveFileButton($massActionRemoveFilesButton);
        }

        if( DomElements.doElementsExists($massActionTransferFilesButton) ){
            this.attachFilesTransferEventOnTransferFileButton($massActionTransferFilesButton);
        }

    };

    /**
     * This function is written specifically for files module
     * @param massActionRemoveRecordsButton
     */
   private attachFilesRemoveEventOnRemoveFileButton(massActionRemoveRecordsButton){
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

            //@ts-ignore
            let filePathCellIndex           = selectedRows.row(1).cell('.mass-action-remove-file-path').index().column;

            //@ts-ignore
            let fileSubdirectoryCellIndex   = selectedRows.row(1).cell('.mass-action-remove-file-subdirectory').index().column;
            let subdirectory                = '';

            selectedRows.indexes().each((index) => {
                //@ts-ignore
                let rowData  = selectedRows.row(index).data();
                let filePath = rowData[filePathCellIndex];

                pathsOfFilesToRemove.push(filePath);
                subdirectory = rowData[fileSubdirectoryCellIndex];
            });

            let data = {
                'files_full_paths': pathsOfFilesToRemove,
                'subdirectory'    : subdirectory
            };

            BootboxWrapper.confirm({
                message: "Do You really want to remove selected files?",
                backdrop: true,
                callback: function (result) {
                    if (result) {
                        Loader.showMainLoader();

                        $.ajax({
                            url: url,
                            method: "POST",
                            data: data,
                        }).always((data) => {

                            Loader.hideMainLoader();

                            let ajaxResponseDto  = AjaxResponseDto.fromArray(data);

                            if( !ajaxResponseDto.isSuccessCode() ){
                                _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                                return;
                            }else {

                                let message = ajaxResponseDto.message;
                                if( !ajaxResponseDto.isMessageSet() ){
                                    message = "Entity has been removed from repository";
                                }

                                if( ajaxResponseDto.isTemplateSet() ){
                                    Ui.insertIntoMainContent(ajaxResponseDto.template);
                                    _this.initializer.reinitializeLogic();
                                }

                                _this.bootstrapNotify.showGreenNotification(message);
                            }
                        });
                    }
                }
            });
        });
    };

    /**
     * This function is written specifically for files module
     * @param massActionRemoveRecordsButton
     */
   private attachFilesTransferEventOnTransferFileButton(massActionRemoveRecordsButton){
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

            //@ts-ignore
            let filePathCellIndex = selectedRows.row(1).cell('.mass-action-remove-file-path').index().column;

            selectedRows.indexes().each((index) => {
                //@ts-ignore
                let rowData  = selectedRows.row(index).data();
                let filePath = rowData[filePathCellIndex];

                pathsOfFilesToTransfer.push(filePath);
            });

            let callback = function (){
                _this.ajaxEvents.loadModuleContentByUrl(Navigation.getCurrentUri());
                BootboxWrapper.hideAll();;
            };

            this.dataTransferDialogs.buildDataTransferDialog(pathsOfFilesToTransfer, 'My Files', callback);
        });
   };

    /**
     * Handle checkbox logic for clicking checkbox in checkbox cell
     * @param table {object}
     */
    private attachSelectingCheckboxForCheckboxCell(table){

        let allSelectCells = $(table).find(this.selectors.classes.checkboxCell);
        allSelectCells.on('click', (event) => {

            let clickedCell = event.currentTarget;
            let checkbox    = $(clickedCell).find('input');
            let isChecked   = DomAttributes.isChecked(checkbox);

            if( isChecked ){
                DomAttributes.unsetChecked(checkbox)
            }else{
                DomAttributes.setChecked(checkbox)
            }
        });
    };

    /**
     * Handle mass action buttons disabled/enabled for selected checkboxes
     * @param table
     * @param massActionButtons
     */
    private attachEnablingAndDisablingMassActionButtonsToCheckboxCells(table, massActionButtons){
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
    };

    /**
     * Makes the special filters above table work and filter the data in datatable
     */
    private attachFilterDependencyForTable(){

        let _this               = this;
        let $allFiltersTable    = $("[" + _this.selectors.attributes.isFilterForTable + "=true]");
        let $allFiltersSelects  = $allFiltersTable.find('select');

        $.each( $allFiltersSelects, function( index, select ){
            let $select = $(select);

            let targetTableSelector = $select.closest('table').attr(_this.selectors.attributes.filterTargetTableSelector);
            let $targetTable        = $(targetTableSelector);

            $select.on( 'change', function () {
                let $selectedOption = $select.find(':selected');
                let optionValue     = $selectedOption.val();
                let targetColumn    = $select.attr(_this.selectors.attributes.filterTargetColumn);

                let $dataTable = $targetTable.DataTable();

                // Apply the search
                $dataTable.columns().every(function(value, index) {
                    // need to fix problem that table is initialized twice

                    let column = $dataTable.column(index);
                    let header = $dataTable.columns(0).header()[0].textContent;

                    if (
                            targetColumn === header
                            //@ts-ignore
                        &&  column !== optionValue
                    ) {
                        //@ts-ignore
                        column.search(optionValue)
                            .draw();
                    }
                });
            });
        });
    }
}