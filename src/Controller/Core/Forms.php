<?php
namespace App\Controller\Core;

use App\Form\Modules\Payments\MyPaymentsSettingsCurrencyMultiplierType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;

class Forms extends AbstractController {

    public function currencyMultiplierForm(array $params = []): FormInterface {
        return $this->createForm(MyPaymentsSettingsCurrencyMultiplierType::class, null, $params);
    }
}