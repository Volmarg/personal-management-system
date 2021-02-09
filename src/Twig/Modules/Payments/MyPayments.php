<?php

namespace App\Twig\Modules\Payments;

use App\Controller\Core\Application;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MyPayments extends AbstractExtension {

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function getFunctions() {
        return [
            new TwigFunction('calculateHomeProductPrice', [$this, 'calculateHomeProductPrice']),
        ];
    }

    public function calculateHomeProductPrice($productPrice, $currencyMultiplier) {
        if (preg_match('/^[-+]?[0-9]*\\.?[0-9]+$/', $productPrice)) {
            return $productPrice * $currencyMultiplier;
        }
        return 'Incorrect product price';
    }

}