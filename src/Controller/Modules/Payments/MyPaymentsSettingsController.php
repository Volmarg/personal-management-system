<?php

namespace App\Controller\Modules\Payments;

use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyPaymentsSettings;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class MyPaymentsSettingsController extends AbstractController {

    private $em;
    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app, EntityManagerInterface $em) {
        $this->em   = $em;
        $this->app  = $app;
    }

    /**
     * @return float|null
     */
    public function fetchCurrencyMultiplier(): ?float
    {
        $multiplier = $this->app->repositories->myPaymentsSettingsRepository->fetchCurrencyMultiplier();
        if (is_null($multiplier)) {
            return null;
        }

        return (float)$multiplier;
    }

    /**
     * @return array
     */
    public function fetchCurrencyMultiplierRecord(): array
    {
        return $this->app->repositories->myPaymentsSettingsRepository->fetchCurrencyMultiplierRecord();
    }

    /**
     * @return array
     */
    public function getAllPaymentsTypes(): array
    {
        return $this->app->repositories->myPaymentsSettingsRepository->getAllPaymentsTypes();
    }

    /**
     * Will return array of years for payments
     *
     * @return string[]
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getYears(): array
    {
        return $this->app->repositories->myPaymentsSettingsRepository->getYears();
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param int $id
     * @return MyPaymentsSettings|null
     */
    public function findOneById(int $id): ?MyPaymentsSettings
    {
        return $this->app->repositories->myPaymentsSettingsRepository->findOneById($id);
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param string $value
     * @return MyPaymentsSettings | null
     */
    public function findOneByValue(string $value): ?MyPaymentsSettings
    {
        return $this->app->repositories->myPaymentsSettingsRepository->findOneByValue($value);
    }

    private function updateCurrencyMultiplierRecord($formData) {
        $ormRecord = $this->app->repositories->myPaymentsSettingsRepository->fetchCurrencyMultiplierRecord()[0];
        $ormRecord->setValue($formData->getValue());
        $this->em->persist($ormRecord);
        $this->em->flush();
    }

    private function createRecord($recordData) {
        $this->em->persist($recordData);
        $this->em->flush();
    }
}
