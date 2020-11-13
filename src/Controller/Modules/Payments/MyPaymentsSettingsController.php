<?php

namespace App\Controller\Modules\Payments;

use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyPaymentsSettings;
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
        $currency_multiplier_form = $this->app->forms->currencyMultiplierForm();
        $currency_multiplier_form->handleRequest($request);

        if ($currency_multiplier_form->isSubmitted() && $currency_multiplier_form->isValid()) {
            $form_data           = $currency_multiplier_form->getData();
            $currency_multiplier = $this->app->repositories->myPaymentsSettingsRepository->fetchCurrencyMultiplier();

            if ($currency_multiplier) {
                $this->updateCurrencyMultiplierRecord($form_data);
                return;
            }
            $this->createRecord($form_data);
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

    private function updateCurrencyMultiplierRecord($form_data) {
        $orm_record = $this->app->repositories->myPaymentsSettingsRepository->fetchCurrencyMultiplierRecord()[0];
        $orm_record->setValue($form_data->getValue());
        $this->em->persist($orm_record);
        $this->em->flush();
    }

    private function createRecord($record_data) {
        $this->em->persist($record_data);
        $this->em->flush();
    }
}
