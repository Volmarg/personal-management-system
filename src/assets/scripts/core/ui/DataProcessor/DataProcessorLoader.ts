import DataProcessorDto         from "../../../DTO/DataProcessorDto";
import Entity                   from "./Entity";
import DataProcessorInterface   from "./DataProcessorInterface";
import SpecialAction            from "./SpecialAction";

/**
 * @description This class is responsible for fetching dto from given type of dataProcessor (Entity/SingleForm etc)
 *              Getters will attempt to return dto with data for given processor type/name.
 *
 *              If type for processor is found then dto is returned by getters
 */
export default class DataProcessorLoader {

    public static readonly PROCESSOR_TYPE_ENTITY         = "entity";
    public static readonly PROCESSOR_TYPE_SPECIAL_ACTION = "specialAction";

    /**
     * @description Will fetch DataProcessorDto for update Data, for given processorType/name and/or baseElement
     * @see SpecialAction.settingsDashboardWidgetsVisibility
     * @see SpecialAction.settingsFinancesCurrencyTable
     * @see SpecialAction.CopyDataBetweenFolders
     * @see SpecialAction.UserLockPassword
     * @see SpecialAction.UserPassword
     * @see SpecialAction.UserNickname
     * @see SpecialAction.UserAvatar
     *
     * @param processorName
     * @param processorType
     * @param $baseElement
     */
    public static getUpdateDataProcessorDto(processorType: string, processorName: string, $baseElement?: JQuery<HTMLElement>): DataProcessorDto|null
    {
        try{
            let processorClass = DataProcessorLoader.decideProcessorClass(processorType);
            let dataProcessor  = <DataProcessorInterface>processorClass[processorName];

            if( "undefined" === typeof dataProcessor ){
                return null;
            }

            let dto = dataProcessor.makeUpdateData($baseElement);

            return dto;
        }catch(Exception){
            throw {
                "message"       : "Could not obtain dataProcessor",
                "exception"     : Exception,
                "processorName" : processorName,
                "processorType" : processorType,
                "baseElement"   : $baseElement
            }
        }
    }

    /**
     * @description Will fetch DataProcessorDto for create Data/Record, for given processorType/name and/or baseElement
     * @see SpecialAction.CreateFolder
     *
     * @param processorName
     * @param processorType
     * @param $baseElement
     */
    public static getCreateDataProcessorDto(processorType: string, processorName: string, $baseElement?: JQuery<HTMLElement>): DataProcessorDto|null
    {
        try{
            let processorClass = DataProcessorLoader.decideProcessorClass(processorType);
            let dataProcessor  = <DataProcessorInterface>processorClass[processorName];

            if( "undefined" === typeof dataProcessor ){
                return null;
            }

            let dto = dataProcessor.makeCreateData($baseElement);

            return dto;
        }catch(Exception){
            throw {
                "message"       : "Could not obtain dataProcessor",
                "exception"     : Exception,
                "processorName" : processorName,
                "processorType" : processorType,
                "baseElement"   : $baseElement
            }
        }
    }

    /**
     * @description Will fetch DataProcessorDto for remove Data/Record, for given processorType/name and/or baseElement
     *
     * @param processorName
     * @param processorType
     * @param $baseElement
     */
    public static getRemoveDataProcessorDto(processorType: string, processorName: string, $baseElement?: JQuery<HTMLElement>): DataProcessorDto|null
    {
        try{
            let processorClass = DataProcessorLoader.decideProcessorClass(processorType);
            let dataProcessor  = <DataProcessorInterface>processorClass[processorName];

            if( "undefined" === typeof dataProcessor ){
                return null;
            }

            let dto = dataProcessor.makeRemoveData($baseElement);

            return dto;
        }catch(Exception){
            throw {
                "message"       : "Could not obtain dataProcessor",
                "exception"     : Exception,
                "processorName" : processorName,
                "processorType" : processorType,
                "baseElement"   : $baseElement
            }
        }
    }

    /**
     * @description Will fetch DataProcessorDto for copying data into clipboard for given processorType/name and/or baseElement
     *
     * @param processorName
     * @param processorType
     * @param $baseElement
     */
    public static getCopyDataProcessorDto(processorType: string, processorName: string, $baseElement?: JQuery<HTMLElement>): DataProcessorDto|null
    {
        try{
            let processorClass = DataProcessorLoader.decideProcessorClass(processorType);
            let dataProcessor  = <DataProcessorInterface>processorClass[processorName];

            if( "undefined" === typeof dataProcessor ){
                return null;
            }

            let dto = dataProcessor.makeCopyData($baseElement);

            return dto;
        }catch(Exception){
            throw {
                "message"       : "Could not obtain dataProcessor",
                "exception"     : Exception,
                "processorName" : processorName,
                "processorType" : processorType,
                "baseElement"   : $baseElement
            }
        }
    }

    /**
     * @description will decide which dataProcessor should be used for fetchingData
     *
     * @param processorType
     */
    private static decideProcessorClass(processorType: string) // make returned type Entities/Targets/Forms
    {
        switch(processorType)
        {
            case DataProcessorLoader.PROCESSOR_TYPE_ENTITY:
            {
                return Entity;
            }

            case DataProcessorLoader.PROCESSOR_TYPE_SPECIAL_ACTION:
            {
                return SpecialAction;
            }

            default:
            {
                throw{
                    "message"       : "Not supported dataProcessor",
                    "processorType" : processorType
                }
            }
        }
    }

}