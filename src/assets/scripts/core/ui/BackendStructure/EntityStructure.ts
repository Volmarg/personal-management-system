import BackendStructureInterface from "./BackendStructureInterface";

/**
 * @description This class contains the entities backend representations
 */
export default class EntityStructure {

    public static MyScheduleType: BackendStructureInterface = {
        getCallback(): Function {
            return ()=>{};
        },
        getNamespace: function() :string{
            return 'App\\Entity\\Modules\\Schedules\\MyScheduleType';
        }
    };

    public static MyPaymentsSettings: BackendStructureInterface = {
        getCallback(): Function {
            return ()=>{};
        },
        getNamespace: function() :string{
            return 'App\\Entity\\Modules\\Payments\\MyPaymentsSettings';
        }
    };

    public static MyPasswordsGroups: BackendStructureInterface = {
        getCallback(): Function {
            return ()=>{};
        },
        getNamespace: function() :string{
            return 'App\\Entity\\Modules\\Passwords\\MyPasswordsGroups';
        }
    };

    public static MyTodo: BackendStructureInterface = {
        getCallback(): Function {
            return ()=>{};
        },
        getNamespace: function() :string{
            return 'App\\Entity\\Modules\\Todo\\MyTodo';
        }
    };

    public static MyIssue: BackendStructureInterface = {
        getCallback(): Function {
            return ()=>{};
        },
        getNamespace: function() :string{
            return 'App\\Entity\\Modules\\Issues\\MyIssue';
        }
    };

}