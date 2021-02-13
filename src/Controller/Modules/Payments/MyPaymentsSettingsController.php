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
     * All this methods below were made the... wrong way. This should be changed at one point... somewhere... in future
     * @param Request $request
     */
    public function insertOrUpdateRecord(Request $request) {
        $currencyMultiplierForm = $this->app->forms->currencyMultiplierForm();
        $currencyMultiplierForm->handleRequest($request);

        if ($currencyMultiplierForm->isSubmitted() && $currencyMultiplierForm->isValid()) {
            $formData           = $currencyMultiplierForm->getData();
            $currencyMultiplier = $this->app->repositories->myPaymentsSettingsRepository->fetchCurrencyMultiplier();

            if ($currencyMultiplier) {
                $this->updateCurrencyMultiplierRecord($formData);
                return;
            }
            $this->createRecord($formData);
        }
    }

    /**
     * @return string|null
     */
    public function fetchCurrencyMultiplier(): ?string
    {
        return $this->app->repositories->myPaymentsSettingsRepository->fetchCurrencyMultiplier();
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
