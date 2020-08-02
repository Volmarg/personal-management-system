import DataProcessorDto from "../../../DTO/DataProcessorDto";

/**
 * @description Common interface that should be used for every named Processor of DataProcessor
 *              as it defines set of required methods, so that DataProcessorLoader can work properly
 */
export default interface DataProcessorInterface {
    makeUpdateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto|null;
    makeRemoveData($baseElement?: JQuery<HTMLElement>): DataProcessorDto|null;
    makeCreateData($baseElement?: JQuery<HTMLElement>): DataProcessorDto|null;
    makeCopyData($baseElement?: JQuery<HTMLElement>):  DataProcessorDto|null;
    processorName : string;
}