/**
 *  @description this class contains common logic for DataProcessors
 */
export default abstract class AbstractDataProcessor {

    protected static messages = {
        entityUpdateSuccess (entityName) {
            return entityName + " record has been successfully updated";
        },
        entityUpdateFail: function (entityName) {
            return "Something went wrong while updating " + entityName + " record";
        },
        entityRemoveSuccess: function (entityName) {
            return entityName + " record was successfully removed";
        },
        entityCreatedRecordSuccess: function (entityName) {
            return "New " + entityName + ' record has been created';
        },
        entityCreatedRecordFail: function (entityName) {
            return "There was a problem while creating " + entityName + ' record';
        },
        entityRemoveFail: function (entityName) {
            return "Something went wrong while removing " + entityName + " record";
        },
        default_copy_data_confirmation_message  : 'Data was copied successfully',
        default_copy_data_fail_message          : 'There was some problem while copying the data',
        password_copy_confirmation_message      : 'Password was copied successfully',
    };
    
    /**
     * @type Object
     */
    protected selectors = {
        dataProcessorUrl : ".dataProcessorUrl"
    };
}