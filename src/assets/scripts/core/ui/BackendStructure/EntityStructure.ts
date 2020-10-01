import BackendStructureInterface from "./BackendStructureInterface";

/**
 * @description This class contains the entities backend representations
 */
export default class EntityStructure {

    public static MyScheduleType: BackendStructureInterface = {
        getNamespace: function() :string{
            return 'App\\Entity\\Modules\\Schedules\\MyScheduleType';
        }
    };

    public static MyPaymentsSettings: BackendStructureInterface = {
        getNamespace: function() :string{
            return 'App\\Entity\\Modules\\Payments\\MyPaymentsSettings';
        }
    };

    public static MyPasswordsGroups: BackendStructureInterface = {
        getNamespace: function() :string{
            return 'App\\Entity\\Modules\\Passwords\\MyPasswordsGroups';
        }
    };

    public static MyGoals: BackendStructureInterface = {
        getNamespace: function() :string{
            return 'App\\Entity\\Modules\\Goals\\MyGoals';
        }
    };

    public static MyTodo: BackendStructureInterface = {
        getNamespace: function() :string{
            return 'App\\Entity\\Modules\\Todo\\MyTodo';
        }
    };

}