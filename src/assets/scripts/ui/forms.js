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


        }

    }

}());