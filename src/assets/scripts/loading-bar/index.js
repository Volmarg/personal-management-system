// Non ajax-based crud. Like create ui elements etc.

export default (function () {
    window.loading_bar = {
        classes: {
            'loading-bar': 'ldBar',
        },
        init: function () {
            $('.' + this.classes["loading-bar"]).each((index, element) => {
                this.calculateDataValue(element);
                this.renderLoadingBar(element);
            });
        },
        calculateDataValue: function (element) {
            let data_value = $(element).find('.pool-progress').html() / $(element).find('.pool-target').html() * 100;
            $(element).attr('data-value', data_value.toPrecision(2));
        },
        renderLoadingBar: function(element){
            window.ldBar(element);
        }
    };

}());




