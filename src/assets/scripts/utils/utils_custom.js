import tinymce from "tinymce";

export default (function () {

    if (typeof utils === 'undefined') {
        window.utils = {}
    }

    utils.atrributes = {
        toggleRequired: function (element) {
            let form_element = $(element).find('input');

            if ($(form_element).length === 0) {
                form_element = $(element).find('select');
            }

            if ($(form_element).attr('required') === 'required') {
                $(form_element).removeAttr('required');
            } else {
                $(form_element).attr({'required': 'required'})
            }
        },
    };

    utils.json = {

    };

    utils.window = {
        redirect: function (url, message) {

            bootstrap_notifications.notify(message, 'warning');

            setTimeout(function () {
                window.location = url;
            }, 3000)

        }
    };

}());