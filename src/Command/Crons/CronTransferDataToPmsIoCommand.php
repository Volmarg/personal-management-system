<?php

namespace App\Command\Crons;

use App\Controller\Core\Application;
use App\Controller\Modules\Notes\MyNotesCategoriesController;
use App\Controller\Modules\Notes\MyNotesController;
use App\Repository\Modules\Passwords\MyPasswordsGroupsRepository;
use App\Repository\Modules\Passwords\MyPasswordsRepository;
use App\Services\External\PmsIoService;
use App\Traits\ExceptionLoggerAwareTrait;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronTransferDataToPmsIoCommand extends Command
{
    use ExceptionLoggerAwareTrait;
    
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
        private readonly MyPasswordsGroupsRepository $passwordsGroupsRepository,
        private readonly MyPasswordsRepository $passwordsRepository,
        private readonly LoggerInterface $logger,
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
            $this->logger->info("Started transferring data to PMS-IO");
            {
                $isAllowedToInsertResponse = $this->pmsIoService->isAllowedToInsert();
                if( !$isAllowedToInsertResponse->isSuccess() ){
                    return Command::SUCCESS;
                }

                $this->insertNotesData();
                $this->insertPasswordsData();
                $this->pmsIoService->setTransferDoneState();
            }
            $this->logger->info("Finished transferring data to PMS-IO");

        }catch(Exception $e){
            $this->logException($e);
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
        $allNotDeletedPasswordsGroups = $this->passwordsGroupsRepository->findAllNotDeleted();
        $allNotDeletedPasswords       = $this->passwordsRepository->findAllNotDeleted();

        $insertPasswordsGroupsResponse = $this->pmsIoService->insertPasswordsGroups($allNotDeletedPasswordsGroups);
        if( !$insertPasswordsGroupsResponse->isSuccess() ){
            $this->logger->critical("Could not insert password groups to PMS-IO", [
                "reason" => $insertPasswordsGroupsResponse->getMessage(),
            ]);
            return false;
        }

        $insertPasswordsResponse = $this->pmsIoService->insertPasswords($allNotDeletedPasswords);
        if( !$insertPasswordsResponse->isSuccess() ){
            $this->logger->critical("Could not insert password to PMS-IO", [
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
            $this->logger->critical("Could not insert notes categories to PMS-IO", [
                "reason" => $insertNotesCategoriesResponse->getMessage(),
            ]);
            return false;
        }

        $insertNotesResponse = $this->pmsIoService->insertNotes($allNotDeletedNotes);
        if( !$insertNotesResponse->isSuccess() ){
            $this->logger->critical("Could not insert notes to PMS-IO", [
                "reason" => $insertNotesResponse->getMessage(),
            ]);
            return false;
        }

        return true;
    }

}
