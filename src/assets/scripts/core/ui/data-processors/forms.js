/**
 * @info: This class contains the methods and representations used to:
 *  - handle formView fetching from backed
 */

export default (function () {

    if (typeof dataProcessors === 'undefined') {
        window.dataProcessors = {}
    }

    dataProcessors.forms = {
        "contactTypeDto": {
            getFormNamespace:function(){
                return 'App\\Form\\Modules\\Contacts\\MyContactTypeDtoType';
            }
        }
    };

}());