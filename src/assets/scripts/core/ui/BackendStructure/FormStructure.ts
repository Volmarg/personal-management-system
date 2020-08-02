import BackendStructureInterface from "./BackendStructureInterface";

/**
 * @description This class contains the methods and representations used to:
 *              - handle formView fetching from backed
 */
export default class FormStructure {

    public static contactTypeDto: BackendStructureInterface = {
        getNamespace: function() :string{
            return 'App\\Form\\Modules\\Contacts\\MyContactTypeDtoType';
        }
    }
}