<?php

namespace App\Command\Crons;

use App\Controller\Core\Application;
use App\Controller\Modules\Notes\MyNotesCategoriesController;
use App\Controller\Modules\Notes\MyNotesController;
use App\Controller\Modules\Passwords\MyPasswordsController;
use App\Controller\Modules\Passwords\MyPasswordsGroupsController;
use App\Services\External\PmsIoService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronTransferDataToPmsIoCommand extends Command
{
    protected static $defaultName = 'cron:transfer-data-to-pms-io';

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var PmsIoService $pmsIoService
     */
    private PmsIoService $pmsIoService;

    public function __construct(
        Application                                  $app,
        PmsIoService                                 $pmsIoService,
        private readonly MyNotesCategoriesController $notesCategoriesController,
        private readonly MyNotesController           $notesController,
        private readonly MyPasswordsGroupsController $passwordsGroupsController,
        private readonly MyPasswordsController       $passwordsController,
    )
    {
        parent::__construct(self::$defaultName);

        $this->app          = $app;
        $this->pmsIoService = $pmsIoService;
    }


    protected function configure()
    {
        $this
            ->setDescription('Will transfer data from PMS to PMS-IO')
            ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try{
            $this->app->logger->info("Started transferring data to PMS-IO");
            {
                $isAllowedToInsertResponse = $this->pmsIoService->isAllowedToInsert();
                if( !$isAllowedToInsertResponse->isSuccess() ){
                    return Command::SUCCESS;
                }

                $this->insertNotesData();
                $this->insertPasswordsData();
                $this->pmsIoService->setTransferDoneState();
            }
            $this->app->logger->info("Finished transferring data to PMS-IO");

        }catch(Exception $e){
            $this->app->logExceptionWasThrown($e);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Will insert passwords related data to PMS-IO
     * Returned true means that everything was transferred, false -> some error happened.
     *
     * @return bool
     * @throws GuzzleException
     */
    private function insertPasswordsData(): bool
    {
        $allNotDeletedPasswordsGroups = $this->passwordsGroupsController->findAllNotDeleted();
        $allNotDeletedPasswords       = $this->passwordsController->findAllNotDeleted();

        $insertPasswordsGroupsResponse = $this->pmsIoService->insertPasswordsGroups($allNotDeletedPasswordsGroups);
        if( !$insertPasswordsGroupsResponse->isSuccess() ){
            $this->app->logger->critical("Could not insert password groups to PMS-IO", [
                "reason" => $insertPasswordsGroupsResponse->getMessage(),
            ]);
            return false;
        }

        $insertPasswordsResponse = $this->pmsIoService->insertPasswords($allNotDeletedPasswords);
        if( !$insertPasswordsResponse->isSuccess() ){
            $this->app->logger->critical("Could not insert password to PMS-IO", [
                "reason" => $insertPasswordsResponse->getMessage(),
            ]);
            return false;
        }

        return true;
    }

    /**
     * Will insert notes related data to PMS-IO
     * Returned true means that everything was transferred, false -> some error happened.
     *
     * @return bool
     * @throws GuzzleException
     */
    private function insertNotesData(): bool
    {
        $allNotDeletedNotesCategories = $this->notesCategoriesController->findAllNotDeleted();
        $allNotDeletedNotes           = $this->notesController->findAllNotDeleted();

        /**
         * The order here is important as the categories must for example exist before adding notes to them
         */
        $insertNotesCategoriesResponse = $this->pmsIoService->insertNotesCategories($allNotDeletedNotesCategories);
        if( !$insertNotesCategoriesResponse->isSuccess() ){
            $this->app->logger->critical("Could not insert notes categories to PMS-IO", [
                "reason" => $insertNotesCategoriesResponse->getMessage(),
            ]);
            return false;
        }

        $insertNotesResponse = $this->pmsIoService->insertNotes($allNotDeletedNotes);
        if( !$insertNotesResponse->isSuccess() ){
            $this->app->logger->critical("Could not insert notes to PMS-IO", [
                "reason" => $insertNotesResponse->getMessage(),
            ]);
            return false;
        }

        return true;
    }

}
