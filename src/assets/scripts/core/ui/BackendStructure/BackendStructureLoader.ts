import BackendStructureInterface    from "./BackendStructureInterface";
import FormStructure                from "./FormStructure";

/**
 * @description This class is responsible for fetching dto from given type of BackendStructure (Form/Module/Controller etc)
 *              Getters will attempt to return dto with data for given BackendStructure type/name.
 *
 *              If type for BackendStructure is found then dto is returned by getters
 */
export default class BackendStructureLoader {

    public static readonly STRUCTURE_TYPE_FORM = "form";

    /**
     * @description Will fetch BackendStructureInterface for given structureName/type
     *
     * @param structureName
     * @param structureType
     */
    public static getNamespace(structureType: string, structureName: string): BackendStructureInterface|null
    {
        try{
            let structureClass   = BackendStructureLoader.decideStructureClass(structureType);
            let backendStructure = <BackendStructureInterface>structureClass[structureName];

            return backendStructure;
        }catch(Exception){
            throw {
                "message"       : "Could not obtain dataStructure",
                "exception"     : Exception,
                "structureType" : structureType,
                "structureName" : structureName
            }
        }
    }

    /**
     * @description will decide which structureType should be used for fetchingData
     *
     * @param structureType
     */
    private static decideStructureClass(structureType: string)
    {
        switch(structureType)
        {
            case BackendStructureLoader.STRUCTURE_TYPE_FORM:
            {
                return FormStructure;
            }

            default:
            {
                throw{
                    "message"       : "Not supported backendStructure",
                    "processorType" : structureType
                }
            }
        }
    }

}