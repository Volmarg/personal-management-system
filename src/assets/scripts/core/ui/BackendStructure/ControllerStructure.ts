import BackendStructureInterface from "./BackendStructureInterface";

/**
 * @description This class contains the controllers backend representations
 */
export default class ControllerStructure {

    static readonly CONST_MODULE_NAME_FILES  = 'MODULE_NAME_FILES';
    static readonly CONST_MODULE_NAME_IMAGES = 'MODULE_NAME_IMAGES';

    public static ModulesController: BackendStructureInterface = {
        getCallback(): Function {
            return ()=>{};
        },
        getNamespace: function() :string{
            return '\\App\\Controller\\Modules\\ModulesController';
        }
    };

}