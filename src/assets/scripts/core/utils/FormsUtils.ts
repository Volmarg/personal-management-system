import StringUtils     from "./StringUtils";
import DomElements     from "./DomElements";
import ArrayUtils      from "./ArrayUtils";
import BootstrapSelect from "../../libs/bootstrap-select/BootstrapSelect";

export default class FormsUtils {

    /**
     * @type Object
     */
    private selectors = {
        classes: {
            listFilterer: '.listFilterer'
        }
    };

    public init() {
        this.filterDependentListOnChange();
    };

    /**
     * @description This function depends on the ChoiceType optgroup, and filters by value of optgroup label
     */
    private filterDependentListOnChange() {

        let listFilterers = $(this.selectors.classes.listFilterer);

        if( $(listFilterers).length === 0 ){
            return;
        }

        $.each(listFilterers, (index, listFilterer) => {

            $(listFilterer).on('change', () => {
                this.filterDependentList(listFilterer);
            });

            this.filterDependentList(listFilterer);

        });
    };

    private filterDependentList(listFilterer) {
        let dependentListSelector = $(listFilterer).attr('data-dependent-list-selector');
        let dependentList         = $(dependentListSelector);
        let optgroups             = $(dependentList).find('optgroup');

        if( $(dependentList).prop('tagName') === 'DATALIST' ){

            let allOptions = $(dependentList).find('option');

            // First hide all options
            allOptions.each((index, option) => {
                let currVal = $(option).val() as string;

                if( !StringUtils.isEmptyString(currVal)){
                    $(option).attr('data-value', currVal);
                    $(option).val('');
                }
            });

            // Now display only these from selected category and select first option
            allOptions.each((index, option) => {

                let categories       = JSON.parse($(option).attr('data-categories'));
                let selectedCategory = $(listFilterer).val() as string|number;

                if(!ArrayUtils.inArray(selectedCategory, categories)){
                    let dataValue = $(option).attr('data-value');
                    $(option).val(dataValue);

                    if( index === 0 ){
                        $(option).attr('selected', 'selected');
                    }
                }

            });

        }else{
            $(optgroups).addClass('d-none');
            $(optgroups).find('option').addClass('d-none').removeAttr('selected');

            let visibleOptgroup         = $(dependentList).find('optgroup[label^="' + $(listFilterer).val() + '"]');
            let visibleOptgroupOptions  = $(visibleOptgroup).find('option');
            let select                  = $(visibleOptgroup).closest('select');
            let form                    = $(select).closest('form');
            let input                   = $(form).find('input');
            let submit                  = $(form).find('button[type="submit"]');
            let noOptionsOption         = $('<option>').html('No folders!').attr('class','helper-option');

            if( !DomElements.doElementsExists($(visibleOptgroupOptions)) ){
                $(select).addClass('disabled');
                $(submit).addClass('disabled');
                $(input).addClass('disabled');

                $(select).append(noOptionsOption);
                noOptionsOption.attr('selected','selected');
            }else{
                $(select).removeClass('disabled');
                $(submit).removeClass('disabled');
                $(input).removeClass('disabled');

                let selectedHelperOption = $(select).find('.helper-option');
                $(selectedHelperOption).remove();
            }

            $(visibleOptgroup).removeClass('d-none');

            if( !DomElements.doElementsExists($(visibleOptgroup)) ){
                $(dependentList).val("");
            }

            $(visibleOptgroupOptions).each((index, option) => {
                $(option).removeClass('d-none');

                if( index === 0 ){
                    $(option).attr('selected', 'selected');
                }

            });

            //handle bootstrap-select
            if( BootstrapSelect.isSelectpicter(select) ){
                BootstrapSelect.refreshSelector(select);
            }

        }

    };

    public validateBetweenMinMax(element){
        let minVal = $(element).attr('min');
        let maxVal = $(element).attr('max');
        let val    = $(element).val();

        let isBelowMin = false;
        let isAboveMax = false;

        if( !StringUtils.isEmptyString(minVal) ){
            if( val < minVal ){
                isBelowMin = true;
            }
        }

        if( !StringUtils.isEmptyString(maxVal)){
            if( val > maxVal){
                isAboveMax = true;
            }
        }

        if( isAboveMax ){
            $(element).val(maxVal);
        }else if( isBelowMin ){
            $(element).val(minVal);
        }
    };

}