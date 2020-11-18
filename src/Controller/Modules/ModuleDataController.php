<?php


namespace App\Controller\Modules;

use App\Controller\Core\Application;
use App\Entity\Modules\ModuleData;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * This class contains action related to the @see ModuleData
 *
 * Class ModuleDataAction
 * @package App\Controller\Modules
 */
class ModuleDataController
{

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * ModuleDataController constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Will return single module data for given parameters, or null if nothing is found
     *
     * @param string $record_type
     * @param string $module
     * @param string $record_identifier
     * @return ModuleData|null
     */
    public function getOneByRecordTypeModuleAndRecordIdentifier(string $record_type, string $module, string $record_identifier): ?ModuleData
    {
        return $this->app->repositories->moduleDataRepository->getOneByRecordTypeModuleAndRecordIdentifier($record_type, $module, $record_identifier);
    }

    /**
     * Will return one entity for given id, if such does not exist then null will be returned
     *
     * @param int $id
     * @return ModuleData|null
     */
    public function findOneById(int $id): ?ModuleData
    {
        return $this->app->repositories->moduleDataRepository->findOneById($id);
    }

    /**
     * Provided `new identifier` parameter will be used to update the existing ModuleData `identifier`
     *
     * @param ModuleData $module_data
     * @param string $new_identifier
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateRecordIdentifier(ModuleData $module_data, string $new_identifier): void
    {
        $module_data->setRecordIdentifier($new_identifier);
        $this->app->repositories->moduleDataRepository->saveEntity($module_data);;
    }
}