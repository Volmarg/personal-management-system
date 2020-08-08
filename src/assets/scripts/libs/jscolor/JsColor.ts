var jscolor = require("./src/jscolor.js");

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
                if( !element.hasPicker ){
                    let color   = element.getAttribute(this.attributes.data.color);
                    new jscolor(element, {'value': color});
                    // @ts-ignore
                    element.hasPicker = true;
                }

            });
        }
    }
}