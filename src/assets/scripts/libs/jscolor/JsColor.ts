var jsc = require("./src/jscolor.js");

export default class JsColor {

    /**
     * @type Object
     */
    private selectors = {
        classes: {
            colorPicker: ".color-picker"
        }
    };

    /**
     * @type Object
     */
    private attributes = {
        data: {
            color: "data-color"
        }
    };

    public init() {
        this.initForSelector();
    };

    private initForSelector() {

        let allPickers = document.querySelectorAll(this.selectors.classes.colorPicker);

        if (allPickers.length !== 0) {

            allPickers.forEach((element, index) => {

                // @ts-ignore
                if( !element.hasPicker ){  // prevent stacking the jscolor
                    let color = element.getAttribute(this.attributes.data.color);

                    // @ts-ignore
                    window.jscolor.instance(element, {'value': color});
                    // @ts-ignore
                    element.hasPicker = true;
                }

            });
        }
    }
}