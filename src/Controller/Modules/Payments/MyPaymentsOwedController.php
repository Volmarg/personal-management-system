<?php

namespace App\Controller\Modules\Payments;

use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyPaymentsOwed;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyPaymentsOwedController extends AbstractController
{

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }

    /**
     * This only gets summary SUM(), not fetching any detailed data.
     * @param bool $owedByMe
     * @return mixed[]
     * @throws DBALException
     */
    public function getMoneyOwedSummaryForTargetsAndOwningSide(bool $owedByMe)
    {
        return $this->app->repositories->myPaymentsOwedRepository->getMoneyOwedSummaryForTargetsAndOwningSide($owedByMe);
    }

    /**
     * Returns total summary how much I owed to someone or someone to me
     * @return array
     * @throws DBALException
     */
    public function fetchSummaryWhoOwesHowMuch(): array
    {
        return $this->app->repositories->myPaymentsOwedRepository->fetchSummaryWhoOwesHowMuch();
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param int $id
     * @return MyPaymentsOwed|null
     */
    public function findOneById(int $id): ?MyPaymentsOwed
    {
        return $this->app->repositories->myPaymentsOwedRepository->findOneById($id);
    }

    /**
     * Will return all not deleted entities but filtered by the owed column
     *
     * @param bool $owedByMe
     * @return array
     */
    public function findAllNotDeletedFilteredByOwedStatus(bool $owedByMe): array
    {
        return $this->app->repositories->myPaymentsOwedRepository->findAllNotDeletedFilteredByOwedStatus($owedByMe);
    }

}
