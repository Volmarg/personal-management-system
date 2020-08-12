// Circles visible for example by payment goals
var ldBar = require('../../../../../node_modules/@loadingio/loading-bar/lib/loading-bar');
// if won't work then try with include from ./src
import * as $ from 'jquery';

export default class LoadingBar {

    /**
     * @type Object
     */
    private classes = {
        'loading-bar': 'ldBar',
    };

    public init() {
        $('.' + this.classes["loading-bar"]).each((index, element) => {
            this.calculateDataValue(element);
            this.renderLoadingBar(element);
        });
    };

    private calculateDataValue(element) {
        let poolProgress = parseFloat($(element).find('.pool-progress').html());
        let poolTarget   = parseFloat($(element).find('.pool-target').html());

        let dataValue =  poolProgress / poolTarget  * 100;
        $(element).attr('data-value', dataValue.toPrecision(2));
    };

    private renderLoadingBar(element){
        // might not work
        new ldBar(element, {});
    }

}