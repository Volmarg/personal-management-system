export default (function () {

    if (typeof window.ui === 'undefined') {
        window.ui = {};
    }

    ui.forms = {
        form_classes: {},
        selectors: {
          classes: {
              listFilterer: '.listFilterer'
          }
        },
        init: function () {
            this.filterDependentListOnChange();
            this.validateBetweenMinMax();
        },
        /**
         * This function depends on the ChoiceType optgroup, and filters by value of optgroup label
         */
        filterDependentListOnChange: function () {

            let listFilterers = $(this.selectors.classes.listFilterer);
            let _this         = this;

            if( $(listFilterers).length === 0 ){
                return;
            }

            $.each(listFilterers, (index, listFilterer) => {

                $(listFilterer).on('change', () => {
                    _this.filterDependentList(listFilterer);
                });

                _this.filterDependentList(listFilterer);

            });

        },
        filterDependentList: function(listFilterer) {
            let dependentListSelector = $(listFilterer).attr('data-dependent-list-selector');
            let dependentList         = $(dependentListSelector);
            let optgroups             = $(dependentList).find('optgroup');

            if( $(dependentList).prop('tagName') === 'DATALIST' ){

                let allOptions = $(dependentList).find('option');

                // First hide all options
                allOptions.each((index, option) => {
                    let currVal = $(option).val();

                    if( currVal !== ""){
                        $(option).attr('data-value', currVal);
                        $(option).val('');
                    }
                });

                // Now display only these from selected category and select first option
                allOptions.each((index, option) => {

                    let categories       = JSON.parse($(option).attr('data-categories'));
                    let selectedCategory = $(listFilterer).val();

                    if($.inArray(selectedCategory, categories) !== -1){
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
                let submit                  = $(form).find('button');
                let noOptionsOption         = $('<option>').html('No folders!').attr('class','helper-option');

                if( $(visibleOptgroupOptions).length === 0 ){
                    $(select).addClass('disabled');
                    $(submit).addClass('disabled');
                    $(input).addClass('disabled');

                    $(select).append(noOptionsOption);
                    noOptionsOption.attr('selected','selected');
                    return;
                }else{
                    $(select).removeClass('disabled');
                    $(submit).removeClass('disabled');
                    $(input).removeClass('disabled');

                    let selectedHelperOption = $(select).find('.helper-option');
                    $(selectedHelperOption).remove();
                }

                $(visibleOptgroup).removeClass('d-none');

                if( $(visibleOptgroup).length === 0 ){
                    $(dependentList).val("");
                }

                $(visibleOptgroupOptions).each((index, option) => {
                    $(option).removeClass('d-none');

                    if( index === 0 ){
                        $(option).attr('selected', 'selected');
                    }

                });

            }


        },
        validateBetweenMinMax: function(element){
            let minVal = $(element).attr('min');
            let maxVal = $(element).attr('max');
            let val    = $(element).val();

            let isBelowMin = false;
            let isAboveMax = false;

            if( "undefined" !== typeof minVal ){
                if( val < minVal ){
                    isBelowMin = true;
                }
            }

            if( "undefined" !== typeof maxVal ){
                if( val > maxVal){
                    isAboveMax = true;
                }
            }

            if( isAboveMax ){
                $(element).val(maxVal);
            }else if( isBelowMin ){
                $(element).val(minVal);
            }
        },
    }

}());