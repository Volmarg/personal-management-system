import BackendStructureInterface from "./BackendStructureInterface";
import BootstrapSelect           from "../../../libs/bootstrap-select/BootstrapSelect";

/**
 * @description This class contains the methods and representations used to:
 *              - handle formView fetching from backed
 */
export default class FormStructure {

    public static contactTypeDto: BackendStructureInterface = {
        getCallback(): Function {
            let callback = function(){
                BootstrapSelect.init();
            }
            return callback;
        },
        getNamespace: function() :string{
            return 'App\\Form\\Modules\\Contacts\\MyContactTypeDtoType';
        }
    }
}