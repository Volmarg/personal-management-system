<?php

namespace App\Controller\Modules\Payments;

use App\Repository\Modules\Payments\MyPaymentsSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyPaymentsSettingsController extends AbstractController {

    public function __construct(
        private readonly MyPaymentsSettingsRepository $paymentsSettingsRepository
    ) {
    }

    /**
     * @return float|null
     */
    public function fetchCurrencyMultiplier(): ?float
    {
        $multiplier = $this->paymentsSettingsRepository->fetchCurrencyMultiplier();
        if (is_null($multiplier)) {
            return null;
        }

        return (float)$multiplier;
    }
}
