/**
 * @description Common interface that should be used for every named BackendStructure
 *              as it defines set of required methods, so that BackendStructureLoader can work properly
 */
export default interface BackendStructureInterface {
    getNamespace(): string;
    getCallback(): Function;
}