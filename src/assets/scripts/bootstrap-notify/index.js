// Non ajax-based crud. Like create ui elements etc.

export default (function () {
    window.bootstrap_notifications = {
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
        }
    };

}());




