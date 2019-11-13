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
        },
        /**
         * This function will return true if needle is in haystack
         * @param needle
         * @param haystack
         */
        inArray: function(needle, haystack){
            let isInArray = false;

            $.each(haystack, (index, value) => {
                if( needle === value ){
                    isInArray = true;
                    return false;
                }
            });

            return isInArray;
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

    utils.validations = { //crashes here - why ? todo:

        /**
         * Checks if the provided string is "true"
         * @param $stringBoolean
         * @returns {boolean}
         */
        isTrue: function($stringBoolean){
            return ( $stringBoolean === 'true');
        },
        /**
         * Checks if the provided string is "false"
         * @param $stringBoolean
         * @returns {boolean}
         */
        isFalse: function($stringBoolean){
            return ( $stringBoolean === 'false');
        },
        /**
         * Checks if there are existing elements for domElements selected with $();
         * @param elements
         * @returns {boolean}
         */
        doElementsExists: function(elements){
            return 0 !== $(elements).length;
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