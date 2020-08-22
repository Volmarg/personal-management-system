import * as $ from 'jquery';

/**
 * @description this method is required in twig for handling flashbag messages
 *
 * @param message
 * @param type
 */
//@ts-ignore
window.legacy_notify = function(message, type){
    //@ts-ignore
    $.notify({
        message: message
    }, {
        position: null,
        type: type,
        placement: {
            from: "top",
            align: "center"
        },
    });
};

/**
 * @description Handles showing small popups with texts
 *
 * @link http://bootstrap-notify.remabledesigns.com/
 */
export default class BootstrapNotify{

    private types = {
        green  : "success",
        red    : "danger",
        orange : "warning",
        blue   : "info"
    };

    static readonly MESSAGE_TYPE_SUCCESS = "success";

    static readonly MESSAGE_TYPE_DANGER = "danger";

    /**
     * @description Shows green popup box
     *
     * @param message {string}
     * @param delay   {number}
     * @param showProgressbar {boolean}
     */
    public showGreenNotification(message, delay:number = null, showProgressbar:boolean = false) {
        this.notify(message, this.types.green, delay, showProgressbar);
    };

    /**
     * @description Shows red popup box
     *
     * @param message {string}
     * @param delay   {number}
     * @param showProgressbar {boolean}
     */
    public showRedNotification (message, delay:number = null, showProgressbar:boolean = false){
        this.notify(message, this.types.red, delay, showProgressbar);
    };

    /**
     * @description Shows orange popup box
     *
     * @param message {string}
     * @param delay   {number}
     * @param showProgressbar {boolean}*
     */
    public showOrangeNotification (message, delay:number = null, showProgressbar:boolean = false) {
        this.notify(message, this.types.orange, delay, showProgressbar);
    };

    /**
     * @description Shows orange popup box
     *
     * @param message {string}
     * @param delay   {number}
     * @param showProgressbar {boolean}
     */
    public showBlueNotification(message, delay:number = null, showProgressbar:boolean = false) {
        this.notify(message, this.types.blue, delay, showProgressbar);
    }

    /**
     * @description Main method with notifications configuration
     *
     * @param message
     * @param type
     * @param delay
     * @param showProgressbar
     */
    public notify(message, type, delay:number = null, showProgressbar:boolean = false) {
        let notify_config = {
            position: null,
            type: type,
            placement: {
                from: "top",
                align: "center"
            },
            showProgressbar: showProgressbar
        };

        if( null !== delay ){
            $.extend(notify_config, {
                delay: delay
            })
        }

        //@ts-ignore
        $.notify({
            message: message
        }, notify_config);
    };

}