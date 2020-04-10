<?php

namespace App\Controller\Modules\Payments;

use App\Controller\Core\Application;
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
