// Non ajax-based crud. Like create ui elements etc.

export default (function () {
    window.bootstrap_notifications = {
        types: {
          green  : "success",
          red    : "danger",
          orange : "warning"
        },
        notify: function (message, type) {
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
        },
        /**
         * Shows green popup box
         * @param message {string}
         */
        showGreenNotification: function (message) {
            this.notify(message, this.types.green);
        },
        /**
         * Shows red popup box
         * @param message {string}
         */
        showRedNotification: function (message){
            this.notify(message, this.types.red);
        },
        /**
         * Shows orange popup box
         * @param message {string}
         */
        showOrangeNotification: function (message) {
            this.notify(message, this.types.orange);
        }
    };

}());




