require("./src/2.3.3/jscolor.js");

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

    public static init() {
        //@ts-ignore
        window.JSColor.install();
    };

}