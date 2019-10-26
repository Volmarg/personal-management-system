export default (function () {

    if (typeof utils === 'undefined') {
        window.utils = {}
    }

    utils.json = {

    };

    utils.array = {
        /**
         * For standard array with values
         * @param array
         * @returns {any[]}
         */
        unique: function (array) {
            let uniqueItems = Array.from(new Set(array));
            return uniqueItems;
        }
    };

    utils.window = {
        redirect: function (url, message) {

            bootstrap_notifications.notify(message, 'warning');

            setTimeout(function () {
                window.location = url;
            }, 3000)

        }
    };

    utils.validations = {

        isTrue: function($stringBoolean){
            return ( $stringBoolean === 'true');
        },
        isFalse: function($stringBoolean){
            return ( $stringBoolean === 'false');
        }

    };

    utils.ui = {
        keepUploadBasedMenuOpen: function(){
            let openedMenu       = $('.folder-based-menu .open');
            let openedMenuParent = openedMenu;

            while( $(openedMenuParent).hasClass('folder-based-menu-element') ){
                openedMenuParent = $(openedMenuParent).parent();
                $(openedMenuParent).addClass('open');
                $(openedMenuParent).css({
                    "display": "block"
                });
            }
        }
    }

}());