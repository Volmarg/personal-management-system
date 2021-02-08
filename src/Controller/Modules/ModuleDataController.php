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
     * @param string $recordType
     * @param string $module
     * @param string $recordIdentifier
     * @return ModuleData|null
     */
    public function getOneByRecordTypeModuleAndRecordIdentifier(string $recordType, string $module, string $recordIdentifier): ?ModuleData
    {
        return $this->app->repositories->moduleDataRepository->getOneByRecordTypeModuleAndRecordIdentifier($recordType, $module, $recordIdentifier);
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
     * @param ModuleData $moduleData
     * @param string $newIdentifier
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateRecordIdentifier(ModuleData $moduleData, string $newIdentifier): void
    {
        $moduleData->setRecordIdentifier($newIdentifier);
        $this->app->repositories->moduleDataRepository->saveEntity($moduleData);;
    }
}